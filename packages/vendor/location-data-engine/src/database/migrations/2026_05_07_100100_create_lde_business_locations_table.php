<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lde_business_locations', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->unsignedBigInteger('latest_scan_session_id')->nullable()->index();
            $table->string('source', 50)->default('google_places')->index();
            $table->string('place_id')->unique();
            $table->string('name')->index();
            $table->string('slug')->nullable()->index();
            $table->string('address')->nullable();
            $table->decimal('latitude', 11, 8)->nullable()->index();
            $table->decimal('longitude', 11, 8)->nullable()->index();
            $table->string('phone')->nullable();
            $table->string('international_phone')->nullable();
            $table->string('website')->nullable();
            $table->string('email')->nullable()->index();
            $table->decimal('rating', 3, 2)->nullable();
            $table->unsignedInteger('reviews_count')->default(0);
            $table->json('categories')->nullable();
            $table->string('business_status')->nullable()->index();
            $table->json('opening_hours')->nullable();
            $table->string('timezone')->nullable();
            $table->string('province')->nullable()->index();
            $table->string('city')->nullable()->index();
            $table->string('country')->nullable()->index();
            $table->string('postal_code')->nullable()->index();
            $table->json('social_links')->nullable();
            $table->string('google_maps_url')->nullable();
            $table->json('images')->nullable();
            $table->json('reviews_json')->nullable();
            $table->json('enrichment_payload')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamp('last_scanned_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['country', 'province', 'city']);
            $table->index(['business_status', 'rating']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lde_business_locations');
    }
};
