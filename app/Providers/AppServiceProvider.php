<?php

namespace App\Providers;

use Illuminate\Foundation\Console\ServeCommand;
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
        // `artisan serve` only hands a whitelist of env vars to the PHP server it spawns. Without
        // TEMP/TMP, PHP on Windows has no writable temp dir and every file upload fails.
        ServeCommand::$passthroughVariables = [...ServeCommand::$passthroughVariables, 'TEMP', 'TMP'];
    }
}
