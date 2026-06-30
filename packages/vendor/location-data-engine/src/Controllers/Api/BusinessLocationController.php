<?php

namespace Vendor\LocationDataEngine\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Maatwebsite\Excel\Facades\Excel;
use Vendor\LocationDataEngine\Exports\BusinessLocationsExport;
use Vendor\LocationDataEngine\Models\BusinessLocation;
use Vendor\LocationDataEngine\Requests\ResultsFilterRequest;

class BusinessLocationController extends Controller
{
    public function index(ResultsFilterRequest $request): JsonResponse
    {
        $query = $this->filteredQuery($request);
        $perPage = $request->integer('per_page') ?: (int) config('location-data-engine.admin.page_size', 24);
        $results = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'results' => $results,
        ]);
    }

    public function show(BusinessLocation $businessLocation): JsonResponse
    {
        $businessLocation->load(['photos', 'reviews', 'scanSession']);

        return response()->json([
            'success' => true,
            'data' => $businessLocation,
        ]);
    }

    public function exportCsv(ResultsFilterRequest $request)
    {
        $rows = $this->filteredQuery($request)->get();
        $delimiter = config('location-data-engine.exports.csv_delimiter', ';');

        $callback = static function () use ($rows, $delimiter): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Name', 'Address', 'Website', 'Email', 'Phone', 'City', 'Province', 'Country', 'Rating', 'Reviews Count'], $delimiter);

            foreach ($rows as $row) {
                fputcsv($out, [
                    $row->name,
                    $row->address,
                    $row->website,
                    $row->email,
                    $row->phone,
                    $row->city,
                    $row->province,
                    $row->country,
                    $row->rating,
                    $row->reviews_count,
                ], $delimiter);
            }

            fclose($out);
        };

        return Response::streamDownload($callback, 'business-locations.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function exportExcel(ResultsFilterRequest $request)
    {
        return Excel::download(new BusinessLocationsExport($this->filteredQuery($request)), 'business-locations.xlsx');
    }

    protected function filteredQuery(ResultsFilterRequest $request)
    {
        return BusinessLocation::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%' . $request->string('search') . '%';
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', $search)
                        ->orWhere('address', 'like', $search)
                        ->orWhere('website', 'like', $search)
                        ->orWhere('email', 'like', $search);
                });
            })
            ->when($request->filled('country'), fn ($query) => $query->where('country', $request->string('country')))
            ->when($request->filled('province'), fn ($query) => $query->where('province', $request->string('province')))
            ->when($request->filled('city'), fn ($query) => $query->where('city', $request->string('city')))
            ->when($request->filled('status'), fn ($query) => $query->where('business_status', $request->string('status')))
            ->when($request->filled('category'), fn ($query) => $query->whereJsonContains('categories', $request->string('category')))
            ->latest('last_scanned_at');
    }
}
