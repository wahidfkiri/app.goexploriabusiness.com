<?php

namespace Vendor\LocationDataEngine\Services;

use Vendor\LocationDataEngine\Contracts\ScanSessionRepositoryInterface;
use Vendor\LocationDataEngine\DTO\ScanRequestData;
use Vendor\LocationDataEngine\Jobs\ScanRegionJob;
use Vendor\LocationDataEngine\Models\ScanSession;

class LocationDataEngineManager
{
    public function __construct(protected ScanSessionRepositoryInterface $scanSessionRepository)
    {
    }

    public function startScan(ScanRequestData $data): ScanSession
    {
        $session = $this->scanSessionRepository->createFromRequest($data);

        ScanRegionJob::dispatch($session->id)->onQueue((string) config('location-data-engine.scan.queue', 'default'));

        return $session->fresh();
    }
}
