<?php

namespace Vendor\LocationDataEngine\DTO;

readonly class ScanRequestData
{
    public function __construct(
        public ?int $countryId,
        public ?int $provinceId,
        public ?int $regionId,
        public ?int $cityId,
        public ?int $sectorId,
        public string $category,
        public int $radius,
        public int $limit,
        public int $gridPrecision,
        public bool $withEnrichment,
        public bool $withImages,
        public ?string $query,
        public ?string $countryName = null,
        public ?string $provinceName = null,
        public ?string $regionName = null,
        public ?string $cityName = null,
        public ?string $sectorName = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'country_id' => $this->countryId,
            'province_id' => $this->provinceId,
            'region_id' => $this->regionId,
            'city_id' => $this->cityId,
            'sector_id' => $this->sectorId,
            'category' => $this->category,
            'radius' => $this->radius,
            'limit' => $this->limit,
            'grid_precision' => $this->gridPrecision,
            'with_enrichment' => $this->withEnrichment,
            'with_images' => $this->withImages,
            'query' => $this->query,
            'country_name' => $this->countryName,
            'province_name' => $this->provinceName,
            'region_name' => $this->regionName,
            'city_name' => $this->cityName,
            'sector_name' => $this->sectorName,
        ];
    }
}
