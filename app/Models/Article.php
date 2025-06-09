<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'news_source_id',
        'title',
        'summary',
        'url',
        'author',
        'published_at',
        'keywords',
        'is_active',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'keywords' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the news source that owns the article
     */
    public function newsSource()
    {
        return $this->belongsTo(NewsSource::class);
    }

    /**
     * Get users who bookmarked this article
     */
    public function bookmarkedBy()
    {
        return $this->belongsToMany(User::class, 'user_bookmarks')
                    ->withTimestamps();
    }
}
