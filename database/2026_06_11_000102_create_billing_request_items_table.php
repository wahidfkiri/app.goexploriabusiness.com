<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('billing_request_id')->constrained('billing_requests')->cascadeOnDelete();
            $table->foreignId('billing_request_service_id')->nullable()->constrained('billing_request_services')->nullOnDelete();
            $table->unsignedInteger('line_number')->default(1);
            $table->string('title');
            $table->longText('description')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['billing_request_id', 'line_number'], 'bri_request_line_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_request_items');
    }
};
