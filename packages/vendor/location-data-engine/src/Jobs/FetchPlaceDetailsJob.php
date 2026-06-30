<?php

namespace Vendor\LocationDataEngine\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Vendor\LocationDataEngine\Contracts\BusinessLocationRepositoryInterface;
use Vendor\LocationDataEngine\Contracts\PlacesClientInterface;
use Vendor\LocationDataEngine\DTO\PlaceDetailsData;
use Vendor\LocationDataEngine\Models\ScanSession;
use Vendor\LocationDataEngine\Services\CrawlLogService;
use Vendor\LocationDataEngine\Services\Repositories\ScanSessionRepository;

class FetchPlaceDetailsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(
        public int $scanSessionId,
        public string $placeId,
        public bool $withEnrichment = false,
        public bool $withImages = false,
    ) {
    }

    public function handle(
        PlacesClientInterface $placesClient,
        BusinessLocationRepositoryInterface $repository,
        CrawlLogService $crawlLogService,
        ScanSessionRepository $scanSessionRepository,
    ): void {
        $session = ScanSession::query()->findOrFail($this->scanSessionId);
        $details = PlaceDetailsData::fromArray(data_get($placesClient->placeDetails($this->placeId), 'result', []));
        $business = $repository->upsertFromPlaceDetails($session, $details);

        $scanSessionRepository->updateProgress($session, [
            'results_count' => (int) $session->fresh()->businesses()->count(),
            'quota_used' => ((int) $session->quota_used) + 1,
        ]);

        $crawlLogService->info($session, 'place.details', 'Fetched place details.', [
            'place_id' => $this->placeId,
            'api_name' => 'google_places_details',
            'quota_units' => 1,
        ], $business);

        if ($this->withEnrichment && $business->website) {
            WebsiteEnrichmentJob::dispatch($business->id)->onQueue((string) config('location-data-engine.scan.queue', 'default'));
        }

        if ($this->withImages && $details->photos !== []) {
            DownloadImagesJob::dispatch($business->id, $details->photos)->onQueue((string) config('location-data-engine.scan.queue', 'default'));
        }
    }
}
