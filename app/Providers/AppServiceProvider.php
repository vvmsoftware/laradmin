<?php

namespace App\Providers;

use App\Services\DockerManager;
use App\Services\DockerEventBroadcaster;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register DockerManager as a singleton service
        $this->app->singleton(DockerManager::class, function ($app) {
            return new DockerManager();
        });
        
        // Register DockerEventBroadcaster as a singleton service
        $this->app->singleton(DockerEventBroadcaster::class, function ($app) {
            return new DockerEventBroadcaster(
                $app->make(DockerManager::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
