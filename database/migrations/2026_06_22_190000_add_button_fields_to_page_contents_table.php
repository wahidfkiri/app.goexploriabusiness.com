<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('page_contents', function (Blueprint $table) {
            if (!Schema::hasColumn('page_contents', 'button_text')) {
                $table->string('button_text')->nullable()->after('about_values');
            }
            if (!Schema::hasColumn('page_contents', 'button_url')) {
                $table->string('button_url', 500)->nullable()->after('button_text');
            }
        });
    }

    public function down(): void
    {
        Schema::table('page_contents', function (Blueprint $table) {
            $table->dropColumn(['button_text', 'button_url']);
        });
    }
};
