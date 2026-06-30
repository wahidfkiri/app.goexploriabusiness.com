<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['invoices', 'quotes'] as $tableName) {
            if (!Schema::hasColumn($tableName, 'client_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                try {
                    $table->dropForeign(['client_id']);
                } catch (Throwable $e) {
                    // The foreign key can already be absent depending on the deployed schema.
                }
            });

            if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
                DB::statement("ALTER TABLE `{$tableName}` MODIFY `client_id` BIGINT UNSIGNED NULL");
            }
        }
    }

    public function down(): void
    {
        foreach (['invoices', 'quotes'] as $tableName) {
            if (!Schema::hasColumn($tableName, 'client_id')) {
                continue;
            }

            if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
                DB::statement("UPDATE `{$tableName}` SET `client_id` = 0 WHERE `client_id` IS NULL");
                DB::statement("ALTER TABLE `{$tableName}` MODIFY `client_id` BIGINT UNSIGNED NOT NULL");
            }
        }
    }
};
