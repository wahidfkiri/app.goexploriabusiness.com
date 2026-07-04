<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['payments', 'payment_transactions'] as $tableName) {
            if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'client_id')) {
                continue;
            }

            $this->dropForeignIfExists($tableName, 'client_id');

            if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
                DB::statement("ALTER TABLE `{$tableName}` MODIFY `client_id` BIGINT UNSIGNED NULL");
            }
        }
    }

    public function down(): void
    {
        foreach (['payments', 'payment_transactions'] as $tableName) {
            if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'client_id')) {
                continue;
            }

            if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
                DB::statement("ALTER TABLE `{$tableName}` MODIFY `client_id` BIGINT UNSIGNED NOT NULL");
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $table->foreign('client_id', "{$tableName}_client_id_foreign")
                    ->references('id')
                    ->on('customers')
                    ->cascadeOnDelete();
            });
        }
    }

    private function dropForeignIfExists(string $tableName, string $columnName): void
    {
        if (!in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        $constraint = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $tableName)
            ->where('COLUMN_NAME', $columnName)
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->value('CONSTRAINT_NAME');

        if ($constraint) {
            Schema::table($tableName, function (Blueprint $table) use ($constraint) {
                $table->dropForeign($constraint);
            });
        }
    }
};
