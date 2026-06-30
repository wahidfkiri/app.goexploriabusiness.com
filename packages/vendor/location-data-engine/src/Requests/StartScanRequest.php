<?php

namespace Vendor\LocationDataEngine\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StartScanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'country_id' => ['nullable', 'integer'],
            'province_id' => ['nullable', 'integer'],
            'region_id' => ['nullable', 'integer'],
            'city_id' => ['nullable', 'integer'],
            'sector_id' => ['nullable', 'integer'],
            'category' => ['required', 'string', Rule::in(array_keys(config('location-data-engine.categories', [])))],
            'radius' => ['nullable', 'integer', 'min:1000', 'max:50000'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'grid_precision' => ['nullable', 'integer', 'min:1', 'max:9'],
            'with_enrichment' => ['nullable', 'boolean'],
            'with_images' => ['nullable', 'boolean'],
            'query' => ['nullable', 'string', 'max:255'],
        ];
    }
}
