<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\View;
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
        // All site views receive $site — the key/value settings managed in the
        // admin panel. Guarded so console commands work before migrations run.
        try {
            View::share('site', Setting::allCached());
        } catch (\Throwable) {
            View::share('site', []);
        }
    }
}
