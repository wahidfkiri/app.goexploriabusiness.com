<?php

namespace Vendor\Etablissement\Models;

use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CountryCalendarEvent extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'country_calendar_events';

    protected $fillable = [
        'country_id',
        'name',
        'slug',
        'event_type',
        'description',
        'recurrence_type',
        'month',
        'day',
        'weekday',
        'nth_occurrence',
        'offset_days',
        'duration_days',
        'specific_start_date',
        'specific_end_date',
        'is_all_day',
        'is_active',
        'color',
        'meta',
    ];

    protected $casts = [
        'month' => 'integer',
        'day' => 'integer',
        'weekday' => 'integer',
        'nth_occurrence' => 'integer',
        'offset_days' => 'integer',
        'duration_days' => 'integer',
        'specific_start_date' => 'date',
        'specific_end_date' => 'date',
        'is_all_day' => 'boolean',
        'is_active' => 'boolean',
        'meta' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getResolvedColorAttribute(): string
    {
        if (!empty($this->color)) {
            return $this->color;
        }

        return match ($this->event_type) {
            'celebration' => '#7c3aed',
            'commemoration' => '#b45309',
            'festival' => '#0f766e',
            default => '#dc2626',
        };
    }

    public function getEventTypeLabelAttribute(): string
    {
        return match ($this->event_type) {
            'celebration' => 'Celebration',
            'commemoration' => 'Commemoration',
            'festival' => 'Festival',
            default => 'Jour ferie',
        };
    }
}
