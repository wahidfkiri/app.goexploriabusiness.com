<?php

namespace Vendor\MapsDataEngine\Services;

use App\Models\Country;
use App\Models\Province;
use App\Models\Region;
use App\Models\Ville;

class LocationScopeResolverService
{
    public function resolveIdsByNames(?string $country, ?string $province, ?string $region, ?string $city): array
    {
        return [
            'country_id' => $country ? Country::query()->where('name', $country)->orWhere('code', $country)->value('id') : null,
            'province_id' => $province ? Province::query()->where('name', $province)->value('id') : null,
            'region_id' => $region ? Region::query()->where('name', $region)->value('id') : null,
            'city_id' => $city ? Ville::query()->where('name', $city)->value('id') : null,
        ];
    }
}
