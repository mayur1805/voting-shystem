<?php

namespace App\Providers;

use Illuminate\Support\Facades\File;
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
        $path = storage_path('framework/cache/persistent');

        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true, true);
        }
    }
}
