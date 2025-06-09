<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\NewsSource;
use App\Models\RssFeed;
use Illuminate\Support\Facades\File;

class ImportNewsSourcesCommand extends Command
{
    protected $signature = 'news:import-sources {--force : Force reimport of existing sources}';
    protected $description = 'Import news sources from JSON files';

    public function handle()
    {
        $this->info('📥 Importing news sources from JSON files...');
        $this->newLine();
        
        $sourcesPath = database_path('sources');
        
        if (!File::exists($sourcesPath)) {
            $this->error('Sources directory not found at: ' . $sourcesPath);
            return 1;
        }
        
        $jsonFiles = File::glob($sourcesPath . '/*.json');
        
        if (empty($jsonFiles)) {
            $this->error('No JSON files found in sources directory');
            return 1;
        }
        
        $imported = 0;
        $updated = 0;
        $skipped = 0;
        
        foreach ($jsonFiles as $file) {
            $filename = basename($file);
            $this->line("Processing: {$filename}");
            
            try {
                $data = json_decode(File::get($file), true);
                
                if (!$data) {
                    $this->error("  ❌ Invalid JSON in {$filename}");
                    continue;
                }
                
                // Find or create news source
                $newsSource = NewsSource::where('slug', $data['slug'])->first();
                
                if ($newsSource && !$this->option('force')) {
                    $this->line("  ⏭️  Skipping existing source: {$data['name']}");
                    $skipped++;
                    continue;
                }
                
                if ($newsSource) {
                    // Update existing
                    $newsSource->update([
                        'name' => $data['name'],
                        'url' => $data['url'],
                        'bias_label' => $data['bias_label'],
                        'country_code' => $data['country_code'] ?? 'US',
                        'categories' => $data['categories'] ?? [],
                        'is_active' => $data['is_active'] ?? true,
                    ]);
                    $this->line("  🔄 Updated: {$data['name']}");
                    $updated++;
                } else {
                    // Create new
                    $newsSource = NewsSource::create([
                        'name' => $data['name'],
                        'slug' => $data['slug'],
                        'url' => $data['url'],
                        'bias_label' => $data['bias_label'],
                        'country_code' => $data['country_code'] ?? 'US',
                        'categories' => $data['categories'] ?? [],
                        'is_active' => $data['is_active'] ?? true,
                    ]);
                    $this->line("  ✅ Created: {$data['name']}");
                    $imported++;
                }
                
                // Import RSS feeds
                if (isset($data['rss_feeds']) && is_array($data['rss_feeds'])) {
                    // Clear existing feeds if force update
                    if ($this->option('force')) {
                        $newsSource->rssFeeds()->delete();
                    }
                    
                    foreach ($data['rss_feeds'] as $feedData) {
                        RssFeed::updateOrCreate([
                            'news_source_id' => $newsSource->id,
                            'url' => $feedData['url'],
                        ], [
                            'name' => $feedData['name'],
                            'category' => $feedData['category'] ?? 'general',
                            'is_active' => $feedData['is_active'] ?? true,
                        ]);
                    }
                    
                    $feedCount = count($data['rss_feeds']);
                    $this->line("    📡 Imported {$feedCount} RSS feeds");
                }
                
            } catch (\Exception $e) {
                $this->error("  ❌ Error processing {$filename}: " . $e->getMessage());
            }
        }
        
        $this->newLine();
        $this->info("✅ Import completed!");
        $this->line("  📊 Sources imported: {$imported}");
        $this->line("  🔄 Sources updated: {$updated}");
        $this->line("  ⏭️  Sources skipped: {$skipped}");
        
        return 0;
    }
}
