<?php

namespace Vendor\LocationDataEngine\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Vendor\LocationDataEngine\Models\Concerns\UsesLocationDataEngineConnection;

class ScanSession extends Model
{
    use HasFactory;
    use SoftDeletes;
    use UsesLocationDataEngineConnection;

    protected $table = 'lde_scan_sessions';

    protected $guarded = [];

    protected $casts = [
        'meta' => 'array',
        'with_enrichment' => 'boolean',
        'with_images' => 'boolean',
        'progress_percentage' => 'float',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'last_heartbeat_at' => 'datetime',
    ];

    public function businesses(): HasMany
    {
        return $this->hasMany(BusinessLocation::class, 'latest_scan_session_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(CrawlLog::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'initiated_by');
    }
}
