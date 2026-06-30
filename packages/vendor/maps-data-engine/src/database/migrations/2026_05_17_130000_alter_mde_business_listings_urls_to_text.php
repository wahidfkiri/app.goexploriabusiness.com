<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE mde_business_listings MODIFY website TEXT NULL');
        DB::statement('ALTER TABLE mde_business_listings MODIFY google_maps_url TEXT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE mde_business_listings MODIFY website VARCHAR(191) NULL');
        DB::statement('ALTER TABLE mde_business_listings MODIFY google_maps_url VARCHAR(191) NULL');
    }
};
