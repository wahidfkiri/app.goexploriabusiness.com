<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModeEmploiLesson extends Model
{
    protected $fillable = [
        'module_id', 'title', 'description', 'video_url',
        'video_path', 'video_provider', 'duration_minutes', 'order', 'is_active',
    ];

    protected $appends = ['video_url_display'];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
        'duration_minutes' => 'integer',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(ModeEmploiModule::class, 'module_id');
    }

    public function getVideoUrlDisplayAttribute(): ?string
    {
        if ($this->video_url) {
            return $this->video_url;
        }
        if ($this->video_path) {
            if (str_starts_with($this->video_path, 'http://') || str_starts_with($this->video_path, 'https://')) {
                return $this->video_path;
            }
            return asset('storage/' . $this->video_path);
        }
        return null;
    }
}
