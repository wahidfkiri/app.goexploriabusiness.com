<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('places', function (Blueprint $table) {
            if (!Schema::hasColumn('places', 'map_category_id')) {
                $table->foreignId('map_category_id')->nullable()->constrained('map_categories')->nullOnDelete()->after('country_id');
            }
        });

        // Migrate existing category strings to new FK
        $categories = DB::table('map_categories')->pluck('id', 'slug');
        $places = DB::table('places')->whereNotNull('category')->get(['id', 'category']);
        foreach ($places as $place) {
            $mapCategoryId = $categories[$place->category] ?? null;
            if ($mapCategoryId) {
                DB::table('places')->where('id', $place->id)->update(['map_category_id' => $mapCategoryId]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('places', function (Blueprint $table) {
            $table->dropForeign(['map_category_id']);
            $table->dropColumn('map_category_id');
        });
    }
};
