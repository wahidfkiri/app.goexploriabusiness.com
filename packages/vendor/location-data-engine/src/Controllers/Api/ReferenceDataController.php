<?php

namespace Vendor\LocationDataEngine\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Province;
use App\Models\Region;
use App\Models\Secteur;
use App\Models\Ville;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReferenceDataController extends Controller
{
    public function locations(string $level, Request $request): JsonResponse
    {
        $items = match ($level) {
            'countries' => Country::query()->orderBy('name')->get(['id', 'name']),
            'provinces' => Province::query()->when($request->integer('country_id'), fn ($q, $countryId) => $q->where('country_id', $countryId))->orderBy('name')->get(['id', 'name']),
            'regions' => Region::query()->when($request->integer('province_id'), fn ($q, $provinceId) => $q->where('province_id', $provinceId))->orderBy('name')->get(['id', 'name']),
            'cities' => Ville::query()->when($request->integer('region_id'), fn ($q, $regionId) => $q->where('region_id', $regionId))->orderBy('name')->get(['id', 'name']),
            'sectors' => $this->sectorsForCity($request->integer('ville_id')),
            default => collect(),
        };

        return response()->json([
            'success' => true,
            'items' => $items,
        ]);
    }

    protected function sectorsForCity(?int $villeId)
    {
        $ville = $villeId ? Ville::query()->find($villeId) : null;

        return Secteur::query()
            ->when($ville?->secteur_id, fn ($query, $secteurId) => $query->whereKey($secteurId))
            ->when(! $ville?->secteur_id && $ville?->region_id, fn ($query) => $query->where('region_id', $ville->region_id))
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
