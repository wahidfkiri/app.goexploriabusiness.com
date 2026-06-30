<?php

namespace Vendor\MapsDataEngine\Database\Seeders;

use Illuminate\Database\Seeder;
use Vendor\MapsDataEngine\Models\MapProxyEndpoint;

class MapsDataEngineSeeder extends Seeder
{
    public function run(): void
    {
        if (! env('MAPS_DATA_ENGINE_CREATE_SAMPLE_PROXY', false)) {
            return;
        }

        MapProxyEndpoint::query()->firstOrCreate(
            ['label' => 'Sample proxy endpoint'],
            [
                'host' => '127.0.0.1',
                'port' => 8080,
                'scheme' => 'http',
                'is_active' => false,
                'meta' => [
                    'note' => 'Replace this placeholder with a real proxy before enabling scraping.',
                ],
            ]
        );
    }
}
