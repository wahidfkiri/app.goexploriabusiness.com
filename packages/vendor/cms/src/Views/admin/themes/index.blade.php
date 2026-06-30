@extends('layouts.app')

@section('title', 'Bibliotheque de templates CMS')

@section('content')
<main class="dashboard-content">
<div class="global-templates-page">
    <div class="page-header-modern">
        <div class="page-header-left">
            <div class="page-icon">
                <i class="fas fa-file-code"></i>
            </div>
            <div>
                <h1 class="page-title">Bibliotheque de templates CMS</h1>
                <p class="page-subtitle">Creez des templates HTML et rendez-les installables par etablissement.</p>
            </div>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('cms.admin.themes.export') }}" class="btn-header-secondary">
                <i class="fas fa-download"></i> Exporter
            </a>
            <button class="btn-header-primary" onclick="openTemplateCreate()">
                <i class="fas fa-plus"></i> Creer un template
            </button>
        </div>
    </div>

    <div class="templates-stats-row">
        <div class="template-stat-card">
            <div class="ts-icon ts-icon-blue">
                <i class="fas fa-layer-group"></i>
            </div>
            <div>
                <div class="ts-value">{{ $stats['total'] }}</div>
                <div class="ts-label">Templates</div>
            </div>
        </div>
        <div class="template-stat-card">
            <div class="ts-icon ts-icon-green">
                <i class="fas fa-check-circle"></i>
            </div>
            <div>
                <div class="ts-value">{{ $stats['active'] }}</div>
                <div class="ts-label">Actifs</div>
            </div>
        </div>
        <div class="template-stat-card">
            <div class="ts-icon ts-icon-purple">
                <i class="fas fa-filter"></i>
            </div>
            <div>
                <div class="ts-value" id="templatesFilteredTotal">{{ $templates->total() }}</div>
                <div class="ts-label">Total filtre</div>
            </div>
        </div>
    </div>

    <form class="templates-filter-bar" method="GET" action="{{ route('cms.admin.themes.index') }}">
        <div class="template-filter-search">
            <i class="fas fa-search"></i>
            <input
                type="search"
                name="search"
                value="{{ $filters['search'] ?? '' }}"
                placeholder="Rechercher par nom, description, URL..."
                class="form-control">
        </div>
        <button type="submit" class="btn-header-primary">
            <i class="fas fa-filter"></i> Filtrer
        </button>
        <a href="{{ route('cms.admin.themes.index') }}" class="btn-header-secondary template-reset-filter {{ (($filters['search'] ?? '') !== '' || ($filters['category'] ?? '') !== '') ? '' : 'd-none' }}">
            <i class="fas fa-times"></i> Reinitialiser
        </a>
    </form>

    @include('cms::admin.themes.partials.category-badges', [
        'templateCategories' => $templateCategories,
        'filters' => $filters,
    ])

    @include('cms::admin.themes.partials.results', [
        'templates' => $templates,
        'filters' => $filters,
    ])
</div>
</main>

