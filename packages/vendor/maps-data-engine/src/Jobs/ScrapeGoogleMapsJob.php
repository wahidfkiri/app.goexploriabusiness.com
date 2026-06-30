<?php

namespace Vendor\MapsDataEngine\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;
use Vendor\MapsDataEngine\DTO\ScrapeSegmentData;
use Vendor\MapsDataEngine\Models\MapScanSession;
use Vendor\MapsDataEngine\Scrapers\GoogleMapsScrapeOrchestrator;
use Vendor\MapsDataEngine\Services\Repositories\MapScanSessionRepository;

class ScrapeGoogleMapsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 2;

    public function __construct(public int $scanSessionId, public array $segment)
    {
    }

    public function handle(GoogleMapsScrapeOrchestrator $orchestrator, MapScanSessionRepository $sessionRepository): void
    {
        $session = MapScanSession::query()->findOrFail($this->scanSessionId);
        $segment = new ScrapeSegmentData(
            segmentKey: (string) ($this->segment['segment_key'] ?? uniqid('segment_', true)),
            label: (string) ($this->segment['label'] ?? 'Unknown segment'),
            query: (string) ($this->segment['query'] ?? ''),
            latitude: isset($this->segment['latitude']) ? (float) $this->segment['latitude'] : null,
            longitude: isset($this->segment['longitude']) ? (float) $this->segment['longitude'] : null,
            country: $this->segment['country'] ?? null,
            province: $this->segment['province'] ?? null,
            region: $this->segment['region'] ?? null,
            city: $this->segment['city'] ?? null,
        );

        try {
            $result = $orchestrator->scrape($session, $segment);
            $fresh = $session->fresh();
            $completed = $fresh->segments_completed + 1;
            $progress = $fresh->segments_total > 0 ? round(($completed / $fresh->segments_total) * 100, 2) : 100;

            $sessionRepository->updateProgress($fresh, [
                'segments_completed' => $completed,
                'results_count' => $fresh->results_count + count((array) ($result['items'] ?? [])),
                'captcha_incidents' => $fresh->captcha_incidents + (($result['status'] ?? null) === 'captcha' ? 1 : 0),
                'proxy_rotations' => $fresh->proxy_rotations + (($result['status'] ?? null) === 'captcha' ? 1 : 0),
                'progress' => [
                    'percentage' => $progress,
                    'message' => 'Processed ' . $segment->label,
                ],
            ]);

            if ($completed >= $fresh->segments_total) {
                $sessionRepository->markCompleted($fresh->fresh());
            }
        } catch (Throwable $throwable) {
            RetryFailedScrapeJob::dispatch($this->scanSessionId, $this->segment, $throwable->getMessage())
                ->delay(now()->addMinutes(5))
                ->onQueue((string) config('maps-data-engine.runtime.queue', 'default'));

            $sessionRepository->updateProgress($session->fresh(), [
                'retry_count' => $session->retry_count + 1,
                'progress' => [
                    'percentage' => data_get($session->progress, 'percentage', 0),
                    'message' => 'Retry scheduled for ' . $segment->label,
                ],
            ]);
        }
    }
}
