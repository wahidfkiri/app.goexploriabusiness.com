<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('etablissements') || ! Schema::hasColumn('etablissements', 'user_id')) {
            return;
        }

        $driver = DB::getDriverName();

        try {
            Schema::table('etablissements', function ($table) {
                $table->dropForeign(['user_id']);
            });
        } catch (\Throwable) {
            // Foreign key may already be absent in some environments.
        }

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE etablissements MODIFY user_id BIGINT UNSIGNED NULL');
            DB::statement('ALTER TABLE etablissements ADD CONSTRAINT etablissements_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE etablissements ALTER COLUMN user_id DROP NOT NULL');
            DB::statement('ALTER TABLE etablissements ADD CONSTRAINT etablissements_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('etablissements') || ! Schema::hasColumn('etablissements', 'user_id')) {
            return;
        }

        $driver = DB::getDriverName();
        $fallbackUserId = DB::table('users')->min('id');

        if ($fallbackUserId !== null) {
            DB::table('etablissements')
                ->whereNull('user_id')
                ->update(['user_id' => $fallbackUserId]);
        }

        try {
            Schema::table('etablissements', function ($table) {
                $table->dropForeign(['user_id']);
            });
        } catch (\Throwable) {
            // Ignore if already removed.
        }

        if (in_array($driver, ['mysql', 'mariadb'], true) && $fallbackUserId !== null) {
            DB::statement('ALTER TABLE etablissements MODIFY user_id BIGINT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE etablissements ADD CONSTRAINT etablissements_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE');
        } elseif ($driver === 'pgsql' && $fallbackUserId !== null) {
            DB::statement('ALTER TABLE etablissements ALTER COLUMN user_id SET NOT NULL');
            DB::statement('ALTER TABLE etablissements ADD CONSTRAINT etablissements_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE');
        }
    }
};
