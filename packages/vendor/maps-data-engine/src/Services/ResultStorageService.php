<?php

namespace Vendor\MapsDataEngine\Services;

use Vendor\MapsDataEngine\DTO\ScrapeResultData;
use Vendor\MapsDataEngine\Models\MapBusinessListing;
use Vendor\MapsDataEngine\Models\MapScanSession;
use Vendor\MapsDataEngine\Services\Repositories\MapBusinessListingRepository;

class ResultStorageService
{
    public function __construct(protected MapBusinessListingRepository $repository)
    {
    }

    public function store(MapScanSession $session, array $scrapedRows, array $segmentContext = []): array
    {
        $saved = [];

        foreach ($scrapedRows as $row) {
            $saved[] = $this->repository->upsertFromScrapeResult($session, ScrapeResultData::fromArray($row), $segmentContext);
        }

        return $saved;
    }
}
