<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OnlineOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number',
        'etablissement_id',
        'customer_id',
        'invoice_id',
        'status',
        'payment_status',
        'subtotal_ht',
        'subtotal_ttc',
        'tax_total',
        'shipping_amount',
        'discount_amount',
        'total',
        'currency',
        'payment_gateway_code',
        'billing_address',
        'shipping_address',
        'metadata',
        'notes',
        'ordered_at',
        'paid_at',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
    ];

    protected $casts = [
        'subtotal_ht' => 'decimal:2',
        'subtotal_ttc' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'billing_address' => 'array',
        'shipping_address' => 'array',
        'metadata' => 'array',
        'ordered_at' => 'datetime',
        'paid_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function etablissement()
    {
        return $this->belongsTo(Etablissement::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function items()
    {
        return $this->hasMany(OnlineOrderItem::class);
    }

    public function transactions()
    {
        return $this->hasMany(PaymentTransaction::class, 'online_order_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'online_order_id');
    }
}
