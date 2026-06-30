<?php

namespace Vendor\LocationDataEngine\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Vendor\LocationDataEngine\Models\BusinessLocation;
use Vendor\LocationDataEngine\Services\GooglePlacesClient;
use Vendor\LocationDataEngine\Services\ImageStorageService;

class DownloadImagesJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 2;

    public function __construct(public int $businessLocationId, public array $photos)
    {
    }

    public function handle(ImageStorageService $service, GooglePlacesClient $placesClient): void
    {
        $business = BusinessLocation::query()->find($this->businessLocationId);
        if (! $business) {
            return;
        }

        $service->downloadForBusiness($business, $this->photos, fn (string $reference) => $placesClient->photoUrl($reference));
    }
}
