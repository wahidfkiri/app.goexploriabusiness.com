<?php

namespace Vendor\Cms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Etablissement;
use App\Models\Continent;
use App\Models\Country;
use App\Models\Province;
use App\Models\Region;
use App\Models\Ville;
use App\Models\Secteur;

class Media extends Model
{
    use SoftDeletes;

    protected $connection = 'cms';
    protected $table = 'cms_media';

    protected $fillable = [
        'etablissement_id',
        'user_id',
        'name',
        'original_name',
        'filename',
        'path',
        'size',
        'mime_type',
        'extension',
        'type',
        'video_url',
        'alt',
        'title',
        'description',
        'width',
        'height',
        'folder',
        'is_public',
        'metadata',
        'continent_id',
        'country_id',
        'province_id',
        'region_id',
        'ville_id',
        'secteur_id',
        'is_slider',
        'is_main_gallery',
        'is_facebook_gallery',
        'is_instagram_gallery',
        'is_pinterest_gallery',
        'order',

        'button_text',
        'button_url',
    ];

    protected $casts = [
        'size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'is_public' => 'boolean',
        'metadata' => 'array',
        'is_slider' => 'boolean',
        'is_main_gallery' => 'boolean',
        'is_facebook_gallery' => 'boolean',
        'is_instagram_gallery' => 'boolean',
        'is_pinterest_gallery' => 'boolean',
        'order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $appends = [
        'url',
        'formatted_size',
    ];

    /**
     * Relation avec l'établissements
     */
    public function etablissement()
    {
        return $this->belongsTo(Etablissement::class, 'etablissement_id');
    }

    public function continent()
    {
        return $this->belongsTo(Continent::class, 'continent_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id');
    }

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    public function ville()
    {
        return $this->belongsTo(Ville::class, 'ville_id');
    }

    public function secteur()
    {
        return $this->belongsTo(Secteur::class, 'secteur_id');
    }

    /**
     * Relation avec l'utilisateur qui a uploadé
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope pour les images
     */
    public function scopeImages($query)
    {
        return $query->whereIn('type', ['image', 'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml']);
    }

    /**
     * Scope pour les documents
     */
    public function scopeDocuments($query)
    {
        return $query->whereIn('type', ['document', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    /**
     * Scope pour les vidéos
     */
    public function scopeVideos($query)
    {
        return $query->whereIn('type', ['video', 'video/mp4', 'video/webm', 'video/ogg']);
    }

    /**
     * Scope pour les fichiers publics
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope par dossier
     */
    public function scopeFolder($query, $folder)
    {
        return $query->where('folder', $folder);
    }

    public function scopeSlider($query)
    {
        return $query->where('is_slider', true);
    }

    public function scopeMainGallery($query)
    {
        return $query->where('is_main_gallery', true);
    }

    public function scopeFacebookGallery($query)
    {
        return $query->where('is_facebook_gallery', true);
    }

    public function scopeInstagramGallery($query)
    {
        return $query->where('is_instagram_gallery', true);
    }

    public function scopePinterestGallery($query)
    {
        return $query->where('is_pinterest_gallery', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc')->orderBy('id', 'desc');
    }

    /**
     * Sauvegarder le chemin public sous forme d'URL absolue.
     */
    public function setPathAttribute($value): void
    {
        $this->attributes['path'] = $this->normalizePublicUrl($value);
    }

    /**
     * Obtenir l'URL du fichier
     */
    public function getUrlAttribute()
    {
        if (!empty($this->path) && preg_match('/^https?:\/\//i', $this->path)) {
            return $this->path;
        }

        $publicUrl = Storage::disk('public')->url($this->path);

        if (preg_match('/^https?:\/\//i', $publicUrl)) {
            return $publicUrl;
        }

        $baseUrl = rtrim((string) config('app.url'), '/');

        return $baseUrl !== '' ? $baseUrl . '/' . ltrim($publicUrl, '/') : url($publicUrl);
    }

    protected function normalizePublicUrl($value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        $value = trim((string) $value);

        if (preg_match('/^https?:\/\//i', $value)) {
            return $value;
        }

        $publicUrl = str_starts_with($value, '/storage/')
            || str_starts_with($value, 'storage/')
            ? '/' . ltrim($value, '/')
            : Storage::disk('public')->url(ltrim($value, '/'));

        if (preg_match('/^https?:\/\//i', $publicUrl)) {
            return $publicUrl;
        }

        $baseUrl = rtrim((string) config('app.url'), '/');

        return $baseUrl !== '' ? $baseUrl . '/' . ltrim($publicUrl, '/') : url($publicUrl);
    }

    /**
     * Obtenir la taille formatée
     */
    public function getFormattedSizeAttribute()
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Vérifier si c'est une image
     */
    public function isImage()
    {
        return in_array($this->mime_type, ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml']);
    }

    /**
     * Vérifier si c'est un document
     */
    public function isDocument()
    {
        return in_array($this->mime_type, ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    /**
     * Vérifier si c'est une vidéo
     */
    public function isVideo()
    {
        return str_starts_with($this->mime_type, 'video/');
    }

    /**
     * Obtenir l'icône Font Awesome
     */
    public function getIconAttribute()
    {
        if ($this->isImage()) {
            return 'fa-file-image';
        }
        
        if ($this->isDocument()) {
            if (str_contains($this->mime_type, 'pdf')) {
                return 'fa-file-pdf';
            }
            if (str_contains($this->mime_type, 'word')) {
                return 'fa-file-word';
            }
            if (str_contains($this->mime_type, 'excel')) {
                return 'fa-file-excel';
            }
            return 'fa-file-alt';
        }
        
        if ($this->isVideo()) {
            return 'fa-file-video';
        }
        
        return 'fa-file';
    }

    /**
     * Obtenir la couleur de l'icône
     */
    public function getIconColorAttribute()
    {
        if ($this->isImage()) {
            return '#10b981';
        }
        
        if ($this->isDocument()) {
            if (str_contains($this->mime_type, 'pdf')) {
                return '#ef4444';
            }
            if (str_contains($this->mime_type, 'word')) {
                return '#3b82f6';
            }
            if (str_contains($this->mime_type, 'excel')) {
                return '#10b981';
            }
            return '#6c757d';
        }
        
        if ($this->isVideo()) {
            return '#ef4444';
        }
        
        return '#6c757d';
    }
}
