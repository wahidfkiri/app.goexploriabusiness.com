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
            if (!Schema::connection('cms')->hasColumn('cms_media', 'is_main_gallery')) {
                $table->boolean('is_main_gallery')->default(false)->index()->after('is_slider');
            }

            if (!Schema::connection('cms')->hasColumn('cms_media', 'is_facebook_gallery')) {
                $table->boolean('is_facebook_gallery')->default(false)->index()->after('is_main_gallery');
            }

            if (!Schema::connection('cms')->hasColumn('cms_media', 'is_instagram_gallery')) {
                $table->boolean('is_instagram_gallery')->default(false)->index()->after('is_facebook_gallery');
            }

            if (!Schema::connection('cms')->hasColumn('cms_media', 'is_pinterest_gallery')) {
                $table->boolean('is_pinterest_gallery')->default(false)->index()->after('is_instagram_gallery');
            }
        });
    }

    public function down(): void
    {
        Schema::connection('cms')->table('cms_media', function (Blueprint $table) {
            foreach ([
                'is_pinterest_gallery',
                'is_instagram_gallery',
                'is_facebook_gallery',
                'is_main_gallery',
            ] as $column) {
                if (Schema::connection('cms')->hasColumn('cms_media', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
