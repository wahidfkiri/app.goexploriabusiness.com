<?php

namespace Vendor\MapsDataEngine\Controllers\Admin;

use App\Http\Controllers\Controller;
use Vendor\MapsDataEngine\Models\MapBrowserSession;
use Vendor\MapsDataEngine\Models\MapProxyEndpoint;
use Vendor\MapsDataEngine\Models\MapScrapeLog;
use Vendor\MapsDataEngine\Models\MapScanSession;

class LogsController extends Controller
{
    public function index()
    {
        return view('maps-data-engine::admin.logs.index', [
            'sessions' => MapScanSession::query()->latest()->limit(20)->get(),
            'logs' => MapScrapeLog::query()->latest()->limit(50)->get(),
            'proxies' => MapProxyEndpoint::query()->orderByDesc('health_score')->limit(10)->get(),
            'browserSessions' => MapBrowserSession::query()->latest()->limit(10)->get(),
        ]);
    }
}
