<?php

namespace Vendor\Cms\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Etablissement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Vendor\Cms\Models\CmsTemplate;
use Vendor\Cms\Models\EtablissementTemplate;
use Vendor\Cms\Models\Page;
use Vendor\Cms\Models\Theme;
use Vendor\Cms\Services\ThemeService;

class AdminThemeController extends Controller
{
    protected $themeService;

    public function __construct(ThemeService $themeService)
    {
        $this->themeService = $themeService;
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $category = trim((string) $request->input('category', ''));

        $templates = CmsTemplate::with('creator')
            ->when($category !== '', function ($query) use ($category) {
                $query->where('category', $category);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('site_url', 'like', "%{$search}%")
                        ->orWhere('version', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        $stats = [
            'total' => CmsTemplate::count(),
            'active' => CmsTemplate::available()->count(),
        ];

        $templateCategories = $this->templateCategories();
        $filters = [
            'search' => $search,
            'category' => $category,
        ];

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('cms::admin.themes.partials.results', [
                    'templates' => $templates,
                    'filters' => $filters,
                ])->render(),
                'badges_html' => view('cms::admin.themes.partials.category-badges', [
                    'templateCategories' => $templateCategories,
                    'filters' => $filters,
                ])->render(),
                'filtered_total' => $templates->total(),
                'contents' => $this->templateContentsPayload($templates),
                'templates' => $this->templatesPayload($templates),
            ]);
        }

        return view('cms::admin.themes.index', [
            'templates' => $templates,
            'themes' => $templates,
            'stats' => $stats,
            'templateCategories' => $templateCategories,
            'filters' => $filters,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'site_url' => 'nullable|url|max:255',
                'page_content' => 'required|string',
                'version' => 'nullable|string|max:50',
                'category' => 'nullable|string|max:120',
                'description' => 'nullable|string',
                'status' => 'nullable|in:draft,active,archived',
                'image_preview' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            ]);

            $template = CmsTemplate::create([
                'name' => $data['name'],
                'slug' => CmsTemplate::uniqueSlug($data['name']),
                'site_url' => $data['site_url'] ?? null,
                'page_content' => $data['page_content'],
                'version' => $data['version'] ?? '1.0.0',
                'creator_id' => Auth::id(),
                'category' => $data['category'] ?? null,
                'image_preview' => $this->storePreviewImage($request),
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? 'active',
                'is_active' => ($data['status'] ?? 'active') === 'active',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Template cree avec succes',
                'template' => $template,
                'theme' => $template,
                'html' => view('cms::admin.themes.partials.theme-card', [
                    'template' => $template,
                    'theme' => $template,
                ])->render(),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Template creation error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la creation : ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $template = CmsTemplate::findOrFail($id);
            $template->etablissementTemplates()->delete();
            $template->delete();

            return response()->json([
                'success' => true,
                'message' => 'Template supprime avec succes',
            ]);
        } catch (\Exception $e) {
            Log::error('Template deletion error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression : ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $template = CmsTemplate::findOrFail($id);
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'site_url' => 'nullable|url|max:255',
                'page_content' => 'required|string',
                'version' => 'nullable|string|max:50',
                'category' => 'nullable|string|max:120',
                'description' => 'nullable|string',
                'status' => 'nullable|in:draft,active,archived',
                'image_preview' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            ]);

            $payload = [
                'name' => $data['name'],
                'site_url' => $data['site_url'] ?? null,
                'page_content' => $data['page_content'],
                'version' => $data['version'] ?? '1.0.0',
                'category' => $data['category'] ?? null,
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? 'active',
                'is_active' => ($data['status'] ?? 'active') === 'active',
            ];

            if ($template->name !== $data['name']) {
                $payload['slug'] = CmsTemplate::uniqueSlug($data['name'], $template->id);
            }

            if ($request->hasFile('image_preview')) {
                $payload['image_preview'] = $this->storePreviewImage($request);
            }

            $template->update($payload);

            return response()->json([
                'success' => true,
                'message' => 'Template mis a jour avec succes',
                'template' => $template->fresh(),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Template update error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise a jour : ' . $e->getMessage(),
            ], 500);
        }
    }

