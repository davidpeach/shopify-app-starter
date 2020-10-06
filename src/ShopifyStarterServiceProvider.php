<?php

namespace DavidPeach\ShopifyStarter;

use DavidPeach\ShopifyStarter\Commands\ShopifyStarterCommand;
use Illuminate\Support\ServiceProvider;

class ShopifyStarterServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerArtisanCommand();
    }

    private function registerArtisanCommand()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ShopifyStarterCommand::class
            ]);
        }
    }
}
