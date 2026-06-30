<?php

namespace Vendor\MapsDataEngine\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Vendor\MapsDataEngine\Models\MapProxyEndpoint;
use Vendor\MapsDataEngine\Models\MapScanSession;
use Vendor\MapsDataEngine\Services\MapsAnalyticsService;

class DashboardController extends Controller
{
    public function index(MapsAnalyticsService $analytics)
    {
        return view('maps-data-engine::admin.dashboard', [
            'stats' => $analytics->overview(),
            'categoryBreakdown' => $analytics->categoryBreakdown(),
            'categories' => config('maps-data-engine.scraping.categories', []),
            'countries' => Country::query()->orderBy('name')->get(['id', 'name']),
            'recentSessions' => MapScanSession::query()->latest()->limit(8)->get(),
            'proxyCount' => MapProxyEndpoint::query()->count(),
        ]);
    }
}
