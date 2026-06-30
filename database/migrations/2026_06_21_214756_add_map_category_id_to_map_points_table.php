<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('map_points', function (Blueprint $table) {
            $table->unsignedBigInteger('map_category_id')->nullable()->after('category');
            $table->foreign('map_category_id')->references('id')->on('map_categories')->nullOnDelete();
        });

        $updated = DB::table('map_points')
            ->join('map_categories', 'map_points.category', '=', 'map_categories.slug')
            ->update(['map_points.map_category_id' => DB::raw('map_categories.id')]);

        $unmatched = DB::table('map_points')->whereNull('map_category_id')->count();
        if ($unmatched > 0) {
            echo "Warning: $unmatched map_points have no matching map_category\n";
        }
    }

    public function down(): void
    {
        Schema::table('map_points', function (Blueprint $table) {
            $table->dropForeign(['map_category_id']);
            $table->dropColumn('map_category_id');
        });
    }
};
