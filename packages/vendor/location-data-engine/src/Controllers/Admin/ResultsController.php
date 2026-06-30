<?php

namespace Vendor\LocationDataEngine\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\View\View;
use Vendor\LocationDataEngine\Models\BusinessLocation;

class ResultsController extends Controller
{
    public function index(): View
    {
        return view('location-data-engine::admin.results.index', [
            'pageSize' => (int) config('location-data-engine.admin.page_size', 24),
            'categories' => config('location-data-engine.categories', []),
            'countries' => Country::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function show(BusinessLocation $businessLocation): View
    {
        $businessLocation->load(['photos', 'reviews', 'scanSession']);

        return view('location-data-engine::admin.results.show', [
            'business' => $businessLocation,
        ]);
    }
}
