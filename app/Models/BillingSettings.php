<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'hide_invoice_button',
        'last_invoice_number',
        'invoice_prefix',
        'invoice_number_length',
        'tax_number_tps',
        'tax_number_tvq',
        'neq',
        'default_shipping_fees',
        'default_administration_fees',
        'default_discount_percentage',
        'default_discount_id',
        'cheque_order',
        'bank_details',
        'payment_button_code',
        'procedure',
        'instructions',
        'default_note',
        'payment_deadline_days',
        'quote_validity_days',
        'legal_mentions',
        'rcs_number',
        'siret',
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
        'metadata',
    ];

    protected $casts = [
        'hide_invoice_button' => 'boolean',
        'invoice_number_length' => 'integer',
        'default_shipping_fees' => 'decimal:2',
        'default_administration_fees' => 'decimal:2',
        'default_discount_percentage' => 'decimal:2',
        'bank_details' => 'array',
        'payment_deadline_days' => 'integer',
        'quote_validity_days' => 'integer',
        'quote_number_length' => 'integer',
        'metadata' => 'array',
    ];

    public function defaultDiscount()
    {
        return $this->belongsTo(BillingDiscount::class, 'default_discount_id');
    }
}
