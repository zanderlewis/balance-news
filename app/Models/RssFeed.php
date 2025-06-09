<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RssFeed extends Model
{
    use HasFactory;

    protected $fillable = [
        'news_source_id',
        'name',
        'url',
        'category',
        'is_active',
        'last_fetched_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_fetched_at' => 'datetime',
    ];

    /**
     * Get the news source that owns this RSS feed
     */
    public function newsSource()
    {
        return $this->belongsTo(NewsSource::class);
    }
}
