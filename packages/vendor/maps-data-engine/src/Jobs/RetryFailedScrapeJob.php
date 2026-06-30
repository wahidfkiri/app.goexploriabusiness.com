<?php

namespace Vendor\MapsDataEngine\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Vendor\MapsDataEngine\Models\MapScanSession;
use Vendor\MapsDataEngine\Services\MapsScrapeLogService;

class RetryFailedScrapeJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1;

    public function __construct(public int $scanSessionId, public array $segment, public string $reason)
    {
    }

    public function handle(MapsScrapeLogService $logService): void
    {
        $session = MapScanSession::query()->find($this->scanSessionId);
        if (! $session) {
            return;
        }

        $logService->warning($session, 'segment.retry.pending', 'Retry placeholder reached queue after failure.', [
            'segment' => $this->segment,
            'reason' => $this->reason,
        ]);
    }
}
