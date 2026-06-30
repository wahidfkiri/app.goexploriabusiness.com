<?php

namespace Vendor\LocationDataEngine\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Vendor\LocationDataEngine\Models\Concerns\UsesLocationDataEngineConnection;

class BusinessPhoto extends Model
{
    use HasFactory;
    use UsesLocationDataEngineConnection;

    protected $table = 'lde_business_photos';

    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
        'is_primary' => 'boolean',
        'width' => 'integer',
        'height' => 'integer',
        'sort_order' => 'integer',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(BusinessLocation::class, 'business_location_id');
    }
}
