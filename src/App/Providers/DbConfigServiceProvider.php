<?php

namespace Techbeansjp\LaravelDbConfig\App\Providers;

use Illuminate\Support\ServiceProvider;
use Techbeansjp\LaravelDbConfig\App\Console\Commands\DbConfigCommand;
use Techbeansjp\LaravelDbConfig\App\Services\DbConfigService;

class DbConfigServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                DbConfigCommand::class,
            ]);
        }
    }

    public function register()
    {
        $this->app->singleton('dbconfig', function ($app) {
            return new DbConfigService();
        });

        $this->app->alias('dbconfig', DbConfigService::class);
    }

    public function provides()
    {
        return ['dbconfig', DbConfigService::class];
    }
}