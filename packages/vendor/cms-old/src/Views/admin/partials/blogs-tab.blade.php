@php
    $blogEnabled = $stats['etablissement']->getSetting('blog_enabled', false);
    $blogSectionTitle = $stats['etablissement']->getSetting('blog_section_title', 'Derniers articles');
@endphp

<div
    class="tab-pane fade"
    id="v-pills-blogs"
    role="tabpanel"
    data-etablissement-id="{{ $stats['etablissement']->id ?? '' }}"
    data-blog-enabled="{{ $blogEnabled ? '1' : '0' }}">
    <div class="tab-content-header">
        <h3 class="tab-title">
            <i class="fas fa-blog me-2" style="color: var(--primary-color);"></i>
            Blog et contenus SEO
        </h3>
        <div>
            <a href="{{ route('cms.admin.blogs.create', ['etablissementId' => $stats['etablissement']->id]) }}" class="btn btn-primary btn-sm me-2">
                <i class="fas fa-plus me-1"></i>Nouvel article
            </a>
            <a href="{{ route('cms.admin.blogs.index', ['etablissementId' => $stats['etablissement']->id]) }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-list me-1"></i>Voir tout
            </a>
        </div>
    </div>

    <div class="blog-status-card" id="blogStatusCard">
        <div>
            <label class="blog-status-title" for="blogEnabledSwitch">Activation du blog</label>
            <p class="blog-status-text mb-0" id="blogStatusText">
                {{ $blogEnabled ? 'Les articles blog sont affiches sur le site public.' : 'Les articles blog sont masques sur le site public.' }}
            </p>
        </div>
        <div class="form-check form-switch blog-status-switch">
            <input
                class="form-check-input"
                type="checkbox"
                role="switch"
                id="blogEnabledSwitch"
                {{ $blogEnabled ? 'checked' : '' }}>
            <label class="form-check-label" for="blogEnabledSwitch" id="blogEnabledLabel">
                {{ $blogEnabled ? 'Actif' : 'Inactif' }}
            </label>
        </div>
    </div>

    <div class="blog-section-title-card">
        <div>
            <label class="blog-status-title" for="blogSectionTitleInput">Titre de section blog</label>
            <p class="blog-status-text mb-0">Ce titre peut être utilisé dans le front avant la liste des articles.</p>
        </div>
        <div class="blog-section-title-control">
            <input type="text" class="form-control form-control-sm" id="blogSectionTitleInput" value="{{ $blogSectionTitle }}" maxlength="191" placeholder="Ex: Nos actualités">
            <button type="button" class="btn btn-sm btn-primary" id="saveBlogSectionTitleBtn">
                <i class="fas fa-save me-1"></i>Enregistrer
            </button>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat-mini-card">
                <div class="stat-mini-value">{{ $stats['total_blog_posts'] ?? 0 }}</div>
                <div class="stat-mini-label">Articles total</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-mini-card">
                <div class="stat-mini-value">{{ $stats['published_blog_posts'] ?? 0 }}</div>
                <div class="stat-mini-label">Articles publies</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-mini-card">
                <div class="stat-mini-value">{{ $stats['draft_blog_posts'] ?? 0 }}</div>
                <div class="stat-mini-label">Brouillons</div>
            </div>
        </div>
    </div>

    <div class="table-container-modern">
        <table class="modern-table">
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
                @forelse(($stats['recent_blog_posts'] ?? []) as $post)
                    <tr>
                        <td>{{ $post->title }}</td>
                        <td><code>{{ $post->slug }}</code></td>
                        <td>
                            @if($post->status === 'published')
                                <span class="badge bg-success">Publie</span>
                            @elseif($post->status === 'archived')
                                <span class="badge bg-secondary">Archive</span>
                            @else
                                <span class="badge bg-warning text-dark">Brouillon</span>
                            @endif
                        </td>
                        <td>{{ optional($post->published_at)->format('d/m/Y H:i') ?? '-' }}</td>
                        <td>
                            <a href="{{ route('cms.admin.blogs.edit', ['etablissementId' => $stats['etablissement']->id, 'id' => $post->id]) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="{{ route('cms.company.blog.show', ['etablissementId' => $stats['etablissement']->id, 'slug' => $post->slug]) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">Aucun article cree pour le moment.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<style>
.blog-status-card,
.blog-section-title-card {
    align-items: center;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    display: flex;
    gap: 18px;
    justify-content: space-between;
    margin-bottom: 18px;
    padding: 16px;
}
.blog-section-title-card {
    display: grid;
    gap: 16px;
    grid-template-columns: minmax(0, 1fr) minmax(260px, 420px);
}
.blog-status-card.is-disabled {
    background: #fff7ed;
    border-color: #fed7aa;
}
.blog-status-title {
    color: #1e293b;
    display: block;
    font-size: 14px;
    font-weight: 700;
    margin-bottom: 4px;
}
.blog-status-text {
    color: #64748b;
    font-size: 13px;
}
.blog-status-switch {
    align-items: center;
    display: flex;
    gap: 10px;
    margin: 0;
    min-width: 128px;
}
.blog-status-switch .form-check-input {
    cursor: pointer;
    height: 1.35rem;
    margin: 0;
    width: 2.5rem;
}
.blog-status-switch .form-check-label {
    color: #334155;
    cursor: pointer;
    font-size: 13px;
    font-weight: 700;
    margin: 0;
}
.blog-section-title-control {
    align-items: center;
    display: flex;
    gap: 10px;
}
@media (max-width: 768px) {
    .blog-status-card {
        align-items: flex-start;
        flex-direction: column;
    }

    .blog-section-title-card {
        grid-template-columns: 1fr;
    }

    .blog-section-title-control {
        align-items: stretch;
        flex-direction: column;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const blogPane = document.getElementById('v-pills-blogs');
    const switchEl = document.getElementById('blogEnabledSwitch');

    updateBlogEnabledUi(blogPane?.dataset?.blogEnabled === '1');
    switchEl?.addEventListener('change', saveBlogEnabled);
    document.getElementById('saveBlogSectionTitleBtn')?.addEventListener('click', saveBlogSectionTitle);
});

async function saveBlogEnabled(event) {
    const checkbox = event.target;
    const isEnabled = checkbox.checked;
    const etablissementId = document.getElementById('v-pills-blogs')?.dataset?.etablissementId;
    const formData = new FormData();

    if (!etablissementId) {
        showBlogToast('Erreur: etablissement non defini', 'error');
        return;
    }

    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    formData.append('site_blog_enabled', isEnabled ? '1' : '0');
    checkbox.disabled = true;

    try {
        const response = await fetch(`/admin/cms/${etablissementId}/settings`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        });
        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || 'Erreur lors de la sauvegarde');
        }

        document.getElementById('v-pills-blogs').dataset.blogEnabled = isEnabled ? '1' : '0';
        updateBlogEnabledUi(isEnabled);
        showBlogToast(isEnabled ? 'Blog active' : 'Blog desactive', 'success');
    } catch (error) {
        checkbox.checked = !isEnabled;
        updateBlogEnabledUi(!isEnabled);
        showBlogToast(error.message || 'Erreur lors de la sauvegarde', 'error');
    } finally {
        checkbox.disabled = false;
    }
}

