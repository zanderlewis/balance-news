<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use App\Models\Article;

class Bookmarks extends BaseNews
{
    protected function getArticlesQuery()
    {
        if (!Auth::check()) {
            // Return an empty query that can still be paginated
            return Article::whereRaw('1 = 0')->with('newsSource');
        }

        // Get only bookmarked articles for the current user
        $query = Auth::user()->bookmarks()->with('newsSource')
            ->orderBy('user_bookmarks.created_at', 'desc');

        // Apply the same filters as BaseNews but to bookmarked articles
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

        return $query;
    }

    public function render()
    {
        $data = $this->getSharedData();
        
        return view('livewire.bookmarks', $data)->layout('components.layouts.app.sidebar');
    }
}
