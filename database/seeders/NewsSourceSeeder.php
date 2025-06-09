<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\NewsSource;
use App\Models\RssFeed;
use Illuminate\Support\Facades\File;

class NewsSourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sourcesPath = database_path('sources');
        
        if (!File::exists($sourcesPath)) {
            $this->command->error("Sources directory not found: {$sourcesPath}");
            return;
        }

        $jsonFiles = File::glob($sourcesPath . '/*.json');
        
        foreach ($jsonFiles as $jsonFile) {
            $this->command->info("Processing: " . basename($jsonFile));
            
            try {
                $data = json_decode(File::get($jsonFile), true);
                
                if (!$data) {
                    $this->command->error("Invalid JSON in: " . basename($jsonFile));
                    continue;
                }
                
                // Create or update news source
                $newsSource = NewsSource::updateOrCreate(
                    ['slug' => $data['slug']],
                    [
                        'name' => $data['name'],
                        'url' => $data['url'],
                        'bias_label' => $data['bias_label'],
                        'country_code' => $data['country_code'] ?? 'US',
                        'categories' => $data['categories'] ?? [],
                        'is_active' => $data['is_active'] ?? true,
                    ]
                );
                
                // Delete existing RSS feeds for this source to avoid duplicates
                $newsSource->rssFeeds()->delete();
                
                // Create RSS feeds
                if (isset($data['rss_feeds']) && is_array($data['rss_feeds'])) {
                    foreach ($data['rss_feeds'] as $feedData) {
                        RssFeed::create([
                            'news_source_id' => $newsSource->id,
                            'name' => $feedData['name'],
                            'url' => $feedData['url'],
                            'category' => $feedData['category'] ?? 'general',
                            'is_active' => true,
                        ]);
                    }
                    
                    $this->command->info("Created {$newsSource->name} with " . count($data['rss_feeds']) . " RSS feeds");
                } else {
                    $this->command->warn("No RSS feeds found for {$newsSource->name}");
                }
                
            } catch (\Exception $e) {
                $this->command->error("Error processing " . basename($jsonFile) . ": " . $e->getMessage());
            }
        }
        
        $this->command->info("News source seeding completed!");
    }
}
