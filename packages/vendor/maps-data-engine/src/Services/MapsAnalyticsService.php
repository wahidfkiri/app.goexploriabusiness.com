<?php

namespace Vendor\MapsDataEngine\Services;

use Illuminate\Support\Facades\DB;
use Vendor\MapsDataEngine\Models\MapBusinessListing;
use Vendor\MapsDataEngine\Models\MapBrowserSession;
use Vendor\MapsDataEngine\Models\MapProxyEndpoint;
use Vendor\MapsDataEngine\Models\MapScanSession;

class MapsAnalyticsService
{
    public function overview(): array
    {
        return [
            'total_sessions' => MapScanSession::query()->count(),
            'running_sessions' => MapScanSession::query()->whereIn('status', ['pending', 'running', 'processing'])->count(),
            'total_listings' => MapBusinessListing::query()->count(),
            'active_proxies' => MapProxyEndpoint::query()->where('is_active', true)->count(),
            'healthy_browser_sessions' => MapBrowserSession::query()->where('is_banned', false)->count(),
            'avg_rating' => round((float) MapBusinessListing::query()->avg('rating'), 2),
        ];
    }

    public function categoryBreakdown(): array
    {
        return MapScanSession::query()
            ->select('category', DB::raw('count(*) as total'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => ['label' => $row->category, 'value' => (int) $row->total])
            ->all();
    }
}
