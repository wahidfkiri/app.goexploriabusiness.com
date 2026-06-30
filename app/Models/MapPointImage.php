<?php
// app/Models/MapPointImage.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MapPointImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'map_point_id',
        'image',
        'thumbnail',
        'caption',
        'sort_order',
        'is_main'
    ];

    protected $casts = [
        'is_main' => 'boolean',
        'sort_order' => 'integer'
    ];

    // Relations
    public function mapPoint()
    {
        return $this->belongsTo(MapPoint::class);
    }

    // Accesseurs
    public function getUrlAttribute()
    {
        if (preg_match('/^https?:\/\//i', $this->image)) {
            return $this->image;
        }

        if (str_starts_with($this->image, '/storage/') || str_starts_with($this->image, 'storage/')) {
            return url('/' . ltrim($this->image, '/'));
        }

        return asset('storage/' . ltrim($this->image, '/'));
    }

    public function getThumbUrlAttribute()
    {
        if (!$this->thumbnail) {
            return $this->url;
        }

        if (preg_match('/^https?:\/\//i', $this->thumbnail)) {
            return $this->thumbnail;
        }

        if (str_starts_with($this->thumbnail, '/storage/') || str_starts_with($this->thumbnail, 'storage/')) {
            return url('/' . ltrim($this->thumbnail, '/'));
        }

        return asset('storage/' . ltrim($this->thumbnail, '/'));
    }

    // Scope
    public function scopeMain($query)
    {
        return $query->where('is_main', true);
    }
}
