<?php

namespace Vendor\MapsDataEngine\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Maatwebsite\Excel\Facades\Excel;
use Vendor\MapsDataEngine\Exports\ListingsExport;
use Vendor\MapsDataEngine\Models\MapBusinessListing;

class ListingsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $this->filteredQuery($request);
        $perPage = $request->integer('per_page') ?: (int) config('maps-data-engine.admin.page_size', 24);

        return response()->json([
            'success' => true,
            'results' => $query->paginate($perPage),
        ]);
    }

    public function show(MapBusinessListing $mapBusinessListing): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $mapBusinessListing->load(['scanSession']),
        ]);
    }

    public function exportCsv(Request $request)
    {
        $rows = $this->filteredQuery($request)->get();
        $delimiter = config('maps-data-engine.exports.csv_delimiter', ';');

        $callback = static function () use ($rows, $delimiter): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Name', 'Address', 'Website', 'Phone', 'City', 'Province', 'Country', 'Rating', 'Reviews'], $delimiter);
            foreach ($rows as $row) {
                fputcsv($out, [$row->name, $row->address, $row->website, $row->phone, $row->city, $row->province, $row->country, $row->rating, $row->reviews_count], $delimiter);
            }
            fclose($out);
        };

        return Response::streamDownload($callback, 'maps-business-listings.csv', ['Content-Type' => 'text/csv']);
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(new ListingsExport($this->filteredQuery($request)), 'maps-business-listings.xlsx');
    }

    protected function filteredQuery(Request $request)
    {
        return MapBusinessListing::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $value = '%' . $request->string('search') . '%';
                $query->where(function ($inner) use ($value) {
                    $inner->where('name', 'like', $value)
                        ->orWhere('address', 'like', $value)
                        ->orWhere('website', 'like', $value)
                        ->orWhere('phone', 'like', $value);
                });
            })
            ->when($request->filled('country'), fn ($query) => $query->where('country', $request->string('country')))
            ->when($request->filled('province'), fn ($query) => $query->where('province', $request->string('province')))
            ->when($request->filled('city'), fn ($query) => $query->where('city', $request->string('city')))
            ->when($request->filled('category'), fn ($query) => $query->whereJsonContains('categories', $request->string('category')))
            ->latest('last_scraped_at');
    }
}
