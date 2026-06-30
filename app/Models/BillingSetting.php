<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'hide_invoice_button',
        'taxes_enabled',
        'last_invoice_number',
        'invoice_prefix',
        'invoice_number_length',
        'quote_prefix',
        'last_quote_number',
        'quote_number_length',
        'tax_number_tps',
        'tax_number_tvq',
        'neq',
        'billing_logo_url',
        'invoice_template',
        'quote_template',
        'currency',
        'locale',
        'invoice_due_label',
        'quote_validity_label',
        'default_shipping_fees',
        'default_administration_fees',
        'default_discount_percentage',
        'default_discount_id',
        'default_tax_ids',
        'cheque_order',
        'bank_details',
        'payment_button_code',
        'enable_online_payment',
        'enable_partial_payments',
        'auto_send_invoice_pdf',
        'auto_convert_accepted_quote',
        'procedure',
        'instructions',
        'default_note',
        'invoice_footer_note',
        'quote_footer_note',
        'terms_and_conditions',
        'payment_deadline_days',
        'quote_validity_days',
        'legal_mentions',
        'rcs_number',
        'siret',
        'metadata'
    ];

    protected $casts = [
        'hide_invoice_button' => 'boolean',
        'taxes_enabled' => 'boolean',
        'default_shipping_fees' => 'decimal:2',
        'default_administration_fees' => 'decimal:2',
        'default_discount_percentage' => 'decimal:2',
        'default_discount_id' => 'integer',
        'default_tax_ids' => 'array',
        'bank_details' => 'array',
        'metadata' => 'array',
        'enable_online_payment' => 'boolean',
        'enable_partial_payments' => 'boolean',
        'auto_send_invoice_pdf' => 'boolean',
        'auto_convert_accepted_quote' => 'boolean',
        'payment_deadline_days' => 'integer',
        'quote_validity_days' => 'integer',
        'invoice_number_length' => 'integer',
        'quote_number_length' => 'integer'
    ];


    public function defaultDiscount()
    {
        return $this->belongsTo(BillingDiscount::class, 'default_discount_id');
    }

    public function getNextInvoiceNumber()
    {
        $last = $this->last_invoice_number;
        $prefix = $this->invoice_prefix;
        $number = intval(str_replace($prefix, '', $last));
        $next = $number + 1;
        
        return $prefix . str_pad($next, $this->invoice_number_length, '0', STR_PAD_LEFT);
    }

    public function getNextQuoteNumber()
    {
        $last = $this->last_quote_number ?: 'D-26000';
        $prefix = $this->quote_prefix ?: 'D-';
        $number = intval(str_replace($prefix, '', $last));
        $next = $number + 1;

        return $prefix . str_pad($next, $this->quote_number_length ?: 5, '0', STR_PAD_LEFT);
    }
}
