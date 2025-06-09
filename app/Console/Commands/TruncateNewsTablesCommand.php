<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TruncateNewsTablesCommand extends Command
{
    protected $signature = 'news:truncate-tables 
                            {--force : Skip confirmation prompt}
                            {--articles : Only truncate articles table}
                            {--sources : Only truncate news sources and RSS feeds}
                            {--bookmarks : Only truncate user bookmarks}';
    
    protected $description = 'Truncate news-related database tables (articles, news_sources, rss_feeds, user_bookmarks)';

    public function handle()
    {
        $this->info('🗑️  News Tables Truncation Tool');
        $this->newLine();

        // Check which tables to truncate based on options
        $truncateArticles = $this->option('articles') || (!$this->option('sources') && !$this->option('bookmarks'));
        $truncateSources = $this->option('sources') || (!$this->option('articles') && !$this->option('bookmarks'));
        $truncateBookmarks = $this->option('bookmarks') || (!$this->option('articles') && !$this->option('sources'));

        // If specific options are provided, only truncate those
        if ($this->option('articles') || $this->option('sources') || $this->option('bookmarks')) {
            $truncateArticles = $this->option('articles');
            $truncateSources = $this->option('sources');
            $truncateBookmarks = $this->option('bookmarks');
        }

        // Show what will be truncated
        $tables = [];
        if ($truncateBookmarks) $tables[] = 'user_bookmarks';
        if ($truncateArticles) $tables[] = 'articles';
        if ($truncateSources) $tables[] = 'rss_feeds, news_sources';

        $tablesList = implode(', ', $tables);
        $this->warn("⚠️  This will truncate the following tables: {$tablesList}");
        $this->warn("⚠️  This action cannot be undone!");
        $this->newLine();

        // Check if tables exist
        $missingTables = [];
        $checkTables = ['articles', 'news_sources', 'rss_feeds', 'user_bookmarks'];
        
        foreach ($checkTables as $table) {
            if (!Schema::hasTable($table)) {
                $missingTables[] = $table;
            }
        }

        if (!empty($missingTables)) {
            $this->error("❌ Missing tables: " . implode(', ', $missingTables));
            return 1;
        }

        // Show current record counts
        $this->showTableCounts();
        $this->newLine();

        // Confirmation
        if (!$this->option('force')) {
            if (!$this->confirm('Are you sure you want to proceed?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        try {
            DB::beginTransaction();

            $truncatedTables = [];

            // Truncate in order to respect foreign key relationships
            // 1. First truncate dependent tables
            if ($truncateBookmarks && Schema::hasTable('user_bookmarks')) {
                DB::table('user_bookmarks')->truncate();
                $truncatedTables[] = 'user_bookmarks';
                $this->line("✅ Truncated: user_bookmarks");
            }

            if ($truncateArticles && Schema::hasTable('articles')) {
                DB::table('articles')->truncate();
                $truncatedTables[] = 'articles';
                $this->line("✅ Truncated: articles");
            }

            // 2. Then truncate RSS feeds (dependent on news_sources)
            if ($truncateSources && Schema::hasTable('rss_feeds')) {
                DB::table('rss_feeds')->truncate();
                $truncatedTables[] = 'rss_feeds';
                $this->line("✅ Truncated: rss_feeds");
            }

            // 3. Finally truncate news_sources
            if ($truncateSources && Schema::hasTable('news_sources')) {
                DB::table('news_sources')->truncate();
                $truncatedTables[] = 'news_sources';
                $this->line("✅ Truncated: news_sources");
            }

            DB::commit();

            $this->newLine();
            $this->info("🎉 Successfully truncated " . count($truncatedTables) . " table(s)");
            
            // Show updated counts
            $this->newLine();
            $this->showTableCounts();

            $this->newLine();
            $this->info("💡 Tip: You can now run 'php artisan news:import-sources' to re-import news sources");
            $this->info("💡 Tip: You can run 'php artisan news:fetch --hours=24' to fetch fresh articles");

        } catch (\Exception $e) {
            DB::rollback();
            $this->error("❌ Error truncating tables: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function showTableCounts(): void
    {
        $this->info("📊 Current table record counts:");
        
        $tables = [
            'news_sources' => 'News Sources',
            'rss_feeds' => 'RSS Feeds', 
            'articles' => 'Articles',
            'user_bookmarks' => 'User Bookmarks'
        ];

        foreach ($tables as $table => $label) {
            if (Schema::hasTable($table)) {
                $count = DB::table($table)->count();
                $this->line("  {$label}: " . number_format($count));
            } else {
                $this->line("  {$label}: Table not found");
            }
        }
    }
}
