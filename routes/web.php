<?php

use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SiteSettingsController;
use App\Http\Controllers\FaqController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

// Public FAQ page
Route::get('/faq', [FaqController::class, 'public'])->name('faq.public');

// Public About Us page
Route::get('/about-us', [SiteSettingsController::class, 'aboutUs'])->name('about-us');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['verified'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // User management routes (admin and moderators only)
    Route::get('/users/search', [\App\Http\Controllers\UserController::class, 'search'])->name('users.search')->middleware(['verified', 'role:admin,moderator']);
    Route::resource('/users', \App\Http\Controllers\UserController::class)->middleware(['verified', 'role:admin,moderator']);

    // Site Settings routes (admin only)
    Route::middleware(['verified', 'role:admin'])->group(function () {
        Route::get('/site-settings', [SiteSettingsController::class, 'index'])->name('site-settings.index');
        Route::put('/site-settings/about-us', [SiteSettingsController::class, 'updateAboutUs'])->name('site-settings.about-us.update');
        Route::put('/site-settings/authentication', [SiteSettingsController::class, 'updateAuthentication'])->name('site-settings.authentication.update');
        
        // FAQ management
        Route::get('/faqs/search', [FaqController::class, 'search'])->name('faqs.search');
        Route::resource('/faqs', FaqController::class)->except(['show', 'create', 'edit', 'index']);
    });
});

require __DIR__.'/auth.php';
