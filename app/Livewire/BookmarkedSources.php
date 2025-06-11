<?php

namespace App\Livewire;

use App\Models\Article;
use App\Models\NewsSource;
use Illuminate\Support\Facades\Auth;

class BookmarkedSources extends BaseNews
{
    protected function getArticlesQuery()
    {
        $user = Auth::user();
        
        if (!$user) {
            return Article::whereRaw('1 = 0'); // Return empty query
        }

        // Get articles only from bookmarked sources
        $bookmarkedSourceIds = $user->sourceBookmarks()->pluck('news_sources.id');
        
        if ($bookmarkedSourceIds->isEmpty()) {
            return Article::whereRaw('1 = 0'); // Return empty query if no sources bookmarked
        }

        $query = Article::with('newsSource')
            ->whereIn('news_source_id', $bookmarkedSourceIds)
            ->orderBy('published_at', 'desc');

        // Apply inherited filters (search, bias, source, time range)
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('summary', 'like', '%' . $this->search . '%')
                  ->orWhereHas('newsSource', function ($sourceQuery) {
                      $sourceQuery->where('name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if ($this->selectedBias) {
            $query->whereHas('newsSource', function ($q) {
                $q->where('bias_label', $this->selectedBias);
            });
        }

        if ($this->selectedSource) {
            $query->where('news_source_id', $this->selectedSource);
        }

        if ($this->selectedTimeRange) {
            $startDate = $this->getStartDateFromTimeRange($this->selectedTimeRange);
            if ($startDate) {
                $query->where('published_at', '>=', $startDate);
            }
        }

        return $query;
    }

    public function render()
    {
        $user = Auth::user();
        
        if (!$user) {
            return view('livewire.bookmarked-sources', [
                'articles' => collect(),
                'biasOptions' => $this->getBiasOptions(),
                'sourceOptions' => collect(),
                'timeRangeOptions' => $this->getTimeRangeOptions(),
                'biasDistribution' => [],
                'bookmarkedIds' => [],
                'bookmarkedSourceIds' => [],
            ]);
        }

        $sharedData = $this->getSharedData();
        
        // Get bookmarked source IDs for the current user
        $bookmarkedSourceIds = $user->sourceBookmarks()->pluck('news_sources.id')->toArray();
        
        // Filter source options to only show bookmarked sources
        $sourceOptions = NewsSource::whereIn('id', $bookmarkedSourceIds)->orderBy('name')->get();

        return view('livewire.bookmarked-sources', array_merge($sharedData, [
            'sourceOptions' => $sourceOptions,
            'bookmarkedSourceIds' => $bookmarkedSourceIds,
        ]));
    }

    public function toggleSourceBookmark($sourceId)
    {
        if (!Auth::check()) {
            return;
        }

        $user = Auth::user();
        
        if ($user->hasBookmarkedSource($sourceId)) {
            $user->sourceBookmarks()->detach($sourceId);
        } else {
            $user->sourceBookmarks()->attach($sourceId);
        }

        // Refresh the page data since source bookmarks changed
        $this->dispatch('$refresh');
    }

    private function getStartDateFromTimeRange($timeRange)
    {
        return match($timeRange) {
            '1h' => now()->subHour(),
            '6h' => now()->subHours(6),
            '12h' => now()->subHours(12),
            '24h' => now()->subHours(24),
            '3d' => now()->subDays(3),
            '7d' => now()->subWeek(),
            '2w' => now()->subWeeks(2),
            '1m' => now()->subMonth(),
            '3m' => now()->subMonths(3),
            '6m' => now()->subMonths(6),
            '1y' => now()->subYear(),
            default => null
        };
    }
}
