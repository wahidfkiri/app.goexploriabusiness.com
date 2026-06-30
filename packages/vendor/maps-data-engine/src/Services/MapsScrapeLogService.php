<?php

namespace Vendor\MapsDataEngine\Services;

use Vendor\MapsDataEngine\Models\MapBusinessListing;
use Vendor\MapsDataEngine\Models\MapScanSession;
use Vendor\MapsDataEngine\Models\MapScrapeLog;

class MapsScrapeLogService
{
    public function write(?MapScanSession $session, string $level, string $event, string $message, array $context = [], ?MapBusinessListing $listing = null): MapScrapeLog
    {
        return MapScrapeLog::query()->create([
            'scan_session_id' => $session?->id,
            'business_listing_id' => $listing?->id,
            'level' => $level,
            'event' => $event,
            'message' => $message,
            'context' => $context,
        ]);
    }

    public function info(?MapScanSession $session, string $event, string $message, array $context = [], ?MapBusinessListing $listing = null): MapScrapeLog
    {
        return $this->write($session, 'info', $event, $message, $context, $listing);
    }

    public function warning(?MapScanSession $session, string $event, string $message, array $context = [], ?MapBusinessListing $listing = null): MapScrapeLog
    {
        return $this->write($session, 'warning', $event, $message, $context, $listing);
    }

    public function error(?MapScanSession $session, string $event, string $message, array $context = [], ?MapBusinessListing $listing = null): MapScrapeLog
    {
        return $this->write($session, 'error', $event, $message, $context, $listing);
    }
}
