<?php

namespace Vendor\LocationDataEngine\Services;

use App\Models\Continent;
use App\Models\Country;
use App\Models\Province;
use App\Models\Region;
use App\Models\Secteur;
use App\Models\Ville;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;
use Vendor\LocationDataEngine\DTO\ScanRequestData;

class LocationResolverService
{
    public function resolveScanCenter(ScanRequestData $data): array
    {
        $model = $this->resolveModel($data);

        $latitude = (float) data_get($model, 'latitude', 0);
        $longitude = (float) data_get($model, 'longitude', 0);

        if (! $latitude || ! $longitude) {
            throw new RuntimeException('Selected location has no coordinates.');
        }

        return [
            'label' => (string) data_get($model, 'name', 'Unknown location'),
            'latitude' => $latitude,
            'longitude' => $longitude,
            'model' => $model,
        ];
    }

    public function resolveModel(ScanRequestData $data): Model
    {
        return match (true) {
            (bool) $data->sectorId => Secteur::query()->findOrFail($data->sectorId),
            (bool) $data->cityId => Ville::query()->findOrFail($data->cityId),
            (bool) $data->regionId => Region::query()->findOrFail($data->regionId),
            (bool) $data->provinceId => Province::query()->findOrFail($data->provinceId),
            (bool) $data->countryId => Country::query()->findOrFail($data->countryId),
            default => Continent::query()->findOrFail(1),
        };
    }

    public function resolveIdsByNames(?string $country, ?string $province, ?string $city): array
    {
        $countryModel = $country ? Country::query()->where('name', $country)->orWhere('code', $country)->first() : null;
        $provinceModel = $province ? Province::query()->where('name', $province)->first() : null;
        $cityModel = $city ? Ville::query()->where('name', $city)->first() : null;

        return [
            'country_id' => $countryModel?->id,
            'province_id' => $provinceModel?->id,
            'city_id' => $cityModel?->id,
        ];
    }
}
