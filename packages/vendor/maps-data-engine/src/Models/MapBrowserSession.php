<?php

namespace Vendor\MapsDataEngine\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Vendor\MapsDataEngine\Models\Concerns\UsesMapsDataEngineConnection;

class MapBrowserSession extends Model
{
    use HasFactory;
    use UsesMapsDataEngineConnection;

    protected $table = 'mde_browser_sessions';

    protected $guarded = [];

    protected $casts = [
        'fingerprint' => 'array',
        'cookies' => 'array',
        'storage_state' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_locked' => 'boolean',
        'is_banned' => 'boolean',
    ];
}
