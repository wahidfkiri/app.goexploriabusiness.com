<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('online_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('online_order_id')->constrained('online_orders')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->onDelete('set null');

            $table->string('product_name');
            $table->string('product_reference')->nullable();
            $table->string('sku')->nullable();
            $table->integer('quantity')->default(1);

            $table->decimal('unit_price_ht', 12, 2)->default(0);
            $table->decimal('unit_price_ttc', 12, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('line_subtotal_ht', 12, 2)->default(0);
            $table->decimal('line_subtotal_ttc', 12, 2)->default(0);
            $table->decimal('line_total', 12, 2)->default(0);

            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['online_order_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('online_order_items');
    }
};
