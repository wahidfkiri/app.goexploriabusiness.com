<?php

namespace Vendor\LocationDataEngine\Helpers;

class PlaceCategoryHelper
{
    public static function normalize(?string $category): string
    {
        $category = strtolower(trim((string) $category));

        return array_key_exists($category, config('location-data-engine.categories', []))
            ? $category
            : 'services';
    }

    public static function definition(string $category): array
    {
        $category = static::normalize($category);

        return config("location-data-engine.categories.{$category}", config('location-data-engine.categories.services', []));
    }
}
