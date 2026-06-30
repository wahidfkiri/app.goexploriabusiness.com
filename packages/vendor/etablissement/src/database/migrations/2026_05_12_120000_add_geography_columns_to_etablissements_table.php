<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('etablissements')) {
            return;
        }

        Schema::table('etablissements', function (Blueprint $table) {
            if (!Schema::hasColumn('etablissements', 'continent_id')) {
                $table->foreignId('continent_id')->nullable()->constrained('continents')->nullOnDelete();
            }
            if (!Schema::hasColumn('etablissements', 'country_id')) {
                $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
            }
            if (!Schema::hasColumn('etablissements', 'province_id')) {
                $table->foreignId('province_id')->nullable()->constrained('provinces')->nullOnDelete();
            }
            if (!Schema::hasColumn('etablissements', 'region_id')) {
                $table->foreignId('region_id')->nullable()->constrained('regions')->nullOnDelete();
            }
            if (!Schema::hasColumn('etablissements', 'ville_id')) {
                $table->foreignId('ville_id')->nullable()->constrained('villes')->nullOnDelete();
            }
            if (!Schema::hasColumn('etablissements', 'secteur_id')) {
                $table->foreignId('secteur_id')->nullable()->constrained('secteurs')->nullOnDelete();
            }
        });

    }

    public function down(): void
    {
        if (!Schema::hasTable('etablissements')) {
            return;
        }

        Schema::table('etablissements', function (Blueprint $table) {
            foreach (['continent_id', 'country_id', 'province_id', 'region_id', 'ville_id', 'secteur_id'] as $column) {
                if (Schema::hasColumn('etablissements', $column)) {
                    try {
                        $table->dropForeign([$column]);
                    } catch (Throwable $e) {
                        // Ignore if foreign key name differs or is already absent.
                    }
                }
            }

            $columnsToDrop = array_values(array_filter([
                Schema::hasColumn('etablissements', 'continent_id') ? 'continent_id' : null,
                Schema::hasColumn('etablissements', 'country_id') ? 'country_id' : null,
                Schema::hasColumn('etablissements', 'province_id') ? 'province_id' : null,
                Schema::hasColumn('etablissements', 'region_id') ? 'region_id' : null,
                Schema::hasColumn('etablissements', 'ville_id') ? 'ville_id' : null,
                Schema::hasColumn('etablissements', 'secteur_id') ? 'secteur_id' : null,
            ]));

            if ($columnsToDrop !== []) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
