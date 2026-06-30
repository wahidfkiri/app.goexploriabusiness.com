<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('billing_requests', function (Blueprint $table) {
            // Remove etablissement_id foreign key and column
            if (Schema::hasColumn('billing_requests', 'etablissement_id')) {
                $table->dropForeign(['etablissement_id']);
                $table->dropIndex(['etablissement_id', 'status']);
                $table->dropColumn('etablissement_id');
            }
            
            // Remove client_etablissement_id foreign key and column
            if (Schema::hasColumn('billing_requests', 'client_etablissement_id')) {
                $table->dropForeign(['client_etablissement_id']);
                $table->dropColumn('client_etablissement_id');
            }
            
            // Add new index without etablissement_id
            $table->index(['status']);
        });
    }

    public function down()
    {
        Schema::table('billing_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('billing_requests', 'etablissement_id')) {
                $table->foreignId('etablissement_id')->after('request_number')->constrained()->cascadeOnDelete();
            }
            
            if (!Schema::hasColumn('billing_requests', 'client_etablissement_id')) {
                $table->foreignId('client_etablissement_id')->nullable()->after('etablissement_id')->constrained('etablissements')->nullOnDelete();
            }
            
            $table->dropIndex(['status']);
            $table->index(['etablissement_id', 'status']);
        });
    }
};
