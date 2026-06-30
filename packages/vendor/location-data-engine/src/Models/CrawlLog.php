<?php

namespace Vendor\LocationDataEngine\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Vendor\LocationDataEngine\Models\Concerns\UsesLocationDataEngineConnection;

class CrawlLog extends Model
{
    use HasFactory;
    use UsesLocationDataEngineConnection;

    protected $table = 'lde_crawl_logs';

    protected $guarded = [];

    protected $casts = [
        'context' => 'array',
        'quota_units' => 'integer',
    ];

    public function scanSession(): BelongsTo
    {
        return $this->belongsTo(ScanSession::class);
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(BusinessLocation::class, 'business_location_id');
    }
}
