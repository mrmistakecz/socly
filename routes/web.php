<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\WallController;
use App\Http\Controllers\ProfileController;
use Inertia\Inertia;

// Public routes
Route::get('/', [WallController::class, 'index'])->name('home');

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
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update']);
});

// API routes for real-time features
Route::middleware('auth')->prefix('api')->group(function () {
    Route::get('/posts', [WallController::class, 'posts']);
    Route::post('/posts/{post}/like', [WallController::class, 'like']);
    Route::post('/posts/{post}/comment', [WallController::class, 'comment']);
});

