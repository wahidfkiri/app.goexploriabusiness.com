<?php

namespace Vendor\MapsDataEngine\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Province;
use App\Models\Region;
use App\Models\Ville;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Vendor\MapsDataEngine\DTO\MapsScanRequestData;
use Vendor\MapsDataEngine\Models\MapBrowserSession;
use Vendor\MapsDataEngine\Models\MapProxyEndpoint;
use Vendor\MapsDataEngine\Models\MapScanSession;
use Vendor\MapsDataEngine\Requests\StartMapsScanRequest;
use Vendor\MapsDataEngine\Services\MapsDataEngineManager;

class ScanController extends Controller
{
    public function store(StartMapsScanRequest $request, MapsDataEngineManager $manager): JsonResponse
    {
        $session = $manager->startScan(new MapsScanRequestData(
            countryId: $request->integer('country_id') ?: null,
            provinceId: $request->integer('province_id') ?: null,
            regionId: $request->integer('region_id') ?: null,
            cityId: $request->integer('city_id') ?: null,
            category: (string) $request->string('category'),
            radius: $request->integer('radius') ?: (int) config('maps-data-engine.scraping.default_radius', 18000),
            limit: $request->integer('limit') ?: (int) config('maps-data-engine.scraping.default_limit', 120),
            withImages: $request->boolean('with_images'),
            withReviews: $request->boolean('with_reviews'),
            withSocialLinks: $request->boolean('with_social_links'),
            query: $request->filled('query') ? (string) $request->string('query') : null,
            countryName: $this->name(Country::class, $request->integer('country_id')),
            provinceName: $this->name(Province::class, $request->integer('province_id')),
            regionName: $this->name(Region::class, $request->integer('region_id')),
            cityName: $this->name(Ville::class, $request->integer('city_id')),
        ));

        return response()->json([
            'success' => true,
            'session' => $session,
        ]);
    }

    public function status(MapScanSession $mapScanSession): JsonResponse
    {
        return response()->json([
            'success' => true,
            'session' => $mapScanSession->fresh(),
            'latest_logs' => $mapScanSession->logs()->latest()->limit(12)->get(),
        ]);
    }

    public function logs(MapScanSession $mapScanSession): JsonResponse
    {
        return response()->json([
            'success' => true,
            'logs' => $mapScanSession->logs()->latest()->paginate(20),
        ]);
    }

    public function infrastructure(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'proxies' => MapProxyEndpoint::query()->orderByDesc('health_score')->get(),
            'browser_sessions' => MapBrowserSession::query()->latest()->get(),
        ]);
    }

    protected function name(string $model, ?int $id): ?string
    {
        return $id ? $model::query()->whereKey($id)->value('name') : null;
    }
}
