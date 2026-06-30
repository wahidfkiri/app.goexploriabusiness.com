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
            if (!Schema::connection('cms')->hasColumn('cms_media', 'button_text')) {
                $table->string('button_text', 120)->nullable()->after('order');
            }

            if (!Schema::connection('cms')->hasColumn('cms_media', 'button_url')) {
                $table->text('button_url')->nullable()->after('button_text');
            }
        });
    }

    public function down(): void
    {
        Schema::connection('cms')->table('cms_media', function (Blueprint $table) {
            foreach (['button_url', 'button_text'] as $column) {
                if (Schema::connection('cms')->hasColumn('cms_media', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
