<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'cms';

    public function up(): void
    {
        Schema::connection('cms')->create('etablissement_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('etablissement_id')->index();
            $table->unsignedBigInteger('template_cms_id')->index();
            $table->unsignedBigInteger('cms_page_id')->nullable()->index();
            $table->longText('page_contents')->nullable();
            $table->enum('status', ['installed', 'draft', 'archived'])->default('installed')->index();
            $table->boolean('is_active')->default(false)->index();
            $table->json('config')->nullable();
            $table->timestamp('installed_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['etablissement_id', 'template_cms_id'], 'etab_template_unique');
            $table->index(['etablissement_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::connection('cms')->dropIfExists('etablissement_templates');
    }
};
