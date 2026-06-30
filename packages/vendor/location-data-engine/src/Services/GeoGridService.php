<?php

namespace Vendor\LocationDataEngine\Services;

class GeoGridService
{
    public function buildGrid(float $latitude, float $longitude, int $radiusMeters, int $precision = 5): array
    {
        $precision = max(1, min($precision, 9));
        $stepMeters = max(2500, (int) floor($radiusMeters / max(1, $precision - 1)));
        $latStep = $stepMeters / 111320;
        $lngStep = $stepMeters / (111320 * max(cos(deg2rad($latitude)), 0.1));

        $points = [];

        for ($x = -$precision; $x <= $precision; $x++) {
            for ($y = -$precision; $y <= $precision; $y++) {
                $points[] = [
                    'latitude' => round($latitude + ($x * $latStep), 8),
                    'longitude' => round($longitude + ($y * $lngStep), 8),
                ];
            }
        }

        return array_values(array_unique($points, SORT_REGULAR));
    }
}
