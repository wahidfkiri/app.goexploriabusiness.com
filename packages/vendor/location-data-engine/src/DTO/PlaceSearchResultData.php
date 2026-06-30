<?php

namespace Vendor\LocationDataEngine\DTO;

readonly class PlaceSearchResultData
{
    public function __construct(
        public string $placeId,
        public string $name,
        public ?string $address,
        public float $latitude,
        public float $longitude,
        public array $types,
        public array $raw,
    ) {
    }

    public static function fromArray(array $payload): self
    {
        return new self(
            placeId: (string) data_get($payload, 'place_id', ''),
            name: (string) data_get($payload, 'name', ''),
            address: data_get($payload, 'formatted_address') ?: data_get($payload, 'vicinity'),
            latitude: (float) data_get($payload, 'geometry.location.lat', 0),
            longitude: (float) data_get($payload, 'geometry.location.lng', 0),
            types: (array) data_get($payload, 'types', []),
            raw: $payload,
        );
    }
}
