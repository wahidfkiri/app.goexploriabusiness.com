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
            <h1 class="blog-editor-title">Nouvel article blog</h1>
            <p class="blog-editor-note">Creation assistee IA, edition riche et optimisation SEO.</p>
        </div>
        <a href="{{ route('cms.admin.blogs.index', ['etablissementId' => $etablissement->id]) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Retour
        </a>
    </div>

    <div class="main-card-modern p-4">
        <form id="blogForm" method="POST" action="{{ route('cms.admin.blogs.store', ['etablissementId' => $etablissement->id]) }}">
            @csrf
            @include('cms::admin.blogs.partials.form-fields')

            <div class="mt-4 d-flex gap-2 flex-wrap">
                <button type="submit" class="btn btn-primary">Creer l'article</button>
                <button type="button" class="btn btn-outline-primary" id="saveAndPublishBtn">Creer et publier</button>
            </div>
        </form>
    </div>
</div>

@include('cms::admin.blogs.partials.form-script')
@endsection
