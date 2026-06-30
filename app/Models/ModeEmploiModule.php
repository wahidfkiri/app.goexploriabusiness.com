<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModeEmploiModule extends Model
{
    protected $fillable = [
        'title', 'description', 'icon', 'order', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    public function lessons(): HasMany
    {
        return $this->hasMany(ModeEmploiLesson::class, 'module_id')->orderBy('order');
    }
}
