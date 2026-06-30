<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mde_business_listings', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->foreignId('latest_scan_session_id')->nullable()->constrained('mde_scan_sessions')->nullOnDelete();
            $table->string('external_id')->nullable()->index();
            $table->string('name')->index();
            $table->string('slug')->nullable()->index();
            $table->string('address')->nullable();
            $table->decimal('latitude', 11, 8)->nullable()->index();
            $table->decimal('longitude', 11, 8)->nullable()->index();
            $table->text('website')->nullable();
            $table->string('phone')->nullable();
            $table->decimal('rating', 3, 2)->nullable();
            $table->unsignedInteger('reviews_count')->default(0);
            $table->json('categories')->nullable();
            $table->json('opening_hours')->nullable();
            $table->text('google_maps_url')->nullable();
            $table->json('images')->nullable();
            $table->json('reviews_preview')->nullable();
            $table->json('social_links')->nullable();
            $table->string('country')->nullable()->index();
            $table->string('province')->nullable()->index();
            $table->string('region')->nullable()->index();
            $table->string('city')->nullable()->index();
            $table->string('business_status')->nullable()->index();
            $table->json('raw_payload')->nullable();
            $table->timestamp('last_scraped_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mde_business_listings');
    }
};
