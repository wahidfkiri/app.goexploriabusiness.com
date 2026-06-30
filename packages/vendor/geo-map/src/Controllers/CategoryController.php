<?php

namespace Vendor\GeoMap\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CategorieType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $typeFilter = $request->get('type');
        $status = $request->get('status');
        $sortField = $request->get('sort', 'name');
        $sortDir = $request->get('direction', 'asc');

        $query = Category::with('type');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($typeFilter) {
            $query->where('categorie_type_id', $typeFilter);
        }

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        $allowedSorts = ['name', 'id'];
        $sortField = in_array($sortField, $allowedSorts) ? $sortField : 'name';
        $sortDir = $sortDir === 'desc' ? 'desc' : 'asc';
        $query->orderBy($sortField, $sortDir);

        $categories = $query->paginate(20);
        $categorieTypes = CategorieType::orderBy('name')->get();

        if ($request->ajax()) {
            return response()->json([
                'data' => $categories->items(),
                'pagination' => (string) $categories->links('pagination::bootstrap-5'),
                'total' => $categories->total(),
            ]);
        }

        return view('geo-map::categories.index', compact('categories', 'categorieTypes'));
    }

    public function updateIcon(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        if ($request->hasFile('icon')) {
            if ($category->icon) {
                $oldPath = Str::startsWith($category->icon, 'http')
                    ? str_replace(asset('storage/'), '', $category->icon)
                    : $category->icon;
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            $icon = $request->file('icon');
            $folder = 'categories';
            $iconName = Str::slug($category->name) . '-' . time() . '.' . $icon->getClientOriginalExtension();
            $path = $icon->storeAs($folder, $iconName, 'public');
            $category->update(['icon' => $path]);

            return response()->json([
                'success' => true,
                'message' => 'Icône mise à jour',
                'icon_url' => $category->icon_url,
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Aucun fichier fourni'], 400);
    }

    public function removeIcon($id)
    {
        $category = Category::findOrFail($id);

        if ($category->icon) {
            $oldPath = Str::startsWith($category->icon, 'http')
                ? str_replace(asset('storage/'), '', $category->icon)
                : $category->icon;
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $category->update(['icon' => null]);

        return response()->json([
            'success' => true,
            'message' => 'Icône supprimée',
        ]);
    }
}
