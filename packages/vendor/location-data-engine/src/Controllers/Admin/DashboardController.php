<?php

namespace Vendor\LocationDataEngine\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\View\View;
use Vendor\LocationDataEngine\Models\ScanSession;
use Vendor\LocationDataEngine\Services\ScanAnalyticsService;

class DashboardController extends Controller
{
    public function index(ScanAnalyticsService $analytics): View
    {
        return view('location-data-engine::admin.dashboard', [
            'stats' => $analytics->overview(),
            'categoryBreakdown' => $analytics->categoryBreakdown(),
            'categories' => config('location-data-engine.categories', []),
            'countries' => Country::query()->orderBy('name')->get(['id', 'name']),
            'recentSessions' => ScanSession::query()->latest()->limit(6)->get(),
        ]);
    }
}
