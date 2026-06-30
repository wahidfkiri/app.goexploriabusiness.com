<?php

namespace Vendor\MapsDataEngine\DTO;

readonly class ScrapeResultData
{
    public function __construct(
        public string $name,
        public ?string $address,
        public ?float $latitude,
        public ?float $longitude,
        public ?string $website,
        public ?string $phone,
        public ?float $rating,
        public ?int $reviewsCount,
        public array $categories,
        public array $openingHours,
        public ?string $googleMapsUrl,
        public array $images,
        public array $reviewsPreview,
        public array $socialLinks,
        public array $raw,
    ) {
    }

    public static function fromArray(array $payload): self
    {
        return new self(
            name: (string) ($payload['name'] ?? ''),
            address: $payload['address'] ?? null,
            latitude: isset($payload['latitude']) ? (float) $payload['latitude'] : null,
            longitude: isset($payload['longitude']) ? (float) $payload['longitude'] : null,
            website: $payload['website'] ?? null,
            phone: $payload['phone'] ?? null,
            rating: isset($payload['rating']) ? (float) $payload['rating'] : null,
            reviewsCount: isset($payload['reviews_count']) ? (int) $payload['reviews_count'] : null,
            categories: array_values((array) ($payload['categories'] ?? [])),
            openingHours: array_values((array) ($payload['opening_hours'] ?? [])),
            googleMapsUrl: $payload['google_maps_url'] ?? null,
            images: array_values((array) ($payload['images'] ?? [])),
            reviewsPreview: array_values((array) ($payload['reviews_preview'] ?? [])),
            socialLinks: (array) ($payload['social_links'] ?? []),
            raw: $payload,
        );
    }
}
