<?php

namespace Vendor\MapsDataEngine\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Vendor\MapsDataEngine\Console\MapsScanCommand;
use Vendor\MapsDataEngine\Middleware\EnsureMapsDataEngineAdmin;
use Vendor\MapsDataEngine\Models\MapBusinessListing;
use Vendor\MapsDataEngine\Models\MapScanSession;
use Vendor\MapsDataEngine\Policies\MapBusinessListingPolicy;
use Vendor\MapsDataEngine\Policies\MapScanSessionPolicy;
use Vendor\MapsDataEngine\Playwright\PlaywrightRunnerService;
use Vendor\MapsDataEngine\Proxy\ProxyManagerService;
use Vendor\MapsDataEngine\Services\MapsDataEngineManager;
use Vendor\MapsDataEngine\Services\Repositories\MapBusinessListingRepository;
use Vendor\MapsDataEngine\Services\Repositories\MapScanSessionRepository;

class MapsDataEngineServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/maps-data-engine.php', 'maps-data-engine');

        $this->app->singleton(MapsDataEngineManager::class);
        $this->app->singleton(PlaywrightRunnerService::class);
        $this->app->singleton(ProxyManagerService::class);
        $this->app->singleton(MapBusinessListingRepository::class);
        $this->app->singleton(MapScanSessionRepository::class);
    }

    public function boot(): void
    {
        $this->app->make(Router::class)->aliasMiddleware('maps-data-engine.admin', EnsureMapsDataEngineAdmin::class);

        Gate::policy(MapBusinessListing::class, MapBusinessListingPolicy::class);
        Gate::policy(MapScanSession::class, MapScanSessionPolicy::class);

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'maps-data-engine');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');

        if ($this->app->runningInConsole()) {
            $this->commands([
                MapsScanCommand::class,
            ]);

            $this->publishes([
                __DIR__ . '/../config/maps-data-engine.php' => config_path('maps-data-engine.php'),
            ], 'maps-data-engine-config');

            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'maps-data-engine-migrations');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/maps-data-engine'),
            ], 'maps-data-engine-views');

            $this->publishes([
                __DIR__ . '/../resources/assets' => public_path('vendor/maps-data-engine'),
            ], 'maps-data-engine-assets');
        }
    }
}
