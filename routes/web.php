<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

use App\Livewire\Home;
use App\Livewire\News;
use App\Livewire\Bookmarks;
use App\Livewire\BookmarkedSources;
use App\Livewire\Sources;

Route::get('/', function () {
    return auth()->check() 
        ? redirect()->route('home') 
        : view('welcome');
});

Route::get('/news', News::class)->name('news');

Route::middleware(['auth'])->group(function () {
    Route::get('/home', Home::class)->name('home');
    Route::get('/bookmarks', Bookmarks::class)->name('bookmarks');
    Route::get('/sources', Sources::class)->name('sources');
    Route::get('/bookmarked-sources', BookmarkedSources::class)->name('bookmarked-sources');
    
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
