<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('billing_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('billing_settings', 'quote_prefix')) {
                $table->string('quote_prefix')->default('D-')->after('invoice_number_length');
            }

            if (!Schema::hasColumn('billing_settings', 'last_quote_number')) {
                $table->string('last_quote_number')->default('D-26000')->after('quote_prefix');
            }

            if (!Schema::hasColumn('billing_settings', 'quote_number_length')) {
                $table->integer('quote_number_length')->default(5)->after('last_quote_number');
            }

            if (!Schema::hasColumn('billing_settings', 'billing_logo_url')) {
                $table->string('billing_logo_url')->nullable()->after('neq');
            }

            if (!Schema::hasColumn('billing_settings', 'invoice_template')) {
                $table->string('invoice_template')->default('classic')->after('billing_logo_url');
            }

            if (!Schema::hasColumn('billing_settings', 'quote_template')) {
                $table->string('quote_template')->default('classic')->after('invoice_template');
            }

            if (!Schema::hasColumn('billing_settings', 'currency')) {
                $table->string('currency', 3)->default('CAD')->after('quote_template');
            }

            if (!Schema::hasColumn('billing_settings', 'locale')) {
                $table->string('locale', 10)->default('fr_CA')->after('currency');
            }

            if (!Schema::hasColumn('billing_settings', 'invoice_due_label')) {
                $table->string('invoice_due_label')->default('Échéance')->after('locale');
            }

            if (!Schema::hasColumn('billing_settings', 'quote_validity_label')) {
                $table->string('quote_validity_label')->default('Valide jusqu’au')->after('invoice_due_label');
            }

            if (!Schema::hasColumn('billing_settings', 'invoice_footer_note')) {
                $table->text('invoice_footer_note')->nullable()->after('default_note');
            }

            if (!Schema::hasColumn('billing_settings', 'quote_footer_note')) {
                $table->text('quote_footer_note')->nullable()->after('invoice_footer_note');
            }

            if (!Schema::hasColumn('billing_settings', 'terms_and_conditions')) {
                $table->longText('terms_and_conditions')->nullable()->after('quote_footer_note');
            }

            if (!Schema::hasColumn('billing_settings', 'enable_online_payment')) {
                $table->boolean('enable_online_payment')->default(false)->after('payment_button_code');
            }

            if (!Schema::hasColumn('billing_settings', 'enable_partial_payments')) {
                $table->boolean('enable_partial_payments')->default(false)->after('enable_online_payment');
            }

            if (!Schema::hasColumn('billing_settings', 'auto_send_invoice_pdf')) {
                $table->boolean('auto_send_invoice_pdf')->default(true)->after('enable_partial_payments');
            }

            if (!Schema::hasColumn('billing_settings', 'auto_convert_accepted_quote')) {
                $table->boolean('auto_convert_accepted_quote')->default(false)->after('auto_send_invoice_pdf');
            }

            if (!Schema::hasColumn('billing_settings', 'default_tax_ids')) {
                $table->json('default_tax_ids')->nullable()->after('default_discount_percentage');
            }
        });
    }

    public function down(): void
    {
        Schema::table('billing_settings', function (Blueprint $table) {
            foreach ([
                'quote_prefix',
                'last_quote_number',
                'quote_number_length',
                'billing_logo_url',
                'invoice_template',
                'quote_template',
                'currency',
                'locale',
                'invoice_due_label',
                'quote_validity_label',
                'invoice_footer_note',
                'quote_footer_note',
                'terms_and_conditions',
                'enable_online_payment',
                'enable_partial_payments',
                'auto_send_invoice_pdf',
                'auto_convert_accepted_quote',
                'default_tax_ids',
            ] as $column) {
                if (Schema::hasColumn('billing_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
