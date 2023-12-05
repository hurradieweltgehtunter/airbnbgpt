<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Inertia\Inertia;
/**
 * This Provider handles all shared data via Inertia
 */

class SharedDataServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        // Share Google api key on housing routes (/housings/...)
        Inertia::share([
            'google_api_key' => function () {
                $currentRouteName = Route::currentRouteName();

                if (Str::startsWith($currentRouteName, 'housings.')) {
                    return env('GOOGLE_API_KEY');
                }

                return null;
            },
        ]);
    }
}
