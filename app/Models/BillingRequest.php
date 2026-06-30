<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BillingRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'request_number',
        'quote_id',
        'invoice_id',
        'status',
        'name',
        'email',
        'phone',
        'company',
        'address',
        'zipcode',
        'city',
        'country',
        'message',
        'subtotal',
        'discount_type',
        'discount_value',
        'discount_amount',
        'tax_total',
        'total',
        'taxes_breakdown',
        'metadata',
        'submitted_at',
        'processed_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'total' => 'decimal:2',
        'taxes_breakdown' => 'array',
        'metadata' => 'array',
        'submitted_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function items()
    {
        return $this->hasMany(BillingRequestItem::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return [
            'new' => 'Nouvelle',
            'reviewed' => 'Revue',
            'quoted' => 'Devis créé',
            'invoiced' => 'Facture créée',
            'sent' => 'Facture envoyée',
            'closed' => 'Terminée',
            'cancelled' => 'Annulée',
        ][$this->status] ?? ucfirst((string) $this->status);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function (BillingRequest $request) {
            if (!$request->request_number) {
                $prefix = 'DF-' . now()->format('Y') . '-';
                $last = static::withTrashed()
                    ->where('request_number', 'like', $prefix . '%')
                    ->orderByDesc('id')
                    ->value('request_number');
                $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;
                $request->request_number = $prefix . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
            }

            $request->submitted_at ??= now();
        });
    }
}
