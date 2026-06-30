<?php

namespace Vendor\MapsDataEngine\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Vendor\MapsDataEngine\Models\Concerns\UsesMapsDataEngineConnection;

class MapProxyEndpoint extends Model
{
    use HasFactory;
    use UsesMapsDataEngineConnection;

    protected $table = 'mde_proxy_endpoints';

    protected $guarded = [];

    protected $casts = [
        'meta' => 'array',
        'last_checked_at' => 'datetime',
        'blacklisted_until' => 'datetime',
        'is_active' => 'boolean',
        'health_score' => 'float',
    ];
}
