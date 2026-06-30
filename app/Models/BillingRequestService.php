<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BillingRequestService extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tax_id',
        'title',
        'description',
        'image_url',
        'unit_price',
        'tax_rate',
        'billing_unit',
        'sort_order',
        'is_active',
        'is_featured',
        'metadata',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'metadata' => 'array',
    ];

    public function tax()
    {
        return $this->belongsTo(Tax::class);
    }

    public function requestItems()
    {
        return $this->hasMany(BillingRequestItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
