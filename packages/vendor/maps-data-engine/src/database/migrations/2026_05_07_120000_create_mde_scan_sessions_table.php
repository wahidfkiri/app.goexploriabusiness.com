<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mde_scan_sessions', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('initiated_by')->nullable()->index();
            $table->string('status', 40)->default('pending')->index();
            $table->string('category')->index();
            $table->string('query')->nullable();
            $table->unsignedBigInteger('country_id')->nullable()->index();
            $table->unsignedBigInteger('province_id')->nullable()->index();
            $table->unsignedBigInteger('region_id')->nullable()->index();
            $table->unsignedBigInteger('city_id')->nullable()->index();
            $table->string('country_name')->nullable();
            $table->string('province_name')->nullable();
            $table->string('region_name')->nullable();
            $table->string('city_name')->nullable();
            $table->string('target_label')->nullable()->index();
            $table->integer('radius')->default(18000);
            $table->integer('limit')->default(120);
            $table->unsignedInteger('segments_total')->default(0);
            $table->unsignedInteger('segments_completed')->default(0);
            $table->unsignedInteger('results_count')->default(0);
            $table->unsignedInteger('captcha_incidents')->default(0);
            $table->unsignedInteger('retry_count')->default(0);
            $table->unsignedInteger('proxy_rotations')->default(0);
            $table->boolean('with_images')->default(false);
            $table->boolean('with_reviews')->default(false);
            $table->boolean('with_social_links')->default(false);
            $table->json('progress')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamp('last_heartbeat_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mde_scan_sessions');
    }
};
