<?php

namespace App\Services;

use App\Models\Article;
use App\Models\NewsSource;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NewsAggregatorService
{
    public function fetchLatestNews(array $sourceSlugs = [], int $hoursBack = 24): Collection
    {
        $sources = $this->getActiveSources($sourceSlugs);
        $articles = collect();
        
        foreach ($sources as $source) {
            try {
                $feedArticles = $this->fetchFromRssFeed($source, $hoursBack);
                $articles = $articles->merge($feedArticles);
                
                $source->update(['last_scraped_at' => now()]);
                
            } catch (\Exception $e) {
                Log::error("Failed to fetch from {$source->name}: " . $e->getMessage());
            }
        }
        
        return $articles;
    }
    
    public function fetchFromSource(NewsSource $source, int $hoursBack = 24): Collection
    {
        try {
            return $this->fetchFromRssFeed($source, $hoursBack);
        } catch (\Exception $e) {
            Log::error("Failed to fetch from {$source->name}: " . $e->getMessage());
            return collect();
        }
    }
    
    private function getActiveSources(array $sourceSlugs): Collection
    {
        $query = NewsSource::where('is_active', true);
            
        if (!empty($sourceSlugs)) {
            $query->whereIn('slug', $sourceSlugs);
        }
        
        return $query->get();
    }
    
    private function fetchFromRssFeed(NewsSource $source, int $hoursBack = 24): Collection
    {
        $articles = collect();
        
        // Get all active RSS feeds for this source
        $rssFeeds = $source->activeRssFeeds;
        
        if ($rssFeeds->isEmpty()) {
            // Fallback to single RSS URL if no feeds defined
            if ($source->rss_url) {
                $rssFeeds = collect([
                    (object)[
                        'url' => $source->rss_url,
                        'name' => 'Main Feed',
                        'category' => 'general'
                    ]
                ]);
            } else {
                return collect();
            }
        }
        
        foreach ($rssFeeds as $feed) {
            try {
                $feedArticles = $this->fetchFromSingleRssFeed($source, $feed, $hoursBack);
                $articles = $articles->merge($feedArticles);
                
                // Update last fetched time
                if (is_object($feed) && method_exists($feed, 'update')) {
                    $feed->update(['last_fetched_at' => now()]);
                }
                
            } catch (\Exception $e) {
                Log::warning("Failed to fetch from RSS feed {$feed->url}: " . $e->getMessage());
            }
        }
        
        return $articles;
    }
    
    private function fetchFromSingleRssFeed(NewsSource $source, $feed, int $hoursBack = 24): Collection
    {
        $url = is_object($feed) ? $feed->url : $feed['url'];
        $category = is_object($feed) ? $feed->category : ($feed['category'] ?? 'general');
        
        Log::info("Fetching from RSS feed: {$url}");
        
        $response = Http::timeout(4) // Set a short timeout for faster response
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (compatible; BalanceNews/1.0; +' . config('app.url') . ')',
            ])
            ->get($url);
        
        if (!$response->successful()) {
            throw new \Exception("HTTP {$response->status()} - {$response->body()}");
        }
        
        $xmlContent = $response->body();
        
        // Handle different encodings
        if (!mb_check_encoding($xmlContent, 'UTF-8')) {
            $xmlContent = mb_convert_encoding($xmlContent, 'UTF-8', 'auto');
        }
        
        // Load XML with error handling
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xmlContent);
        
        if (!$xml) {
            $errors = libxml_get_errors();
            $errorMsg = "Invalid XML feed";
            if (!empty($errors)) {
                $errorMsg .= ": " . $errors[0]->message;
            }
            throw new \Exception($errorMsg);
        }
        
        $articles = collect();
        
        // Handle different feed formats (RSS, Atom)
        $items = $this->extractFeedItems($xml);
        
        foreach ($items as $item) {
            try {
                $article = $this->createArticleFromFeedItem($source, $item, $category, $hoursBack);
                if ($article) {
                    $articles->push($article);
                }
            } catch (\Exception $e) {
                Log::warning("Failed to create article from feed item: " . $e->getMessage());
                continue;
            }
        }
        
        Log::info("Extracted {$articles->count()} articles from {$url}");
        
        return $articles;
    }
    
    private function extractFeedItems($xml): array
    {
        // RSS 2.0
        if (isset($xml->channel->item)) {
            // Convert SimpleXMLElement to array
            $items = [];
            foreach ($xml->channel->item as $item) {
                $items[] = $item;
            }
            return $items;
        }
        
        // RSS 1.0 / RDF
        if (isset($xml->item)) {
            $items = [];
            foreach ($xml->item as $item) {
                $items[] = $item;
            }
            return $items;
        }
        
        // Atom feeds
        if (isset($xml->entry)) {
            $items = [];
            foreach ($xml->entry as $entry) {
                $items[] = $entry;
            }
            return $items;
        }
        
        // Check for namespaced elements
        $namespaces = $xml->getNamespaces(true);
        foreach ($namespaces as $prefix => $uri) {
            if ($prefix === '' || $prefix === 'atom') {
                $entries = $xml->xpath('//entry') ?: $xml->xpath('//item');
                if (!empty($entries)) {
                    return $entries;
                }
            }
        }
        
        return [];
    }
    
    private function createArticleFromFeedItem(NewsSource $source, $item, string $category = 'general', int $hoursBack = 24): ?Article
    {
        $title = (string) $item->title;
        $url = (string) ($item->link ?? $item->guid);
        $description = (string) ($item->description ?? '');
        $pubDate = (string) ($item->pubDate ?? $item->{'dc:date'} ?? '');
        
        // Skip if article already exists
        if (Article::where('url', $url)->exists()) {
            return null;
        }
        
        // Parse publish date first to check if it's within the specified time range
        $publishedAt = $this->parsePublishDate($pubDate);
        
        // Skip articles older than the specified number of hours
        if ($publishedAt->isBefore(Carbon::now()->subHours($hoursBack))) {
            return null;
        }
        
        // Clean title and description
        $cleanTitle = $this->cleanTitle($title);
        $summary = $this->cleanDescription($description);
        
        return Article::create([
            'news_source_id' => $source->id,
            'title' => $cleanTitle,
            'summary' => $summary,
            'url' => $url,
            'author' => (string) ($item->author ?? $item->{'dc:creator'} ?? 'Unknown'),
            'published_at' => $publishedAt,
            'keywords' => $this->extractKeywords($cleanTitle . ' ' . $summary),
            'is_active' => true,
        ]);
    }
    
    private function cleanTitle(string $title): string
    {
        // Remove HTML tags and decode entities
        $clean = html_entity_decode(strip_tags($title), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Remove extra whitespace and normalize
        $clean = preg_replace('/\s+/', ' ', $clean);
        
        // Trim and return
        return trim($clean);
    }
    
    private function cleanDescription(string $description): string
    {
        // Remove HTML tags and decode entities more efficiently
        $clean = html_entity_decode(strip_tags($description), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Remove common unwanted text patterns
        $clean = preg_replace('/\s+/', ' ', $clean); // Normalize whitespace
        
        // Truncate to reasonable length
        return Str::limit(trim($clean), 300);
    }
    
    private function parsePublishDate(string $pubDate): Carbon
    {
        try {
            return Carbon::parse($pubDate);
        } catch (\Exception $e) {
            return Carbon::now();
        }
    }
    

    
    private function extractKeywords(string $text): array
    {
        // Simple keyword extraction
        $stopWords = ['the', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'an', 'a', 'is', 'are', 'was', 'were', 'be', 'been', 'being', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should'];
        
        $words = str_word_count(strtolower($text), 1);
        $keywords = array_filter($words, function($word) use ($stopWords) {
            return strlen($word) > 3 && !in_array($word, $stopWords);
        });
        
        return array_slice(array_unique($keywords), 0, 10);
    }
    
    public function groupIntoStoryClusters(Collection $articles): Collection
    {
        return $articles;
    }
}