function updateBlogEnabledUi(isEnabled) {
    const card = document.getElementById('blogStatusCard');
    const label = document.getElementById('blogEnabledLabel');
    const text = document.getElementById('blogStatusText');
    const checkbox = document.getElementById('blogEnabledSwitch');

    if (checkbox) checkbox.checked = isEnabled;
    if (card) card.classList.toggle('is-disabled', !isEnabled);
    if (label) label.textContent = isEnabled ? 'Actif' : 'Inactif';
    if (text) {
        text.textContent = isEnabled
            ? 'Les articles blog sont affiches sur le site public.'
            : 'Les articles blog sont masques sur le site public.';
    }
}

async function saveBlogSectionTitle() {
    const pane = document.getElementById('v-pills-blogs');
    const input = document.getElementById('blogSectionTitleInput');
    const button = document.getElementById('saveBlogSectionTitleBtn');
    const formData = new FormData();
    const etablissementId = pane?.dataset?.etablissementId;

    if (!etablissementId) {
        showBlogToast('Erreur: etablissement non defini', 'error');
        return;
    }

    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    formData.append('site_blog_section_title', input?.value?.trim() || '');
    if (button) button.disabled = true;

    try {
        const response = await fetch(`/admin/cms/${etablissementId}/settings`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        });
        const result = await response.json();
        if (!result.success) throw new Error(result.message || 'Erreur lors de la sauvegarde');
        showBlogToast('Titre de section blog sauvegardé', 'success');
    } catch (error) {
        showBlogToast(error.message || 'Erreur lors de la sauvegarde', 'error');
    } finally {
        if (button) button.disabled = false;
    }
}

function showBlogToast(message, type = 'success') {
    if (typeof showToast === 'function') {
        showToast(message, type);
        return;
    }

    console[type === 'error' ? 'error' : 'log'](message);
}
</script>
