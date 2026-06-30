<?php

namespace Vendor\MapsDataEngine\DTO;

readonly class MapsScanRequestData
{
    public function __construct(
        public ?int $countryId,
        public ?int $provinceId,
        public ?int $regionId,
        public ?int $cityId,
        public string $category,
        public int $radius,
        public int $limit,
        public bool $withImages,
        public bool $withReviews,
        public bool $withSocialLinks,
        public ?string $query,
        public ?string $countryName = null,
        public ?string $provinceName = null,
        public ?string $regionName = null,
        public ?string $cityName = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'country_id' => $this->countryId,
            'province_id' => $this->provinceId,
            'region_id' => $this->regionId,
            'city_id' => $this->cityId,
            'category' => $this->category,
            'radius' => $this->radius,
            'limit' => $this->limit,
            'with_images' => $this->withImages,
            'with_reviews' => $this->withReviews,
            'with_social_links' => $this->withSocialLinks,
            'query' => $this->query,
            'country_name' => $this->countryName,
            'province_name' => $this->provinceName,
            'region_name' => $this->regionName,
            'city_name' => $this->cityName,
        ];
    }
}
