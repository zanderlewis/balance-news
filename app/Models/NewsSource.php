<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NewsSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'url',
        'rss_url',
        'logo_url',
        'bias_label',
        'country_code',
        'categories',
        'is_active',
        'last_scraped_at',
    ];

    protected $casts = [
        'categories' => 'array',
        'last_scraped_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get articles from this news source
     */
    public function articles()
    {
        return $this->hasMany(Article::class);
    }

    /**
     * Get RSS feeds for this news source
     */
    public function rssFeeds()
    {
        return $this->hasMany(RssFeed::class);
    }

    /**
     * Get active RSS feeds for this news source
     */
    public function activeRssFeeds()
    {
        return $this->hasMany(RssFeed::class)->where('is_active', true);
    }

    /**
     * Get bias color for UI display
     */
    public function getBiasColor(): string
    {
        return match($this->bias_label) {
            'left' => 'bg-blue-600',
            'lean-left' => 'bg-blue-400',
            'center' => 'bg-gray-500',
            'lean-right' => 'bg-red-400',
            'right' => 'bg-red-600',
            default => 'bg-gray-500',
        };
    }

    /**
     * Get bias label display text
     */
    public function getBiasDisplayLabel(): string
    {
        return match($this->bias_label) {
            'left' => 'Left',
            'lean-left' => 'Lean Left',
            'center' => 'Center',
            'lean-right' => 'Lean Right',
            'right' => 'Right',
            default => 'Center',
        };
    }
}
