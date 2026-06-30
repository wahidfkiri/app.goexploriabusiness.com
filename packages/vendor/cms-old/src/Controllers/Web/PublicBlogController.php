<?php

namespace Vendor\Cms\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Etablissement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Vendor\Cms\Models\BlogPost;

class PublicBlogController extends Controller
{
    public function index(Request $request, $etablissementId)
    {
        $etablissement = Etablissement::findOrFail($etablissementId);

        if (!$this->isBlogEnabled($etablissement)) {
            abort(404);
        }

        if (!Schema::connection('cms')->hasTable('cms_blog_posts')) {
            $posts = collect();
            return view('cms::web.blog.index', compact('etablissement', 'posts'));
        }

        $posts = BlogPost::where('etablissement_id', $etablissement->id)
            ->published()
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = trim((string) $request->q);
                $query->where(function ($builder) use ($q) {
                    $builder->where('title', 'like', "%{$q}%")
                        ->orWhere('excerpt', 'like', "%{$q}%")
                        ->orWhere('content', 'like', "%{$q}%")
                        ->orWhere('seo_keywords', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->paginate(9)
            ->withQueryString();

        return view('cms::web.blog.index', compact('etablissement', 'posts'));
    }

    public function show($etablissementId, $slug)
    {
        $etablissement = Etablissement::findOrFail($etablissementId);

        if (!$this->isBlogEnabled($etablissement)) {
            abort(404);
        }

        if (!Schema::connection('cms')->hasTable('cms_blog_posts')) {
            abort(404);
        }

        $post = BlogPost::where('etablissement_id', $etablissement->id)
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $relatedPosts = BlogPost::where('etablissement_id', $etablissement->id)
            ->published()
            ->where('id', '!=', $post->id)
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        return view('cms::web.blog.show', compact('etablissement', 'post', 'relatedPosts'));
    }

    public function indexApi(Request $request, $etablissementId)
    {
        $etablissement = Etablissement::findOrFail($etablissementId);

        if (!$this->isBlogEnabled($etablissement)) {
            return response()->json([
                'success' => true,
                'data' => [
                    'data' => [],
                ],
            ]);
        }

        if (!Schema::connection('cms')->hasTable('cms_blog_posts')) {
            return response()->json([
                'success' => true,
                'data' => [
                    'data' => [],
                ],
            ]);
        }

        $posts = BlogPost::where('etablissement_id', $etablissement->id)
            ->published()
            ->select([
                'id',
                'title',
                'slug',
                'excerpt',
                'featured_image',
                'tags',
                'published_at',
                'seo_title',
                'seo_description',
            ])
            ->orderByDesc('published_at')
            ->paginate(12);

        return response()->json([
            'success' => true,
            'data' => $posts,
        ]);
    }

    public function showApi($etablissementId, $slug)
    {
        $etablissement = Etablissement::findOrFail($etablissementId);

        if (!$this->isBlogEnabled($etablissement)) {
            abort(404);
        }

        if (!Schema::connection('cms')->hasTable('cms_blog_posts')) {
            abort(404);
        }

        $post = BlogPost::where('etablissement_id', $etablissement->id)
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $post,
        ]);
    }

    private function isBlogEnabled(Etablissement $etablissement): bool
    {
        if (function_exists('is_blog_enabled')) {
            return is_blog_enabled($etablissement->id);
        }

        $value = $etablissement->getSetting('blog_enabled', false, 'general');

        if (is_bool($value)) {
            return $value;
        }

        $normalizedValue = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        return $normalizedValue ?? false;
    }
}
