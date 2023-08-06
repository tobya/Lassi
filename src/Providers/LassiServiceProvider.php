<?php

namespace Lassi\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Lassi\Commands\LassiTestCommand;
use Lassi\Client\Commands\LassiSyncCommand;


class LassiServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
         // merge package and app config
          $this->mergeConfigFrom(
                __DIR__.'/../config/lassi.php', 'lassi'
            );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

        $this->loadRoutesFrom(__DIR__ .'/../routes/lassi.php');
        $this->loadMigrationsFrom(__DIR__ . '/../migrations');

        if ($this->app->runningInConsole()){
            $this->commands([
                LassiSyncCommand::class,
                LassiTestCommand::class,
            ]);
        }

        $this->publishes([
            __DIR__.'/../config/lassi.php' => config_path('lassi.php'),
            __DIR__.'/../migrations/' => database_path('migrations'),
        ], 'lassi');

    }
}
