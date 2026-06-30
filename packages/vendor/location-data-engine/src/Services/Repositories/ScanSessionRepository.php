<?php

namespace Vendor\LocationDataEngine\Services\Repositories;

use Illuminate\Support\Str;
use Vendor\LocationDataEngine\Contracts\ScanSessionRepositoryInterface;
use Vendor\LocationDataEngine\DTO\ScanRequestData;
use Vendor\LocationDataEngine\Models\ScanSession;

class ScanSessionRepository implements ScanSessionRepositoryInterface
{
    public function createFromRequest(ScanRequestData $data): ScanSession
    {
        return ScanSession::query()->create([
            'uuid' => (string) Str::uuid(),
            'initiated_by' => auth()->id(),
            'status' => 'pending',
            'category' => $data->category,
            'query' => $data->query,
            'country_id' => $data->countryId,
            'province_id' => $data->provinceId,
            'region_id' => $data->regionId,
            'city_id' => $data->cityId,
            'sector_id' => $data->sectorId,
            'country_name' => $data->countryName,
            'province_name' => $data->provinceName,
            'region_name' => $data->regionName,
            'city_name' => $data->cityName,
            'sector_name' => $data->sectorName,
            'target_label' => collect([$data->sectorName, $data->cityName, $data->regionName, $data->provinceName, $data->countryName])->filter()->implode(' / '),
            'radius' => $data->radius,
            'limit' => $data->limit,
            'grid_precision' => $data->gridPrecision,
            'with_enrichment' => $data->withEnrichment,
            'with_images' => $data->withImages,
            'meta' => $data->toArray(),
        ]);
    }

    public function markRunning(ScanSession $session): ScanSession
    {
        $session->forceFill([
            'status' => 'running',
            'started_at' => $session->started_at ?: now(),
            'last_heartbeat_at' => now(),
        ])->save();

        return $session->fresh();
    }

    public function updateProgress(ScanSession $session, array $payload): ScanSession
    {
        $payload['last_heartbeat_at'] = now();
        $session->forceFill($payload)->save();

        return $session->fresh();
    }

    public function finish(ScanSession $session, array $payload = []): ScanSession
    {
        $session->forceFill(array_merge($payload, [
            'status' => 'completed',
            'progress_percentage' => 100,
            'finished_at' => now(),
            'last_heartbeat_at' => now(),
        ]))->save();

        return $session->fresh();
    }

    public function fail(ScanSession $session, string $message, array $context = []): ScanSession
    {
        $meta = (array) $session->meta;
        $meta['failure'] = ['message' => $message, 'context' => $context];

        $session->forceFill([
            'status' => 'failed',
            'finished_at' => now(),
            'last_heartbeat_at' => now(),
            'meta' => $meta,
        ])->save();

        return $session->fresh();
    }
}
