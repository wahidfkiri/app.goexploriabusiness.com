<?php

namespace Vendor\LocationDataEngine\Services\Repositories;

use Illuminate\Support\Str;
use Vendor\LocationDataEngine\Contracts\BusinessLocationRepositoryInterface;
use Vendor\LocationDataEngine\DTO\PlaceDetailsData;
use Vendor\LocationDataEngine\Models\BusinessLocation;
use Vendor\LocationDataEngine\Models\BusinessReview;
use Vendor\LocationDataEngine\Models\ScanSession;

class BusinessLocationRepository implements BusinessLocationRepositoryInterface
{
    public function upsertFromPlaceDetails(ScanSession $session, PlaceDetailsData $details): BusinessLocation
    {
        $business = BusinessLocation::query()->updateOrCreate(
            ['place_id' => $details->placeId],
            [
                'latest_scan_session_id' => $session->id,
                'name' => $details->name,
                'slug' => Str::slug($details->name . '-' . $details->placeId),
                'address' => $details->address,
                'latitude' => $details->latitude,
                'longitude' => $details->longitude,
                'phone' => $details->phone,
                'international_phone' => $details->internationalPhone,
                'website' => $details->website,
                'rating' => $details->rating,
                'reviews_count' => $details->reviewsCount ?? 0,
                'categories' => $details->categories,
                'business_status' => $details->businessStatus,
                'opening_hours' => $details->openingHours,
                'timezone' => $details->timezone,
                'province' => $details->province,
                'city' => $details->city,
                'country' => $details->country,
                'postal_code' => $details->postalCode,
                'google_maps_url' => $details->googleMapsUrl,
                'images' => array_values(array_filter(array_map(fn (array $photo) => $photo['photo_reference'] ?? null, $details->photos))),
                'reviews_json' => $details->reviews,
                'raw_payload' => $details->raw,
                'last_scanned_at' => now(),
            ]
        );

        if ($details->reviews !== []) {
            BusinessReview::query()->where('business_location_id', $business->id)->delete();

            foreach ($details->reviews as $review) {
                BusinessReview::query()->create([
                    'business_location_id' => $business->id,
                    'author_name' => data_get($review, 'author_name'),
                    'author_url' => data_get($review, 'author_url'),
                    'rating' => data_get($review, 'rating'),
                    'language' => data_get($review, 'language'),
                    'text' => data_get($review, 'text'),
                    'relative_time_description' => data_get($review, 'relative_time_description'),
                    'published_at' => null,
                    'raw_payload' => $review,
                ]);
            }
        }

        return $business;
    }

    public function touchScan(BusinessLocation $location, ScanSession $session): void
    {
        $location->forceFill([
            'latest_scan_session_id' => $session->id,
            'last_scanned_at' => now(),
        ])->save();
    }
}
