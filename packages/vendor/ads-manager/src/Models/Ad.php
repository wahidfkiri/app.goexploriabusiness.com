<?php

namespace Vendor\AdsManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ad extends Model
{
    use SoftDeletes;

    protected $table = 'ads';

    protected $fillable = [
        'titre', 'description',
        'advertiser_id', 'advertiser_name', 'advertiser_email',
        'type', 'image_path', 'video_url', 'html_content', 'text_content',
        'destination_url', 'open_new_tab',
        'format', 'width', 'height',
        'start_date', 'end_date',
        'target_etablissements', 'target_categories', 'target_pages', 'target_audience',
        'pricing_model', 'rate', 'budget_total', 'budget_spent', 'budget_daily',
        'impression_limit', 'click_limit', 'frequency_cap',
        'status', 'rejection_reason',
        'priority',
        'created_by', 'approved_by', 'approved_at',
    ];

    protected $casts = [
        'open_new_tab' => 'boolean',
        'width' => 'integer',
        'height' => 'integer',
        'priority' => 'integer',
        'budget_spent' => 'decimal:2',
        'budget_total' => 'decimal:2',
        'budget_daily' => 'decimal:2',
        'rate' => 'decimal:4',
        'impression_limit' => 'integer',
        'click_limit' => 'integer',
        'frequency_cap' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
        'target_etablissements' => 'array',
        'target_categories' => 'array',
        'target_pages' => 'array',
    ];

    public function placements()
    {
        return $this->belongsToMany(AdPlacement::class, 'ad_placement', 'ad_id', 'placement_id')
            ->using(AdPlacementPivot::class)
            ->withPivot('is_active')
            ->withTimestamps();
    }

    public function impressions()
    {
        return $this->hasMany(AdImpression::class, 'ad_id');
    }

    public function clicks()
    {
        return $this->hasMany(AdClick::class, 'ad_id');
    }

    public function dailyReports()
    {
        return $this->hasMany(AdDailyReport::class, 'ad_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByFormat($query, string $format)
    {
        return $query->where('format', $format);
    }

    public function scopeWithinDateRange($query)
    {
        $today = now()->toDateString();
        return $query
            ->where(fn ($q) => $q->whereNull('start_date')->orWhere('start_date', '<=', $today))
            ->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', $today));
    }

    public function scopeWithBudgetAvailable($query)
    {
        return $query->where(fn ($q) => $q->whereNull('budget_total')->orWhereRaw('budget_total > budget_spent'));
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? asset('storage/' . $this->image_path) : null;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Brouillon',
            'pending' => 'En attente',
            'active' => 'Active',
            'paused' => 'Pausée',
            'expired' => 'Expirée',
            'rejected' => 'Rejetée',
            default => $this->status,
        };
    }

    public function getPricingModelLabelAttribute(): string
    {
        return match ($this->pricing_model) {
            'cpm' => 'CPM',
            'cpc' => 'CPC',
            'cpa' => 'CPA',
            'flat' => 'Forfait',
            default => $this->pricing_model,
        };
    }

    public function getBudgetRemainingAttribute(): ?float
    {
        if ($this->budget_total === null) return null;
        return round(max(0, (float)$this->budget_total - (float)$this->budget_spent), 2);
    }

    public function getBudgetPercentAttribute(): ?float
    {
        if (!$this->budget_total) return null;
        return round(((float)$this->budget_spent / (float)$this->budget_total) * 100, 1);
    }
}
