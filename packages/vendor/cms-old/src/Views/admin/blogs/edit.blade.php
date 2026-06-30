@extends('layouts.app')

@section('content')
<style>
    .blog-editor-shell {
        border: 1px solid #d7dfeb;
        border-radius: 20px;
        background: linear-gradient(160deg, #ffffff, #f8fbff 70%);
        padding: 1rem;
    }
    .blog-editor-title {
        margin: 0;
        color: #0d233f;
        font-weight: 800;
    }
    .blog-editor-note {
        color: #5f6b7a;
        margin-top: .35rem;
    }
</style>

<div class="dashboard-content">
    <div class="blog-editor-shell mb-4 d-flex justify-content-between align-items-start gap-3 flex-wrap">
        <div>
            <h1 class="blog-editor-title">Modifier article blog</h1>
            <p class="blog-editor-note">Mise a jour du contenu, SEO et publication.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('cms.company.blog.show', ['etablissementId' => $etablissement->id, 'slug' => $post->slug]) }}" target="_blank" class="btn btn-outline-secondary">
                <i class="fas fa-eye me-1"></i> Voir en ligne
            </a>
            <a href="{{ route('cms.admin.blogs.index', ['etablissementId' => $etablissement->id]) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Retour
            </a>
        </div>
    </div>

    <div class="main-card-modern p-4">
        <form id="blogForm" method="POST" action="{{ route('cms.admin.blogs.update', ['etablissementId' => $etablissement->id, 'id' => $post->id]) }}">
            @csrf
            @method('PUT')
            @include('cms::admin.blogs.partials.form-fields', ['post' => $post])

            <div class="mt-4 d-flex gap-2 flex-wrap">
                <button type="submit" class="btn btn-primary">Enregistrer</button>

                @if($post->status !== 'published')
                    <button type="button" class="btn btn-success" id="publishNowBtn">Publier maintenant</button>
                @endif

                <button type="button" class="btn btn-outline-danger" onclick="deleteCurrentPost()">Supprimer</button>
            </div>
        </form>
    </div>
</div>

<script>
function deleteCurrentPost() {
    if (!confirm('Supprimer cet article ?')) return;

    fetch(`/admin/cms/{{ $etablissement->id }}/blogs/{{ $post->id }}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = @json(route('cms.admin.blogs.index', ['etablissementId' => $etablissement->id]));
            return;
        }
        alert(data.message || 'Erreur lors de la suppression');
    })
    .catch(() => alert('Erreur lors de la suppression'));
}
</script>

@include('cms::admin.blogs.partials.form-script', ['post' => $post])
@endsection
