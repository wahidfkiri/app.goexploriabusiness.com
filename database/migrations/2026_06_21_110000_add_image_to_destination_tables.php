<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = ['provinces', 'regions', 'secteurs', 'villes'];

        foreach ($tables as $table) {
            if (!Schema::hasColumn($table, 'image')) {
                Schema::table($table, function (Blueprint $t) use ($table) {
                    $t->string('image', 255)->nullable()->after('description');
                });
            }
        }
    }

    public function down(): void
    {
        $tables = ['provinces', 'regions', 'secteurs', 'villes'];

        foreach ($tables as $table) {
            if (Schema::hasColumn($table, 'image')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropColumn('image');
                });
            }
        }
    }
};
