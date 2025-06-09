<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\NewsSource;
use App\Models\Article;
use App\Services\NewsAggregatorService;
use Carbon\Carbon;

class RefreshNewsCommand extends Command
{
    protected $signature = 'news:refresh {--hours=24 : Number of hours to fetch articles from (default: 24)}';
    protected $description = 'Refresh news content from RSS feeds';

    public function __construct(
        private NewsAggregatorService $newsAggregator
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('🌍 Refreshing news from multiple sources...');
        $this->newLine();
        
        $hours = (int) $this->option('hours');
        
        // Get active sources across the political spectrum
        $leftSources = NewsSource::where('bias_label', 'left')->where('is_active', true)->get();
        $leanLeftSources = NewsSource::where('bias_label', 'lean-left')->where('is_active', true)->get();
        $centerSources = NewsSource::where('bias_label', 'center')->where('is_active', true)->get();
        $leanRightSources = NewsSource::where('bias_label', 'lean-right')->where('is_active', true)->get();
        $rightSources = NewsSource::where('bias_label', 'right')->where('is_active', true)->get();
        
        $this->info("📊 Source Distribution:");
        $this->line("   Left: {$leftSources->count()} sources");
        $this->line("   Lean-Left: {$leanLeftSources->count()} sources");
        $this->line("   Center: {$centerSources->count()} sources");
        $this->line("   Lean-Right: {$leanRightSources->count()} sources");
        $this->line("   Right: {$rightSources->count()} sources");
        $this->newLine();
        
        $totalArticles = 0;
        $newArticles = 0;
        
        // Fetch from each source type
        $allSources = [
            'left' => $leftSources,
            'lean-left' => $leanLeftSources,
            'center' => $centerSources,
            'lean-right' => $leanRightSources,
            'right' => $rightSources
        ];
        
        foreach ($allSources as $bias => $sources) {
            $this->info("🔍 Fetching from {$bias} sources...");
            
            foreach ($sources as $source) {
                try {
                    $this->line("   📡 {$source->name}...");
                    
                    $articles = $this->newsAggregator->fetchFromSource($source, $hours);
                    $sourceNewArticles = $articles->count();
                    
                    $totalArticles += $sourceNewArticles;
                    $newArticles += $sourceNewArticles;
                    
                    $this->line("     ✅ {$sourceNewArticles} new articles");
                    
                    // Update last scraped time
                    $source->update(['last_scraped_at' => now()]);
                    
                } catch (\Exception $e) {
                    $this->error("     ❌ Failed: " . $e->getMessage());
                }
            }
            
            $this->newLine();
        }
        
        // Display bias distribution
        $this->displayBiasDistribution();
        
        $this->newLine();
        $this->info("✅ News refresh completed!");
        $this->line("   📊 Total articles processed: {$totalArticles}");
        $this->line("   🆕 New articles added: {$newArticles}");
        
        return 0;
    }
    
    private function displayBiasDistribution()
    {
        $this->newLine();
        $this->info('📊 Current Bias Distribution (by News Source):');
        
        $distribution = Article::join('news_sources', 'articles.news_source_id', '=', 'news_sources.id')
            ->selectRaw('news_sources.bias_label, COUNT(*) as count')
            ->where('articles.created_at', '>=', now()->subDays(7))
            ->groupBy('news_sources.bias_label')
            ->pluck('count', 'bias_label')
            ->toArray();
            
        $total = array_sum($distribution);
        
        foreach (['left', 'lean-left', 'center', 'lean-right', 'right'] as $bias) {
            $count = $distribution[$bias] ?? 0;
            $percentage = $total > 0 ? round(($count / $total) * 100, 1) : 0;
            $bar = str_repeat('█', (int)($percentage / 2));
            
            $label = ucfirst(str_replace('-', ' ', $bias));
            $this->line(sprintf('   %-12s %3d%% %s (%d)', $label, $percentage, $bar, $count));
        }
    }
}
