<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingRequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'billing_request_id',
        'billing_request_service_id',
        'line_number',
        'title',
        'description',
        'quantity',
        'unit_price',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'total',
        'metadata',
    ];

    protected $casts = [
        'line_number' => 'integer',
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function billingRequest()
    {
        return $this->belongsTo(BillingRequest::class);
    }

    public function service()
    {
        return $this->belongsTo(BillingRequestService::class, 'billing_request_service_id');
    }
}
