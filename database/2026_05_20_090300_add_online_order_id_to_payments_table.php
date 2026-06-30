<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'online_order_id')) {
                $table->foreignId('online_order_id')
                    ->nullable()
                    ->after('invoice_id')
                    ->constrained('online_orders')
                    ->onDelete('set null');

                $table->index('online_order_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'online_order_id')) {
                $table->dropConstrainedForeignId('online_order_id');
            }
        });
    }
};
