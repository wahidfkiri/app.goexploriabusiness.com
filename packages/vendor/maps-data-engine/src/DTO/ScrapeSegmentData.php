<?php

namespace Vendor\MapsDataEngine\DTO;

readonly class ScrapeSegmentData
{
    public function __construct(
        public string $segmentKey,
        public string $label,
        public string $query,
        public ?float $latitude = null,
        public ?float $longitude = null,
        public ?string $country = null,
        public ?string $province = null,
        public ?string $region = null,
        public ?string $city = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'segment_key' => $this->segmentKey,
            'label' => $this->label,
            'query' => $this->query,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'country' => $this->country,
            'province' => $this->province,
            'region' => $this->region,
            'city' => $this->city,
        ];
    }
}
