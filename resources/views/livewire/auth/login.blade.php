<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Log in to your account')" :description="__('Enter your email or username and password below to log in')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />
    <form wire:submit="authenticate" class="flex flex-col gap-6">
        <!-- Email or Username -->
        <flux:input
            wire:model="login"
            :label="__('Email or Username')"
            type="text"
            required
            autofocus
            autocomplete="username"
            placeholder="email@example.com or username"
        />

        <!-- Password -->
        <div class="relative">
            <flux:input
                wire:model="password"
                :label="__('Password')"
                type="password"
                required
                autocomplete="current-password"
                :placeholder="__('Password')"
                viewable
            />

            @if (Route::has('password.request'))
                <flux:link class="absolute end-0 top-0 text-sm" :href="route('password.request')" wire:navigate>
                    Forgot your password?
                </flux:link>
            @endif
        </div>

        <!-- Remember Me -->
        <flux:checkbox wire:model="remember" checked :label="__('Remember me')" />
        
        <div class="flex items-center justify-end">
            <flux:button wire:click="authenticate" variant="primary" type="button" class="w-full">Log in</flux:button>
        </div>
    </form>
    @if (Route::has('register'))
        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            Don't have an account?
            <flux:link :href="route('register')" wire:navigate>Sign up</flux:link>
        </div>
    @endif
</div>
