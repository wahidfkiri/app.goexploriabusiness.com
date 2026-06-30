<?php
// database/migrations/2026_01_01_000003_make_page_contents_polymorphic.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('page_contents', function (Blueprint $table) {
            // Rendre polymorphique
            $table->string('pageable_type')->nullable()->after('activity_id');
            $table->unsignedBigInteger('pageable_id')->nullable()->after('pageable_type');
            
            // Rendre activity_id nullable
            $table->unsignedBigInteger('activity_id')->nullable()->change();
            
            // Ajouter les index
            $table->index(['pageable_type', 'pageable_id']);
        });
    }

    public function down()
    {
        Schema::table('page_contents', function (Blueprint $table) {
            $table->dropIndex(['pageable_type', 'pageable_id']);
            $table->dropColumn(['pageable_type', 'pageable_id']);
            $table->unsignedBigInteger('activity_id')->nullable(false)->change();
        });
    }
};