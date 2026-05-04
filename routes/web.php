<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\WallController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\MessageController;
use Inertia\Inertia;

// Main feed (requires auth)
Route::get('/', [WallController::class, 'index'])->middleware('auth')->name('home');

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:5,1');
    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:5,1');
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
    Route::post('/posts', [PostController::class, 'store'])->middleware('throttle:10,1')->name('posts.store');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');

    // Interactions
    Route::post('/posts/{post}/like', [WallController::class, 'like'])->middleware('throttle:60,1')->name('posts.like');
    Route::post('/posts/{post}/bookmark', [WallController::class, 'bookmark'])->middleware('throttle:60,1')->name('posts.bookmark');
    Route::post('/posts/{post}/comment', [WallController::class, 'comment'])->middleware('throttle:30,1')->name('posts.comment');

    // Follow & Subscribe
    Route::post('/users/{user}/follow', [FollowController::class, 'toggle'])->middleware('throttle:30,1')->name('users.follow');
    Route::post('/users/{user}/subscribe', [FollowController::class, 'subscribe'])->middleware('throttle:10,1')->name('users.subscribe');

    // Messages
    Route::get('/messages/{user}', [MessageController::class, 'show'])->middleware('throttle:60,1')->name('messages.show');
    Route::post('/messages', [MessageController::class, 'store'])->middleware('throttle:20,1')->name('messages.store');
    Route::post('/messages/upload', [MessageController::class, 'upload'])->middleware('throttle:10,1')->name('messages.upload');
    Route::post('/messages/{user}/read', [MessageController::class, 'markRead'])->middleware('throttle:60,1')->name('messages.read');
    Route::put('/messages/{message}', [MessageController::class, 'update'])->middleware('throttle:30,1')->name('messages.update');
    Route::delete('/messages/{message}', [MessageController::class, 'destroy'])->middleware('throttle:30,1')->name('messages.destroy');
    Route::post('/messages/{message}/react', [MessageController::class, 'addReaction'])->middleware('throttle:30,1')->name('messages.react');
    Route::delete('/messages/{message}/react', [MessageController::class, 'removeReaction'])->middleware('throttle:30,1')->name('messages.unreact');

    // Bookmarks
    Route::get('/api/bookmarks', [WallController::class, 'bookmarks'])->middleware('throttle:30,1')->name('bookmarks');

    // Feed / Posts API
    Route::get('/api/posts', [WallController::class, 'postsApi'])->middleware('throttle:60,1')->name('posts.api');

    // Discover
    Route::get('/api/discover', [WallController::class, 'discover'])->middleware('throttle:30,1')->name('discover');

    // Search
    Route::get('/api/search', [SearchController::class, 'index'])->middleware('throttle:30,1')->name('search');
});

// Admin routes
Route::middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])->prefix('admin')->group(function () {
    Route::get('/', [App\Http\Controllers\AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::put('/users/{user}', [App\Http\Controllers\AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::delete('/users/{user}', [App\Http\Controllers\AdminController::class, 'deleteUser'])->name('admin.users.delete');
    Route::delete('/posts/{post}', [App\Http\Controllers\AdminController::class, 'deletePost'])->name('admin.posts.delete');
});

