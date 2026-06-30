<?php

namespace Vendor\LocationDataEngine\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Vendor\LocationDataEngine\Models\Concerns\UsesLocationDataEngineConnection;

class BusinessReview extends Model
{
    use HasFactory;
    use UsesLocationDataEngineConnection;

    protected $table = 'lde_business_reviews';

    protected $guarded = [];

    protected $casts = [
        'rating' => 'float',
        'published_at' => 'datetime',
        'raw_payload' => 'array',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(BusinessLocation::class, 'business_location_id');
    }
}
