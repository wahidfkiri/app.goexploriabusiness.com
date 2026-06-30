<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'cms';

    public function up(): void
    {
        Schema::connection('cms')->table('cms_media', function (Blueprint $table) {
            if (!Schema::connection('cms')->hasColumn('cms_media', 'video_url')) {
                $table->text('video_url')->nullable()->after('type');
            }

            if (!Schema::connection('cms')->hasColumn('cms_media', 'continent_id')) {
                $table->unsignedBigInteger('continent_id')->nullable()->index()->after('metadata');
            }
            if (!Schema::connection('cms')->hasColumn('cms_media', 'country_id')) {
                $table->unsignedBigInteger('country_id')->nullable()->index()->after('continent_id');
            }
            if (!Schema::connection('cms')->hasColumn('cms_media', 'province_id')) {
                $table->unsignedBigInteger('province_id')->nullable()->index()->after('country_id');
            }
            if (!Schema::connection('cms')->hasColumn('cms_media', 'region_id')) {
                $table->unsignedBigInteger('region_id')->nullable()->index()->after('province_id');
            }
            if (!Schema::connection('cms')->hasColumn('cms_media', 'ville_id')) {
                $table->unsignedBigInteger('ville_id')->nullable()->index()->after('region_id');
            }
            if (!Schema::connection('cms')->hasColumn('cms_media', 'secteur_id')) {
                $table->unsignedBigInteger('secteur_id')->nullable()->index()->after('ville_id');
            }
            if (!Schema::connection('cms')->hasColumn('cms_media', 'is_slider')) {
                $table->boolean('is_slider')->default(false)->index()->after('secteur_id');
            }
            if (!Schema::connection('cms')->hasColumn('cms_media', 'order')) {
                $table->integer('order')->default(0)->index()->after('is_slider');
            }
        });

        // Force UTF-8 on table for special characters support.
        try {
            Schema::connection('cms')->getConnection()->statement(
                "ALTER TABLE cms_media CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
            );
        } catch (\Throwable $e) {
            // Ignore on non-MySQL engines.
        }
    }

    public function down(): void
    {
        Schema::connection('cms')->table('cms_media', function (Blueprint $table) {
            foreach ([
                'video_url', 'continent_id', 'country_id', 'province_id',
                'region_id', 'ville_id', 'secteur_id', 'is_slider', 'order'
            ] as $col) {
                if (Schema::connection('cms')->hasColumn('cms_media', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

