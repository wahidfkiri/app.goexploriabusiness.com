<?php

namespace Vendor\LocationDataEngine\Database\Seeders;

use Illuminate\Database\Seeder;
use Vendor\LocationDataEngine\Models\ScanSession;

class LocationDataEngineDemoSeeder extends Seeder
{
    public function run(): void
    {
        ScanSession::query()->firstOrCreate(
            ['uuid' => 'demo-location-data-engine-session'],
            [
                'status' => 'completed',
                'category' => 'tourism',
                'target_label' => 'Quebec demo region',
                'radius' => 25000,
                'limit' => 50,
                'grid_precision' => 4,
                'results_count' => 0,
                'progress_percentage' => 100,
                'started_at' => now()->subHour(),
                'finished_at' => now()->subMinutes(30),
            ]
        );
    }
}
