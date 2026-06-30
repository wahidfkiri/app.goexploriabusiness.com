<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'cms';

    public function up(): void
    {
        Schema::connection('cms')->create('cms_header_footers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('etablissement_id')->index();
            $table->string('type', 20);
            $table->string('name')->nullable();
            $table->longText('content')->nullable();
            $table->longText('html_content')->nullable();
            $table->longText('css_content')->nullable();
            $table->json('settings')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['etablissement_id', 'type']);
            $table->index(['etablissement_id', 'type', 'deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::connection('cms')->dropIfExists('cms_header_footers');
    }
};
