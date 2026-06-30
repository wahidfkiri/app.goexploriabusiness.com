<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Vendor\MapMarker\Database\Seeders\MapMarkerGeoSeeder;

class MapMarkerGeoPackageSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(MapMarkerGeoSeeder::class);
    }
}
