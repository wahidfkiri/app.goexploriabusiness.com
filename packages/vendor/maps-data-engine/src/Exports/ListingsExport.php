<?php

namespace Vendor\MapsDataEngine\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ListingsExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(protected Builder $query)
    {
    }

    public function collection()
    {
        return (clone $this->query)->get(['name', 'address', 'website', 'phone', 'city', 'province', 'country', 'rating', 'reviews_count', 'google_maps_url']);
    }

    public function headings(): array
    {
        return ['Name', 'Address', 'Website', 'Phone', 'City', 'Province', 'Country', 'Rating', 'Reviews Count', 'Google Maps URL'];
    }
}
