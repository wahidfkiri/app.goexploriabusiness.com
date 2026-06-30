<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->dropForeignIfExists('invoices', 'client_id');
        $this->dropForeignIfExists('quotes', 'client_id');

        DB::statement('
            UPDATE invoices i
            LEFT JOIN etablissements e ON e.id = i.client_id
            SET i.client_id = i.etablissement_id
            WHERE e.id IS NULL
        ');

        DB::statement('
            UPDATE quotes q
            LEFT JOIN etablissements e ON e.id = q.client_id
            SET q.client_id = q.etablissement_id
            WHERE e.id IS NULL
        ');

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreign('client_id', 'invoices_client_id_foreign')
                ->references('id')
                ->on('etablissements')
                ->restrictOnDelete();
        });

        Schema::table('quotes', function (Blueprint $table) {
            $table->foreign('client_id', 'quotes_client_id_foreign')
                ->references('id')
                ->on('etablissements')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        $this->dropForeignIfExists('invoices', 'client_id');
        $this->dropForeignIfExists('quotes', 'client_id');

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreign('client_id', 'invoices_client_id_foreign')
                ->references('id')
                ->on('customers')
                ->cascadeOnDelete();
        });

        Schema::table('quotes', function (Blueprint $table) {
            $table->foreign('client_id', 'quotes_client_id_foreign')
                ->references('id')
                ->on('customers')
                ->cascadeOnDelete();
        });
    }

    protected function dropForeignIfExists(string $table, string $column): void
    {
        $constraint = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->where('COLUMN_NAME', $column)
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->value('CONSTRAINT_NAME');

        if ($constraint) {
            Schema::table($table, function (Blueprint $table) use ($constraint) {
                $table->dropForeign($constraint);
            });
        }
    }
};
