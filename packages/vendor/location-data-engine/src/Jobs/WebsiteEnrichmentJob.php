<?php

namespace Vendor\LocationDataEngine\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Vendor\LocationDataEngine\Models\BusinessLocation;
use Vendor\LocationDataEngine\Services\WebsiteEnrichmentService;

class WebsiteEnrichmentJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 2;

    public function __construct(public int $businessLocationId)
    {
    }

    public function handle(WebsiteEnrichmentService $service): void
    {
        $business = BusinessLocation::query()->find($this->businessLocationId);
        if (! $business || ! $business->website) {
            return;
        }

        $enrichment = $service->enrich($business->website);

        $business->forceFill([
            'email' => $enrichment->emails[0] ?? $business->email,
            'social_links' => $enrichment->socialLinks,
            'enrichment_payload' => $enrichment->toArray(),
        ])->save();
    }
}
