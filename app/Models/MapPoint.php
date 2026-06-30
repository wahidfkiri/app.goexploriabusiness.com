<?php
// app/Models/MapPoint.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MapPoint extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'category',
        'map_category_id',
        'type',
        'main_image',
        'youtube_url',
        'youtube_id',
        'latitude',
        'longitude',
        'adresse',
        'ville',
        'code_postal',
        'details_url',
        'has_details_page',
        'etablissement_id',
        'user_id',
        'is_active',
        'is_featured',
        'views'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'has_details_page' => 'boolean',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'views' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    // Relations
    public function images()
    {
        return $this->hasMany(MapPointImage::class)->orderBy('sort_order');
    }

    public function mainImage()
    {
        return $this->hasOne(MapPointImage::class)->where('is_main', true);
    }

    public function videos()
    {
        return $this->hasMany(MapPointVideo::class)->orderBy('sort_order');
    }

    public function details()
    {
        return $this->hasOne(MapPointDetail::class);
    }

    public function etablissement()
    {
        return $this->belongsTo(Etablissement::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function mapCategory()
    {
        return $this->belongsTo(MapCategory::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeInBounds($query, $southWest, $northEast)
    {
        return $query->whereBetween('latitude', [$southWest['lat'], $northEast['lat']])
                     ->whereBetween('longitude', [$southWest['lng'], $northEast['lng']]);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->whereHas('mapCategory', function($q) use ($category) {
            $q->where('slug', $category);
        });
    }

    // Accesseurs
    public function getThumbnailAttribute()
    {
        if ($this->youtube_id) {
            return "https://img.youtube.com/vi/{$this->youtube_id}/hqdefault.jpg";
        }
        
        if ($this->main_image) {
            if (preg_match('/^https?:\/\//i', $this->main_image)) {
                return $this->main_image;
            }

            if (str_starts_with($this->main_image, '/storage/') || str_starts_with($this->main_image, 'storage/')) {
                return url('/' . ltrim($this->main_image, '/'));
            }

            return asset('storage/' . ltrim($this->main_image, '/'));
        }
        
        return asset('images/default-placeholder.jpg');
    }

    public function getPopupContentAttribute()
    {
        return view('components.map-popup', ['point' => $this])->render();
    }

    // Incrémenter les vues
    public function incrementViews()
    {
        $this->increment('views');
    }
}
