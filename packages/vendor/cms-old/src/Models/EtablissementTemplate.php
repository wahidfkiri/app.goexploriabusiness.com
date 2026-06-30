<?php

namespace Vendor\Cms\Models;

use App\Models\Etablissement;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EtablissementTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'cms';
    protected $table = 'etablissement_templates';

    protected $fillable = [
        'etablissement_id',
        'template_cms_id',
        'cms_page_id',
        'page_contents',
        'status',
        'is_active',
        'config',
        'installed_at',
        'activated_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'config' => 'array',
        'installed_at' => 'datetime',
        'activated_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function etablissement()
    {
        return $this->belongsTo(Etablissement::class, 'etablissement_id');
    }

    public function template()
    {
        return $this->belongsTo(CmsTemplate::class, 'template_cms_id');
    }

    public function page()
    {
        return $this->belongsTo(Page::class, 'cms_page_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
