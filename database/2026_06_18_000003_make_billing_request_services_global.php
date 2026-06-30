<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('billing_request_services', function (Blueprint $table) {
            // Remove etablissement_id foreign key and column
            if (Schema::hasColumn('billing_request_services', 'etablissement_id')) {
                $table->dropForeign(['etablissement_id']);
                $table->dropIndex('brs_etab_active_sort_idx');
                $table->dropColumn('etablissement_id');
                
                // Add new index without etablissement_id
                $table->index(['is_active', 'sort_order'], 'brs_active_sort_idx');
            }
        });
    }

    public function down()
    {
        Schema::table('billing_request_services', function (Blueprint $table) {
            if (!Schema::hasColumn('billing_request_services', 'etablissement_id')) {
                $table->foreignId('etablissement_id')->after('id')->constrained()->cascadeOnDelete();
                $table->dropIndex('brs_active_sort_idx');
                $table->index(['etablissement_id', 'is_active', 'sort_order'], 'brs_etab_active_sort_idx');
            }
        });
    }
};
