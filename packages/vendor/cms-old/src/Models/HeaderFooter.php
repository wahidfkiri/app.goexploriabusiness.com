<?php

namespace Vendor\Cms\Models;

use App\Models\Etablissement;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HeaderFooter extends Model
{
    use SoftDeletes;

    public const TYPE_HEADER = 'header';
    public const TYPE_FOOTER = 'footer';

    protected $connection = 'cms';
    protected $table = 'cms_header_footers';

    protected $fillable = [
        'etablissement_id',
        'type',
        'name',
        'content',
        'html_content',
        'css_content',
        'settings',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'settings' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function etablissement()
    {
        return $this->belongsTo(Etablissement::class, 'etablissement_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeForType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function getRenderedContentAttribute(): string
    {
        if (!empty($this->content)) {
            return (string) $this->content;
        }

        $html = (string) ($this->html_content ?? '');
        $css = trim((string) ($this->css_content ?? ''));

        return $css !== '' ? '<style>' . $css . '</style>' . $html : $html;
    }
}
