<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NewsAggregatorService;

class FetchNewsCommand extends Command
{
    protected $signature = 'news:fetch {--sources=all : Comma-separated list of source slugs or "all"} {--hours=24 : Number of hours to fetch articles from (default: 24)}';
    protected $description = 'Fetch latest news from RSS feeds';

    public function __construct(
        private NewsAggregatorService $newsAggregator
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('🔄 Starting news fetch...');
        
        $sources = $this->option('sources');
        $hours = (int) $this->option('hours');
        $sourceList = $sources === 'all' ? [] : explode(',', $sources);
        
        try {
            // Fetch news from RSS feeds
            $articles = $this->newsAggregator->fetchLatestNews($sourceList, $hours);
            $this->info("📰 Fetched {$articles->count()} new articles from the last {$hours} hours");
            
            $this->info('✅ News fetch completed successfully!');
            
        } catch (\Exception $e) {
            $this->error('❌ Error during news fetch: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}
