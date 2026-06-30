<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'cms';

    public function up(): void
    {
        Schema::connection('cms')->create('cms_slideshows', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('etablissement_id')->index();
            $table->string('title')->nullable();
            $table->text('subtitle')->nullable();
            $table->string('source')->default('url');
            $table->text('video_url');
            $table->string('video_path')->nullable();
            $table->text('poster_url')->nullable();
            $table->string('button_text')->nullable();
            $table->text('button_url')->nullable();
            $table->string('button_target', 20)->default('_self');
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->json('options')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['etablissement_id', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::connection('cms')->dropIfExists('cms_slideshows');
    }
};
