<?php

namespace Vendor\MapsDataEngine\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Vendor\MapsDataEngine\Models\MapBusinessListing;
use Vendor\MapsDataEngine\Services\MapsScrapeLogService;

class DownloadImagesJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 2;

    public function __construct(public int $listingId)
    {
    }

    public function handle(MapsScrapeLogService $logService): void
    {
        $listing = MapBusinessListing::query()->find($this->listingId);
        if (! $listing) {
            return;
        }

        $logService->info($listing->scanSession, 'listing.images.download', 'Image download placeholder job executed.', [
            'listing_id' => $listing->id,
            'images_count' => count((array) $listing->images),
        ], $listing);
    }
}
