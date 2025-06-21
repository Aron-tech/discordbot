<?php

namespace App\Providers;

use App\Gates\PermissionGate;
use App\Http\Middleware\CheckPermissionMiddleware;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
            $event->extendSocialite('discord', \SocialiteProviders\Discord\Provider::class);
        });

        PermissionGate::register();

        Route::aliasMiddleware('check.permission', CheckPermissionMiddleware::class);
    }
}
