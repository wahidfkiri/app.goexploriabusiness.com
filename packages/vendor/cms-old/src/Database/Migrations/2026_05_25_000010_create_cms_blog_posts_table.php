<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'cms';

    public function up(): void
    {
        Schema::connection('cms')->create('cms_blog_posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('etablissement_id')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('title');
            $table->string('slug');
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();
            $table->string('featured_image')->nullable();
            $table->json('tags')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->boolean('allow_comments')->default(true);
            $table->timestamp('published_at')->nullable();

            // SEO fields
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('seo_keywords')->nullable();
            $table->string('canonical_url')->nullable();
            $table->string('og_image_url')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['etablissement_id', 'slug']);
            $table->index(['etablissement_id', 'status', 'published_at']);
            $table->index(['etablissement_id', 'is_featured']);
        });
    }

    public function down(): void
    {
        Schema::connection('cms')->dropIfExists('cms_blog_posts');
    }
};
