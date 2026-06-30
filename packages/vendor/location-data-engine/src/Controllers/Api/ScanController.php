<?php

namespace Vendor\LocationDataEngine\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Province;
use App\Models\Region;
use App\Models\Secteur;
use App\Models\Ville;
use Illuminate\Http\JsonResponse;
use Vendor\LocationDataEngine\DTO\ScanRequestData;
use Vendor\LocationDataEngine\Models\ScanSession;
use Vendor\LocationDataEngine\Requests\StartScanRequest;
use Vendor\LocationDataEngine\Services\LocationDataEngineManager;

class ScanController extends Controller
{
    public function store(StartScanRequest $request, LocationDataEngineManager $manager): JsonResponse
    {
        $session = $manager->startScan(new ScanRequestData(
            countryId: $request->integer('country_id') ?: null,
            provinceId: $request->integer('province_id') ?: null,
            regionId: $request->integer('region_id') ?: null,
            cityId: $request->integer('city_id') ?: null,
            sectorId: $request->integer('sector_id') ?: null,
            category: (string) $request->string('category'),
            radius: $request->integer('radius') ?: (int) config('location-data-engine.scan.default_radius', 25000),
            limit: $request->integer('limit') ?: (int) config('location-data-engine.scan.default_limit', 250),
            gridPrecision: $request->integer('grid_precision') ?: (int) config('location-data-engine.scan.grid_precision', 5),
            withEnrichment: $request->boolean('with_enrichment'),
            withImages: $request->boolean('with_images'),
            query: $request->filled('query') ? (string) $request->string('query') : null,
            countryName: $this->name(Country::class, $request->integer('country_id')),
            provinceName: $this->name(Province::class, $request->integer('province_id')),
            regionName: $this->name(Region::class, $request->integer('region_id')),
            cityName: $this->name(Ville::class, $request->integer('city_id')),
            sectorName: $this->name(Secteur::class, $request->integer('sector_id')),
        ));

        return response()->json([
            'success' => true,
            'message' => 'Scan session queued.',
            'session' => [
                'id' => $session->id,
                'uuid' => $session->uuid,
                'status' => $session->status,
                'target_label' => $session->target_label,
            ],
        ]);
    }

    public function status(ScanSession $scanSession): JsonResponse
    {
        return response()->json([
            'success' => true,
            'session' => $scanSession->fresh(),
            'latest_logs' => $scanSession->logs()->latest()->limit(10)->get(),
        ]);
    }

    public function logs(ScanSession $scanSession): JsonResponse
    {
        return response()->json([
            'success' => true,
            'logs' => $scanSession->logs()->latest()->paginate(20),
        ]);
    }

    protected function name(string $model, ?int $id): ?string
    {
        return $id ? $model::query()->whereKey($id)->value('name') : null;
    }
}
