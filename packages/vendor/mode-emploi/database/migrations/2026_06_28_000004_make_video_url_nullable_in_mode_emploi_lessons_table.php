<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('mode_emploi_lessons', function (Blueprint $table) {
            $table->string('video_url')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('mode_emploi_lessons', function (Blueprint $table) {
            $table->string('video_url')->nullable(false)->change();
        });
    }
};
