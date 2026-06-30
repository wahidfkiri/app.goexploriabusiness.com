<?php

namespace Vendor\Cms\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Block;
use App\Models\Etablissement;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlockController extends Controller
{
    public function globalIndex(Request $request)
    {
        return view('cms::admin.blocks.index');
    }

    public function globalList(Request $request): JsonResponse
    {
        return response()->json($this->blocksPayload($request));
    }

    public function globalStore(Request $request): JsonResponse
    {
        $data = $this->validatedBlockData($request);
        $data['slug'] = $this->uniqueBlockSlug($data['name']);
        $data['thumbnail'] = $this->storeThumbnail($request);

        $block = Block::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Bloc cree avec succes',
            'block' => $this->formatBlock($block->load('section')),
        ]);
    }

    public function globalUpdate(Request $request, $id): JsonResponse
    {
        $block = Block::findOrFail($id);
        $data = $this->validatedBlockData($request, $block->id);

        if ($block->name !== $data['name']) {
            $data['slug'] = $this->uniqueBlockSlug($data['name'], $block->id);
        }

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $this->storeThumbnail($request);
        }

        $block->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Bloc mis a jour avec succes',
            'block' => $this->formatBlock($block->fresh('section')),
        ]);
    }

    public function globalDestroy(Request $request, $id): JsonResponse
    {
        $block = Block::findOrFail($id);
        $block->delete();

        return response()->json([
            'success' => true,
            'message' => 'Bloc supprime avec succes',
        ]);
    }

    public function globalPreview(Request $request, $id)
    {
        $block = Block::findOrFail($id);
        $block->increment('views_count');

        return response($this->wrapPreviewHtml($block));
    }

    /**
     * Affiche la page de gestion des blocks
     */
    public function index(Request $request, $etablissementId)
    {
        $etablissement = Etablissement::findOrFail($etablissementId);
        
        return view('cms::admin.blocks.index', compact('etablissement'));
    }
    
    /**
     * API pour récupérer les blocks au format JSON pour GrapeJS
     */
    public function api(Request $request, $etablissementId): JsonResponse
    {
        $etablissement = Etablissement::findOrFail($etablissementId);
        
        $blocks = Block::active()
            ->ordered()
            ->with('categorie')
            ->get();
        
        // Formater les blocks pour GrapeJS
        $formattedBlocks = [];
        
        foreach ($blocks as $block) {
            // Déterminer la catégorie
            $category = $block->categorie ? $block->categorie->name : ($block->category ?: 'Autres');
            
            // Créer le contenu HTML complet avec CSS intégré
            $htmlContent = $this->buildBlockHtml($block);
            
            $formattedBlocks[] = [
                'id' => $block->id,
                'label' => $block->name,
                'content' => $htmlContent,
                'category' => $category,
                'media' => $block->thumbnail ? asset('storage/' . $block->thumbnail) : null,
                'attributes' => [
                    'class' => 'block-' . $block->slug,
                    'data-block-id' => $block->id
                ],
                'style' => [
                    'display' => 'block',
                    'width' => $block->width ?: '100%',
                    'height' => $block->height ?: 'auto',
                ]
            ];
        }
        
        return response()->json([
            'success' => true,
            'blocks' => $formattedBlocks,
            'categories' => $this->getCategoriesWithCount($blocks)
        ]);
    }
    
    /**
     * Construit le HTML complet du block avec CSS intégré
     */
    protected function buildBlockHtml($block): string
    {
        $html = '';
        
        // Ajouter le CSS inline si présent
        if ($block->css_content) {
            $html .= '<style>';
            $html .= $block->css_content;
            $html .= '</style>';
        }
        
        // Ajouter le HTML du block
        $html .= $block->html_content;
        
        // Ajouter le JS si présent
        if ($block->js_content) {
            $html .= '<script>';
            $html .= $block->js_content;
            $html .= '</script>';
        }
        
        return $html;
    }
    
    /**
     * Récupère les catégories avec leur nombre de blocks
     */
    protected function getCategoriesWithCount($blocks): array
    {
        $categories = [];
        
        foreach ($blocks as $block) {
            $catName = $block->categorie ? $block->categorie->name : ($block->category ?: 'Autres');
            
            if (!isset($categories[$catName])) {
                $categories[$catName] = 0;
            }
            $categories[$catName]++;
        }
        
        $result = [];
        foreach ($categories as $name => $count) {
            $result[] = [
                'id' => \Illuminate\Support\Str::slug($name),
                'label' => $name . " ($count)",
                'name' => $name,
                'count' => $count
            ];
        }
        
        return $result;
    }
    
    /**
     * Récupère les catégories disponibles
     */
    public function categories(Request $request, $etablissementId): JsonResponse
    {
        $categories = Block::active()
            ->with('categorie')
            ->get()
            ->groupBy(function($block) {
                return $block->categorie ? $block->categorie->name : ($block->category ?: 'Autres');
            })
            ->map(function($items, $category) {
                return [
                    'name' => $category,
                    'slug' => \Illuminate\Support\Str::slug($category),
                    'count' => $items->count()
                ];
            })
            ->values();
        
        return response()->json([
            'success' => true,
            'categories' => $categories
        ]);
    }

    public function list(Request $request, $etablissementId): JsonResponse
    {
        Etablissement::findOrFail($etablissementId);

        return response()->json($this->blocksPayload($request));
    }

    protected function blocksPayload(Request $request): array
    {
        $perPage = max(6, min((int) $request->input('per_page', 12), 48));

        $blocks = Block::with('section')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%")
                        ->orWhere('website_type', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        $blocks->setCollection(
            $blocks->getCollection()->map(fn (Block $block) => $this->formatBlock($block))
        );

        return [
            'success' => true,
            'blocks' => $blocks->items(),
            'pagination' => [
                'current_page' => $blocks->currentPage(),
                'last_page' => $blocks->lastPage(),
                'per_page' => $blocks->perPage(),
                'total' => $blocks->total(),
                'from' => $blocks->firstItem(),
                'to' => $blocks->lastItem(),
            ],
            'sections' => Section::ordered()->get(['id', 'name', 'slug']),
        ];
    }

    public function store(Request $request, $etablissementId): JsonResponse
    {
        Etablissement::findOrFail($etablissementId);

        $data = $this->validatedBlockData($request);
        $data['slug'] = $this->uniqueBlockSlug($data['name']);
        $data['thumbnail'] = $this->storeThumbnail($request);

        $block = Block::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Bloc cree avec succes',
            'block' => $this->formatBlock($block->load('section')),
        ]);
    }

    public function update(Request $request, $etablissementId, $id): JsonResponse
    {
        Etablissement::findOrFail($etablissementId);

        $block = Block::findOrFail($id);
        $data = $this->validatedBlockData($request, $block->id);

        if ($block->name !== $data['name']) {
            $data['slug'] = $this->uniqueBlockSlug($data['name'], $block->id);
        }

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $this->storeThumbnail($request);
        }

        $block->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Bloc mis a jour avec succes',
            'block' => $this->formatBlock($block->fresh('section')),
        ]);
    }

    public function destroy(Request $request, $etablissementId, $id): JsonResponse
    {
        Etablissement::findOrFail($etablissementId);

        $block = Block::findOrFail($id);
        $block->delete();

        return response()->json([
            'success' => true,
            'message' => 'Bloc supprime avec succes',
        ]);
    }

    public function preview(Request $request, $etablissementId, $id)
    {
        Etablissement::findOrFail($etablissementId);

        $block = Block::findOrFail($id);
        $block->increment('views_count');

        return response($this->wrapPreviewHtml($block));
    }

    protected function validatedBlockData(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:80',
            'html_content' => 'required|string',
            'css_content' => 'nullable|string',
            'js_content' => 'nullable|string',
            'section_id' => 'nullable|exists:sections,id',
            'category' => 'nullable|string|max:120',
            'website_type' => 'nullable|string|max:120',
            'tags' => 'nullable|string',
            'is_responsive' => 'nullable|boolean',
            'is_free' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'width' => 'nullable|integer|min:1',
            'height' => 'nullable|integer|min:1',
            'order' => 'nullable|integer',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ]);

        $data['icon'] = $data['icon'] ?? 'fa-cube';
        $data['category'] = $data['category'] ?? 'Basic';
        $data['website_type'] = $data['website_type'] ?? 'General';
        $data['is_responsive'] = $request->boolean('is_responsive');
        $data['is_free'] = $request->boolean('is_free');
        $data['is_active'] = $request->boolean('is_active', true);
        $data['tags'] = $this->parseTags($data['tags'] ?? null);
        $data['order'] = $data['order'] ?? 0;

        return $data;
    }

    protected function formatBlock(Block $block): array
    {
        return [
            'id' => $block->id,
            'name' => $block->name,
            'slug' => $block->slug,
            'description' => $block->description,
            'icon' => $block->icon,
            'html_content' => $block->html_content,
            'css_content' => $block->css_content,
            'js_content' => $block->js_content,
            'section_id' => $block->section_id,
            'section_name' => $block->section?->name,
            'category' => $block->category,
            'website_type' => $block->website_type,
            'tags' => $block->tags ?: [],
            'is_responsive' => (bool) $block->is_responsive,
            'is_free' => (bool) $block->is_free,
            'is_active' => (bool) $block->is_active,
            'width' => $block->width,
            'height' => $block->height,
            'order' => $block->order,
            'usage_count' => $block->usage_count,
            'views_count' => $block->views_count,
            'thumbnail_url' => $this->thumbnailUrl($block),
            'preview_url' => request()->route('etablissementId')
                ? route('cms.admin.blocks.preview', [
                    'etablissementId' => request()->route('etablissementId'),
                    'id' => $block->id,
                ])
                : route('cms.admin.global-blocks.preview', ['id' => $block->id]),
        ];
    }

    protected function storeThumbnail(Request $request): ?string
    {
        if (!$request->hasFile('thumbnail')) {
            return null;
        }

        return $request->file('thumbnail')->store('blocks/previews', 'public');
    }

    protected function thumbnailUrl(Block $block): ?string
    {
        if (!$block->thumbnail) {
            return null;
        }

        if (Str::startsWith($block->thumbnail, ['http://', 'https://'])) {
            return $block->thumbnail;
        }

        return Storage::disk('public')->url($block->thumbnail);
    }

    protected function uniqueBlockSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'bloc';
        $slug = $base;
        $counter = 2;

        while (Block::query()
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    protected function parseTags(?string $tags): array
    {
        if (!$tags) {
            return [];
        }

        return collect(explode(',', $tags))
            ->map(fn ($tag) => trim($tag))
            ->filter()
            ->values()
            ->all();
    }

    protected function wrapPreviewHtml(Block $block): string
    {
        $title = e($block->name);
        $html = $block->html_content;
        $css = $block->css_content ?: '';
        $js = $block->js_content ?: '';

        return <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body { margin: 0; background: #f8fafc; }
        .block-preview-shell { max-width: 1240px; margin: 0 auto; background: #fff; min-height: 100vh; }
        {$css}
    </style>
</head>
<body>
    <main class="block-preview-shell">
        {$html}
    </main>
    <script>{$js}</script>
</body>
</html>
HTML;
    }
}
