<?php

namespace Vendor\LocationDataEngine\Contracts;

interface PlacesClientInterface
{
    public function textSearch(string $query, ?string $pageToken = null): array;

    public function nearbySearch(float $latitude, float $longitude, int $radius, string $keyword, ?string $pageToken = null, ?string $type = null): array;

    public function placeDetails(string $placeId): array;

    public function photoUrl(string $photoReference, int $maxWidth = 1600): string;
}
