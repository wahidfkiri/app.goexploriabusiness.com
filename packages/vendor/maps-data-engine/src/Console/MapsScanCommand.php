<?php

namespace Vendor\MapsDataEngine\Console;

use Illuminate\Console\Command;
use Vendor\MapsDataEngine\DTO\MapsScanRequestData;
use Vendor\MapsDataEngine\Services\MapsDataEngineManager;
use Vendor\MapsDataEngine\Services\LocationScopeResolverService;

class MapsScanCommand extends Command
{
    protected $signature = 'maps:scan
        {--country=}
        {--province=}
        {--region=}
        {--city=}
        {--category=services}
        {--radius=18000}
        {--limit=120}
        {--with-images}
        {--with-reviews}
        {--with-social-links}
        {--query=}';

    protected $description = 'Launch a Google Maps scraping session through Playwright.';

    public function handle(MapsDataEngineManager $manager, LocationScopeResolverService $resolver): int
    {
        $ids = $resolver->resolveIdsByNames(
            $this->option('country'),
            $this->option('province'),
            $this->option('region'),
            $this->option('city')
        );

        $session = $manager->startScan(new MapsScanRequestData(
            countryId: $ids['country_id'] ?? null,
            provinceId: $ids['province_id'] ?? null,
            regionId: $ids['region_id'] ?? null,
            cityId: $ids['city_id'] ?? null,
            category: (string) $this->option('category'),
            radius: (int) $this->option('radius'),
            limit: (int) $this->option('limit'),
            withImages: (bool) $this->option('with-images'),
            withReviews: (bool) $this->option('with-reviews'),
            withSocialLinks: (bool) $this->option('with-social-links'),
            query: $this->option('query') ? (string) $this->option('query') : null,
            countryName: $this->option('country') ? (string) $this->option('country') : null,
            provinceName: $this->option('province') ? (string) $this->option('province') : null,
            regionName: $this->option('region') ? (string) $this->option('region') : null,
            cityName: $this->option('city') ? (string) $this->option('city') : null,
        ));

        $this->info('Maps scan session queued: ' . $session->uuid);

        return self::SUCCESS;
    }
}
