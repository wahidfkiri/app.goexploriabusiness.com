<?php

namespace Vendor\MapsDataEngine\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Vendor\MapsDataEngine\DTO\MapsScanRequestData;
use Vendor\MapsDataEngine\Models\MapScanSession;
use Vendor\MapsDataEngine\Services\GeoSegmentationService;
use Vendor\MapsDataEngine\Services\MapsScrapeLogService;
use Vendor\MapsDataEngine\Services\Repositories\MapScanSessionRepository;

class ScanRegionJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 2;

    public function __construct(public int $scanSessionId)
    {
    }

    public function handle(
        GeoSegmentationService $segmentationService,
        MapScanSessionRepository $sessionRepository,
        MapsScrapeLogService $logService,
    ): void {
        $session = MapScanSession::query()->findOrFail($this->scanSessionId);
        $data = new MapsScanRequestData(
            countryId: $session->country_id,
            provinceId: $session->province_id,
            regionId: $session->region_id,
            cityId: $session->city_id,
            category: $session->category,
            radius: $session->radius,
            limit: $session->limit,
            withImages: (bool) $session->with_images,
            withReviews: (bool) $session->with_reviews,
            withSocialLinks: (bool) $session->with_social_links,
            query: $session->query,
            countryName: $session->country_name,
            provinceName: $session->province_name,
            regionName: $session->region_name,
            cityName: $session->city_name,
        );

        $segments = $segmentationService->buildSegments($data);
        if (empty($segments)) {
            $sessionRepository->markFailed($session, 'No segments generated for selected scope.');
            $logService->warning($session, 'scan.no_segments', 'No segments generated for selected scope.', [
                'country_id' => $session->country_id,
                'province_id' => $session->province_id,
                'region_id' => $session->region_id,
                'city_id' => $session->city_id,
                'query' => $session->query,
            ]);

            return;
        }

        $session = $sessionRepository->markRunning($session, count($segments));
        $logService->info($session, 'scan.started', 'Maps scan started.', ['segments' => count($segments)]);

        $spacing = max(0, (int) config('maps-data-engine.runtime.dispatch_spacing_seconds', 12));

        foreach ($segments as $index => $segment) {
            ScrapeGoogleMapsJob::dispatch($session->id, $segment->toArray())
                ->delay(now()->addSeconds($index * $spacing))
                ->onQueue((string) config('maps-data-engine.runtime.queue', 'default'));
        }
    }
}
