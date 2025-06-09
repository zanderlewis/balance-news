<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <!-- Public Header -->
        <flux:header class="border-b border-zinc-200 dark:border-zinc-700">
            <a href="{{ route('home') }}" class="flex items-center space-x-2" wire:navigate>
                <x-app-logo />
            </a>

            <flux:spacer />

            @auth
                <!-- Authenticated User Menu -->
                <flux:navlist variant="outline" class="hidden md:flex">
                    <flux:navlist.item icon="home" :href="route('home')" wire:navigate>
                        Home
                    </flux:navlist.item>
                </flux:navlist>

                <flux:dropdown position="bottom" align="end">
                    <flux:profile
                        :name="auth()->user()->name"
                        :initials="auth()->user()->initials()"
                        icon-trailing="chevron-down"
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
                <!-- Guest Menu -->
                <div class="flex items-center gap-4">
                    <flux:button href="{{ route('login') }}" variant="ghost" wire:navigate>
                        Sign In
                    </flux:button>
                    <flux:button href="{{ route('register') }}" variant="primary" wire:navigate>
                        Get Started
                    </flux:button>
                </div>
            @endauth
        </flux:header>

        <!-- Main Content -->
        <flux:main class="min-h-screen">
            {{ $slot }}
        </flux:main>
    </body>
</html>
