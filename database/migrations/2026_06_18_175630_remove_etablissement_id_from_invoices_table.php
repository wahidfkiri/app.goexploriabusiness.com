<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Supprimer la clé étrangère si elle existe
            try {
                $table->dropForeign(['etablissement_id']);
            } catch (\Exception $e) {
                // La clé étrangère n'existe peut-être pas
            }
            
            // Supprimer la colonne etablissement_id
            $table->dropColumn('etablissement_id');
            
            // Ajouter les colonnes client_email et client_phone si elles n'existent pas
            if (!Schema::hasColumn('invoices', 'client_email')) {
                $table->string('client_email')->nullable()->after('client_name');
            }
            if (!Schema::hasColumn('invoices', 'client_phone')) {
                $table->string('client_phone')->nullable()->after('client_email');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Restaurer la colonne etablissement_id
            $table->unsignedBigInteger('etablissement_id')->nullable()->after('id');
            $table->foreign('etablissement_id')->references('id')->on('etablissements')->onDelete('cascade');
            
            // Supprimer les colonnes ajoutées
            $table->dropColumn(['client_email', 'client_phone']);
        });
    }
};