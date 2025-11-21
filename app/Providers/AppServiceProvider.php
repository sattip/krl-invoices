<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        // Define super-admin gate for menu visibility
        Gate::define('super-admin', function ($user) {
            return $user->isSuperAdmin();
        });

        // Include impersonation banner in all AdminLTE views
        Blade::include('partials.impersonation-banner', 'impersonationBanner');
    }
}
