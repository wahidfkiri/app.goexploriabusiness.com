@extends('layouts.app')

@section('content')
<style>
    .blogs-admin-wrap {
        --blog-ink: #10243f;
        --blog-muted: #5f6b7a;
        --blog-line: #d7dfeb;
        --blog-paper: #f7f9fd;
        --blog-primary: #0b6ef5;
        --blog-success: #0b8e5f;
    }
    .blogs-admin-wrap .hero-shell {
        border-radius: 20px;
        padding: 1.25rem;
        border: 1px solid var(--blog-line);
        background: radial-gradient(circle at top right, #dfefff, #ffffff 55%);
    }
    .blogs-admin-wrap .hero-title {
        color: var(--blog-ink);
        margin: 0;
        font-weight: 800;
        letter-spacing: .2px;
    }
    .blogs-admin-wrap .hero-subtitle {
        margin: .35rem 0 0;
        color: var(--blog-muted);
    }
    .blogs-admin-wrap .stat-card {
        height: 100%;
        border: 1px solid var(--blog-line);
        border-radius: 16px;
        background: #fff;
        padding: .95rem 1rem;
    }
    .blogs-admin-wrap .stat-label {
        color: var(--blog-muted);
        font-size: .82rem;
        text-transform: uppercase;
        letter-spacing: .07em;
        font-weight: 700;
    }
    .blogs-admin-wrap .stat-value {
        color: var(--blog-ink);
        font-size: 1.65rem;
        margin-top: .3rem;
        font-weight: 800;
    }
    .blogs-admin-wrap .table-shell {
        border: 1px solid var(--blog-line);
        border-radius: 16px;
        overflow: hidden;
        background: #fff;
    }
    .blogs-admin-wrap .table thead th {
        background: var(--blog-paper);
        border-bottom: 1px solid var(--blog-line);
        color: var(--blog-muted);
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .06em;
    }
    .blogs-admin-wrap .article-title {
        color: var(--blog-ink);
        font-weight: 700;
    }
    .blogs-admin-wrap .article-excerpt {
        color: var(--blog-muted);
        font-size: .87rem;
    }
    .blogs-admin-wrap .status-pill {
        border-radius: 999px;
        padding: .3rem .7rem;
        font-size: .74rem;
        font-weight: 700;
        letter-spacing: .02em;
    }
    .blogs-admin-wrap .status-published {
        background: rgba(11, 142, 95, .12);
        color: var(--blog-success);
    }
    .blogs-admin-wrap .status-draft {
        background: rgba(255, 176, 0, .14);
        color: #8a6400;
    }
    .blogs-admin-wrap .status-archived {
        background: rgba(107, 114, 128, .18);
        color: #475467;
    }
</style>

<div class="dashboard-content blogs-admin-wrap">
    <div class="hero-shell mb-4">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
            <div>
                <h1 class="hero-title">Blog Manager · {{ $etablissement->name }}</h1>
                <p class="hero-subtitle">Creez, optimisez et publiez vos articles SEO depuis un seul espace.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('cms.admin.dashboard', ['etablissementId' => $etablissement->id, 'slug' => ($etablissement->slug ?: \Illuminate\Support\Str::slug((string) $etablissement->name)), 'section' => 'blogs']) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Dashboard
                </a>
                <a href="{{ route('cms.admin.blogs.create', ['etablissementId' => $etablissement->id]) }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Nouvel article
                </a>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-label">Total</div>
                <div class="stat-value">{{ (int) ($stats['total'] ?? 0) }}</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-label">Publies</div>
                <div class="stat-value">{{ (int) ($stats['published'] ?? 0) }}</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-label">Brouillons</div>
                <div class="stat-value">{{ (int) ($stats['drafts'] ?? 0) }}</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-label">Archives</div>
                <div class="stat-value">{{ (int) ($stats['archived'] ?? 0) }}</div>
            </div>
        </div>
    </div>

    <div class="main-card-modern p-4 mb-4">
        <form method="GET" class="row g-2 mb-0">
            <div class="col-lg-6">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Rechercher par titre, slug, extrait...">
            </div>
            <div class="col-lg-3">
                <select name="status" class="form-select">
                    <option value="all">Tous les statuts</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Brouillon</option>
                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Publie</option>
                    <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Archive</option>
                </select>
            </div>
            <div class="col-lg-3 d-flex gap-2">
                <button class="btn btn-outline-primary w-100" type="submit">Filtrer</button>
                <a href="{{ route('cms.admin.blogs.index', ['etablissementId' => $etablissement->id]) }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>

    <div class="table-shell">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Slug</th>
                        <th>Statut</th>
                        <th>Publie le</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($posts as $post)
                        <tr>
                            <td>
                                <div class="article-title">{{ $post->title }}</div>
                                @if($post->is_featured)
                                    <span class="badge bg-primary ms-1">Featured</span>
                                @endif
                                <div class="article-excerpt mt-1">{{ \Illuminate\Support\Str::limit($post->excerpt, 110) }}</div>
                            </td>
                            <td><code>{{ $post->slug }}</code></td>
                            <td>
                                @if($post->status === 'published')
                                    <span class="status-pill status-published">Publie</span>
                                @elseif($post->status === 'archived')
                                    <span class="status-pill status-archived">Archive</span>
                                @else
                                    <span class="status-pill status-draft">Brouillon</span>
                                @endif
                            </td>
                            <td>{{ optional($post->published_at)->format('d/m/Y H:i') ?? '-' }}</td>
                            <td class="d-flex gap-1 flex-wrap">
                                <a href="{{ route('cms.admin.blogs.edit', ['etablissementId' => $etablissement->id, 'id' => $post->id]) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('cms.company.blog.show', ['etablissementId' => $etablissement->id, 'slug' => $post->slug]) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteBlogPost({{ $post->id }})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">Aucun article pour le moment.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $posts->links() }}</div>
</div>

<script>
function deleteBlogPost(postId) {
    if (!confirm('Supprimer cet article ?')) return;

    fetch(`/admin/cms/{{ $etablissement->id }}/blogs/${postId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
            return;
        }
        alert(data.message || 'Erreur de suppression');
    })
    .catch(() => alert('Erreur de suppression'));
}
</script>
@endsection
