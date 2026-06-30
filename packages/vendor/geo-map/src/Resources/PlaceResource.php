<?php

namespace Vendor\GeoMap\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlaceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'category' => $this->mapCategory?->slug,
            'category_name' => $this->mapCategory?->name,
            'icon_class' => $this->mapCategory?->icon_class,
            'color' => $this->mapCategory?->color,
            'category_icon_url' => $this->mapCategory?->image_url,
            'images' => $this->images ?? [],
            'video_url' => $this->video_url,
            'video_id' => $this->video_id,
            'address' => $this->address,
            'phone' => $this->phone,
            'website' => $this->website,
        ];
    }
}
