<?php

namespace Vendor\LocationDataEngine\Services;

use Vendor\LocationDataEngine\Crawlers\WebsiteContactCrawler;
use Vendor\LocationDataEngine\DTO\WebsiteEnrichmentData;

class WebsiteEnrichmentService
{
    public function __construct(protected WebsiteContactCrawler $crawler)
    {
    }

    public function enrich(string $website): WebsiteEnrichmentData
    {
        $payload = $this->crawler->crawl($website, (array) config('location-data-engine.enrichment.contact_paths', []));

        return new WebsiteEnrichmentData(
            emails: $payload['emails'] ?? [],
            socialLinks: $payload['social_links'] ?? [],
            bookingLinks: $payload['booking_links'] ?? [],
            contactPage: $payload['contact_page'] ?? null,
            raw: $payload['raw'] ?? []
        );
    }
}
