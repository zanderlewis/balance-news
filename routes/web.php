<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Volt;

use App\Livewire\Home;

Route::get('/', function () {
    return auth()->check() 
        ? redirect()->route('home') 
        : view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/home', Home::class)->name('home');
    
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
