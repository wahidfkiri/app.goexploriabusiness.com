<?php

namespace Vendor\Cms\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Etablissement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Vendor\Cms\Models\BlogPost;
use Vendor\Cms\Requests\BlogPostRequest;
use Vendor\Cms\Services\GeminiBlogService;

class BlogController extends Controller
{
    public function generateAiContent(Request $request, $etablissementId, GeminiBlogService $geminiBlogService): JsonResponse
    {
        try {
            $validated = $request->validate([
                'subject' => 'required|string|min:3|max:255',
                'business_context' => 'nullable|string|max:2000',
                'target_keyword' => 'nullable|string|max:255',
                'tone' => 'nullable|string|max:100',
                'language' => 'nullable|string|max:10',
                'min_words' => 'nullable|integer|min:300|max:3000',
            ]);

            $etablissement = Etablissement::findOrFail($etablissementId);

            $data = $geminiBlogService->generateArticleAndSeo(array_merge($validated, [
                'business_name' => (string) $etablissement->name,
            ]));

            if (blank($data['slug']) && !blank($data['title'])) {
                $data['slug'] = $this->normalizeSlug((string) $data['title']);
            }

            $data['slug'] = BlogPost::generateUniqueSlug(
                $this->normalizeSlug((string) ($data['slug'] ?: (string) $validated['subject'])),
                (int) $etablissement->id
            );

            return response()->json([
                'success' => true,
                'message' => 'Contenu généré avec IA.',
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            Log::error('AI blog generation error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur génération IA: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function index(Request $request, $etablissementId)
    {
        $etablissement = Etablissement::findOrFail($etablissementId);

        $query = BlogPost::where('etablissement_id', $etablissement->id);

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        $posts = $query->orderByDesc('updated_at')->paginate(15)->withQueryString();

        $stats = [
            'total' => BlogPost::where('etablissement_id', $etablissement->id)->count(),
            'published' => BlogPost::where('etablissement_id', $etablissement->id)->where('status', 'published')->count(),
            'drafts' => BlogPost::where('etablissement_id', $etablissement->id)->where('status', 'draft')->count(),
            'archived' => BlogPost::where('etablissement_id', $etablissement->id)->where('status', 'archived')->count(),
        ];

        return view('cms::admin.blogs.index', compact('posts', 'stats', 'etablissement'));
    }

    public function create($etablissementId)
    {
        $etablissement = Etablissement::findOrFail($etablissementId);

        return view('cms::admin.blogs.create', compact('etablissement'));
    }

    public function store(BlogPostRequest $request, $etablissementId): JsonResponse
    {
        try {
            DB::connection('cms')->beginTransaction();

            $etablissement = Etablissement::findOrFail($etablissementId);
            $data = $this->prepareData($request, $etablissement->id);

            $post = BlogPost::create($data);

            DB::connection('cms')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Article cree avec succes.',
                'post' => $post,
                'redirect' => route('cms.admin.blogs.edit', [
                    'etablissementId' => $etablissement->id,
                    'id' => $post->id,
                ]),
            ]);
        } catch (\Exception $e) {
            DB::connection('cms')->rollBack();
            Log::error('Blog post creation error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la creation: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function edit($etablissementId, $id)
    {
        $etablissement = Etablissement::findOrFail($etablissementId);

        $post = BlogPost::where('etablissement_id', $etablissement->id)
            ->findOrFail($id);

        return view('cms::admin.blogs.edit', compact('post', 'etablissement'));
    }

    public function update(BlogPostRequest $request, $etablissementId, $id): JsonResponse
    {
        try {
            DB::connection('cms')->beginTransaction();

            $etablissement = Etablissement::findOrFail($etablissementId);
            $post = BlogPost::where('etablissement_id', $etablissement->id)->findOrFail($id);

            $data = $this->prepareData($request, $etablissement->id, $post->id);
            $post->update($data);

            DB::connection('cms')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Article mis a jour avec succes.',
                'post' => $post,
            ]);
        } catch (\Exception $e) {
            DB::connection('cms')->rollBack();
            Log::error('Blog post update error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise a jour: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($etablissementId, $id): JsonResponse
    {
        try {
            $etablissement = Etablissement::findOrFail($etablissementId);

            $post = BlogPost::where('etablissement_id', $etablissement->id)->findOrFail($id);
            $post->delete();

            return response()->json([
                'success' => true,
                'message' => 'Article supprime avec succes.',
            ]);
        } catch (\Exception $e) {
            Log::error('Blog post deletion error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function publish($etablissementId, $id): JsonResponse
    {
        return $this->setStatus($etablissementId, $id, 'published');
    }

    public function unpublish($etablissementId, $id): JsonResponse
    {
        return $this->setStatus($etablissementId, $id, 'draft');
    }

    protected function setStatus($etablissementId, $id, string $status): JsonResponse
    {
        try {
            $etablissement = Etablissement::findOrFail($etablissementId);
            $post = BlogPost::where('etablissement_id', $etablissement->id)->findOrFail($id);

            $post->status = $status;
            if ($status === 'published') {
                $post->published_at = now();
            }
            $post->save();

            return response()->json([
                'success' => true,
                'message' => $status === 'published' ? 'Article publie.' : 'Article passe en brouillon.',
                'post' => $post,
            ]);
        } catch (\Exception $e) {
            Log::error('Blog post status update error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du changement de statut: ' . $e->getMessage(),
            ], 500);
        }
    }

    protected function prepareData(BlogPostRequest $request, int $etablissementId, ?int $ignoreId = null): array
    {
        $data = $request->validated();

        $data['etablissement_id'] = $etablissementId;
        $data['user_id'] = optional($request->user())->id;

        $slug = !empty($data['slug'])
            ? $this->normalizeSlug((string) $data['slug'])
            : $this->normalizeSlug((string) $data['title']);
        $data['slug'] = BlogPost::generateUniqueSlug($slug, $etablissementId, $ignoreId);

        $data['is_featured'] = $request->boolean('is_featured');
        $data['allow_comments'] = $request->boolean('allow_comments');

        $data['featured_image'] = $this->normalizeUrl($data['featured_image'] ?? null);
        $data['og_image_url'] = $this->normalizeUrl($data['og_image_url'] ?? null);

        $tags = array_filter(array_map('trim', explode(',', (string) ($data['tags'] ?? ''))));
        $data['tags'] = array_values(array_unique($tags));

        if (($data['status'] ?? 'draft') === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        return $data;
    }

    protected function normalizeUrl(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        $value = trim($value);

        if (Str::startsWith($value, ['http://', 'https://'])) {
            return $value;
        }

        if (Str::startsWith($value, '/')) {
            return url($value);
        }

        return url('/' . ltrim($value, '/'));
    }

    protected function normalizeSlug(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return 'article';
        }

        $slug = Str::slug(Str::ascii($value));

        return $slug !== '' ? $slug : 'article';
    }
}
