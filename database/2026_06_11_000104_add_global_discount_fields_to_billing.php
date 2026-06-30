<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('billing_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('billing_settings', 'default_discount_id')) {
                $table->foreignId('default_discount_id')->nullable()->after('default_discount_percentage')->constrained('billing_discounts')->nullOnDelete();
            }
        });

        Schema::table('billing_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('billing_requests', 'discount_type')) {
                $table->string('discount_type', 30)->nullable()->after('subtotal');
            }

            if (!Schema::hasColumn('billing_requests', 'discount_value')) {
                $table->decimal('discount_value', 12, 2)->default(0)->after('discount_type');
            }

            if (!Schema::hasColumn('billing_requests', 'discount_amount')) {
                $table->decimal('discount_amount', 12, 2)->default(0)->after('discount_value');
            }
        });
    }

    public function down(): void
    {
        Schema::table('billing_requests', function (Blueprint $table) {
            if (Schema::hasColumn('billing_requests', 'discount_amount')) {
                $table->dropColumn('discount_amount');
            }

            if (Schema::hasColumn('billing_requests', 'discount_value')) {
                $table->dropColumn('discount_value');
            }

            if (Schema::hasColumn('billing_requests', 'discount_type')) {
                $table->dropColumn('discount_type');
            }
        });

        Schema::table('billing_settings', function (Blueprint $table) {
            if (Schema::hasColumn('billing_settings', 'default_discount_id')) {
                $table->dropConstrainedForeignId('default_discount_id');
            }
        });
    }
};
