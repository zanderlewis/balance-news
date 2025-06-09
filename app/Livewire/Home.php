<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Article;
use App\Models\NewsSource;
use Livewire\WithPagination;
use Carbon\Carbon;

class Home extends Component
{
    use WithPagination;
    
    public $search = '';
    public $selectedBias = '';
    public $selectedSource = '';
    public $selectedTimeRange = '';
    public $sortBy = 'published_at';
    public $sortDirection = 'desc';
    
    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function updatingSelectedBias()
    {
        $this->resetPage();
    }
    
    public function updatingSelectedSource()
    {
        $this->resetPage();
    }
    
    public function updatingSelectedTimeRange()
    {
        $this->resetPage();
    }
    
    public function clearFilters()
    {
        $this->search = '';
        $this->selectedBias = '';
        $this->selectedSource = '';
        $this->selectedTimeRange = '';
        $this->resetPage();
    }
    
    public function render()
    {
        $query = Article::query()
            ->with(['newsSource'])
            ->orderBy($this->sortBy, $this->sortDirection);
            
        // Search filter
        if ($this->search) {
            $searchTerm = trim($this->search);
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'like', '%' . $searchTerm . '%')
                  ->orWhere('summary', 'like', '%' . $searchTerm . '%')
                  ->orWhereHas('newsSource', function($sourceQuery) use ($searchTerm) {
                      $sourceQuery->where('name', 'like', '%' . $searchTerm . '%');
                  });
            });
        }
        
        // Bias filter
        if ($this->selectedBias) {
            $query->whereHas('newsSource', function($sourceQuery) {
                $sourceQuery->where('bias_label', $this->selectedBias);
            });
        }
        
        // Source filter
        if ($this->selectedSource) {
            $query->where('news_source_id', $this->selectedSource);
        }
        
        // Time range filter
        if ($this->selectedTimeRange) {
            $timeConstraint = match($this->selectedTimeRange) {
                '1h' => Carbon::now()->subHour(),
                '6h' => Carbon::now()->subHours(6),
                '12h' => Carbon::now()->subHours(12),
                '24h' => Carbon::now()->subHours(24),
                '3d' => Carbon::now()->subDays(3),
                '7d' => Carbon::now()->subWeek(),
                '2w' => Carbon::now()->subWeeks(2),
                '1m' => Carbon::now()->subMonth(),
                '3m' => Carbon::now()->subMonths(3),
                '6m' => Carbon::now()->subMonths(6),
                '1y' => Carbon::now()->subYear(),
                default => null, // No time constraint
            };
            
            if ($timeConstraint) {
                $query->where('published_at', '>=', $timeConstraint);
            }
        }
        
        // Get filter options
        $biasOptions = [
            'left' => 'Left',
            'lean-left' => 'Lean Left',
            'center' => 'Center',
            'lean-right' => 'Lean Right',
            'right' => 'Right',
        ];
        
        $sourceOptions = NewsSource::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'bias_label']);
            
        $timeRangeOptions = [
            '1h' => 'Last hour',
            '6h' => 'Last 6 hours',
            '12h' => 'Last 12 hours',
            '24h' => 'Last 24 hours',
            '3d' => 'Last 3 days',
            '7d' => 'Last week',
            '2w' => 'Last 2 weeks',
            '1m' => 'Last month',
            '3m' => 'Last 3 months',
            '6m' => 'Last 6 months',
            '1y' => 'Last year',
            '' => 'All time',
        ];
        
        // Get bias distribution for current results
        $biasDistribution = [];
        $allArticlesQuery = clone $query;
        $allArticles = $allArticlesQuery->get();
        
        if ($allArticles->count() > 0) {
            foreach ($biasOptions as $bias => $label) {
                $count = $allArticles->filter(function($article) use ($bias) {
                    return $article->newsSource->bias_label === $bias;
                })->count();
                
                if ($count > 0) {
                    $biasDistribution[$bias] = [
                        'label' => $label,
                        'count' => $count,
                        'percentage' => round(($count / $allArticles->count()) * 100, 1),
                        'color' => match($bias) {
                            'left' => 'bg-blue-600',
                            'lean-left' => 'bg-blue-400',
                            'center' => 'bg-gray-500',
                            'lean-right' => 'bg-red-400',
                            'right' => 'bg-red-600',
                        }
                    ];
                }
            }
        }
        
        return view('livewire.home', [
            'articles' => $query->paginate(10),
            'biasOptions' => $biasOptions,
            'sourceOptions' => $sourceOptions,
            'timeRangeOptions' => $timeRangeOptions,
            'biasDistribution' => $biasDistribution,
        ])->layout('components.layouts.app', [
            'title' => 'Home',
        ]);
    }
}
