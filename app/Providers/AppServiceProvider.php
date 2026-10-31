<?php

namespace App\Providers;

use App\Support\SiteContent;
use App\Support\SiteSettings;
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
        // Admin-edited settings override the defaults in config/zenray.php.
        SiteSettings::apply();

        // Website content (equipment, services, slides, gallery…) comes from the database.
        SiteContent::apply();
    }
}
