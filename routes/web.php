<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\WelcomeController;
use App\Http\Middleware\ValidateGuildSelectionMiddleware;
use App\Http\Middleware\VerifyDeveloperMiddleware;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

Route::view('/documentation', 'documentation')->name('documentation');
Route::view('/terms-of-service', 'term-service')->name('terms.of.service');
Route::view('/privacy-policy', 'privacy')->name('privacy.policy');

Route::get('login', [AuthController::class, 'redirect'])->name('login');

Route::get('login/discord/callback', [AuthController::class, 'callback']);

Route::get('/cache-clear', function () {
    \Artisan::call('cache:clear');
    return 'Cache cleared successfully.';
})->name('cache.clear')->middleware(['auth', VerifyDeveloperMiddleware::class]);

Route::middleware(['auth'])->group(function () {

    Volt::route('/guild-selector', 'guild.selector')->name('guild.selector');

    Volt::route('admin/install', 'admin.settings')->name('admin.install');

    Route::middleware(ValidateGuildSelectionMiddleware::class)->group(function () {

        Volt::route('/dashboard', 'pages.dashboard')->name('dashboard');
        Volt::route('/toplist', 'pages.toplist')->name('toplist');

        Route::prefix('admin/')->name('admin.')->group(function () {

            Volt::route('settings', 'admin.settings')->name('settings')->middleware('check.permission:view_settings');

            Volt::route('duty/logs', 'admin.logs')->name('logs')->middleware('check.permission:view_duty_logs');

            Volt::route('/duty/active', 'admin.active')->name('duty.active')->middleware('check.permission:view_duty_active');

            Volt::route('panel', 'admin.panel')->name('panel')->middleware('check.permission:view_admin_panel');
        });
    });

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

});
