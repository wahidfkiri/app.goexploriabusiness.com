<?php

namespace Vendor\Cms\Models;

use App\Models\Etablissement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CmsSlideshow extends Model
{
    use SoftDeletes;

    protected $connection = 'cms';
    protected $table = 'cms_slideshows';

    protected $fillable = [
        'etablissement_id',
        'title',
        'subtitle',
        'source',
        'video_url',
        'video_path',
        'poster_url',
        'button_text',
        'button_url',
        'button_target',
        'is_active',
        'sort_order',
        'options',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'options' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function etablissement()
    {
        return $this->belongsTo(Etablissement::class, 'etablissement_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
