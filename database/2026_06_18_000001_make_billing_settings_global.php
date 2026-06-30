<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('billing_settings', function (Blueprint $table) {
            // Remove etablissement_id foreign key and column
            if (Schema::hasColumn('billing_settings', 'etablissement_id')) {
                $table->dropForeign(['etablissement_id']);
                $table->dropIndex('billing_settings_etablissement_unique');
                $table->dropColumn('etablissement_id');
            }
        });
    }

    public function down()
    {
        Schema::table('billing_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('billing_settings', 'etablissement_id')) {
                $table->foreignId('etablissement_id')->after('id')->constrained()->onDelete('cascade');
                $table->unique(['etablissement_id'], 'billing_settings_etablissement_unique');
            }
        });
    }
};
