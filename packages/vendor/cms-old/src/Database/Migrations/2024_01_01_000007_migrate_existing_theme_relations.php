<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'cms';

    public function up(): void
    {
        if (!Schema::connection('cms')->hasColumn('cms_themes', 'etablissement_id')) {
            return;
        }

        // Migrer les relations existantes
        $themes = DB::connection('cms')->table('cms_themes')->get();
        
        foreach ($themes as $theme) {
            if (!empty($theme->etablissement_id)) {
                DB::connection('cms')->table('cms_etablissement_theme')->updateOrInsert(
                    [
                        'etablissement_id' => $theme->etablissement_id,
                        'theme_id' => $theme->id,
                    ],
                    [
                        'is_active' => (bool) ($theme->is_active ?? false),
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        DB::connection('cms')->table('cms_etablissement_theme')->truncate();
    }
};
