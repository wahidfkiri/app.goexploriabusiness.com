<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('billing_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('billing_settings', 'taxes_enabled')) {
                $table->boolean('taxes_enabled')->default(true)->after('hide_invoice_button');
            }
        });
    }

    public function down(): void
    {
        Schema::table('billing_settings', function (Blueprint $table) {
            if (Schema::hasColumn('billing_settings', 'taxes_enabled')) {
                $table->dropColumn('taxes_enabled');
            }
        });
    }
};
