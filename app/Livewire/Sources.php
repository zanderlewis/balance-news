<?php

namespace App\Livewire;

use App\Models\NewsSource;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Sources extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedBias = '';
    public $showOnlyBookmarked = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedBias' => ['except' => ''],
        'showOnlyBookmarked' => ['except' => false],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingSelectedBias()
    {
        $this->resetPage();
    }

    public function updatingShowOnlyBookmarked()
    {
        $this->resetPage();
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

        // Refresh component to update bookmark status
        $this->dispatch('$refresh');
    }

    public function getBiasOptions()
    {
        return [
            'left' => 'Left',
            'lean-left' => 'Lean Left',
            'center' => 'Center',
            'lean-right' => 'Lean Right',
            'right' => 'Right',
        ];
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->selectedBias = '';
        $this->showOnlyBookmarked = false;
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();
        
        if (!$user) {
            return view('livewire.sources', [
                'sources' => collect(),
                'biasOptions' => $this->getBiasOptions(),
                'bookmarkedSourceIds' => [],
            ])->layout('components.layouts.app.sidebar');
        }

        $query = NewsSource::query()->withCount('articles');

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%')
                  ->orWhere('url', 'like', '%' . $this->search . '%');
            });
        }

        // Apply bias filter
        if ($this->selectedBias) {
            $query->where('bias_label', $this->selectedBias);
        }

        // Apply bookmarked filter
        if ($this->showOnlyBookmarked) {
            $bookmarkedSourceIds = $user->sourceBookmarks()->pluck('news_sources.id');
            if ($bookmarkedSourceIds->isEmpty()) {
                $query->whereRaw('1 = 0'); // Return empty query
            } else {
                $query->whereIn('id', $bookmarkedSourceIds);
            }
        }

        $sources = $query->orderBy('name')->paginate(20);
        
        // Get bookmarked source IDs for the current user
        $bookmarkedSourceIds = $user->sourceBookmarks()->pluck('news_sources.id')->toArray();

        return view('livewire.sources', [
            'sources' => $sources,
            'biasOptions' => $this->getBiasOptions(),
            'bookmarkedSourceIds' => $bookmarkedSourceIds,
        ])->layout('components.layouts.app.sidebar');
    }
}
