<?php

namespace Vendor\AdsManager\Models;

use Illuminate\Database\Eloquent\Model;

class AdPlacement extends Model
{
    protected $table = 'ad_placements';

    protected $fillable = [
        'nom', 'code', 'description',
        'position', 'format', 'width', 'height',
        'is_active',
        'etablissement_id', 'page_context',
        'max_ads',
    ];

    protected $casts = [
        'width' => 'integer',
        'height' => 'integer',
        'is_active' => 'boolean',
        'max_ads' => 'integer',
    ];

    public function ads()
    {
        return $this->belongsToMany(Ad::class, 'ad_placement', 'placement_id', 'ad_id')
            ->using(AdPlacementPivot::class)
            ->withPivot('is_active')
            ->withTimestamps();
    }

    public function impressions()
    {
        return $this->hasMany(AdImpression::class, 'placement_id');
    }

    public function clicks()
    {
        return $this->hasMany(AdClick::class, 'placement_id');
    }

    public function dailyReports()
    {
        return $this->hasMany(AdDailyReport::class, 'placement_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCode($query, string $code)
    {
        return $query->where('code', $code);
    }

    public function scopeByPosition($query, string $position)
    {
        return $query->where('position', $position);
    }

    public function scopeByEtablissement($query, ?int $etablissementId)
    {
        if ($etablissementId) {
            return $query->where(fn ($q) => $q->whereNull('etablissement_id')->orWhere('etablissement_id', $etablissementId));
        }
        return $query;
    }

    public function getPositionLabelAttribute(): string
    {
        return config('ads-manager.placement_positions.' . $this->position, $this->position);
    }

    public function getFormatLabelAttribute(): string
    {
        $format = config('ads-manager.ad_formats.' . $this->format);
        return $format['label'] ?? $this->format;
    }

    public function getDimensionsAttribute(): string
    {
        if ($this->width && $this->height) {
            return $this->width . '×' . $this->height;
        }
        return '';
    }
}
