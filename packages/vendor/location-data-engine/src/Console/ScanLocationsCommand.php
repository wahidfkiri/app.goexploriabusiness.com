<?php

namespace Vendor\LocationDataEngine\Console;

use Illuminate\Console\Command;
use Vendor\LocationDataEngine\DTO\ScanRequestData;
use Vendor\LocationDataEngine\Services\LocationDataEngineManager;
use Vendor\LocationDataEngine\Services\LocationResolverService;

class ScanLocationsCommand extends Command
{
    protected $signature = 'locations:scan
        {--country=}
        {--province=}
        {--city=}
        {--category=services}
        {--radius=25000}
        {--limit=250}
        {--grid-precision=5}
        {--with-enrichment}
        {--with-images}
        {--query=}';

    protected $description = 'Scan businesses, places and destinations from Google Places API.';

    public function handle(LocationDataEngineManager $manager, LocationResolverService $resolver): int
    {
        $ids = $resolver->resolveIdsByNames(
            $this->option('country'),
            $this->option('province'),
            $this->option('city')
        );

        $session = $manager->startScan(new ScanRequestData(
            countryId: $ids['country_id'] ?? null,
            provinceId: $ids['province_id'] ?? null,
            regionId: null,
            cityId: $ids['city_id'] ?? null,
            sectorId: null,
            category: (string) $this->option('category'),
            radius: (int) $this->option('radius'),
            limit: (int) $this->option('limit'),
            gridPrecision: (int) $this->option('grid-precision'),
            withEnrichment: (bool) $this->option('with-enrichment'),
            withImages: (bool) $this->option('with-images'),
            query: $this->option('query') ? (string) $this->option('query') : null,
            countryName: $this->option('country') ? (string) $this->option('country') : null,
            provinceName: $this->option('province') ? (string) $this->option('province') : null,
            regionName: null,
            cityName: $this->option('city') ? (string) $this->option('city') : null,
            sectorName: null,
        ));

        $this->info('Scan session created: ' . $session->uuid);

        return self::SUCCESS;
    }
}
