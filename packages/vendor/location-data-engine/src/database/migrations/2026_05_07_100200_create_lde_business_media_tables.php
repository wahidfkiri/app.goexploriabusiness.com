<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lde_business_photos', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->foreignId('business_location_id')->constrained('lde_business_locations')->cascadeOnDelete();
            $table->string('source', 50)->default('google_places')->index();
            $table->string('remote_reference')->nullable()->index();
            $table->string('file_path')->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->string('cdn_url')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->boolean('is_primary')->default(false)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('lde_business_reviews', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->foreignId('business_location_id')->constrained('lde_business_locations')->cascadeOnDelete();
            $table->string('author_name')->nullable();
            $table->string('author_url')->nullable();
            $table->decimal('rating', 3, 2)->nullable();
            $table->string('language', 20)->nullable();
            $table->text('text')->nullable();
            $table->string('relative_time_description')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();
            $table->index(['business_location_id', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lde_business_reviews');
        Schema::dropIfExists('lde_business_photos');
    }
};
