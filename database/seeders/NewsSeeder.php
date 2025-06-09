<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\NewsSource;
use App\Models\Article;
use Carbon\Carbon;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        // Load news sources from JSON files
        $sourceFiles = glob(database_path('sources/*.json'));
        
        foreach ($sourceFiles as $file) {
            $sources = json_decode(file_get_contents($file), true);
            foreach ($sources as $source) {
                NewsSource::firstOrCreate(
                    ['slug' => $source['slug']], 
                    $source
                );
            }
        }

        // Create sample articles for each source
        $this->createSampleArticles();
    }

    private function createSampleArticles(): void
    {
        $newsSources = NewsSource::all();
        
        $sampleArticles = [
            [
                'title' => 'Climate Change Summit Reaches Historic Agreement',
                'summary' => 'World leaders agree on ambitious climate targets at international summit.',
                'content' => 'Representatives from over 190 countries have reached a groundbreaking agreement on climate action...',
                'author' => 'Sarah Johnson',
                'keywords' => ['climate', 'environment', 'politics', 'international'],
            ],
            [
                'title' => 'Economic Policy Changes Spark Congressional Debate',
                'summary' => 'New economic proposals face bipartisan scrutiny in heated legislative session.',
                'content' => 'Congress engaged in extensive debate over proposed economic reforms that could reshape...',
                'author' => 'Michael Chen',
                'keywords' => ['economy', 'congress', 'policy', 'politics'],
            ],
            [
                'title' => 'Technology Giants Face New Regulatory Proposals',
                'summary' => 'Lawmakers introduce comprehensive tech regulation framework.',
                'content' => 'A bipartisan group of legislators has introduced sweeping technology regulation...',
                'author' => 'Lisa Rodriguez',
                'keywords' => ['technology', 'regulation', 'big tech', 'congress'],
            ],
            [
                'title' => 'Healthcare Reform Bill Advances Through Senate',
                'summary' => 'Major healthcare legislation moves forward amid partisan divide.',
                'content' => 'The Senate has advanced significant healthcare reform legislation that would...',
                'author' => 'David Wilson',
                'keywords' => ['healthcare', 'reform', 'senate', 'politics'],
            ],
            [
                'title' => 'International Trade Agreement Negotiations Continue',
                'summary' => 'Diplomatic talks progress on multilateral trade framework.',
                'content' => 'Trade representatives from multiple nations continue negotiations on a comprehensive...',
                'author' => 'Emma Thompson',
                'keywords' => ['trade', 'international', 'diplomacy', 'economy'],
            ],
        ];

        foreach ($newsSources as $source) {
            foreach ($sampleArticles as $articleData) {
                Article::create([
                    'news_source_id' => $source->id,
                    'title' => $articleData['title'],
                    'summary' => $articleData['summary'],
                    'content' => $articleData['content'],
                    'url' => $source->url . '/article/' . Str::slug($articleData['title']),
                    'author' => $articleData['author'],
                    'published_at' => Carbon::now()->subMinutes(rand(30, 1440)),
                    'keywords' => $articleData['keywords'],
                    'is_active' => true,
                ]);
            }
        }
    }
}
