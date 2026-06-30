<?php

namespace Vendor\LocationDataEngine\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Vendor\LocationDataEngine\Models\CrawlLog;
use Vendor\LocationDataEngine\Models\ScanSession;

class LogsController extends Controller
{
    public function index(): View
    {
        return view('location-data-engine::admin.logs.index', [
            'sessions' => ScanSession::query()->latest()->limit(20)->get(),
            'latestLogs' => CrawlLog::query()->latest()->limit(50)->get(),
        ]);
    }
}
