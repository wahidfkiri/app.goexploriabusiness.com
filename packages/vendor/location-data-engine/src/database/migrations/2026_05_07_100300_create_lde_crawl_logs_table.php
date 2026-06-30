<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lde_crawl_logs', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->foreignId('scan_session_id')->nullable()->constrained('lde_scan_sessions')->nullOnDelete();
            $table->foreignId('business_location_id')->nullable()->constrained('lde_business_locations')->nullOnDelete();
            $table->string('level', 20)->default('info')->index();
            $table->string('event')->nullable()->index();
            $table->string('api_name')->nullable()->index();
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->unsignedInteger('quota_units')->default(0);
            $table->text('message');
            $table->json('context')->nullable();
            $table->timestamps();
            $table->index(['scan_session_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lde_crawl_logs');
    }
};
