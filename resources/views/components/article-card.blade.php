@props(['article', 'isBookmarked' => false, 'bookmarkedIds' => []])

<div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
    <div class="flex items-start justify-between">
        <div class="flex-1">
            <div class="flex items-start justify-between mb-2">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 flex-1 mr-4">
                    <a href="{{ $article->url }}" target="_blank" class="hover:text-blue-600 dark:hover:text-blue-400">
                        {{ $article->title }}
                    </a>
                </h3>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <!-- Bookmark Button -->
                    @auth
                        <button 
                            wire:click="toggleBookmark({{ $article->id }})"
                            class="p-2 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors {{ $isBookmarked ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}"
                            title="{{ $isBookmarked ? 'Remove bookmark' : 'Add bookmark' }}"
                        >
                            @if($isBookmarked)
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 fill-current" viewBox="0 0 24 24">
                                    <path d="M17 3H7a2 2 0 0 0-2 2v16l7-3 7 3V5a2 2 0 0 0-2-2z"/>
                                </svg>
                            @else
                                <svg class="w-5 h-5 text-zinc-400 dark:text-zinc-500 hover:text-blue-500 dark:hover:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 3H7a2 2 0 0 0-2 2v16l7-3 7 3V5a2 2 0 0 0-2-2z"/>
                                </svg>
                            @endif
                        </button>
                    @endauth
                    <!-- Bias Badge -->
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium text-white {{ $article->newsSource->getBiasColor() }}">
                        {{ $article->newsSource->getBiasDisplayLabel() }}
                    </span>
                </div>
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
