<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('etablissements')) {
            return;
        }

        Schema::table('etablissements', function (Blueprint $table) {
            if (! Schema::hasColumn('etablissements', 'primary_activity_id')) {
                $table->foreignId('primary_activity_id')
                    ->nullable()
                    ->after('secteur_id')
                    ->constrained('activities')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('etablissements', 'other_activity_label')) {
                $table->string('other_activity_label', 255)
                    ->nullable()
                    ->after('primary_activity_id');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('etablissements')) {
            return;
        }

        Schema::table('etablissements', function (Blueprint $table) {
            if (Schema::hasColumn('etablissements', 'primary_activity_id')) {
                $table->dropConstrainedForeignId('primary_activity_id');
            }

            if (Schema::hasColumn('etablissements', 'other_activity_label')) {
                $table->dropColumn('other_activity_label');
            }
        });
    }
};
