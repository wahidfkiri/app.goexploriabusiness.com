<?php

namespace Vendor\LocationDataEngine\Services;

use Throwable;
use Vendor\LocationDataEngine\Contracts\PlacesClientInterface;
use Vendor\LocationDataEngine\Contracts\ScanSessionRepositoryInterface;
use Vendor\LocationDataEngine\DTO\PlaceSearchResultData;
use Vendor\LocationDataEngine\DTO\ScanRequestData;
use Vendor\LocationDataEngine\Helpers\PlaceCategoryHelper;
use Vendor\LocationDataEngine\Jobs\FetchPlaceDetailsJob;
use Vendor\LocationDataEngine\Models\ScanSession;

class PlacesScannerService
{
    public function __construct(
        protected PlacesClientInterface $placesClient,
        protected ScanSessionRepositoryInterface $scanSessionRepository,
        protected LocationResolverService $locationResolver,
        protected GeoGridService $geoGridService,
        protected CrawlLogService $crawlLogService,
    ) {
    }

    public function handleScan(ScanSession $session): void
    {
        $session = $this->scanSessionRepository->markRunning($session);

        $requestData = new ScanRequestData(
            countryId: $session->country_id,
            provinceId: $session->province_id,
            regionId: $session->region_id,
            cityId: $session->city_id,
            sectorId: $session->sector_id,
            category: $session->category,
            radius: $session->radius,
            limit: $session->limit,
            gridPrecision: $session->grid_precision,
            withEnrichment: (bool) $session->with_enrichment,
            withImages: (bool) $session->with_images,
            query: $session->query,
            countryName: $session->country_name,
            provinceName: $session->province_name,
            regionName: $session->region_name,
            cityName: $session->city_name,
            sectorName: $session->sector_name,
        );

        try {
            $center = $this->locationResolver->resolveScanCenter($requestData);
            $grid = $this->geoGridService->buildGrid($center['latitude'], $center['longitude'], $session->radius, $session->grid_precision);
            $this->scanSessionRepository->updateProgress($session, [
                'target_label' => $center['label'],
                'total_points' => count($grid),
                'progress_percentage' => 3,
            ]);

            $definition = PlaceCategoryHelper::definition($session->category);
            $keyword = $session->query ?: ($definition['search_terms'][0] ?? $session->category);
            $type = $definition['google_type'] ?? null;

            $scheduled = 0;
            $seen = [];
            $pointIndex = 0;

            foreach ($grid as $point) {
                $pointIndex++;

                foreach ($this->collectNearbyResults($point['latitude'], $point['longitude'], $session->radius, $keyword, $type) as $result) {
                    if (isset($seen[$result->placeId])) {
                        continue;
                    }

                    $seen[$result->placeId] = true;
                    $scheduled++;

                    FetchPlaceDetailsJob::dispatch($session->id, $result->placeId, (bool) $session->with_enrichment, (bool) $session->with_images)
                        ->onQueue((string) config('location-data-engine.scan.queue', 'default'));

                    if ($scheduled >= $session->limit) {
                        break 2;
                    }
                }

                $this->scanSessionRepository->updateProgress($session->fresh(), [
                    'scanned_points' => $pointIndex,
                    'progress_percentage' => min(85, round(($pointIndex / max(1, count($grid))) * 85, 2)),
                    'duplicates_count' => max(0, ($pointIndex * 10) - count($seen)),
                    'status' => 'running',
                ]);
            }

            foreach ($this->collectTextSearchResults(trim($keyword . ' in ' . $center['label'])) as $result) {
                if (isset($seen[$result->placeId])) {
                    continue;
                }

                $seen[$result->placeId] = true;
                $scheduled++;

                FetchPlaceDetailsJob::dispatch($session->id, $result->placeId, (bool) $session->with_enrichment, (bool) $session->with_images)
                    ->onQueue((string) config('location-data-engine.scan.queue', 'default'));

                if ($scheduled >= $session->limit) {
                    break;
                }
            }

            $this->scanSessionRepository->updateProgress($session->fresh(), [
                'status' => config('queue.default') === 'sync' ? 'completed' : 'processing_details',
                'results_count' => $scheduled,
                'progress_percentage' => config('queue.default') === 'sync' ? 100 : 92,
            ]);

            if (config('queue.default') === 'sync') {
                $this->scanSessionRepository->finish($session->fresh(), [
                    'results_count' => $scheduled,
                ]);
            }

            $this->crawlLogService->info($session->fresh(), 'scan.completed', 'Scan grid completed and detail jobs scheduled.', [
                'scheduled_jobs' => $scheduled,
                'api_name' => 'google_places',
            ]);
        } catch (Throwable $throwable) {
            $this->scanSessionRepository->fail($session->fresh(), $throwable->getMessage());
            $this->crawlLogService->error($session, 'scan.failed', $throwable->getMessage(), [
                'exception' => get_class($throwable),
                'api_name' => 'google_places',
            ]);
            throw $throwable;
        }
    }

    protected function collectNearbyResults(float $latitude, float $longitude, int $radius, string $keyword, ?string $type): array
    {
        $payloads = [];
        $nextToken = null;
        $cycles = 0;

        do {
            $response = $this->placesClient->nearbySearch($latitude, $longitude, $radius, $keyword, $nextToken, $type);
            $payloads = array_merge($payloads, (array) data_get($response, 'results', []));
            $nextToken = data_get($response, 'next_page_token');
            $cycles++;
        } while ($nextToken && $cycles < 3);

        return array_map(fn (array $row) => PlaceSearchResultData::fromArray($row), $payloads);
    }

    protected function collectTextSearchResults(string $query): array
    {
        $payloads = [];
        $nextToken = null;
        $cycles = 0;

        do {
            $response = $this->placesClient->textSearch($query, $nextToken);
            $payloads = array_merge($payloads, (array) data_get($response, 'results', []));
            $nextToken = data_get($response, 'next_page_token');
            $cycles++;
        } while ($nextToken && $cycles < 3);

        return array_map(fn (array $row) => PlaceSearchResultData::fromArray($row), $payloads);
    }
}
