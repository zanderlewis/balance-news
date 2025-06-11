<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'preferences',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'preferences' => 'array',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Get the user's bookmarked articles
     */
    public function bookmarks()
    {
        return $this->belongsToMany(Article::class, 'user_bookmarks')
                    ->withTimestamps();
    }

    /**
     * Get the user's bookmarked news sources
     */
    public function sourceBookmarks()
    {
        return $this->belongsToMany(NewsSource::class, 'user_source_bookmarks')
                    ->withTimestamps();
    }

    /**
     * Check if user has bookmarked an article
     */
    public function hasBookmarked($articleId): bool
    {
        return $this->bookmarks()->where('article_id', $articleId)->exists();
    }

    /**
     * Check if user has bookmarked a news source
     */
    public function hasBookmarkedSource($sourceId): bool
    {
        return $this->sourceBookmarks()->where('news_source_id', $sourceId)->exists();
    }

    /**
     * Get user's bias preferences (default to center)
     */
    public function getBiasPreferences(): array
    {
        return $this->preferences['bias_preferences'] ?? [
            'show_left' => true,
            'show_lean_left' => true,
            'show_center' => true,
            'show_lean_right' => true,
            'show_right' => true,
        ];
    }
}
