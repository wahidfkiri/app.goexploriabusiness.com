<?php
// app/Models/PageContent.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PageContent extends Model
{
    use SoftDeletes;

    protected $table = 'page_contents';

    protected $fillable = [
        'activity_id',
        'pageable_type',
        'pageable_id',
        'type',
        'title',
        'content',
        'image',
        'video_url',
        'meta_title',
        'meta_description',
        'order',
        'is_active',
        'published_at',
        'duration',
        'event_start_date',
        'event_end_date',
        'event_location',
        'event_address',
        'event_capacity',
        'event_price',
        'event_is_free',
        'blog_author',
        'blog_category',
        'blog_excerpt',
        'faq_question',
        'testimonial_name',
        'testimonial_role',
        'testimonial_rating',
        'testimonial_content',
        'contact_email',
        'contact_phone',
        'contact_address',
        'contact_hours',
        'contact_message',
        'about_subtitle',
        'about_values',
        'button_text',
        'button_url',
        'video_muted',
        'extra_data',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'published_at' => 'datetime',
        'order' => 'integer',
        'duration' => 'integer',
        'event_start_date' => 'datetime',
        'event_end_date' => 'datetime',
        'event_capacity' => 'integer',
        'event_price' => 'decimal:2',
        'event_is_free' => 'boolean',
        'testimonial_rating' => 'integer',
        'video_muted' => 'boolean',
        'extra_data' => 'array',
    ];

    /**
     * Relation avec l'activité
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    /**
     * Accesseur pour l'URL complète de l'image
     */
    public function getImageUrlAttribute()
    {
        if (!$this->image) {
            return null;
        }

        if (Str::startsWith($this->image, 'http://') || Str::startsWith($this->image, 'https://')) {
            return $this->image;
        }

        if (Str::startsWith($this->image, 'storage/')) {
            return asset($this->image);
        }

        if (Storage::disk('public')->exists($this->image)) {
            return asset('storage/' . $this->image);
        }

        return null;
    }

    /**
     * Accesseur pour le chemin de l'image
     */
    public function getImagePathAttribute()
    {
        return $this->image;
    }

    /**
     * Vérifier si l'image existe
     */
    public function hasImage(): bool
    {
        return $this->image && Storage::disk('public')->exists($this->image);
    }

    /**
     * Supprimer l'image associée
     */
    public function deleteImage(): bool
    {
        if ($this->hasImage()) {
            return Storage::disk('public')->delete($this->image);
        }
        return false;
    }

    /**
     * Accesseur pour le contenu formaté
     */
    public function getFormattedContentAttribute()
    {
        return $this->content ? nl2br(e($this->content)) : null;
    }

    /**
     * Scope pour les contenus actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope par type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope pour les contenus publiés
     */
    public function scopePublished($query)
    {
        return $query->where('is_active', true)
                     ->where('published_at', '<=', now());
    }

    /**
     * Obtenir le label du type
     */
    public function getTypeLabelAttribute()
    {
        $labels = [
            'about' => 'À propos',
            'event' => 'Événement',
            'blog' => 'Blog',
            'video' => 'Vidéo',
            'gallery' => 'Galerie',
            'testimonial' => 'Témoignage',
            'faq' => 'FAQ',
            'contact' => 'Contact',
        ];
        return $labels[$this->type] ?? ucfirst($this->type);
    }

    /**
     * Obtenir l'icône du type
     */
    public function getTypeIconAttribute()
    {
        $icons = [
            'about' => 'info-circle',
            'event' => 'calendar-alt',
            'blog' => 'newspaper',
            'video' => 'video',
            'gallery' => 'images',
            'testimonial' => 'comment',
            'faq' => 'question-circle',
            'contact' => 'envelope',
        ];
        return $icons[$this->type] ?? 'file-alt';
    }


// Ajouter ces méthodes à la fin de la classe

/**
 * Relation polymorphique avec le parent (Activity ou Destination)
 */
public function pageable()
{
    return $this->morphTo();
}

/**
 * Scope pour récupérer les contenus par type de parent
 */
public function scopeByPageableType($query, $type)
{
    return $query->where('pageable_type', $type);
}

/**
 * Scope pour récupérer les contenus par ID de parent
 */
public function scopeByPageableId($query, $id)
{
    return $query->where('pageable_id', $id);
}
}