<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-zinc-900 dark:text-zinc-100 mb-2">
            Balance News
        </h1>
        <p class="text-lg text-zinc-600 dark:text-zinc-400">
            Compare news coverage across the political spectrum
        </p>
    </div>
    
    <!-- Search and Filters -->
    <div class="mb-8 space-y-6">
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
            <div class="flex-1">
                <h2 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100 mb-2">
                    Latest News Stories
                </h2>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                    Search and filter articles by bias, source, and time range
                </p>
            </div>
            
            <div class="w-full sm:w-96">
                <flux:input 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Search articles, sources, or topics..." 
                    icon="magnifying-glass"
                    class="w-full"
                />
                @if($search)
                    <div class="mt-2 flex items-center gap-2">
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">
                            Searching for: <strong>{{ $search }}</strong>
                        </span>
                        <flux:button wire:click="$set('search', '')" variant="ghost" size="xs">
                            Clear
                        </flux:button>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Filters -->
        <div class="bg-zinc-50 dark:bg-zinc-800 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Bias Filter -->
                <div>
                    <flux:select wire:model.live="selectedBias" placeholder="All bias perspectives">
                        <option value="">All perspectives</option>
                        @foreach($biasOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                </div>
                
                <!-- Source Filter -->
                <div>
                    <flux:select wire:model.live="selectedSource" placeholder="All sources">
                        <option value="">All sources</option>
                        @foreach($sourceOptions as $source)
                            <option value="{{ $source->id }}">
                                {{ $source->name }} ({{ ucfirst(str_replace('-', ' ', $source->bias_label)) }})
                            </option>
                        @endforeach
                    </flux:select>
                </div>
                
                <!-- Time Range Filter -->
                <div>
                    <flux:select wire:model.live="selectedTimeRange" placeholder="All time">
                        @foreach($timeRangeOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                </div>
                
                <!-- Clear Filters Button -->
                <div>
                    @if($search || $selectedBias || $selectedSource || $selectedTimeRange)
                        <flux:button wire:click="clearFilters" variant="outline" class="w-full">
                            <div class="flex items-center justify-center">
                                <flux:icon name="x-mark" class="w-4 h-4 mr-2" />
                                <span>Clear Filters</span>
                            </div>
                        </flux:button>
                    @else
                        <div class="h-10 flex items-center justify-center text-sm text-zinc-400 bg-zinc-100 dark:bg-zinc-700 dark:text-zinc-500 rounded-md border border-zinc-200 dark:border-zinc-600">
                            No active filters
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Active Filters Display -->
            @if($search || $selectedBias || $selectedSource || $selectedTimeRange)
                <div class="mt-4 pt-4 border-t border-zinc-200 dark:border-zinc-600">
                    <div class="flex flex-wrap gap-2 items-center">
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">Active filters:</span>
                        
                        @if($search)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                Search: {{ $search }}
                                <button wire:click="$set('search', '')" class="ml-1 hover:text-blue-600">
                                    <flux:icon name="x-mark" class="w-3 h-3" />
                                </button>
                            </span>
                        @endif
                        
                        @if($selectedBias)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                Bias: {{ $biasOptions[$selectedBias] }}
                                <button wire:click="$set('selectedBias', '')" class="ml-1 hover:text-purple-600">
                                    <flux:icon name="x-mark" class="w-3 h-3" />
                                </button>
                            </span>
                        @endif
                        
                        @if($selectedSource)
                            @php
                                $sourceName = $sourceOptions->find($selectedSource)?->name ?? 'Unknown';
                            @endphp
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                Source: {{ $sourceName }}
                                <button wire:click="$set('selectedSource', '')" class="ml-1 hover:text-green-600">
                                    <flux:icon name="x-mark" class="w-3 h-3" />
                                </button>
                            </span>
                        @endif
                        
                        @if($selectedTimeRange)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
                                Time: {{ $timeRangeOptions[$selectedTimeRange] }}
                                <button wire:click="$set('selectedTimeRange', '')" class="ml-1 hover:text-orange-600">
                                    <flux:icon name="x-mark" class="w-3 h-3" />
                                </button>
                            </span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
        
    <!-- Results Summary -->
    <div class="mb-6 flex items-center justify-between">
        <div class="text-sm text-zinc-600 dark:text-zinc-400">
            @if($articles->total() > 0)
                Showing {{ $articles->firstItem() }}-{{ $articles->lastItem() }} of {{ $articles->total() }} articles
            @else
                No articles found
            @endif
        </div>
        
        @if($articles->total() > 0)
            <div class="text-xs text-zinc-500 dark:text-zinc-500">
                Page {{ $articles->currentPage() }} of {{ $articles->lastPage() }}
            </div>
        @endif
    </div>
    
    <!-- Bias Distribution -->
    @if(!empty($biasDistribution) && count($biasDistribution) > 1)
        <div class="mb-6 bg-white dark:bg-zinc-800 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700">
            <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100 mb-3">
                <flux:icon name="chart-bar" class="w-4 h-4 mr-2 inline" />
                Bias Distribution in Current Results
            </h3>
            <div class="space-y-2">
                @foreach($biasDistribution as $bias => $data)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full {{ $data['color'] }}"></span>
                            <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ $data['label'] }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-16 bg-zinc-200 dark:bg-zinc-600 rounded-full h-2">
                                <div class="{{ $data['color'] }} h-2 rounded-full" style="width: {{ $data['percentage'] }}%"></div>
                            </div>
                            <span class="text-xs text-zinc-500 dark:text-zinc-400 w-12 text-right">
                                {{ $data['count'] }} ({{ $data['percentage'] }}%)
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
        
        <!-- Articles -->
        <div class="space-y-6">
            @forelse($articles as $article)
                <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-start justify-between mb-2">
                                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 flex-1 mr-4">
                                    <a href="{{ $article->url }}" target="_blank" class="hover:text-blue-600 dark:hover:text-blue-400">
                                        {{ $article->title }}
                                    </a>
                                </h3>
                                <!-- Bias Badge (more prominent) -->
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium text-white {{ $article->newsSource->getBiasColor() }} flex-shrink-0">
                                    {{ $article->newsSource->getBiasDisplayLabel() }}
                                </span>
                            </div>
                            <p class="text-zinc-600 dark:text-zinc-400 mb-3">
                                {{ $article->summary }}
                            </p>
                            <div class="flex items-center gap-4 text-sm text-zinc-500 dark:text-zinc-400">
                                <span class="font-medium flex items-center">
                                    <flux:icon name="building-office" class="w-4 h-4 mr-1" />
                                    {{ $article->newsSource->name }}
                                </span>
                                <span class="flex items-center">
                                    <flux:icon name="clock" class="w-4 h-4 mr-1" />
                                    {{ $article->published_at->diffForHumans() }}
                                </span>
                                @if($article->author && $article->author !== 'Unknown')
                                    <span class="flex items-center">
                                        <flux:icon name="user" class="w-4 h-4 mr-1" />
                                        {{ $article->author }}
                                    </span>
                                @endif
                            </div>
                            
                            <!-- Article Tags/Keywords -->
                            @if(!empty($article->keywords))
                                <div class="mt-3 flex flex-wrap gap-1">
                                    @foreach(array_slice($article->keywords, 0, 5) as $keyword)
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-zinc-100 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300">
                                            {{ $keyword }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-16">
                    <flux:icon name="document-text" class="w-16 h-16 mx-auto text-zinc-400 dark:text-zinc-600 mb-4" />
                    <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-2">
                        No articles found
                    </h3>
                    <p class="text-zinc-600 dark:text-zinc-400">
                        @if($search || $selectedBias || $selectedSource || $selectedTimeRange)
                            Try adjusting your filters or clearing them to see more results.
                        @else
                            Check back later for new articles.
                        @endif
                    </p>
                    
                    @if($search || $selectedBias || $selectedSource || $selectedTimeRange)
                        <flux:button wire:click="clearFilters" variant="outline" size="sm" class="mt-4">
                            Clear All Filters
                        </flux:button>
                    @endif
                </div>
            @endforelse
        </div>
        
        <!-- Pagination -->
        @if($articles->hasPages())
            <div class="mt-8">
                {{ $articles->links('custom-pagination') }}
            </div>
        @endif
</div>
