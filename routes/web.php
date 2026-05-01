<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\WallController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\SearchController;
use Inertia\Inertia;

// Main feed (requires auth)
Route::get('/', [WallController::class, 'index'])->middleware('auth')->name('home');

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
});

// Protected routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    // Profile
    Route::get('/profile', [ProfileController::class, 'me'])->name('profile');
    Route::get('/profile/{user}', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Settings
    Route::get('/settings', [ProfileController::class, 'settings'])->name('settings');

    // Posts
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');

    // Interactions
    Route::post('/posts/{post}/like', [WallController::class, 'like'])->name('posts.like');
    Route::post('/posts/{post}/bookmark', [WallController::class, 'bookmark'])->name('posts.bookmark');
    Route::post('/posts/{post}/comment', [WallController::class, 'comment'])->name('posts.comment');

    // Follow
    Route::post('/users/{user}/follow', [FollowController::class, 'toggle'])->name('users.follow');

    // Search
    Route::get('/api/search', [SearchController::class, 'index'])->name('search');
});

