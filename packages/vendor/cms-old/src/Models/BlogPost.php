<?php

namespace Vendor\Cms\Models;

use App\Models\Etablissement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'cms';
    protected $table = 'cms_blog_posts';

    protected $fillable = [
        'etablissement_id',
        'user_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'tags',
        'status',
        'is_featured',
        'allow_comments',
        'published_at',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'canonical_url',
        'og_image_url',
    ];

    protected $casts = [
        'tags' => 'array',
        'is_featured' => 'boolean',
        'allow_comments' => 'boolean',
        'published_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (BlogPost $post) {
            if (blank($post->slug)) {
                $post->slug = Str::slug($post->title);
            }

            if (!blank($post->slug)) {
                $post->slug = static::generateUniqueSlug($post->slug, (int) $post->etablissement_id);
            }
        });
    }

    public function etablissement()
    {
        return $this->belongsTo(Etablissement::class, 'etablissement_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function getDisplayTitleAttribute(): string
    {
        return $this->seo_title ?: $this->title;
    }

    public function getReadingTimeAttribute(): int
    {
        $text = strip_tags((string) $this->content);
        $wordCount = str_word_count($text);

        return max(1, (int) ceil($wordCount / 200));
    }

    public static function generateUniqueSlug(string $slug, int $etablissementId, ?int $ignoreId = null): string
    {
        $base = Str::slug($slug);
        $base = $base ?: 'article';
        $candidate = $base;
        $counter = 1;

        while (static::query()
            ->where('etablissement_id', $etablissementId)
            ->where('slug', $candidate)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $candidate = $base . '-' . $counter;
            $counter++;
        }

        return $candidate;
    }
}
