<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('map_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon_class', 100)->nullable()->comment('Font Awesome class, ex: fas fa-utensils');
            $table->string('color', 20)->nullable()->comment('Hex color, ex: #e53e3e');
            $table->string('image', 255)->nullable()->comment('Custom icon image path');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Seed default categories
        $defaults = [
            ['name' => 'Restaurant',     'slug' => 'restaurant',     'icon_class' => 'fas fa-utensils',        'color' => '#e53e3e', 'sort_order' => 1],
            ['name' => 'Hôtel',          'slug' => 'hotel',          'icon_class' => 'fas fa-hotel',            'color' => '#38a169', 'sort_order' => 2],
            ['name' => 'Musée',          'slug' => 'museum',         'icon_class' => 'fas fa-landmark',         'color' => '#805ad5', 'sort_order' => 3],
            ['name' => 'Parc',           'slug' => 'park',           'icon_class' => 'fas fa-tree',             'color' => '#d69e2e', 'sort_order' => 4],
            ['name' => 'Plage',          'slug' => 'beach',          'icon_class' => 'fas fa-umbrella-beach',   'color' => '#3182ce', 'sort_order' => 5],
            ['name' => 'Shopping',       'slug' => 'shopping',       'icon_class' => 'fas fa-shopping-bag',     'color' => '#dd6b20', 'sort_order' => 6],
            ['name' => 'Attraction',     'slug' => 'attraction',     'icon_class' => 'fas fa-camera',           'color' => '#e53e3e', 'sort_order' => 7],
            ['name' => 'Historique',     'slug' => 'historic',       'icon_class' => 'fas fa-monument',         'color' => '#718096', 'sort_order' => 8],
            ['name' => 'Religieux',      'slug' => 'religious',      'icon_class' => 'fas fa-church',           'color' => '#a0aec0', 'sort_order' => 9],
            ['name' => 'Naturel',        'slug' => 'natural',        'icon_class' => 'fas fa-mountain',         'color' => '#48bb78', 'sort_order' => 10],
            ['name' => 'Culturel',       'slug' => 'cultural',       'icon_class' => 'fas fa-theater-masks',    'color' => '#ed64a6', 'sort_order' => 11],
            ['name' => 'Sport',          'slug' => 'sport',          'icon_class' => 'fas fa-futbol',           'color' => '#4299e1', 'sort_order' => 12],
            ['name' => 'Divertissement', 'slug' => 'entertainment',  'icon_class' => 'fas fa-film',             'color' => '#ed8936', 'sort_order' => 13],
        ];

        foreach ($defaults as $cat) {
            DB::table('map_categories')->insert($cat);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('map_categories');
    }
};
