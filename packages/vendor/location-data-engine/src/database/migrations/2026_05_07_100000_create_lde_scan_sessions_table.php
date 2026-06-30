<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lde_scan_sessions', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('initiated_by')->nullable()->index();
            $table->string('status', 30)->default('pending')->index();
            $table->string('category')->index();
            $table->string('query')->nullable();
            $table->unsignedBigInteger('country_id')->nullable()->index();
            $table->unsignedBigInteger('province_id')->nullable()->index();
            $table->unsignedBigInteger('region_id')->nullable()->index();
            $table->unsignedBigInteger('city_id')->nullable()->index();
            $table->unsignedBigInteger('sector_id')->nullable()->index();
            $table->string('country_name')->nullable();
            $table->string('province_name')->nullable();
            $table->string('region_name')->nullable();
            $table->string('city_name')->nullable();
            $table->string('sector_name')->nullable();
            $table->string('target_label')->nullable()->index();
            $table->integer('radius')->default(25000);
            $table->integer('limit')->default(250);
            $table->integer('grid_precision')->default(5);
            $table->boolean('with_enrichment')->default(false);
            $table->boolean('with_images')->default(false);
            $table->unsignedInteger('total_points')->default(0);
            $table->unsignedInteger('scanned_points')->default(0);
            $table->unsignedInteger('results_count')->default(0);
            $table->unsignedInteger('duplicates_count')->default(0);
            $table->unsignedInteger('error_count')->default(0);
            $table->unsignedInteger('quota_used')->default(0);
            $table->decimal('progress_percentage', 5, 2)->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamp('last_heartbeat_at')->nullable();
            $table->longText('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lde_scan_sessions');
    }
};
