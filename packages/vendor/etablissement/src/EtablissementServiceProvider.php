<?php

namespace Vendor\Etablissement;

use Illuminate\Support\ServiceProvider;

class EtablissementServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/Views', 'etablissement');
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        $this->publishes([
            __DIR__ . '/../resources/assets' => public_path('vendor/etablissement'),
        ], 'etablissement-assets');
    }

    public function register(): void
    {
        // Register package services here when needed.
    }
}
