@php
    $post = $post ?? null;
@endphp

<style>
    .blog-form-layout .section-card {
        border: 1px solid #d7dfeb;
        border-radius: 16px;
        background: #fff;
        padding: 1rem;
        margin-bottom: 1rem;
    }
    .blog-form-layout .section-title {
        font-size: .95rem;
        text-transform: uppercase;
        letter-spacing: .07em;
        color: #546375;
        margin-bottom: .85rem;
        font-weight: 700;
    }
    .blog-form-layout .ia-banner {
        border: 1px solid #b8d7ff;
        border-radius: 14px;
        background: linear-gradient(135deg, #eef6ff, #f9fcff);
        padding: .9rem;
    }
    .blog-form-layout .ia-title {
        color: #0f3568;
        font-weight: 700;
        margin-bottom: .65rem;
    }
    .blog-form-layout .preview-note {
        font-size: .82rem;
        color: #67768a;
    }
    .blog-form-layout .sticky-sidebar {
        position: sticky;
        top: 1rem;
    }
</style>

<div class="row g-4 blog-form-layout">
    <div class="col-lg-8">
        <div class="ia-banner mb-3">
            <div class="ia-title">Assistant IA (Gemini)</div>
            <input type="hidden" id="aiGenerateEndpoint" value="{{ route('cms.admin.blogs.ai.generate', ['etablissementId' => $etablissement->id]) }}">
            <div class="row g-2">
                <div class="col-md-8">
                    <input type="text" id="aiSubject" class="form-control" placeholder="Sujet article: ex. Comment choisir un appartement familial ?">
                </div>
                <div class="col-md-4">
                    <input type="text" id="aiKeyword" class="form-control" placeholder="Mot-cle principal">
                </div>
                <div class="col-md-8">
                    <input type="text" id="aiBusinessContext" class="form-control" placeholder="Contexte activite: immobilier residentiel a Montreal">
                </div>
                <div class="col-md-2">
                    <select id="aiTone" class="form-select">
                        <option value="professionnel">Professionnel</option>
                        <option value="commercial">Commercial</option>
                        <option value="educatif">Educatif</option>
                        <option value="inspirant">Inspirant</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="number" id="aiMinWords" class="form-control" min="300" max="3000" value="900" title="Nombre minimum de mots">
                </div>
                <div class="col-12 d-flex gap-2 align-items-center flex-wrap">
                    <button type="button" id="generateWithAiBtn" class="btn btn-outline-primary">
                        Generer article + SEO
                    </button>
                    <small id="aiGenerationStatus" class="text-muted"></small>
                </div>
            </div>
        </div>

        <div class="section-card">
            <div class="section-title">Contenu principal</div>
            <div class="mb-3">
                <label class="form-label">Titre *</label>
                <input type="text" id="blogTitleField" name="title" class="form-control" required value="{{ old('title', $post->title ?? '') }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Slug</label>
                <input type="text" id="blogSlugField" name="slug" class="form-control" value="{{ old('slug', $post->slug ?? '') }}" placeholder="auto-depuis-le-titre">
                <div class="preview-note mt-1">Laissez vide pour generation automatique.</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Resume</label>
                <textarea id="blogExcerptField" name="excerpt" class="form-control" rows="3" placeholder="Petit resume de l'article">{{ old('excerpt', $post->excerpt ?? '') }}</textarea>
            </div>

            <div class="mb-0">
                <label class="form-label">Contenu de l'article</label>
                <textarea id="blogContentEditor" name="content" class="form-control" rows="16" placeholder="Saisissez le contenu de l'article...">{{ old('content', $post->content ?? '') }}</textarea>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="sticky-sidebar">
            <div class="section-card">
                <div class="section-title">Publication</div>

                <div class="mb-3">
                    <label class="form-label">Statut</label>
                    <select name="status" id="statusField" class="form-select">
                        <option value="draft" {{ old('status', $post->status ?? 'draft') === 'draft' ? 'selected' : '' }}>Brouillon</option>
                        <option value="published" {{ old('status', $post->status ?? '') === 'published' ? 'selected' : '' }}>Publie</option>
                        <option value="archived" {{ old('status', $post->status ?? '') === 'archived' ? 'selected' : '' }}>Archive</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Date de publication</label>
                    <input type="datetime-local" name="published_at" class="form-control" value="{{ old('published_at', optional($post->published_at ?? null)->format('Y-m-d\TH:i')) }}">
                </div>

                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="isFeatured" {{ old('is_featured', $post->is_featured ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="isFeatured">Article mis en avant</label>
                </div>

                <div class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" name="allow_comments" value="1" id="allowComments" {{ old('allow_comments', $post->allow_comments ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="allowComments">Autoriser les commentaires</label>
                </div>
            </div>

            <div class="section-card mb-0">
                <div class="section-title">SEO et media</div>

                <div class="mb-3">
                    <label class="form-label">Image principale (URL)</label>
                    <input type="text" name="featured_image" class="form-control" value="{{ old('featured_image', $post->featured_image ?? '') }}" placeholder="https://...">
                </div>

                <div class="mb-3">
                    <label class="form-label">Titre SEO</label>
                    <input type="text" id="blogSeoTitleField" name="seo_title" class="form-control" value="{{ old('seo_title', $post->seo_title ?? '') }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Description SEO</label>
                    <textarea id="blogSeoDescriptionField" name="seo_description" class="form-control" rows="3">{{ old('seo_description', $post->seo_description ?? '') }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Mots-cles SEO</label>
                    <input type="text" id="blogSeoKeywordsField" name="seo_keywords" class="form-control" value="{{ old('seo_keywords', $post->seo_keywords ?? '') }}" placeholder="mot1, mot2, mot3">
                </div>

                <div class="mb-3">
                    <label class="form-label">Tags (separes par virgule)</label>
                    <input type="text" id="blogTagsField" name="tags" class="form-control" value="{{ old('tags', isset($post) && is_array($post->tags) ? implode(', ', $post->tags) : '') }}" placeholder="actualite, offre, produit">
                </div>

                <div class="mb-3">
                    <label class="form-label">URL canonique</label>
                    <input type="url" id="blogCanonicalUrlField" name="canonical_url" class="form-control" value="{{ old('canonical_url', $post->canonical_url ?? '') }}">
                </div>

                <div class="mb-0">
                    <label class="form-label">Image Open Graph (URL)</label>
                    <input type="text" name="og_image_url" class="form-control" value="{{ old('og_image_url', $post->og_image_url ?? '') }}" placeholder="https://...">
                </div>
            </div>
        </div>
    </div>
</div>
