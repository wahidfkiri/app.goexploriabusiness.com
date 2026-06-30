<?php

namespace Vendor\MapsDataEngine\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Vendor\MapsDataEngine\Models\MapBusinessListing;

class ResultsController extends Controller
{
    public function index()
    {
        return view('maps-data-engine::admin.results.index', [
            'pageSize' => (int) config('maps-data-engine.admin.page_size', 24),
            'categories' => config('maps-data-engine.scraping.categories', []),
            'countries' => Country::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function show(MapBusinessListing $mapBusinessListing)
    {
        $mapBusinessListing->load(['scanSession']);

        return view('maps-data-engine::admin.results.show', [
            'listing' => $mapBusinessListing,
        ]);
    }
}
