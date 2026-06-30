<?php

namespace Vendor\AdsManager\Models;

use Illuminate\Database\Eloquent\Model;

class AdDailyReport extends Model
{
    protected $table = 'ad_daily_reports';

    protected $fillable = [
        'ad_id', 'placement_id', 'report_date',
        'impressions', 'unique_impressions', 'clicks', 'fraud_clicks',
        'ctr', 'revenue', 'cost',
    ];

    protected $casts = [
        'report_date' => 'date',
        'impressions' => 'integer',
        'unique_impressions' => 'integer',
        'clicks' => 'integer',
        'fraud_clicks' => 'integer',
        'ctr' => 'decimal:2',
        'revenue' => 'decimal:4',
        'cost' => 'decimal:4',
    ];

    public function ad()
    {
        return $this->belongsTo(Ad::class, 'ad_id');
    }

    public function placement()
    {
        return $this->belongsTo(AdPlacement::class, 'placement_id');
    }

    public function scopeForDate($query, string $date)
    {
        return $query->where('report_date', $date);
    }

    public function scopeForPeriod($query, string $start, string $end)
    {
        return $query->whereBetween('report_date', [$start, $end]);
    }

    public function scopeForAd($query, int $adId)
    {
        return $query->where('ad_id', $adId);
    }

    public function scopeForPlacement($query, int $placementId)
    {
        return $query->where('placement_id', $placementId);
    }
}
