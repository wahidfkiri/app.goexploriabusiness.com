<?php

namespace Vendor\MapsDataEngine\Services\Repositories;

use Illuminate\Support\Str;
use Vendor\MapsDataEngine\DTO\MapsScanRequestData;
use Vendor\MapsDataEngine\Models\MapScanSession;

class MapScanSessionRepository
{
    public function createFromRequest(MapsScanRequestData $data): MapScanSession
    {
        return MapScanSession::query()->create([
            'uuid' => (string) Str::uuid(),
            'initiated_by' => auth()->id(),
            'status' => 'pending',
            'category' => $data->category,
            'query' => $data->query,
            'country_id' => $data->countryId,
            'province_id' => $data->provinceId,
            'region_id' => $data->regionId,
            'city_id' => $data->cityId,
            'country_name' => $data->countryName,
            'province_name' => $data->provinceName,
            'region_name' => $data->regionName,
            'city_name' => $data->cityName,
            'target_label' => collect([$data->cityName, $data->regionName, $data->provinceName, $data->countryName])->filter()->implode(' / '),
            'radius' => $data->radius,
            'limit' => $data->limit,
            'with_images' => $data->withImages,
            'with_reviews' => $data->withReviews,
            'with_social_links' => $data->withSocialLinks,
            'progress' => [
                'percentage' => 0,
                'message' => 'Queued',
            ],
            'meta' => $data->toArray(),
        ]);
    }

    public function updateProgress(MapScanSession $session, array $payload): MapScanSession
    {
        $payload['last_heartbeat_at'] = now();
        $session->forceFill($payload)->save();

        return $session->fresh();
    }

    public function markRunning(MapScanSession $session, int $segmentsTotal): MapScanSession
    {
        return $this->updateProgress($session, [
            'status' => 'running',
            'started_at' => $session->started_at ?: now(),
            'segments_total' => $segmentsTotal,
            'progress' => [
                'percentage' => 3,
                'message' => 'Segments prepared',
            ],
        ]);
    }

    public function markCompleted(MapScanSession $session): MapScanSession
    {
        return $this->updateProgress($session, [
            'status' => 'completed',
            'finished_at' => now(),
            'progress' => [
                'percentage' => 100,
                'message' => 'Completed',
            ],
        ]);
    }

    public function markFailed(MapScanSession $session, string $message): MapScanSession
    {
        $meta = (array) $session->meta;
        $meta['failure'] = $message;

        return $this->updateProgress($session, [
            'status' => 'failed',
            'finished_at' => now(),
            'meta' => $meta,
            'progress' => [
                'percentage' => data_get($session->progress, 'percentage', 0),
                'message' => $message,
            ],
        ]);
    }
}
