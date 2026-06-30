<?php

namespace Vendor\LocationDataEngine\Services;

use Illuminate\Support\Facades\DB;
use Vendor\LocationDataEngine\Models\BusinessLocation;
use Vendor\LocationDataEngine\Models\CrawlLog;
use Vendor\LocationDataEngine\Models\ScanSession;

class ScanAnalyticsService
{
    public function overview(): array
    {
        return [
            'total_sessions' => ScanSession::query()->count(),
            'running_sessions' => ScanSession::query()->whereIn('status', ['pending', 'running', 'processing_details'])->count(),
            'completed_sessions' => ScanSession::query()->where('status', 'completed')->count(),
            'total_businesses' => BusinessLocation::query()->count(),
            'avg_rating' => round((float) BusinessLocation::query()->avg('rating'), 2),
            'api_calls' => CrawlLog::query()->whereNotNull('api_name')->count(),
            'quota_used' => (int) CrawlLog::query()->sum('quota_units'),
        ];
    }

    public function categoryBreakdown(): array
    {
        return ScanSession::query()
            ->select('category', DB::raw('count(*) as total'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => ['label' => $row->category, 'value' => (int) $row->total])
            ->all();
    }
}
