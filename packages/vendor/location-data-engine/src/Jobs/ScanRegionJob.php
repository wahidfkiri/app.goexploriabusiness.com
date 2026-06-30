<?php

namespace Vendor\LocationDataEngine\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Vendor\LocationDataEngine\Models\ScanSession;
use Vendor\LocationDataEngine\Services\PlacesScannerService;

class ScanRegionJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(public int $scanSessionId)
    {
    }

    public function handle(PlacesScannerService $scannerService): void
    {
        $session = ScanSession::query()->findOrFail($this->scanSessionId);
        $scannerService->handleScan($session);
    }
}
