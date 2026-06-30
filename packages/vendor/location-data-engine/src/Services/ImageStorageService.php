<?php

namespace Vendor\LocationDataEngine\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Vendor\LocationDataEngine\Models\BusinessLocation;
use Vendor\LocationDataEngine\Models\BusinessPhoto;

class ImageStorageService
{
    public function downloadForBusiness(BusinessLocation $business, array $photos, callable $photoUrlResolver): void
    {
        if (! config('location-data-engine.images.enabled', true) || $photos === []) {
            return;
        }

        $disk = Storage::disk((string) config('location-data-engine.images.disk', 'public'));
        $baseDir = trim((string) config('location-data-engine.images.directory', 'location-data-engine/places'), '/');
        $manager = class_exists(ImageManager::class) ? ImageManager::gd() : null;

        foreach (array_slice($photos, 0, 5) as $index => $photo) {
            $reference = $photo['photo_reference'] ?? null;
            if (! $reference) {
                continue;
            }

            $contents = Http::timeout(20)->get($photoUrlResolver($reference))->body();
            if (! $contents) {
                continue;
            }

            $relativePath = sprintf('%s/%s/%s.jpg', $baseDir, $business->place_id, $reference);
            $disk->put($relativePath, $contents);

            $thumbnailPath = null;
            if ($manager) {
                try {
                    $image = $manager->read($contents)->scale(width: (int) config('location-data-engine.images.thumbnail_width', 480));
                    $thumbnailPath = sprintf('%s/%s/thumb-%s.jpg', $baseDir, $business->place_id, $reference);
                    $disk->put($thumbnailPath, (string) $image->toJpeg());
                } catch (\Throwable) {
                    $thumbnailPath = null;
                }
            }

            BusinessPhoto::query()->updateOrCreate(
                [
                    'business_location_id' => $business->id,
                    'remote_reference' => $reference,
                ],
                [
                    'source' => 'google_places',
                    'file_path' => $relativePath,
                    'thumbnail_path' => $thumbnailPath,
                    'cdn_url' => $disk->url($relativePath),
                    'is_primary' => $index === 0,
                    'sort_order' => $index,
                    'metadata' => $photo,
                ]
            );
        }
    }
}
