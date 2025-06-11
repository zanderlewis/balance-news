<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @if (auth()->check())
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-zinc-900 dark:text-zinc-100">News Sources</h1>
            <p class="text-zinc-600 dark:text-zinc-400 mt-2">
                Discover and bookmark news sources to customize your feed
            </p>
        </div>

        <!-- Filters -->
        <div class="bg-zinc-50 dark:bg-zinc-800 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Search -->
                <div>
                    <flux:input wire:model.live.debounce.300ms="search" placeholder="Search sources..."
                        icon="magnifying-glass" />
                </div>

                <!-- Bias Filter -->
                <div>
                    <flux:select wire:model.live="selectedBias" placeholder="All bias perspectives">
                        <option value="">All perspectives</option>
                        @foreach ($biasOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                </div>

                <!-- Bookmarked Toggle -->
                <div class="flex items-center">
                    <label class="flex items-center">
                        <input type="checkbox" wire:model.live="showOnlyBookmarked"
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Show only bookmarked</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Sources Grid -->
        @if ($sources->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                @foreach ($sources as $source)
                    <div
                        class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6 hover:shadow-lg transition-shadow duration-200">
                        <!-- Source Header -->
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-1">
                                    {{ $source->name }}
                                </h3>

                                <!-- Bias Badge -->
                                @if ($source->bias_label)
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @switch($source->bias_label)
                                                    @case('Left')
                                                        bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300
                                                        @break
                                                    @case('Lean Left')
                                                        bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400
                                                        @break
                                                    @case('Center')
                                                        bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300
                                                        @break
                                                    @case('Lean Right')
                                                        bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400
                                                        @break
                                                    @case('Right')
                                                        bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300
                                                        @break
                                                    @default
                                                        bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300
                                                @endswitch
                                            ">
                                        {{ $source->bias_label }}
                                    </span>
                                @endif
                            </div>

                            <!-- Bookmark Button -->
                            <flux:button wire:click="toggleSourceBookmark({{ $source->id }})" variant="ghost"
                                size="sm" class="ml-2">
                                @if (in_array($source->id, $bookmarkedSourceIds))
                                    <svg class="w-4 h-4 text-blue-500 fill-current" fill="currentColor"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                                    </svg>
                                @else
                                    <svg class="w-4 h-4 text-zinc-400 dark:text-zinc-500" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                                    </svg>
                                @endif
                            </flux:button>
                        </div>

                        <!-- Description -->
                        @if ($source->description)
                            <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-3 line-clamp-3">
                                {{ $source->description }}
                            </p>
                        @endif

                        <!-- URL -->
                        <div class="flex items-center justify-between">
                            <a href="{{ $source->url }}" target="_blank"
                                class="text-sm text-blue-600 dark:text-blue-400 hover:underline flex items-center">
                                <span class="truncate">{{ parse_url($source->url, PHP_URL_HOST) }}</span>
                                <svg class="w-3 h-3 ml-1 flex-shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14">
                                    </path>
                                </svg>
                            </a>

                            <!-- Article Count -->
                            @if ($source->articles_count ?? 0 > 0)
                                <span class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $source->articles_count }} articles
                                </span>
                            @else
                                <span class="text-xs text-zinc-500 dark:text-zinc-400">
                                    0 articles
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="flex justify-center">
                {{ $sources->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-16">
                <div class="mx-auto h-24 w-24 text-zinc-400 dark:text-zinc-600 mb-6">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-full h-full">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                    </svg>
                </div>

                <h3 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100 mb-2">
                    No sources found
                </h3>

                <p class="text-zinc-600 dark:text-zinc-400 mb-6 max-w-md mx-auto">
                    @if ($search || $selectedBias || $showOnlyBookmarked)
                        Try adjusting your filters to see more sources.
                    @else
                        There are no news sources available at the moment.
                    @endif
                </p>

                @if ($search || $selectedBias || $showOnlyBookmarked)
                    <flux:button wire:click="clearFilters" variant="ghost">
                        Clear Filters
                    </flux:button>
                @endif
            </div>
        @endif
    @else
        <!-- Not authenticated -->
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="text-center py-16">
                <h3 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100 mb-2">
                    Sign in to manage sources
                </h3>
                <p class="text-zinc-600 dark:text-zinc-400 mb-6">
                    Create an account to bookmark your favorite news sources and customize your feed.
                </p>
                <flux:button :href="route('login')" wire:navigate>
                    Sign In
                </flux:button>
            </div>
        </div>
    @endif
</div>
