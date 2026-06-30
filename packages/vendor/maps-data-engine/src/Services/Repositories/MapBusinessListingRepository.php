<?php

namespace Vendor\MapsDataEngine\Services\Repositories;

use Illuminate\Support\Str;
use Vendor\MapsDataEngine\DTO\ScrapeResultData;
use Vendor\MapsDataEngine\Models\MapBusinessListing;
use Vendor\MapsDataEngine\Models\MapScanSession;

class MapBusinessListingRepository
{
    public function upsertFromScrapeResult(MapScanSession $session, ScrapeResultData $data, array $context = []): MapBusinessListing
    {
        return MapBusinessListing::query()->updateOrCreate(
            [
                'name' => $data->name,
                'address' => $data->address,
            ],
            [
                'latest_scan_session_id' => $session->id,
                'external_id' => md5(($data->googleMapsUrl ?? $data->name) . '|' . ($data->address ?? '')),
                'slug' => Str::slug($data->name . '-' . substr(md5((string) $data->address), 0, 8)),
                'latitude' => $data->latitude,
                'longitude' => $data->longitude,
                'website' => $data->website,
                'phone' => $data->phone,
                'rating' => $data->rating,
                'reviews_count' => $data->reviewsCount ?? 0,
                'categories' => $data->categories,
                'opening_hours' => $data->openingHours,
                'google_maps_url' => $data->googleMapsUrl,
                'images' => $data->images,
                'reviews_preview' => $data->reviewsPreview,
                'social_links' => $data->socialLinks,
                'country' => $context['country'] ?? null,
                'province' => $context['province'] ?? null,
                'region' => $context['region'] ?? null,
                'city' => $context['city'] ?? null,
                'business_status' => $context['business_status'] ?? null,
                'raw_payload' => $data->raw,
                'last_scraped_at' => now(),
            ]
        );
    }
}
