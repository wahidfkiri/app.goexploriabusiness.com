<?php

namespace Vendor\Cms\Models;

use App\Models\Etablissement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactMessage extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'cms';
    protected $table = 'cms_contact_messages';

    protected $fillable = [
        'etablissement_id',
        'page_id',
        'assigned_to',
        'form_name',
        'source',
        'source_url',
        'referrer',
        'name',
        'first_name',
        'last_name',
        'email',
        'phone',
        'company',
        'preferred_contact_method',
        'subject',
        'message',
        'status',
        'priority',
        'consent',
        'newsletter_opt_in',
        'read_at',
        'replied_at',
        'archived_at',
        'ip_address',
        'user_agent',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'metadata',
    ];

    protected $casts = [
        'consent' => 'boolean',
        'newsletter_opt_in' => 'boolean',
        'read_at' => 'datetime',
        'replied_at' => 'datetime',
        'archived_at' => 'datetime',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function etablissement()
    {
        return $this->belongsTo(Etablissement::class, 'etablissement_id');
    }

    public function page()
    {
        return $this->belongsTo(Page::class, 'page_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function scopeUnread($query)
    {
        return $query->where('status', 'new');
    }

    public function scopeForEtablissement($query, int $etablissementId)
    {
        return $query->where('etablissement_id', $etablissementId);
    }

    public function getDisplayNameAttribute(): string
    {
        $name = trim((string) ($this->name ?: trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''))));

        return $name !== '' ? $name : 'Visiteur anonyme';
    }

    public function getStatusLabelAttribute(): string
    {
        return [
            'new' => 'Nouveau',
            'read' => 'Lu',
            'replied' => 'Répondu',
            'archived' => 'Archivé',
            'spam' => 'Spam',
        ][$this->status] ?? ucfirst((string) $this->status);
    }

    public function getPriorityLabelAttribute(): string
    {
        return [
            'low' => 'Basse',
            'normal' => 'Normale',
            'high' => 'Haute',
            'urgent' => 'Urgente',
        ][$this->priority] ?? ucfirst((string) $this->priority);
    }
}
