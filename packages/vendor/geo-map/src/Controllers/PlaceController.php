<?php

namespace Vendor\GeoMap\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Place;
use App\Models\MapCategory;
use Vendor\GeoMap\Resources\PlaceResource;
use Illuminate\Http\Request;

class PlaceController extends Controller
{
    public function index(Request $request)
    {
        $query = Place::query()->with('mapCategory');
        
        // Filtrage par catégorie
        if ($request->has('category') && $request->category !== 'all') {
            $query->whereHas('mapCategory', fn($q) => $q->where('slug', $request->category));
        }
        
        // Filtrage par rayon géographique (optionnel)
        if ($request->has(['lat', 'lng', 'radius'])) {
            $radius = 2000; // en kilomètres
            $query->whereRaw("
                (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * 
                cos(radians(longitude) - radians(?)) + sin(radians(?)) * 
                sin(radians(latitude)))) < ?
            ", [$request->lat, $request->lng, $request->lat, $radius]);
        }
        
        return PlaceResource::collection($query->get());
    }
    
    public function categories()
    {
        $categories = MapCategory::active()->ordered()->get(['id', 'name', 'slug', 'icon_class', 'color', 'image']);
            
        return response()->json($categories);
    }
}