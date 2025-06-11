<div>
    @if (auth()->check())
        @if (count($bookmarkedSourceIds) > 0)
            <x-base-news :articles="$articles" :biasOptions="$biasOptions" :sourceOptions="$sourceOptions" :timeRangeOptions="$timeRangeOptions" :biasDistribution="$biasDistribution"
                :bookmarkedIds="$bookmarkedIds" :search="$search" :selectedBias="$selectedBias" :selectedSource="$selectedSource" :selectedTimeRange="$selectedTimeRange"
                :showHeader="false" headerTitle="News from Bookmarked Sources"
                headerSubtitle="Latest articles from your favorite news sources" />
        @else
            <!-- Empty state for no bookmarked sources -->
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="text-center py-16">
                    <div class="mx-auto h-24 w-24 text-zinc-400 dark:text-zinc-600 mb-6">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-full h-full">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                        </svg>
                    </div>

                    <h3 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100 mb-2">
                        No bookmarked sources yet
                    </h3>

                    <p class="text-zinc-600 dark:text-zinc-400 mb-6 max-w-md mx-auto">
                        Start bookmarking your favorite news sources to see personalized content here.
                        Browse all available sources and bookmark the ones you trust most.
                    </p>

                    <div class="space-y-4">
                        <flux:button :href="route('sources')" wire:navigate>
                            Browse All Sources
                        </flux:button>

                        <div class="text-center">
                            <flux:button :href="route('news')" variant="ghost" wire:navigate>
                                View All News
                            </flux:button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @else
        <!-- Not authenticated -->
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="text-center py-16">
                <h3 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100 mb-2">
                    Sign in to bookmark sources
                </h3>
                <p class="text-zinc-600 dark:text-zinc-400 mb-6">
                    Create an account to bookmark your favorite news sources and get personalized content.
                </p>
                <flux:button :href="route('login')" wire:navigate>
                    Sign In
                </flux:button>
            </div>
        </div>  
    @endif
</div>
