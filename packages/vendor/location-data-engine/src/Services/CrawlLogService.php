<?php

namespace Vendor\LocationDataEngine\Services;

use Vendor\LocationDataEngine\Models\BusinessLocation;
use Vendor\LocationDataEngine\Models\CrawlLog;
use Vendor\LocationDataEngine\Models\ScanSession;

class CrawlLogService
{
    public function info(?ScanSession $session, string $event, string $message, array $context = [], ?BusinessLocation $business = null): CrawlLog
    {
        return $this->write('info', $session, $event, $message, $context, $business);
    }

    public function warning(?ScanSession $session, string $event, string $message, array $context = [], ?BusinessLocation $business = null): CrawlLog
    {
        return $this->write('warning', $session, $event, $message, $context, $business);
    }

    public function error(?ScanSession $session, string $event, string $message, array $context = [], ?BusinessLocation $business = null): CrawlLog
    {
        return $this->write('error', $session, $event, $message, $context, $business);
    }

    protected function write(string $level, ?ScanSession $session, string $event, string $message, array $context = [], ?BusinessLocation $business = null): CrawlLog
    {
        return CrawlLog::query()->create([
            'scan_session_id' => $session?->id,
            'business_location_id' => $business?->id,
            'level' => $level,
            'event' => $event,
            'api_name' => $context['api_name'] ?? null,
            'status_code' => $context['status_code'] ?? null,
            'quota_units' => $context['quota_units'] ?? 0,
            'message' => $message,
            'context' => $context,
        ]);
    }
}
