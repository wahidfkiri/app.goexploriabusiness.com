<?php

namespace Vendor\MapsDataEngine\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StartMapsScanRequest extends FormRequest
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
            'category' => ['required', 'string', Rule::in(array_keys(config('maps-data-engine.scraping.categories', [])))],
            'radius' => ['nullable', 'integer', 'min:1000', 'max:50000'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:250'],
            'query' => ['nullable', 'string', 'max:255'],
            'with_images' => ['nullable', 'boolean'],
            'with_reviews' => ['nullable', 'boolean'],
            'with_social_links' => ['nullable', 'boolean'],
        ];
    }
}
