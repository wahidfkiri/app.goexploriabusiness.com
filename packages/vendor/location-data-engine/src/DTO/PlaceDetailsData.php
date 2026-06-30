<?php

namespace Vendor\LocationDataEngine\DTO;

readonly class PlaceDetailsData
{
    public function __construct(
        public string $placeId,
        public string $name,
        public ?string $address,
        public float $latitude,
        public float $longitude,
        public ?string $phone,
        public ?string $internationalPhone,
        public ?string $website,
        public ?float $rating,
        public ?int $reviewsCount,
        public array $categories,
        public ?string $businessStatus,
        public array $openingHours,
        public ?string $timezone,
        public ?string $googleMapsUrl,
        public ?string $country,
        public ?string $province,
        public ?string $city,
        public ?string $postalCode,
        public array $photos,
        public array $reviews,
        public array $raw,
    ) {
    }

    public static function fromArray(array $payload): self
    {
        $components = collect((array) data_get($payload, 'address_components', []));

        $findComponent = static function (string $type) use ($components): ?string {
            $component = $components->first(function (array $item) use ($type) {
                return in_array($type, (array) ($item['types'] ?? []), true);
            });

            return $component['long_name'] ?? null;
        };

        return new self(
            placeId: (string) data_get($payload, 'place_id', ''),
            name: (string) data_get($payload, 'name', ''),
            address: data_get($payload, 'formatted_address'),
            latitude: (float) data_get($payload, 'geometry.location.lat', 0),
            longitude: (float) data_get($payload, 'geometry.location.lng', 0),
            phone: data_get($payload, 'formatted_phone_number'),
            internationalPhone: data_get($payload, 'international_phone_number'),
            website: data_get($payload, 'website'),
            rating: data_get($payload, 'rating') !== null ? (float) data_get($payload, 'rating') : null,
            reviewsCount: data_get($payload, 'user_ratings_total') !== null ? (int) data_get($payload, 'user_ratings_total') : null,
            categories: (array) data_get($payload, 'types', []),
            businessStatus: data_get($payload, 'business_status'),
            openingHours: (array) data_get($payload, 'opening_hours', []),
            timezone: data_get($payload, 'utc_offset') !== null ? (string) data_get($payload, 'utc_offset') : null,
            googleMapsUrl: data_get($payload, 'url'),
            country: $findComponent('country'),
            province: $findComponent('administrative_area_level_1'),
            city: $findComponent('locality') ?: $findComponent('administrative_area_level_3'),
            postalCode: $findComponent('postal_code'),
            photos: (array) data_get($payload, 'photos', []),
            reviews: (array) data_get($payload, 'reviews', []),
            raw: $payload,
        );
    }
}
