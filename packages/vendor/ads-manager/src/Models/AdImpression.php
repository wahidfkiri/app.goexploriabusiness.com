<?php

namespace Vendor\AdsManager\Models;

use Illuminate\Database\Eloquent\Model;

class AdImpression extends Model
{
    protected $table = 'ad_impressions';

    public $timestamps = false;

    protected $fillable = [
        'ad_id', 'placement_id', 'etablissement_id',
        'ip_address', 'user_agent', 'page_url', 'referrer',
        'country_code', 'user_id', 'is_unique', 'viewed_at',
    ];

    protected $casts = [
        'is_unique' => 'boolean',
        'viewed_at' => 'datetime',
    ];

    public function ad()
    {
        return $this->belongsTo(Ad::class, 'ad_id');
    }

    public function placement()
    {
        return $this->belongsTo(AdPlacement::class, 'placement_id');
    }

    public function scopeUnique($query)
    {
        return $query->where('is_unique', true);
    }

    public function scopeForDate($query, string $date)
    {
        return $query->whereDate('viewed_at', $date);
    }

    public function scopeForPeriod($query, string $start, string $end)
    {
        return $query->whereBetween('viewed_at', [$start, $end]);
    }

    public function scopeForAd($query, int $adId)
    {
        return $query->where('ad_id', $adId);
    }
}
