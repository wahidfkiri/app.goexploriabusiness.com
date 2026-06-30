<?php

namespace Vendor\LocationDataEngine\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Vendor\LocationDataEngine\Models\Concerns\UsesLocationDataEngineConnection;

class BusinessLocation extends Model
{
    use HasFactory;
    use SoftDeletes;
    use UsesLocationDataEngineConnection;

    protected $table = 'lde_business_locations';

    protected $guarded = [];

    protected $casts = [
        'categories' => 'array',
        'opening_hours' => 'array',
        'social_links' => 'array',
        'images' => 'array',
        'reviews_json' => 'array',
        'enrichment_payload' => 'array',
        'raw_payload' => 'array',
        'latitude' => 'float',
        'longitude' => 'float',
        'rating' => 'float',
        'reviews_count' => 'integer',
        'last_scanned_at' => 'datetime',
    ];

    public function scanSession(): BelongsTo
    {
        return $this->belongsTo(ScanSession::class, 'latest_scan_session_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(BusinessPhoto::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(BusinessReview::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(CrawlLog::class);
    }
}
