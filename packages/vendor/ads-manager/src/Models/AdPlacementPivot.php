<?php

namespace Vendor\AdsManager\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class AdPlacementPivot extends Pivot
{
    protected $table = 'ad_placement';

    protected $fillable = [
        'ad_id', 'placement_id', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function ad()
    {
        return $this->belongsTo(Ad::class, 'ad_id');
    }

    public function placement()
    {
        return $this->belongsTo(AdPlacement::class, 'placement_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
