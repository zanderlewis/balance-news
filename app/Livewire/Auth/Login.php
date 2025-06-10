<?php

namespace App\Livewire\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class Login extends Component
{
    #[Validate('required|string')]
    public string $login = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    /**
     * Handle an incoming authentication request.
     */
    public function authenticate(): void
    {
        try {
            $this->validate();

            if (!$this->ensureIsNotRateLimited()) {
                return;
            }

            $credentials = filter_var($this->login, FILTER_VALIDATE_EMAIL) 
                ? ['email' => $this->login, 'password' => $this->password]
                : ['username' => $this->login, 'password' => $this->password];

            if (! Auth::attempt($credentials, $this->remember)) {
                RateLimiter::hit($this->throttleKey());

                $this->addError('login', __('auth.failed'));
                return;
            }

            RateLimiter::clear($this->throttleKey());
            Session::regenerate();

            $this->redirectRoute('home');
            
        } catch (\Exception $e) {
            $this->addError('login', 'An error occurred during login. Please try again.');
            logger()->error('Login error: ' . $e->getMessage());
        }
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): bool
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return true;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        $this->addError('login', __('auth.throttle', [
            'seconds' => $seconds,
            'minutes' => ceil($seconds / 60),
        ]));
        
        return false;
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->login).'|'.request()->ip());
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
