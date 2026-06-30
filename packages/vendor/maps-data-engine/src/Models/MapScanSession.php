<?php

namespace Vendor\MapsDataEngine\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Vendor\MapsDataEngine\Models\Concerns\UsesMapsDataEngineConnection;

class MapScanSession extends Model
{
    use HasFactory;
    use SoftDeletes;
    use UsesMapsDataEngineConnection;

    protected $table = 'mde_scan_sessions';

    protected $guarded = [];

    protected $casts = [
        'meta' => 'array',
        'progress' => 'array',
        'with_images' => 'boolean',
        'with_reviews' => 'boolean',
        'with_social_links' => 'boolean',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'last_heartbeat_at' => 'datetime',
    ];

    public function listings(): HasMany
    {
        return $this->hasMany(MapBusinessListing::class, 'latest_scan_session_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(MapScrapeLog::class, 'scan_session_id');
    }
}
