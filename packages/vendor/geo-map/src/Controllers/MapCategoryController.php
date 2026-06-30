<?php

namespace Vendor\GeoMap\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MapCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class MapCategoryController extends Controller
{
    public function index()
    {
        $categories = MapCategory::ordered()->withCount('places')->get();
        return view('geo-map::map-categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon_class' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:20',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->storeAs(
                'map-categories',
                Str::slug($validated['name']) . '-' . time() . '.' . $request->file('image')->getClientOriginalExtension(),
                'public'
            );
            $validated['image'] = asset('storage/' . $path);
        }

        $category = MapCategory::create($validated);

        return response()->json(['success' => true, 'message' => 'Catégorie créée', 'data' => $category]);
    }

    public function update(Request $request, $id)
    {
        $category = MapCategory::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon_class' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:20',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        if ($request->hasFile('image')) {
            if ($category->image) {
                $oldPath = str_replace(asset('storage/'), '', $category->image);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
            $path = $request->file('image')->storeAs(
                'map-categories',
                Str::slug($validated['name']) . '-' . time() . '.' . $request->file('image')->getClientOriginalExtension(),
                'public'
            );
            $validated['image'] = asset('storage/' . $path);
        } elseif ($request->input('remove_image') === '1') {
            if ($category->image) {
                $oldPath = str_replace(asset('storage/'), '', $category->image);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
            $validated['image'] = null;
        }

        $category->update($validated);

        return response()->json(['success' => true, 'message' => 'Catégorie mise à jour', 'data' => $category]);
    }

    public function destroy($id)
    {
        $category = MapCategory::findOrFail($id);

        if ($category->places()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer : ' . $category->places()->count() . ' lieu(x) utilisent cette catégorie'
            ], 409);
        }

        if ($category->image) {
            $oldPath = str_replace(asset('storage/'), '', $category->image);
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $category->delete();

        return response()->json(['success' => true, 'message' => 'Catégorie supprimée']);
    }

    public function toggleStatus($id)
    {
        $category = MapCategory::findOrFail($id);
        $category->update(['is_active' => !$category->is_active]);

        return response()->json([
            'success' => true,
            'message' => $category->is_active ? 'Catégorie activée' : 'Catégorie désactivée',
            'is_active' => $category->is_active
        ]);
    }
}
