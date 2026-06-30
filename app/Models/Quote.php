<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quote extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'quote_number',
        'client_id',
        'project_id',
        'quote_date',
        'valid_until',
        'accepted_date',
        'rejected_date',
        'subtotal',
        'shipping_fees',
        'administration_fees',
        'discount_percentage',
        'discount_amount',
        'tax_total',
        'total',
        'taxes_breakdown',
        'status',
        'notes',
        'conditions',
        'metadata'
    ];

    protected $casts = [
        'quote_date' => 'date',
        'valid_until' => 'date',
        'accepted_date' => 'date',
        'rejected_date' => 'date',
        'subtotal' => 'decimal:2',
        'shipping_fees' => 'decimal:2',
        'administration_fees' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'total' => 'decimal:2',
        'taxes_breakdown' => 'array',
        'metadata' => 'array'
    ];

    public function client()
    {
        return $this->belongsTo(Etablissement::class, 'client_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function lines()
    {
        return $this->hasMany(QuoteLine::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function getIsExpiredAttribute()
    {
        return $this->valid_until
            && !in_array($this->status, ['accepte', 'refuse', 'converti_en_facture', 'annule'], true)
            && $this->valid_until->isPast();
    }

    public function getIsConvertibleAttribute()
    {
        return in_array($this->status, ['accepte', 'envoye', 'en_attente'], true) && !$this->invoice;
    }

    public function getStatusLabelAttribute()
    {
        return [
            'brouillon' => 'Brouillon',
            'envoye' => 'EnvoyÃ©',
            'en_attente' => 'En attente',
            'accepte' => 'AcceptÃ©',
            'refuse' => 'RefusÃ©',
            'expire' => 'ExpirÃ©',
            'converti_en_facture' => 'Converti en facture',
            'annule' => 'AnnulÃ©',
        ][$this->status] ?? ucfirst((string) $this->status);
    }

    public function getStatusBadgeAttribute()
    {
        return [
            'brouillon' => 'secondary',
            'envoye' => 'primary',
            'en_attente' => 'warning',
            'accepte' => 'success',
            'refuse' => 'danger',
            'expire' => 'dark',
            'converti_en_facture' => 'info',
            'annule' => 'dark',
        ][$this->status] ?? 'secondary';
    }

    public function accept()
    {
        $this->status = 'accepte';
        $this->accepted_date = now();
        $this->save();
    }

    public function reject($reason = null)
    {
        $this->status = 'refuse';
        $this->rejected_date = now();
        $this->notes = $reason;
        $this->save();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($quote) {
            if (!$quote->quote_number) {
                $quote->quote_number = static::nextQuoteNumber();
            }
        });

        static::created(function ($quote) {
            $settings = BillingSetting::first();
            if (!$settings) {
                $settings = BillingSetting::create([]);
            }
            $settings->update(['last_quote_number' => $quote->quote_number]);
        });
    }

    protected static function nextQuoteNumber(): string
    {
        $settings = BillingSetting::first();
        if (!$settings) {
            $settings = BillingSetting::create([]);
        }

        $prefix = $settings->quote_prefix ?: 'D-';
        $length = (int) ($settings->quote_number_length ?: 5);
        $candidate = $settings->getNextQuoteNumber();
        $next = ((int) substr($candidate, strlen($prefix))) + 1;

        do {
            $exists = static::withTrashed()->where('quote_number', $candidate)->exists();
            if (!$exists) {
                break;
            }

            $candidate = $prefix . str_pad((string) $next, $length, '0', STR_PAD_LEFT);
            $next++;
        } while (true);

        return $candidate;
    }
}