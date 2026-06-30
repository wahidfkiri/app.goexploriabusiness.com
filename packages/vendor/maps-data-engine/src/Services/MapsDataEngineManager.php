<?php

namespace Vendor\MapsDataEngine\Services;

use Vendor\MapsDataEngine\DTO\MapsScanRequestData;
use Vendor\MapsDataEngine\Jobs\ScanRegionJob;
use Vendor\MapsDataEngine\Models\MapScanSession;
use Vendor\MapsDataEngine\Services\Repositories\MapScanSessionRepository;

class MapsDataEngineManager
{
    public function __construct(protected MapScanSessionRepository $sessionRepository)
    {
    }

    public function startScan(MapsScanRequestData $data): MapScanSession
    {
        $session = $this->sessionRepository->createFromRequest($data);

        ScanRegionJob::dispatch($session->id)->onQueue((string) config('maps-data-engine.runtime.queue', 'default'));

        return $session->fresh();
    }
}