<div class="modal fade" id="createTemplateModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-template-builder">
        <div class="modal-content rounded-20">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-600">
                    <i class="fas fa-file-code me-2 text-primary"></i>
                    <span id="templateFormTitle">Creer un template CMS</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createTemplateForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="templateId" name="id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-500">Nom du template</label>
                            <input type="text" class="form-control" name="name" id="templateName" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-500">Version</label>
                            <input type="text" class="form-control" name="version" value="1.0.0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-500">Statut</label>
                            <select class="form-select" name="status">
                                <option value="active">Actif</option>
                                <option value="draft">Brouillon</option>
                                <option value="archived">Archive</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-500">URL source du site</label>
                            <input type="url" class="form-control" name="site_url" placeholder="https://exemple.com">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-500">Categorie</label>
                            <select class="form-select" name="category" id="templateCategory">
                                <option value="">Selectionner une categorie</option>
                                @foreach($templateCategories as $category)
                                    <option value="{{ $category }}">{{ $category }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted d-block mt-1">
                                Categories possibles: {{ implode(', ', $templateCategories) }}
                            </small>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-500">Image preview</label>
                            <input type="file" class="form-control" name="image_preview" accept="image/*">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-500">Description</label>
                            <textarea class="form-control" name="description" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <div class="template-content-workspace">
                                <div class="row g-3">
                                    <div class="col-lg-6">
                                        <div class="template-editor-panel">
                                            <div class="code-editor-toolbar">
                                                <span><i class="fas fa-code me-1"></i>Code HTML/CSS/JS</span>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertTemplateStarter()">
                                                    Starter
                                                </button>
                                            </div>
                                            <textarea class="form-control template-code-editor" name="page_content" id="templatePageContent" required spellcheck="false"></textarea>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="template-editor-panel">
                                            <div class="code-editor-toolbar template-preview-toolbar">
                                                <span><i class="fas fa-eye me-1"></i>Preview design</span>
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="refreshTemplateLivePreview()">
                                                    Actualiser
                                                </button>
                                            </div>
                                            <iframe id="templateLivePreviewFrame" class="template-live-preview-frame" title="Preview template"></iframe>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-danger mt-3 d-none" id="templateFormErrors"></div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary" id="createTemplateBtn">
                        <i class="fas fa-save me-2"></i>Sauvegarder
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="templatePreviewModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content rounded-20">
            <div class="modal-header border-0">
                <h5 class="modal-title"><i class="fas fa-eye me-2"></i>Preview template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <iframe id="templatePreviewFrame" class="template-preview-frame"></iframe>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteTemplateModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-20">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-600">
                    <i class="fas fa-trash-alt me-2 text-danger"></i>
                    Supprimer le template
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-1">Confirmer la suppression de ce template CMS ?</p>
                <p class="text-muted small mb-0" id="deleteTemplateName"></p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteTemplateBtn" onclick="confirmDeleteTemplate()">
                    <i class="fas fa-trash-alt me-2"></i>Supprimer
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.global-templates-page { padding: 24px; max-width: 1400px; margin: 0 auto; }
.modal-template-builder { max-width: min(1500px, calc(100vw - 32px)); }
.page-header-modern { display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; gap: 16px; flex-wrap: wrap; }
.page-header-left { display: flex; align-items: center; gap: 16px; }
.page-icon { width: 52px; height: 52px; background: linear-gradient(135deg, #2563eb, #14b8a6); border-radius: 14px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.4rem; }
.page-title { font-size: 1.5rem; font-weight: 700; color: #1e293b; margin: 0 0 4px; }
.page-subtitle { font-size: .88rem; color: #64748b; margin: 0; }
.page-header-actions { display: flex; gap: 10px; flex-wrap: wrap; }
.btn-header-primary, .btn-header-secondary { border-radius: 10px; padding: 10px 18px; font-size: .88rem; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; border: 1px solid transparent; }
.btn-header-primary { background: #2563eb; color: #fff; }
.btn-header-secondary { background: #fff; color: #475569; border-color: #e2e8f0; }
.templates-stats-row { display: flex; gap: 16px; margin-bottom: 28px; }
.template-stat-card { align-items: center; background: #fff; border-radius: 12px; box-shadow: 0 1px 4px rgba(15,23,42,.08); display: flex; flex: 1; gap: 14px; justify-content: center; padding: 16px 24px; text-align: left; }
.ts-icon { align-items: center; border-radius: 14px; display: inline-flex; flex: 0 0 46px; height: 46px; justify-content: center; width: 46px; }
.ts-icon-blue { background: #dbeafe; color: #2563eb; }
.ts-icon-green { background: #dcfce7; color: #16a34a; }
.ts-icon-purple { background: #ede9fe; color: #7c3aed; }
.ts-value { font-size: 2rem; font-weight: 700; color: #0f172a; }
.ts-label { color: #64748b; font-size: .82rem; }
.templates-filter-bar { align-items: center; background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; box-shadow: 0 1px 4px rgba(15,23,42,.06); display: flex; gap: 12px; margin-bottom: 22px; padding: 14px; }
.template-filter-search { flex: 1; min-width: 260px; position: relative; }
.template-filter-search i { color: #94a3b8; left: 13px; pointer-events: none; position: absolute; top: 50%; transform: translateY(-50%); }
.template-filter-search .form-control { border-color: #dbe4f0; border-radius: 10px; min-height: 42px; padding-left: 38px; }
.template-category-badges { display: flex; flex-wrap: wrap; gap: 8px; margin: -8px 0 22px; }
.template-category-badge { background: #fff; border: 1px solid #dbe4f0; border-radius: 999px; color: #475569; font-size: .82rem; font-weight: 700; padding: 8px 13px; text-decoration: none; transition: all .18s ease; }
.template-category-badge:hover { background: #eff6ff; border-color: #93c5fd; color: #1d4ed8; text-decoration: none; }
.template-category-badge.active { background: #2563eb; border-color: #2563eb; color: #fff; box-shadow: 0 8px 18px rgba(37,99,235,.18); }
#globalThemesResults.is-loading { opacity: .55; pointer-events: none; }
.global-templates-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 18px; }
.global-template-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(15,23,42,.06); }
.gtc-preview { height: 168px; background: #f1f5f9; position: relative; display: flex; align-items: center; justify-content: center; overflow: hidden; }
.gtc-preview img { width: 100%; height: 100%; object-fit: cover; }
.gtc-placeholder { font-size: 2.6rem; color: #94a3b8; }
.gtc-status { position: absolute; top: 10px; right: 10px; font-size: .68rem; font-weight: 700; padding: 4px 9px; border-radius: 999px; background: #dbeafe; color: #1d4ed8; }
.gtc-status.draft { background: #fef3c7; color: #92400e; }
.gtc-status.archived { background: #fee2e2; color: #991b1b; }
.gtc-body { padding: 15px; }
.gtc-header { display: flex; justify-content: space-between; gap: 10px; align-items: start; margin-bottom: 8px; }
.gtc-name { font-size: 1rem; font-weight: 700; color: #1e293b; margin: 0; }
.gtc-version { background: #f1f5f9; color: #64748b; border-radius: 999px; padding: 2px 8px; font-size: .72rem; }
.gtc-desc { color: #64748b; font-size: .82rem; line-height: 1.45; min-height: 36px; }
.gtc-meta { color: #64748b; font-size: .76rem; display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 10px; }
.gtc-site-link { display: inline-flex; gap: 6px; align-items: center; color: #2563eb; font-size: .8rem; margin-bottom: 12px; text-decoration: none; }
.gtc-actions { display: flex; gap: 8px; }
.gtc-btn { border: none; border-radius: 8px; padding: 8px 10px; display: inline-flex; align-items: center; justify-content: center; gap: 6px; font-size: .78rem; font-weight: 600; cursor: pointer; }
.gtc-btn-preview { flex: 1; background: #e0f2fe; color: #0369a1; }
.gtc-btn-edit { background: #dcfce7; color: #15803d; }
.gtc-btn-duplicate { background: #f1f5f9; color: #475569; }
.gtc-btn-delete { background: #fee2e2; color: #991b1b; }
.pagination-modern { align-items: center; display: flex; justify-content: center; margin-top: 28px; width: 100%; }
.pagination-modern nav { width: 100%; }
.pagination-modern .pagination { align-items: center; display: flex; flex-wrap: wrap; gap: 8px; justify-content: center; margin: 0; padding: 0; }
.pagination-modern .page-item { list-style: none; }
.pagination-modern .page-link {
    align-items: center;
    background: #fff;
    border: 1px solid #dbe4f0;
    border-radius: 10px;
    color: #475569;
    display: inline-flex;
    font-size: .86rem;
    font-weight: 700;
    justify-content: center;
    line-height: 1;
    min-height: 40px;
    min-width: 40px;
    padding: 10px 13px;
    text-decoration: none;
    transition: all .18s ease;
}
.pagination-modern .page-link:hover {
    background: #eff6ff;
    border-color: #93c5fd;
    color: #1d4ed8;
    transform: translateY(-1px);
}
.pagination-modern .page-item.active .page-link {
    background: #2563eb;
    border-color: #2563eb;
    box-shadow: 0 8px 18px rgba(37,99,235,.18);
    color: #fff;
}
.pagination-modern .page-item.disabled .page-link {
    background: #f8fafc;
    border-color: #e2e8f0;
    color: #94a3b8;
    cursor: not-allowed;
    opacity: 1;
    transform: none;
}
.empty-global-state { background: #fff; border: 1px dashed #cbd5e1; border-radius: 14px; text-align: center; padding: 56px 20px; color: #64748b; }
.eg-icon { font-size: 3rem; color: #94a3b8; margin-bottom: 16px; }
.template-content-workspace { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 12px; }
.template-editor-panel { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 1px 4px rgba(15,23,42,.05); height: 100%; overflow: hidden; }
.template-code-editor { border: 0; border-radius: 0 0 12px 12px; min-height: 460px; font-family: Consolas, Monaco, "Courier New", monospace; font-size: 13px; line-height: 1.5; color: #e2e8f0; background: #0f172a; border-color: #334155; resize: vertical; }
.code-editor-toolbar { display: flex; justify-content: space-between; align-items: center; background: #f8fafc; border: 1px solid #e2e8f0; border-bottom: 0; border-radius: 8px 8px 0 0; padding: 9px 12px; color: #475569; font-size: .84rem; font-weight: 600; }
.template-editor-panel .code-editor-toolbar { border: 0; border-bottom: 1px solid #e2e8f0; border-radius: 12px 12px 0 0; min-height: 44px; }
.template-preview-toolbar { background: #fff; }
.template-live-preview-frame { width: 100%; min-height: 460px; border: 0; background: #fff; display: block; }
.template-preview-frame { width: 100%; height: 76vh; border: 0; background: #fff; }
@media (max-width: 991px) { .template-code-editor, .template-live-preview-frame { min-height: 360px; } }
@media (max-width: 768px) { .templates-stats-row, .templates-filter-bar { flex-direction: column; align-items: stretch; } .template-filter-search { flex: 1 1 auto; min-width: 0; width: 100%; } .pagination-modern .pagination { gap: 6px; } .pagination-modern .page-link { min-height: 36px; min-width: 36px; padding: 9px 11px; } }
</style>

@php
    $globalTemplateContents = $templates->getCollection()
        ->mapWithKeys(fn ($template) => [$template->id => $template->page_content])
        ->all();

    $globalTemplatesPayload = $templates->getCollection()
        ->mapWithKeys(fn ($template) => [
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
@endphp

<script>
let globalTemplateContents = @json($globalTemplateContents);
let globalTemplates = @json($globalTemplatesPayload);
let pendingDeleteTemplate = null;
let templateLivePreviewTimer = null;

document.addEventListener('DOMContentLoaded', () => {
    bindTemplateCategoryFilters();
    bindTemplatePaginationLinks();
    bindTemplateLivePreview();

    window.addEventListener('popstate', () => {
        loadThemeTemplates(window.location.href, false);
    });
});

function bindTemplateLivePreview() {
    const editor = document.getElementById('templatePageContent');
    if (!editor) return;

    editor.addEventListener('input', () => {
        clearTimeout(templateLivePreviewTimer);
        templateLivePreviewTimer = setTimeout(refreshTemplateLivePreview, 250);
    });
}

function buildTemplatePreviewDocument(content) {
    return `<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body{margin:0;background:#f8fafc;color:#0f172a}
        .preview-shell{max-width:1240px;margin:0 auto;background:#fff;min-height:100vh}
    </style>
</head>
<body>
    <main class="preview-shell">${content || ''}</main>
</body>
</html>`;
}

function writeTemplateFrame(frame, content) {
    if (!frame) return;

    const doc = frame.contentDocument || frame.contentWindow?.document;
    if (!doc) return;

    doc.open();
    doc.write(buildTemplatePreviewDocument(content));
    doc.close();
}

function refreshTemplateLivePreview() {
    const editor = document.getElementById('templatePageContent');
    const frame = document.getElementById('templateLivePreviewFrame');

    writeTemplateFrame(frame, editor?.value || '');
}

function bindTemplateCategoryFilters() {
    document.querySelectorAll('#templateCategoryBadges .template-category-badge').forEach(link => {
        link.addEventListener('click', event => {
            event.preventDefault();
            loadThemeTemplates(link.href);
        });
    });
}

function bindTemplatePaginationLinks() {
    document.querySelectorAll('#globalThemesResults .pagination a').forEach(link => {
        link.addEventListener('click', event => {
            event.preventDefault();
            loadThemeTemplates(link.href);
        });
    });
}

async function loadThemeTemplates(url, pushState = true) {
    const results = document.getElementById('globalThemesResults');
    if (results) {
        results.classList.add('is-loading');
    }

    try {
        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Filtrage impossible.');
        }

        const nextUrl = new URL(url, window.location.origin);
        const currentResults = document.getElementById('globalThemesResults');
        const currentBadges = document.getElementById('templateCategoryBadges');
        const total = document.getElementById('templatesFilteredTotal');
        const searchInput = document.querySelector('.templates-filter-bar input[name="search"]');
        const resetLink = document.querySelector('.template-reset-filter');

        if (currentResults) {
            currentResults.outerHTML = data.html;
        }

        if (currentBadges) {
            currentBadges.outerHTML = data.badges_html;
        }

        if (total) {
            total.textContent = data.filtered_total ?? 0;
        }

        if (searchInput) {
            searchInput.value = nextUrl.searchParams.get('search') || '';
        }

        if (resetLink) {
            const hasFilters = Boolean(nextUrl.searchParams.get('search') || nextUrl.searchParams.get('category'));
            resetLink.classList.toggle('d-none', !hasFilters);
        }

        globalTemplateContents = data.contents || {};
        globalTemplates = data.templates || {};

        if (pushState) {
            window.history.pushState({}, '', url);
        }

        bindTemplateCategoryFilters();
        bindTemplatePaginationLinks();
    } catch (error) {
        console.error(error);
        if (results) {
            results.classList.remove('is-loading');
        }
    }
}

document.getElementById('createTemplateForm')?.addEventListener('submit', async function (event) {
    event.preventDefault();

    const button = document.getElementById('createTemplateBtn');
    const errors = document.getElementById('templateFormErrors');
    const formData = new FormData(this);
    const templateId = document.getElementById('templateId').value;

    if (templateId) {
        formData.append('_method', 'PUT');
    }

    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sauvegarde...';
    errors.classList.add('d-none');
    errors.innerHTML = '';

    try {
        const response = await fetch(templateId ? `/admin/cms/themes/${templateId}` : '{{ route('cms.admin.themes.store') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: formData,
        });
        const data = await response.json();

        if (!response.ok || !data.success) {
            const message = data.errors ? Object.values(data.errors).flat().join('<br>') : (data.message || 'Erreur');
            errors.innerHTML = message;
            errors.classList.remove('d-none');
            return;
        }

        location.reload();
    } catch (error) {
        errors.textContent = 'Erreur reseau pendant la sauvegarde.';
        errors.classList.remove('d-none');
    } finally {
        button.disabled = false;
        button.innerHTML = '<i class="fas fa-save me-2"></i>Sauvegarder';
    }
});

function openTemplateCreate() {
    const form = document.getElementById('createTemplateForm');
    form.reset();
    document.getElementById('templateId').value = '';
    document.getElementById('templateFormTitle').textContent = 'Creer un template CMS';
    document.getElementById('createTemplateBtn').innerHTML = '<i class="fas fa-save me-2"></i>Sauvegarder';
    document.getElementById('templateFormErrors').classList.add('d-none');
    document.getElementById('templatePageContent').value = '';
    form.querySelector('[name="version"]').value = '1.0.0';
    form.querySelector('[name="status"]').value = 'active';
    new bootstrap.Modal(document.getElementById('createTemplateModal')).show();
    setTimeout(refreshTemplateLivePreview, 150);
}

function openTemplateEdit(templateId) {
    const template = globalTemplates[templateId];
    if (!template) return;

    const form = document.getElementById('createTemplateForm');
    form.reset();
    document.getElementById('templateId').value = template.id;
    document.getElementById('templateFormTitle').textContent = 'Modifier le template CMS';
    document.getElementById('createTemplateBtn').innerHTML = '<i class="fas fa-save me-2"></i>Mettre a jour';
    document.getElementById('templateFormErrors').classList.add('d-none');
    form.querySelector('[name="name"]').value = template.name || '';
    form.querySelector('[name="version"]').value = template.version || '1.0.0';
    form.querySelector('[name="status"]').value = template.status || 'active';
    form.querySelector('[name="site_url"]').value = template.site_url || '';
    form.querySelector('[name="category"]').value = template.category || '';
    form.querySelector('[name="description"]').value = template.description || '';
    form.querySelector('[name="page_content"]').value = template.page_content || '';
    new bootstrap.Modal(document.getElementById('createTemplateModal')).show();
    setTimeout(refreshTemplateLivePreview, 150);
}

function insertTemplateStarter() {
    const editor = document.getElementById('templatePageContent');
    if (!editor || editor.value.trim()) return;

    editor.value = `<section style="padding:80px 20px;background:#f8fafc;">
  <div style="max-width:1180px;margin:0 auto;">
    <h1 style="font-size:48px;margin:0 0 16px;color:#0f172a;">Titre du template</h1>
    <p style="font-size:18px;color:#475569;max-width:720px;">Contenu de depart du template CMS.</p>
  </div>
</section>`;
    editor.focus();
    refreshTemplateLivePreview();
}

function previewGlobalTemplate(templateId) {
    const frame = document.getElementById('templatePreviewFrame');
    const modal = new bootstrap.Modal(document.getElementById('templatePreviewModal'));
    const content = globalTemplateContents[templateId] || '';

    writeTemplateFrame(frame, content);
    modal.show();
}

function duplicateGlobalTheme(templateId, btn) {
    btn.disabled = true;
    fetch(`/admin/cms/themes/${templateId}/duplicate`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
    }).then(() => location.reload()).catch(() => btn.disabled = false);
}

function deleteGlobalTheme(templateId, btn) {
    const template = globalTemplates[templateId] || {};
    pendingDeleteTemplate = { id: templateId, button: btn };
    document.getElementById('deleteTemplateName').textContent = template.name ? `Template: ${template.name}` : '';
    new bootstrap.Modal(document.getElementById('deleteTemplateModal')).show();
}

function confirmDeleteTemplate() {
    if (!pendingDeleteTemplate) return;

    const templateId = pendingDeleteTemplate.id;
    const btn = pendingDeleteTemplate.button;
    const confirmBtn = document.getElementById('confirmDeleteTemplateBtn');
    const modalEl = document.getElementById('deleteTemplateModal');

    btn.disabled = true;
    confirmBtn.disabled = true;
    confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Suppression...';

    fetch(`/admin/cms/themes/${templateId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
    }).then(response => response.json()).then(data => {
        if (data.success) {
            document.getElementById(`gtc-${templateId}`)?.remove();
            bootstrap.Modal.getInstance(modalEl)?.hide();
            pendingDeleteTemplate = null;
        } else {
            document.getElementById('deleteTemplateName').textContent = data.message || 'Erreur pendant la suppression.';
            btn.disabled = false;
        }
    }).catch(() => {
        btn.disabled = false;
    }).finally(() => {
        confirmBtn.disabled = false;
        confirmBtn.innerHTML = '<i class="fas fa-trash-alt me-2"></i>Supprimer';
    });
}
</script>
@endsection
