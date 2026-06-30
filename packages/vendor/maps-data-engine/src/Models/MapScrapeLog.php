<?php

namespace Vendor\MapsDataEngine\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Vendor\MapsDataEngine\Models\Concerns\UsesMapsDataEngineConnection;

class MapScrapeLog extends Model
{
    use HasFactory;
    use UsesMapsDataEngineConnection;

    protected $table = 'mde_scrape_logs';

    protected $guarded = [];

    protected $casts = [
        'context' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(MapScanSession::class, 'scan_session_id');
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(MapBusinessListing::class, 'business_listing_id');
    }
}
