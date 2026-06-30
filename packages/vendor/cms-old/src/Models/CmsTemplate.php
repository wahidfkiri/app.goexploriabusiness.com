<?php

namespace Vendor\Cms\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class CmsTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'cms';
    protected $table = 'cms_templates';

    protected $fillable = [
        'name',
        'slug',
        'site_url',
        'page_content',
        'version',
        'creator_id',
        'category',
        'image_preview',
        'description',
        'status',
        'is_active',
        'meta',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'meta' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (CmsTemplate $template) {
            if (empty($template->slug)) {
                $template->slug = static::uniqueSlug($template->name);
            }
        });
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function etablissementTemplates()
    {
        return $this->hasMany(EtablissementTemplate::class, 'template_cms_id');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'active')->where('is_active', true);
    }

    public function getPreviewImageUrl(): ?string
    {
        return $this->image_preview ?: null;
    }

    public static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'template';
        $slug = $base;
        $counter = 2;

        while (static::query()
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
