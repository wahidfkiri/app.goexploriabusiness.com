<?php

namespace Vendor\AdsManager\Models;

use Illuminate\Database\Eloquent\Model;

class AdClick extends Model
{
    protected $table = 'ad_clicks';

    public $timestamps = false;

    protected $fillable = [
        'ad_id', 'placement_id', 'etablissement_id',
        'ip_address', 'user_agent', 'page_url', 'referrer',
        'country_code', 'user_id', 'is_fraud', 'cost', 'clicked_at',
    ];

    protected $casts = [
        'is_fraud' => 'boolean',
        'cost' => 'decimal:4',
        'clicked_at' => 'datetime',
    ];

    public function ad()
    {
        return $this->belongsTo(Ad::class, 'ad_id');
    }

    public function placement()
    {
        return $this->belongsTo(AdPlacement::class, 'placement_id');
    }

    public function scopeNotFraud($query)
    {
        return $query->where('is_fraud', false);
    }

    public function scopeFraud($query)
    {
        return $query->where('is_fraud', true);
    }

    public function scopeForDate($query, string $date)
    {
        return $query->whereDate('clicked_at', $date);
    }

    public function scopeForPeriod($query, string $start, string $end)
    {
        return $query->whereBetween('clicked_at', [$start, $end]);
    }

    public function scopeForAd($query, int $adId)
    {
        return $query->where('ad_id', $adId);
    }

    public function scopeForIp($query, string $ip)
    {
        return $query->where('ip_address', $ip);
    }

    public function scopeRecentByIp($query, int $adId, string $ip)
    {
        return $query->where('ad_id', $adId)
            ->where('ip_address', $ip)
            ->where('clicked_at', '>=', now()->subHour());
    }
}
