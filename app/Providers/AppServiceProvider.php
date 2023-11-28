<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Models\Agent;
use App\Factories\AgentFactory;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // If an agent is called, load the specific agent class, not the base class
        Route::bind('agent', function ($value) {
            // $value is the id of the agent, coming from the route

            if ($value) {
                return AgentFactory::load($value);
            }
        });
    }
}
