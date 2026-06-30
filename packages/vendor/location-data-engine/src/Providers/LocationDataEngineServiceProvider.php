<?php

namespace Vendor\LocationDataEngine\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Vendor\LocationDataEngine\Console\ScanLocationsCommand;
use Vendor\LocationDataEngine\Contracts\BusinessLocationRepositoryInterface;
use Vendor\LocationDataEngine\Contracts\PlacesClientInterface;
use Vendor\LocationDataEngine\Contracts\ScanSessionRepositoryInterface;
use Vendor\LocationDataEngine\Middleware\EnsureLocationEngineAdmin;
use Vendor\LocationDataEngine\Models\BusinessLocation;
use Vendor\LocationDataEngine\Models\ScanSession;
use Vendor\LocationDataEngine\Policies\BusinessLocationPolicy;
use Vendor\LocationDataEngine\Policies\ScanSessionPolicy;
use Vendor\LocationDataEngine\Services\GooglePlacesClient;
use Vendor\LocationDataEngine\Services\LocationDataEngineManager;
use Vendor\LocationDataEngine\Services\Repositories\BusinessLocationRepository;
use Vendor\LocationDataEngine\Services\Repositories\ScanSessionRepository;

class LocationDataEngineServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/location-data-engine.php', 'location-data-engine');

        $this->app->singleton(PlacesClientInterface::class, GooglePlacesClient::class);
        $this->app->singleton(BusinessLocationRepositoryInterface::class, BusinessLocationRepository::class);
        $this->app->singleton(ScanSessionRepositoryInterface::class, ScanSessionRepository::class);
        $this->app->singleton('location-data-engine', LocationDataEngineManager::class);
    }

    public function boot(Router $router): void
    {
        $router->aliasMiddleware('location-data-engine.admin', EnsureLocationEngineAdmin::class);
        Gate::policy(BusinessLocation::class, BusinessLocationPolicy::class);
        Gate::policy(ScanSession::class, ScanSessionPolicy::class);

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'location-data-engine');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'location-data-engine');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');

        if ($this->app->runningInConsole()) {
            $this->commands([
                ScanLocationsCommand::class,
            ]);

            $this->publishes([
                __DIR__ . '/../config/location-data-engine.php' => config_path('location-data-engine.php'),
            ], 'location-data-engine-config');

            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'location-data-engine-migrations');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/location-data-engine'),
            ], 'location-data-engine-views');

            $this->publishes([
                __DIR__ . '/../resources/assets' => public_path('vendor/location-data-engine'),
            ], 'location-data-engine-assets');
        }
    }
}
