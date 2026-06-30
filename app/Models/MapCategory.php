<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MapCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'icon_class',
        'color',
        'image',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function places(): HasMany
    {
        return $this->hasMany(Place::class, 'map_category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function getImageUrlAttribute()
    {
        if (!$this->image) return null;
        if (Str::startsWith($this->image, 'http')) return $this->image;
        return asset('storage/' . $this->image);
    }
}
