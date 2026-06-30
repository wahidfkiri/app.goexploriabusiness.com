<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mde_scrape_logs', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->foreignId('scan_session_id')->nullable()->constrained('mde_scan_sessions')->nullOnDelete();
            $table->foreignId('business_listing_id')->nullable()->constrained('mde_business_listings')->nullOnDelete();
            $table->string('level', 20)->default('info')->index();
            $table->string('event')->nullable()->index();
            $table->text('message');
            $table->json('context')->nullable();
            $table->timestamps();
        });

        Schema::create('mde_proxy_endpoints', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->string('label');
            $table->string('host');
            $table->unsignedInteger('port');
            $table->string('scheme')->default('http');
            $table->text('username')->nullable();
            $table->text('password')->nullable();
            $table->float('health_score')->default(100);
            $table->unsignedInteger('success_count')->default(0);
            $table->unsignedInteger('failure_count')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->json('meta')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamp('blacklisted_until')->nullable();
            $table->timestamps();
        });

        Schema::create('mde_browser_sessions', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->foreignId('proxy_endpoint_id')->nullable()->constrained('mde_proxy_endpoints')->nullOnDelete();
            $table->string('session_key')->unique();
            $table->string('storage_state_path')->nullable();
            $table->json('fingerprint')->nullable();
            $table->json('cookies')->nullable();
            $table->json('storage_state')->nullable();
            $table->boolean('is_locked')->default(false)->index();
            $table->boolean('is_banned')->default(false)->index();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mde_browser_sessions');
        Schema::dropIfExists('mde_proxy_endpoints');
        Schema::dropIfExists('mde_scrape_logs');
    }
};
