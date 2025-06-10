<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Article;
use App\Models\NewsSource;
use Carbon\Carbon;

abstract class BaseNews extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedBias = '';
    public $selectedSource = '';
    public $selectedTimeRange = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedBias' => ['except' => ''],
        'selectedSource' => ['except' => ''],
        'selectedTimeRange' => ['except' => '']
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectedBias()
    {
        $this->resetPage();
    }

    public function updatedSelectedSource()
    {
        $this->resetPage();
    }

    public function updatedSelectedTimeRange()
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

    protected function getArticlesQuery()
    {
        $query = Article::with('newsSource')
            ->orderBy('published_at', 'desc');

        // Search functionality
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('summary', 'like', '%' . $this->search . '%')
                  ->orWhereHas('newsSource', function ($sourceQuery) {
                      $sourceQuery->where('name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        // Bias filter
        if ($this->selectedBias) {
            $query->whereHas('newsSource', function ($q) {
                $q->where('bias_label', $this->selectedBias);
            });
        }

        // Source filter
        if ($this->selectedSource) {
            $query->where('news_source_id', $this->selectedSource);
        }

        // Time range filter
        if ($this->selectedTimeRange) {
            $startDate = match($this->selectedTimeRange) {
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
                default => null
            };
            
            if ($startDate) {
                $query->where('published_at', '>=', $startDate);
            }
        }

        return $query;
    }

    protected function getBiasOptions()
    {
        return [
            'left' => 'Left',
            'lean-left' => 'Lean Left',
            'center' => 'Center',
            'lean-right' => 'Lean Right',
            'right' => 'Right',
        ];
    }

    protected function getTimeRangeOptions()
    {
        return [
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
        ];
    }

    protected function getBiasDistribution($articles)
    {
        $totalCount = $articles->total();
        
        if ($totalCount === 0) {
            return [];
        }

        // Get bias distribution from all articles in current query
        // Clone the query and remove ORDER BY to avoid GROUP BY conflicts
        $query = clone $this->getArticlesQuery();
        
        $biasData = $query->reorder() // Remove existing ORDER BY clauses
            ->join('news_sources', 'articles.news_source_id', '=', 'news_sources.id')
            ->selectRaw('news_sources.bias_label, COUNT(*) as count')
            ->groupBy('news_sources.bias_label')
            ->pluck('count', 'bias_label')
            ->toArray();

        $biasOptions = $this->getBiasOptions();
        $colors = [
            'left' => 'bg-blue-600',
            'lean-left' => 'bg-blue-400',
            'center' => 'bg-gray-500',
            'lean-right' => 'bg-red-400',
            'right' => 'bg-red-600',
        ];

        $distribution = [];
        foreach ($biasData as $bias => $count) {
            $percentage = round(($count / $totalCount) * 100, 1);
            $distribution[$bias] = [
                'label' => $biasOptions[$bias] ?? ucfirst(str_replace('-', ' ', $bias)),
                'count' => $count,
                'percentage' => $percentage,
                'color' => $colors[$bias] ?? 'bg-gray-400'
            ];
        }

        return $distribution;
    }

    protected function getSharedData()
    {
        $articles = $this->getArticlesQuery()->paginate(20);
        
        return [
            'articles' => $articles,
            'biasOptions' => $this->getBiasOptions(),
            'sourceOptions' => NewsSource::orderBy('name')->get(),
            'timeRangeOptions' => $this->getTimeRangeOptions(),
            'biasDistribution' => $this->getBiasDistribution($articles),
        ];
    }
}
