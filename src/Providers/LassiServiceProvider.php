<?php

namespace Lassi\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class LassiServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
        Log::debug('boot');
        $this->loadRoutesFrom(__DIR__ .'/../routes/lassi.php');
        $this->loadMigrationsFrom(__DIR__ . '/../migrations');


        $this->publishes([
            __DIR__.'/../config/lassi.php' => config_path('lassi.php'),
        ]);

    }
}
