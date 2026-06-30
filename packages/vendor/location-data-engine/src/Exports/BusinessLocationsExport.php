<?php

namespace Vendor\LocationDataEngine\Exports;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BusinessLocationsExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(protected Builder $query)
    {
    }

    public function collection()
    {
        return (clone $this->query)->get([
            'name',
            'address',
            'website',
            'email',
            'phone',
            'city',
            'province',
            'country',
            'rating',
            'reviews_count',
            'business_status',
            'google_maps_url',
        ]);
    }

    public function headings(): array
    {
        return [
            'Name',
            'Address',
            'Website',
            'Email',
            'Phone',
            'City',
            'Province',
            'Country',
            'Rating',
            'Reviews Count',
            'Business Status',
            'Google Maps URL',
        ];
    }
}
