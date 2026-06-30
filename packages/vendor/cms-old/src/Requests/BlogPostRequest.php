<?php

namespace Vendor\Cms\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BlogPostRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|regex:/^[a-z0-9-]+$/',
            'excerpt' => 'nullable|string|max:1500',
            'content' => 'nullable|string',
            'featured_image' => 'nullable|string|max:2048',
            'tags' => 'nullable|string|max:1000',
            'status' => 'required|in:draft,published,archived',
            'is_featured' => 'nullable|boolean',
            'allow_comments' => 'nullable|boolean',
            'published_at' => 'nullable|date',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:1000',
            'seo_keywords' => 'nullable|string|max:1000',
            'canonical_url' => 'nullable|url|max:2048',
            'og_image_url' => 'nullable|string|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Le titre est requis.',
            'status.required' => 'Le statut est requis.',
            'slug.regex' => 'Le slug ne peut contenir que des lettres minuscules, chiffres et tirets.',
            'canonical_url.url' => 'L\'URL canonique doit etre une URL valide.',
        ];
    }
}
