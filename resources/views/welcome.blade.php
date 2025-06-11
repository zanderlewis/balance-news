<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-900">
        <!-- Navigation Header -->
        <flux:header class="border-b border-zinc-200 dark:border-zinc-700 bg-white/80 dark:bg-zinc-900/80 backdrop-blur-md">
            <a href="/" class="flex items-center space-x-2">
                <x-app-logo />
            </a>

            <flux:spacer />

            @auth
                <div class="flex items-center gap-4">
                    <flux:button href="{{ route('home') }}" variant="ghost" wire:navigate>
                        Dashboard
                    </flux:button>
                    <flux:dropdown position="bottom" align="end">
                        <flux:profile
                            :name="auth()->user()->name"
                            :initials="auth()->user()->initials()"
                            icon-trailing="chevron-down"
                        />
                        <flux:menu class="w-[220px]">
                            <flux:menu.item :href="route('home')" icon="home" wire:navigate>Home</flux:menu.item>
                            <flux:menu.item :href="route('news')" icon="newspaper" wire:navigate>News</flux:menu.item>
                            <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>Settings</flux:menu.item>
                            <flux:menu.separator />
                            <form method="POST" action="{{ route('logout') }}" class="w-full">
                                @csrf
                                <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                                    Log Out
                                </flux:menu.item>
                            </form>
                        </flux:menu>
                    </flux:dropdown>
                </div>
            @else
                <div class="flex items-center gap-4">
                    <flux:button href="{{ route('news') }}" variant="ghost" wire:navigate>
                        News
                    </flux:button>
                    <flux:button href="{{ route('login') }}" variant="ghost" wire:navigate>
                        Sign In
                    </flux:button>
                    <flux:button href="{{ route('register') }}" variant="primary" wire:navigate>
                        Get Started
                    </flux:button>
                </div>
            @endauth
        </flux:header>

        <!-- Hero Section -->
        <section class="relative overflow-hidden bg-gradient-to-br from-blue-50 via-white to-purple-50 dark:from-zinc-900 dark:via-zinc-800 dark:to-zinc-900 py-24 sm:py-32">
            <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%239C92AC" fill-opacity="0.05"%3E%3Ccircle cx="30" cy="30" r="2"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')] dark:bg-[url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%23ffffff" fill-opacity="0.02"%3E%3Ccircle cx="30" cy="30" r="2"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')]"></div>
            
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h1 class="text-4xl sm:text-6xl lg:text-7xl font-bold tracking-tight text-zinc-900 dark:text-white mb-6">
                        Balance News
                    </h1>
                    <p class="text-xl sm:text-2xl text-zinc-600 dark:text-zinc-300 mb-8 max-w-3xl mx-auto">
                        See the <span class="font-semibold text-blue-600 dark:text-blue-400">full picture</span> of every story. 
                        Compare news coverage across the entire political spectrum.
                    </p>
                    
                    @guest
                        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-12">
                            <flux:button href="{{ route('register') }}" variant="primary" class="w-full sm:w-auto px-8 py-3 text-lg">
                                Start Reading Balanced News
                            </flux:button>
                            <flux:button href="{{ route('news') }}" variant="outline" class="w-full sm:w-auto px-8 py-3 text-lg" wire:navigate>
                                Browse News
                            </flux:button>
                            <flux:button href="{{ route('login') }}" variant="ghost" class="w-full sm:w-auto px-8 py-3 text-lg">
                                Sign In
                            </flux:button>
                        </div>
                    @else
                        <div class="mb-12">
                            <flux:button href="{{ route('home') }}" variant="primary" class="px-8 py-3 text-lg" wire:navigate>
                                Go to Dashboard
                            </flux:button>
                        </div>
                    @endguest
                    
                    <!-- Visual Demo -->
                    <div class="max-w-4xl mx-auto">
                        <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-2xl border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                            <div class="p-6 sm:p-8">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <!-- Left Perspective -->
                                    <div class="space-y-3">
                                        <div class="flex items-center gap-2">
                                            <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                                            <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Left Perspective</span>
                                        </div>
                                        <div class="space-y-2">
                                            <div class="h-4 bg-blue-100 dark:bg-blue-900/30 rounded"></div>
                                            <div class="h-4 bg-blue-100 dark:bg-blue-900/30 rounded w-3/4"></div>
                                            <div class="h-4 bg-blue-100 dark:bg-blue-900/30 rounded w-1/2"></div>
                                        </div>
                                    </div>
                                    
                                    <!-- Center Perspective -->
                                    <div class="space-y-3">
                                        <div class="flex items-center gap-2">
                                            <div class="w-3 h-3 bg-gray-500 rounded-full"></div>
                                            <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Center Perspective</span>
                                        </div>
                                        <div class="space-y-2">
                                            <div class="h-4 bg-gray-100 dark:bg-gray-900/30 rounded"></div>
                                            <div class="h-4 bg-gray-100 dark:bg-gray-900/30 rounded w-4/5"></div>
                                            <div class="h-4 bg-gray-100 dark:bg-gray-900/30 rounded w-2/3"></div>
                                        </div>
                                    </div>
                                    
                                    <!-- Right Perspective -->
                                    <div class="space-y-3">
                                        <div class="flex items-center gap-2">
                                            <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                                            <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Right Perspective</span>
                                        </div>
                                        <div class="space-y-2">
                                            <div class="h-4 bg-red-100 dark:bg-red-900/30 rounded"></div>
                                            <div class="h-4 bg-red-100 dark:bg-red-900/30 rounded w-5/6"></div>
                                            <div class="h-4 bg-red-100 dark:bg-red-900/30 rounded w-3/5"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="py-24 bg-white dark:bg-zinc-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl sm:text-4xl font-bold text-zinc-900 dark:text-white mb-4">
                        Why Balance News?
                    </h2>
                    <p class="text-lg text-zinc-600 dark:text-zinc-300 max-w-2xl mx-auto">
                        In an era of information silos, we bring you the complete story from all perspectives.
                    </p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Feature 1 -->
                    <div class="text-center group">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 dark:bg-blue-900/30 rounded-2xl mb-6 group-hover:scale-110 transition-transform duration-200">
                            <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-zinc-900 dark:text-white mb-3">Multi-Perspective Analysis</h3>
                        <p class="text-zinc-600 dark:text-zinc-300">
                            Compare how the same story is covered across left, center, and right-leaning news sources.
                        </p>
                    </div>
                    
                    <!-- Feature 2 -->
                    <div class="text-center group">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 dark:bg-green-900/30 rounded-2xl mb-6 group-hover:scale-110 transition-transform duration-200">
                            <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-zinc-900 dark:text-white mb-3">Smart Search & Filtering</h3>
                        <p class="text-zinc-600 dark:text-zinc-300">
                            Find relevant stories quickly with powerful search and filter by bias, source, or time range.
                        </p>
                    </div>
                    
                    <!-- Feature 3 -->
                    <div class="text-center group">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-purple-100 dark:bg-purple-900/30 rounded-2xl mb-6 group-hover:scale-110 transition-transform duration-200">
                            <svg class="w-8 h-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-zinc-900 dark:text-white mb-3">Hourly Updates</h3>
                        <p class="text-zinc-600 dark:text-zinc-300">
                            Stay informed with the latest news from trusted sources, updated every hour.
                        </p>
                    </div>
                    
                    <!-- Feature 4 -->
                    <div class="text-center group">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-red-100 dark:bg-red-900/30 rounded-2xl mb-6 group-hover:scale-110 transition-transform duration-200">
                            <svg class="w-8 h-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-zinc-900 dark:text-white mb-3">Bias Transparency</h3>
                        <p class="text-zinc-600 dark:text-zinc-300">
                            Each source is clearly labeled with its bias rating so you can make informed reading choices.
                        </p>
                    </div>
                    
                    <!-- Feature 5 -->
                    <div class="text-center group">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-yellow-100 dark:bg-yellow-900/30 rounded-2xl mb-6 group-hover:scale-110 transition-transform duration-200">
                            <svg class="w-8 h-8 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-zinc-900 dark:text-white mb-3">Save & Organize</h3>
                        <p class="text-zinc-600 dark:text-zinc-300">
                            Bookmark important articles or news sources and organize your reading list for later reference.
                        </p>
                    </div>
                    
                    <!-- Feature 6 -->
                    <div class="text-center group">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-indigo-100 dark:bg-indigo-900/30 rounded-2xl mb-6 group-hover:scale-110 transition-transform duration-200">
                            <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-zinc-900 dark:text-white mb-3">Mobile Optimized</h3>
                        <p class="text-zinc-600 dark:text-zinc-300">
                            Read balanced news anywhere with our responsive design that works on all devices.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Call to Action Section -->
        <section class="py-24 bg-gradient-to-r from-blue-600 to-purple-600 dark:from-blue-800 dark:to-purple-800">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl sm:text-4xl font-bold text-white mb-6">
                    Ready to See the Full Picture?
                </h2>
                <p class="text-lg sm:text-xl text-blue-100 mb-8 max-w-2xl mx-auto">
                    Join readers who are taking control of their news consumption.
                </p>
                
                @guest
                    <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                        <flux:button href="{{ route('register') }}" variant="primary" class="w-full sm:w-auto bg-white text-blue-600 hover:bg-gray-50 px-8 py-3 text-lg">
                            Get Started Free
                        </flux:button>
                        <flux:button href="{{ route('login') }}" variant="ghost" class="w-full sm:w-auto text-white border-white hover:bg-white/10 px-8 py-3 text-lg">
                            Sign In
                        </flux:button>
                    </div>
                @else
                    <flux:button href="{{ route('home') }}" variant="primary" class="bg-white text-blue-600 hover:bg-gray-50 px-8 py-3 text-lg" wire:navigate>
                        Continue to Dashboard
                    </flux:button>
                @endguest
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-zinc-900 dark:bg-zinc-950 text-white py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div>
                        <div class="flex items-center space-x-2 mb-4">
                            <x-app-logo />
                        </div>
                        <p class="text-zinc-400 mb-4">
                            Bringing balance to news consumption by providing comprehensive coverage across the political spectrum.
                        </p>
                    </div>
                    
                    <div>
                        <h3 class="font-semibold mb-4">Product</h3>
                        <ul class="space-y-2 text-zinc-400">
                            @auth
                                <li><a href="{{ route('home') }}" class="hover:text-white transition-colors" wire:navigate>Dashboard</a></li>
                                <li><a href="{{ route('settings.profile') }}" class="hover:text-white transition-colors" wire:navigate>Settings</a></li>
                            @else
                                <li><a href="{{ route('register') }}" class="hover:text-white transition-colors" wire:navigate>Sign Up</a></li>
                                <li><a href="{{ route('login') }}" class="hover:text-white transition-colors" wire:navigate>Sign In</a></li>
                            @endauth
                        </ul>
                    </div>
                </div>
                
                <div class="border-t border-zinc-800 mt-12 pt-8 text-center text-zinc-400">
                    <p>&copy; {{ date('Y') }} Zander Lewis. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </body>
</html>