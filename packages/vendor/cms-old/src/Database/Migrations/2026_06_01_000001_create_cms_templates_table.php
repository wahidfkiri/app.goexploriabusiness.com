<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'cms';

    public function up(): void
    {
        Schema::connection('cms')->create('cms_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('site_url')->nullable();
            $table->longText('page_content')->nullable();
            $table->string('version')->default('1.0.0');
            $table->unsignedBigInteger('creator_id')->nullable()->index();
            $table->string('category')->nullable()->index();
            $table->string('image_preview')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['draft', 'active', 'archived'])->default('active')->index();
            $table->boolean('is_active')->default(true)->index();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::connection('cms')->dropIfExists('cms_templates');
    }
};
