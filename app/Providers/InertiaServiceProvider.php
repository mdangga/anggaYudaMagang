<?php

namespace App\Providers;

use App\Models\ProfileWeb;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class InertiaServiceProvider extends ServiceProvider
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
    public function boot(): void
    {
        Inertia::share([
            'profile_web' => function () {
                return Cache::rememberForever('profile_web', function () {
                    return ProfileWeb::first();
                });
            }
        ]);
    }
}
