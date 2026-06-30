<?php

namespace Vendor\Etablissement\Models;

use App\Models\Etablissement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RendezVous extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'rendez_vous';

    protected $fillable = [
        'etablissement_id',
        'title',
        'contact_name',
        'contact_email',
        'contact_phone',
        'starts_at',
        'ends_at',
        'all_day',
        'status',
        'meeting_type',
        'location',
        'notes',
        'color',
        'created_by',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'all_day' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function etablissement(): BelongsTo
    {
        return $this->belongsTo(Etablissement::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getResolvedColorAttribute(): string
    {
        if (!empty($this->color)) {
            return $this->color;
        }

        return match ($this->status) {
            'planned' => '#4f46e5',
            'confirmed' => '#059669',
            'completed' => '#0f172a',
            'cancelled' => '#dc2626',
            'rescheduled' => '#d97706',
            default => '#334155',
        };
    }
}
