<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnlineOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'online_order_id',
        'product_id',
        'product_variant_id',
        'product_name',
        'product_reference',
        'sku',
        'quantity',
        'unit_price_ht',
        'unit_price_ttc',
        'tax_rate',
        'tax_amount',
        'line_subtotal_ht',
        'line_subtotal_ttc',
        'line_total',
        'metadata',
    ];

    protected $casts = [
        'unit_price_ht' => 'decimal:2',
        'unit_price_ttc' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'line_subtotal_ht' => 'decimal:2',
        'line_subtotal_ttc' => 'decimal:2',
        'line_total' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(OnlineOrder::class, 'online_order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
