<?php

namespace Vendor\MapsDataEngine\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Vendor\MapsDataEngine\Models\Concerns\UsesMapsDataEngineConnection;

class MapBusinessListing extends Model
{
    use HasFactory;
    use SoftDeletes;
    use UsesMapsDataEngineConnection;

    protected $table = 'mde_business_listings';

    protected $guarded = [];

    protected $casts = [
        'categories' => 'array',
        'opening_hours' => 'array',
        'images' => 'array',
        'reviews_preview' => 'array',
        'social_links' => 'array',
        'raw_payload' => 'array',
        'last_scraped_at' => 'datetime',
        'rating' => 'float',
        'reviews_count' => 'integer',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function scanSession(): BelongsTo
    {
        return $this->belongsTo(MapScanSession::class, 'latest_scan_session_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(MapScrapeLog::class, 'business_listing_id');
    }
}