    public function uploadPreview(Request $request, $id): JsonResponse
    {
        try {
            $request->validate([
                'preview_image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            ]);

            $template = CmsTemplate::findOrFail($id);
            $path = $request->file('preview_image')->store('cms/templates/previews', 'public');
            $url = url(Storage::disk('public')->url($path));

            $template->update(['image_preview' => $url]);

            return response()->json([
                'success' => true,
                'message' => 'Image de preview mise a jour',
                'url' => $url,
            ]);
        } catch (\Exception $e) {
            Log::error('Template preview upload error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage(),
            ], 500);
        }
    }

    public function attach(Request $request, $etablissementId, $id): JsonResponse
    {
        return $this->install($request, $etablissementId, $id);
    }

    public function install(Request $request, $etablissementId, $id): JsonResponse
    {
        try {
            $etablissement = Etablissement::findOrFail($etablissementId);
            $template = CmsTemplate::available()->findOrFail($id);

            $installed = DB::connection('cms')->transaction(function () use ($etablissement, $template) {
                $existing = EtablissementTemplate::where('etablissement_id', $etablissement->id)
                    ->where('template_cms_id', $template->id)
                    ->first();

                if ($existing) {
                    return $existing->load('template', 'page');
                }

                $isFirst = !EtablissementTemplate::where('etablissement_id', $etablissement->id)
                    ->where('status', 'installed')
                    ->exists();

                $page = $this->createCmsPageForTemplate($etablissement, $template, $isFirst);

                if ($isFirst) {
                    EtablissementTemplate::where('etablissement_id', $etablissement->id)
                        ->update(['is_active' => false]);
                }

                return EtablissementTemplate::create([
                    'etablissement_id' => $etablissement->id,
                    'template_cms_id' => $template->id,
                    'cms_page_id' => $page->id,
                    'page_contents' => $template->page_content,
                    'status' => 'installed',
                    'is_active' => $isFirst,
                    'installed_at' => now(),
                    'activated_at' => $isFirst ? now() : null,
                ])->load('template', 'page');
            });

            return response()->json([
                'success' => true,
                'message' => $installed->wasRecentlyCreated ? 'Template installe avec succes' : 'Template deja installe',
                'template_id' => $template->id,
                'etablissement_template_id' => $installed->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Template install error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage(),
            ], 500);
        }
    }

    public function detach(Request $request, $etablissementId, $id): JsonResponse
    {
        try {
            $installed = EtablissementTemplate::where('etablissement_id', $etablissementId)
                ->where('template_cms_id', $id)
                ->firstOrFail();

            if ($installed->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de retirer le template actif. Activez un autre template avant.',
                ], 400);
            }

            $installed->delete();

            return response()->json([
                'success' => true,
                'message' => 'Template retire de cet etablissement',
            ]);
        } catch (\Exception $e) {
            Log::error('Template detach error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage(),
            ], 500);
        }
    }

    public function activate(Request $request, $etablissementId, $id): JsonResponse
    {
        try {
            $installed = EtablissementTemplate::with('template', 'page')
                ->where('etablissement_id', $etablissementId)
                ->where('template_cms_id', $id)
                ->first();

            if (!$installed) {
                $installResponse = $this->install($request, $etablissementId, $id);
                if ($installResponse->getStatusCode() >= 400) {
                    return $installResponse;
                }

                $installed = EtablissementTemplate::with('template', 'page')
                    ->where('etablissement_id', $etablissementId)
                    ->where('template_cms_id', $id)
                    ->firstOrFail();
            }

            DB::connection('cms')->transaction(function () use ($installed, $etablissementId) {
                EtablissementTemplate::where('etablissement_id', $etablissementId)
                    ->update(['is_active' => false]);

                $installed->update([
                    'is_active' => true,
                    'status' => 'installed',
                    'activated_at' => now(),
                ]);

                if ($installed->page) {
                    Page::where('etablissement_id', $etablissementId)->update(['is_home' => false]);
                    $installed->page->update([
                        'content' => $installed->page_contents,
                        'status' => 'published',
                        'is_home' => true,
                        'published_at' => $installed->page->published_at ?: now(),
                    ]);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Template active avec succes',
                'template_id' => $installed->template_cms_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Template activation error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l activation : ' . $e->getMessage(),
            ], 500);
        }
    }

    public function deactivate(Request $request, $etablissementId, $id): JsonResponse
    {
        try {
            EtablissementTemplate::where('etablissement_id', $etablissementId)
                ->where('template_cms_id', $id)
                ->update(['is_active' => false]);

            return response()->json([
                'success' => true,
                'message' => 'Template desactive',
            ]);
        } catch (\Exception $e) {
            Log::error('Template deactivation error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage(),
            ], 500);
        }
    }

    public function preview(Request $request, $etablissementId, $id)
    {
        try {
            $installed = EtablissementTemplate::with('template')
                ->where('etablissement_id', $etablissementId)
                ->where('template_cms_id', $id)
                ->first();

            $template = $installed?->template ?: CmsTemplate::findOrFail($id);
            $content = $installed?->page_contents ?: $template->page_content;

            return response($this->wrapPreviewHtml($template->name, $content));
        } catch (\Exception $e) {
            Log::error('Template preview error: ' . $e->getMessage());

            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function duplicate(Request $request, $id): JsonResponse
    {
        try {
            $template = CmsTemplate::findOrFail($id);
            $copy = $template->replicate();
            $copy->name = $template->name . ' (copie)';
            $copy->slug = CmsTemplate::uniqueSlug($copy->name);
            $copy->creator_id = Auth::id();
            $copy->status = 'draft';
            $copy->is_active = false;
            $copy->save();

            return response()->json([
                'success' => true,
                'message' => 'Template duplique avec succes',
                'template' => $copy,
            ]);
        } catch (\Exception $e) {
            Log::error('Template duplicate error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage(),
            ], 500);
        }
    }

    public function export(Request $request)
    {
        $templates = CmsTemplate::orderByDesc('created_at')->get();
        $filename = 'cms_templates_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function () use ($templates) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Nom', 'Slug', 'Site URL', 'Version', 'Categorie', 'Statut', 'Cree le']);

            foreach ($templates as $template) {
                fputcsv($file, [
                    $template->id,
                    $template->name,
                    $template->slug,
                    $template->site_url,
                    $template->version,
                    $template->category,
                    $template->status,
                    optional($template->created_at)->format('d/m/Y H:i'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    protected function storePreviewImage(Request $request): ?string
    {
        if (!$request->hasFile('image_preview')) {
            return null;
        }

        $path = $request->file('image_preview')->store('cms/templates/previews', 'public');

        return url(Storage::disk('public')->url($path));
    }

    protected function templateCategories(): array
    {
        $defaults = [
            'General',
            'Landing page',
            'Restaurant',
            'Hotel',
            'Ecommerce',
            'Portfolio',
            'Blog',
            'SaaS',
            'Immobilier',
            'Industrie',
            'Services',
            'Tourisme',
            'Agroalimentaire',
            'Construction Boid',
            'Place Immoblier',
            'Automobile',
            'Next Level',
            'Education',
            'Medical',
            'Association',
            'Evenement',
        ];

        $existing = CmsTemplate::query()
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->filter()
            ->all();

        return collect($defaults)
            ->merge($existing)
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    protected function templateContentsPayload($templates): array
    {
        return $templates->getCollection()
            ->mapWithKeys(fn (CmsTemplate $template) => [$template->id => $template->page_content])
            ->all();
    }

    protected function templatesPayload($templates): array
    {
        return $templates->getCollection()
            ->mapWithKeys(fn (CmsTemplate $template) => [
                $template->id => [
                    'id' => $template->id,
                    'name' => $template->name,
                    'site_url' => $template->site_url,
                    'page_content' => $template->page_content,
                    'version' => $template->version,
                    'category' => $template->category,
                    'description' => $template->description,
                    'status' => $template->status,
                ],
            ])
            ->all();
    }

    protected function createCmsPageForTemplate(Etablissement $etablissement, CmsTemplate $template, bool $isHome): Page
    {
        $slug = $this->uniquePageSlug($etablissement->id, $template->slug);

        return Page::create([
            'etablissement_id' => $etablissement->id,
            'user_id' => Auth::id(),
            'title' => $template->name,
            'slug' => $slug,
            'content' => $template->page_content,
            'meta' => [
                'source' => 'cms_template',
                'template_cms_id' => $template->id,
            ],
            'status' => 'published',
            'visibility' => 'public',
            'is_home' => $isHome,
            'published_at' => now(),
        ]);
    }

    protected function uniquePageSlug(int $etablissementId, string $baseSlug): string
    {
        $base = Str::slug($baseSlug) ?: 'template';
        $slug = $base;
        $counter = 2;

        while (Page::where('etablissement_id', $etablissementId)->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    protected function wrapPreviewHtml(string $title, ?string $content): string
    {
        $safeTitle = e($title);

        return <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$safeTitle}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body { margin: 0; background: #f8fafc; }
        .template-preview-shell { max-width: 1240px; margin: 0 auto; background: #fff; min-height: 100vh; }
    </style>
</head>
<body>
    <main class="template-preview-shell">
        {$content}
    </main>
</body>
</html>
HTML;
    }
}
