<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800 flex">
        <flux:sidebar sticky stashable class="h-screen min-h-screen border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ route('home') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
                <x-app-logo />
            </a>

            <flux:navlist variant="outline">
                <flux:navlist.group heading="Navigation" class="grid">
                    @auth
                        <flux:navlist.item icon="home" :href="route('home')" :current="request()->routeIs('home')" wire:navigate>Home</flux:navlist.item>
                        <flux:navlist.item icon="bookmark" :href="route('bookmarks')" :current="request()->routeIs('bookmarks')" wire:navigate>Bookmarks</flux:navlist.item>
                        <flux:navlist.item icon="building-library" :href="route('sources')" :current="request()->routeIs('sources')" wire:navigate>Sources</flux:navlist.item>
                        <flux:navlist.item icon="star" :href="route('bookmarked-sources')" :current="request()->routeIs('bookmarked-sources')" wire:navigate>My Sources</flux:navlist.item>
                    @else
                    <flux:navlist.item icon="newspaper" :href="route('news')" :current="request()->routeIs('news')" wire:navigate>News</flux:navlist.item>
                    @endauth
                </flux:navlist.group>
            </flux:navlist>

            <flux:spacer />

            <flux:navlist variant="outline">
                <flux:navlist.item icon="information-circle" href="https://github.com/zanderlewis/balance-news" target="_blank">
                About
                </flux:navlist.item>

                <flux:navlist.item icon="envelope" href="mailto:zander@zanderlewis.dev">
                Contact
                </flux:navlist.item>
            </flux:navlist>

            <!-- Desktop User Menu -->
            @auth
                <flux:dropdown class="hidden lg:block" position="bottom" align="start">
                    <flux:profile
                        :name="auth()->user()->name"
                        :initials="auth()->user()->initials()"
                        icon:trailing="chevrons-up-down"
                    />

                    <flux:menu class="w-[220px]">
                        <flux:menu.radio.group>
                            <div class="p-0 text-sm font-normal">
                                <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                    <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                        <span
                                            class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                        >
                                            {{ auth()->user()->initials() }}
                                        </span>
                                    </span>

                                    <div class="grid flex-1 text-start text-sm leading-tight">
                                        <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                        <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                    </div>
                                </div>
                            </div>
                        </flux:menu.radio.group>

                        <flux:menu.separator />

                        <flux:menu.radio.group>
                            <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>Settings</flux:menu.item>
                        </flux:menu.radio.group>

                        <flux:menu.separator />

                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                                Log Out
                            </flux:menu.item>
                        </form>
                    </flux:menu>
                </flux:dropdown>
            @else
                <div class="hidden lg:block">
                    <flux:button :href="route('login')" wire:navigate variant="primary" size="sm" class="w-full mb-2">
                        Sign In
                    </flux:button>
                    <flux:button :href="route('register')" wire:navigate variant="outline" size="sm" class="w-full">
                        Register
                    </flux:button>
                </div>
            @endauth
        </flux:sidebar>

        <!-- Mobile User Menu -->
        @auth
            <flux:header class="lg:hidden">
                <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

                <flux:spacer />

                <flux:dropdown position="top" align="end">
                    <flux:profile
                        :initials="auth()->user()->initials()"
                        icon-trailing="chevron-down"
                    />

                    <flux:menu>
                        <flux:menu.radio.group>
                            <div class="p-0 text-sm font-normal">
                                <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                    <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                        <span
                                            class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                        >
                                            {{ auth()->user()->initials() }}
                                        </span>
                                    </span>

                                    <div class="grid flex-1 text-start text-sm leading-tight">
                                        <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                        <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                    </div>
                                </div>
                            </div>
                        </flux:menu.radio.group>

                        <flux:menu.separator />

                        <flux:menu.radio.group>
                            <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>Settings</flux:menu.item>
                        </flux:menu.radio.group>

                        <flux:menu.separator />

                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                                Log Out
                            </flux:menu.item>
                        </form>
                    </flux:menu>
                </flux:dropdown>
            </flux:header>
        @else
            <flux:header class="lg:hidden">
                <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

                <flux:spacer />

                <div class="flex items-center gap-2">
                    <flux:button :href="route('login')" wire:navigate variant="ghost" size="sm">
                        Sign In
                    </flux:button>
                    <flux:button :href="route('register')" wire:navigate variant="primary" size="sm">
                        Register
                    </flux:button>
                </div>
            </flux:header>
        @endauth

        <div class="flex-1 min-h-screen">
            {{ $slot }}
        </div>

        @fluxScripts
    </body>
</html>
