<?php

namespace Vendor\MapsDataEngine\Services;

use App\Models\Country;
use App\Models\Province;
use App\Models\Region;
use App\Models\Ville;
use Vendor\MapsDataEngine\DTO\MapsScanRequestData;
use Vendor\MapsDataEngine\DTO\ScrapeSegmentData;

class GeoSegmentationService
{
    public function buildSegments(MapsScanRequestData $data): array
    {
        $categoryLabel = ucfirst($data->category);

        if ($data->cityId) {
            $city = Ville::query()->findOrFail($data->cityId);

            return $this->segmentsFromAnchor(
                anchorKey: 'city-' . $city->id,
                label: $city->name,
                query: $data->query ?: sprintf('%s %s', $city->name, $categoryLabel),
                latitude: $city->latitude,
                longitude: $city->longitude,
                country: $city->country?->name ?? $data->countryName,
                province: $city->province?->name ?? $data->provinceName,
                region: $city->region?->name ?? $data->regionName,
                city: $city->name,
            );
        }

        if ($data->regionId) {
            $segments = Ville::query()
                ->where('region_id', $data->regionId)
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->orderBy('name')
                ->limit((int) config('maps-data-engine.scraping.segment_batch_size', 5) * 2)
                ->get()
                ->flatMap(fn (Ville $city) => $this->segmentsFromAnchor(
                    anchorKey: 'city-' . $city->id,
                    label: $city->name,
                    query: $data->query ?: sprintf('%s %s', $city->name, $categoryLabel),
                    latitude: $city->latitude,
                    longitude: $city->longitude,
                    country: $city->country?->name,
                    province: $city->province?->name,
                    region: $city->region?->name,
                    city: $city->name,
                ))->all();

            if (! empty($segments)) {
                return $segments;
            }

            $region = Region::query()->find($data->regionId);

            return $this->segmentsFromAnchor(
                anchorKey: 'region-' . $data->regionId,
                label: $region?->name ?? ($data->regionName ?? 'Region'),
                query: $data->query ?: sprintf('%s %s', $region?->name ?? ($data->regionName ?? 'Region'), $categoryLabel),
                latitude: $region?->latitude ? (float) $region->latitude : null,
                longitude: $region?->longitude ? (float) $region->longitude : null,
                country: $region?->country?->name ?? $data->countryName,
                province: $region?->province?->name ?? $data->provinceName,
                region: $region?->name ?? $data->regionName,
                city: $data->cityName,
            );
        }

        if ($data->provinceId) {
            $segments = Ville::query()
                ->where('province_id', $data->provinceId)
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->orderByDesc('population')
                ->limit((int) config('maps-data-engine.scraping.segment_batch_size', 5) * 3)
                ->get()
                ->flatMap(fn (Ville $city) => $this->segmentsFromAnchor(
                    anchorKey: 'city-' . $city->id,
                    label: $city->name,
                    query: $data->query ?: sprintf('%s %s', $city->name, $categoryLabel),
                    latitude: $city->latitude,
                    longitude: $city->longitude,
                    country: $city->country?->name,
                    province: $city->province?->name,
                    region: $city->region?->name,
                    city: $city->name,
                ))->all();

            if (! empty($segments)) {
                return $segments;
            }

            $province = Province::query()->find($data->provinceId);

            return $this->segmentsFromAnchor(
                anchorKey: 'province-' . $data->provinceId,
                label: $province?->name ?? ($data->provinceName ?? 'Province'),
                query: $data->query ?: sprintf('%s %s', $province?->name ?? ($data->provinceName ?? 'Province'), $categoryLabel),
                latitude: $province?->latitude ? (float) $province->latitude : null,
                longitude: $province?->longitude ? (float) $province->longitude : null,
                country: $province?->country?->name ?? $data->countryName,
                province: $province?->name ?? $data->provinceName,
                region: $data->regionName,
                city: $data->cityName,
            );
        }

        if ($data->countryId) {
            $segments = Province::query()
                ->where('country_id', $data->countryId)
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->orderBy('name')
                ->limit((int) config('maps-data-engine.scraping.segment_batch_size', 5) * 2)
                ->get()
                ->flatMap(fn (Province $province) => $this->segmentsFromAnchor(
                    anchorKey: 'province-' . $province->id,
                    label: $province->name,
                    query: $data->query ?: sprintf('%s %s', $province->name, $categoryLabel),
                    latitude: $province->latitude,
                    longitude: $province->longitude,
                    country: $province->country?->name,
                    province: $province->name,
                ))->all();

            if (! empty($segments)) {
                return $segments;
            }

            $country = Country::query()->find($data->countryId);

            return $this->segmentsFromAnchor(
                anchorKey: 'country-' . $data->countryId,
                label: $country?->name ?? ($data->countryName ?? 'Country'),
                query: $data->query ?: sprintf('%s %s', $country?->name ?? ($data->countryName ?? 'Country'), $categoryLabel),
                latitude: $country?->latitude ? (float) $country->latitude : null,
                longitude: $country?->longitude ? (float) $country->longitude : null,
                country: $country?->name ?? $data->countryName,
                province: $data->provinceName,
                region: $data->regionName,
                city: $data->cityName,
            );
        }

        return Country::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('name')
            ->limit((int) config('maps-data-engine.scraping.segment_batch_size', 5))
            ->get()
            ->flatMap(fn (Country $country) => $this->segmentsFromAnchor(
                anchorKey: 'country-' . $country->id,
                label: $country->name,
                query: $data->query ?: sprintf('%s %s', $country->name, $categoryLabel),
                latitude: $country->latitude,
                longitude: $country->longitude,
                country: $country->name,
            ))->all();
    }

    protected function segmentsFromAnchor(
        string $anchorKey,
        string $label,
        string $query,
        ?float $latitude,
        ?float $longitude,
        ?string $country = null,
        ?string $province = null,
        ?string $region = null,
        ?string $city = null,
    ): array {
        if (! $latitude || ! $longitude || ! config('maps-data-engine.grid.enabled', true)) {
            return [new ScrapeSegmentData(
                segmentKey: $anchorKey,
                label: $label,
                query: $query,
                latitude: $latitude,
                longitude: $longitude,
                country: $country,
                province: $province,
                region: $region,
                city: $city,
            )];
        }

        $stepKm = (float) config('maps-data-engine.grid.step_km', 3.5);
        $pointsPerAnchor = max(1, (int) config('maps-data-engine.grid.points_per_anchor', 5));
        $latStep = $stepKm / 111;
        $lonStep = $stepKm / max(cos(deg2rad($latitude)) * 111, 1);
        $offsets = [
            [0.0, 0.0],
            [$latStep, 0.0],
            [-$latStep, 0.0],
            [0.0, $lonStep],
            [0.0, -$lonStep],
            [$latStep, $lonStep],
            [$latStep, -$lonStep],
            [-$latStep, $lonStep],
            [-$latStep, -$lonStep],
        ];

        return collect(array_slice($offsets, 0, $pointsPerAnchor))
            ->map(fn (array $offset, int $index) => new ScrapeSegmentData(
                segmentKey: $anchorKey . '-grid-' . ($index + 1),
                label: $index === 0 ? $label : $label . ' grid ' . ($index + 1),
                query: $query,
                latitude: round($latitude + $offset[0], 8),
                longitude: round($longitude + $offset[1], 8),
                country: $country,
                province: $province,
                region: $region,
                city: $city,
            ))
            ->all();
    }
}
