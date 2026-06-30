<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BillingDiscount extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'type',
        'value',
        'is_default',
        'is_active',
        'description',
        'metadata',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getDisplayValueAttribute(): string
    {
        return $this->type === 'fixed'
            ? number_format((float) $this->value, 2, ',', ' ') . ' $'
            : number_format((float) $this->value, 2, ',', ' ') . ' %';
    }
}
