<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('page_contents', function (Blueprint $table) {
            if (!Schema::hasColumn('page_contents', 'video_muted')) {
                $table->boolean('video_muted')->default(false)->after('button_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('page_contents', function (Blueprint $table) {
            if (Schema::hasColumn('page_contents', 'video_muted')) {
                $table->dropColumn('video_muted');
            }
        });
    }
};
