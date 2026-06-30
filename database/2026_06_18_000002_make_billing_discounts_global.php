<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('billing_discounts', function (Blueprint $table) {
            // Remove etablissement_id foreign key and column
            if (Schema::hasColumn('billing_discounts', 'etablissement_id')) {
                $table->dropForeign(['etablissement_id']);
                $table->dropIndex(['etablissement_id', 'is_active']);
                $table->dropIndex(['etablissement_id', 'is_default']);
                $table->dropColumn('etablissement_id');
            }
        });
    }

    public function down()
    {
        Schema::table('billing_discounts', function (Blueprint $table) {
            if (!Schema::hasColumn('billing_discounts', 'etablissement_id')) {
                $table->foreignId('etablissement_id')->nullable()->after('id')->constrained()->nullOnDelete();
                $table->index(['etablissement_id', 'is_active']);
                $table->index(['etablissement_id', 'is_default']);
            }
        });
    }
};
