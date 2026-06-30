@extends('layouts.app')

@section('title', 'Blocs CMS')

@section('content')
<main class="dashboard-content">
    <div class="cms-blocks-page">
        <div class="blocks-page-header">
            <div>
                <h1 class="blocks-page-title"><i class="fas fa-cubes me-2"></i>Blocs CMS</h1>
                <p class="blocks-page-subtitle">Gerez les blocs reutilisables disponibles dans l'editeur de pages.</p>
            </div>
            <button type="button" class="btn btn-primary" onclick="openGlobalBlockForm()">
                <i class="fas fa-plus me-1"></i>Nouveau bloc
            </button>
        </div>

        <div class="blocks-toolbar">
            <input type="search" class="form-control" id="globalBlocksSearch" placeholder="Rechercher un bloc..." oninput="filterGlobalBlocks()">
        </div>

        <div class="global-blocks-grid" id="globalBlocksGrid">
            <div class="blocks-loading">Chargement des blocs...</div>
        </div>

        <div class="blocks-pagination" id="globalBlocksPagination"></div>
    </div>
</main>

<div class="modal fade" id="globalBlockFormModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content rounded-20">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-600">
                    <i class="fas fa-cube me-2 text-primary"></i>
                    <span id="globalBlockFormTitle">Nouveau bloc</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="globalBlockForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="globalBlockId" name="id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nom</label>
                            <input type="text" class="form-control" name="name" id="globalBlockName" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Icone FontAwesome</label>
                            <input type="text" class="form-control" name="icon" id="globalBlockIcon" placeholder="fa-cube">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Ordre</label>
                            <input type="number" class="form-control" name="order" id="globalBlockOrder" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Section</label>
                            <select class="form-select" name="section_id" id="globalBlockSection">
                                <option value="">Aucune section</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Categorie</label>
                            <input type="text" class="form-control" name="category" id="globalBlockCategory" value="Basic">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Type de site</label>
                            <input type="text" class="form-control" name="website_type" id="globalBlockWebsiteType" value="General">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Largeur</label>
                            <input type="number" class="form-control" name="width" id="globalBlockWidth">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Hauteur</label>
                            <input type="number" class="form-control" name="height" id="globalBlockHeight">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Image preview</label>
                            <input type="file" class="form-control" name="thumbnail" id="globalBlockThumbnail" accept="image/*">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="globalBlockDescription" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Tags</label>
                            <input type="text" class="form-control" name="tags" id="globalBlockTags" placeholder="hero, contact, pricing">
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_responsive" id="globalBlockResponsive" value="1" checked>
                                <label class="form-check-label" for="globalBlockResponsive">Responsive</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_free" id="globalBlockFree" value="1" checked>
                                <label class="form-check-label" for="globalBlockFree">Gratuit</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="globalBlockActive" value="1" checked>
                                <label class="form-check-label" for="globalBlockActive">Actif</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">HTML</label>
                            <textarea class="form-control block-code-area" name="html_content" id="globalBlockHtml" required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">CSS</label>
                            <textarea class="form-control block-code-area small" name="css_content" id="globalBlockCss"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">JS</label>
                            <textarea class="form-control block-code-area small" name="js_content" id="globalBlockJs"></textarea>
                        </div>
                    </div>
                    <div class="alert alert-danger mt-3 d-none" id="globalBlockFormErrors"></div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary" id="globalBlockFormSubmit">
                        <i class="fas fa-save me-1"></i>Sauvegarder
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.cms-blocks-page { padding: 24px; max-width: 1400px; margin: 0 auto; }
.blocks-page-header { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 20px; flex-wrap: wrap; }
.blocks-page-title { margin: 0 0 4px; font-size: 1.55rem; font-weight: 800; color: #0f172a; }
.blocks-page-subtitle { margin: 0; color: #64748b; font-size: .9rem; }
.blocks-toolbar { max-width: 420px; margin-bottom: 18px; }
.global-blocks-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px; }
.blocks-loading { grid-column: 1 / -1; padding: 32px; text-align: center; color: #64748b; background: #fff; border: 1px dashed #cbd5e1; border-radius: 12px; }
.blocks-pagination { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; margin-top: 18px; }
.blocks-pagination-info { color: #64748b; font-size: .88rem; font-weight: 600; }
.blocks-pagination-actions { display: flex; gap: 6px; flex-wrap: wrap; }
.blocks-page-btn { min-width: 38px; height: 38px; border: 1px solid #dbe3ee; border-radius: 8px; background: #fff; color: #334155; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; }
.blocks-page-btn:hover:not(:disabled), .blocks-page-btn.active { border-color: #2563eb; background: #2563eb; color: #fff; }
.blocks-page-btn:disabled, .blocks-page-btn.disabled { opacity: .45; cursor: not-allowed; }
.block-admin-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(15,23,42,.06); }
.block-admin-preview { height: 132px; background: #f8fafc; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden; }
.block-admin-preview img { width: 100%; height: 100%; object-fit: cover; }
.block-preview-design { position: absolute; top: 0; left: 50%; width: 1000px; height: 520px; border: 0; background: #fff; transform: translateX(-50%) scale(.28); transform-origin: top center; pointer-events: none; }
.block-preview-design-overlay { position: absolute; inset: 0; background: linear-gradient(180deg, rgba(15,23,42,0) 62%, rgba(15,23,42,.12)); pointer-events: none; }
.block-admin-placeholder { color: #94a3b8; font-size: 2.3rem; }
.block-admin-status { position: absolute; top: 9px; right: 9px; padding: 3px 8px; border-radius: 999px; font-size: .66rem; font-weight: 800; background: #dcfce7; color: #15803d; z-index: 2; }
.block-admin-status.inactive { background: #fee2e2; color: #991b1b; }
.block-admin-body { padding: 14px; }
.block-admin-title { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 7px; }
.block-admin-title h5 { margin: 0; font-size: .98rem; font-weight: 800; color: #1e293b; }
.block-admin-desc { min-height: 38px; color: #64748b; font-size: .8rem; line-height: 1.45; margin-bottom: 10px; }
.block-admin-meta { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 12px; }
.block-admin-pill { background: #f1f5f9; color: #475569; font-size: .68rem; font-weight: 700; padding: 3px 8px; border-radius: 999px; }
.block-admin-actions { display: flex; gap: 7px; }
.block-admin-btn { border: 0; border-radius: 8px; height: 34px; min-width: 36px; padding: 0 10px; display: inline-flex; align-items: center; justify-content: center; gap: 6px; font-size: .78rem; font-weight: 700; text-decoration: none; }
.block-admin-btn.edit { background: #e0f2fe; color: #0369a1; }
.block-admin-btn.preview { background: #f1f5f9; color: #475569; }
.block-admin-btn.delete { background: #fee2e2; color: #991b1b; margin-left: auto; }
.block-code-area { min-height: 220px; font-family: Consolas, Monaco, "Courier New", monospace; font-size: 13px; line-height: 1.45; background: #0f172a; color: #e2e8f0; border-color: #334155; }
.block-code-area.small { min-height: 150px; }
</style>

@php
    $globalBlocksRoutes = [
        'manage' => route('cms.admin.global-blocks.manage'),
        'store' => route('cms.admin.global-blocks.store'),
        'update' => route('cms.admin.global-blocks.update', ['id' => '__ID__']),
        'destroy' => route('cms.admin.global-blocks.destroy', ['id' => '__ID__']),
    ];
@endphp

<script>
const globalBlocksRoutes = @json($globalBlocksRoutes);

let globalBlocksItems = [];
let globalBlocksSections = [];
let globalBlocksPagination = null;
let globalBlocksCurrentPage = 1;
let globalBlocksSearchTimer = null;

document.addEventListener('DOMContentLoaded', () => loadGlobalBlocks());

async function loadGlobalBlocks(page = 1) {
    const grid = document.getElementById('globalBlocksGrid');
    if (grid) grid.innerHTML = '<div class="blocks-loading">Chargement des blocs...</div>';

    try {
        const params = new URLSearchParams({
            page,
            per_page: 12,
            search: document.getElementById('globalBlocksSearch')?.value || '',
        });
        const response = await fetch(`${globalBlocksRoutes.manage}?${params.toString()}`, { headers: { 'Accept': 'application/json' } });
        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message || 'Erreur');
        }

        globalBlocksItems = data.blocks || [];
        globalBlocksSections = data.sections || [];
        globalBlocksPagination = data.pagination || null;
        globalBlocksCurrentPage = globalBlocksPagination?.current_page || page;
        populateGlobalBlockSections();
        renderGlobalBlocks(globalBlocksItems);
        renderGlobalBlocksPagination();
    } catch (error) {
        if (grid) grid.innerHTML = '<div class="blocks-loading">Impossible de charger les blocs.</div>';
    }
}

function populateGlobalBlockSections() {
    const select = document.getElementById('globalBlockSection');
    if (!select) return;

    select.innerHTML = '<option value="">Aucune section</option>';
    globalBlocksSections.forEach(section => {
        const option = document.createElement('option');
        option.value = section.id;
        option.textContent = section.name;
        select.appendChild(option);
    });
}

function renderGlobalBlocks(blocks) {
    const grid = document.getElementById('globalBlocksGrid');
    if (!grid) return;

    if (!blocks.length) {
        grid.innerHTML = '<div class="blocks-loading">Aucun bloc trouve.</div>';
        renderGlobalBlocksPagination();
        return;
    }

    grid.innerHTML = blocks.map(block => `
        <div class="block-admin-card" data-block-id="${block.id}">
            <div class="block-admin-preview">
                ${renderGlobalBlockPreviewSlot(block)}
                <span class="block-admin-status ${block.is_active ? '' : 'inactive'}">${block.is_active ? 'ACTIF' : 'INACTIF'}</span>
            </div>
            <div class="block-admin-body">
                <div class="block-admin-title">
                    <h5>${escapeGlobalBlock(block.name)}</h5>
                    <span class="block-admin-pill">${escapeGlobalBlock(block.category || 'Basic')}</span>
                </div>
                <p class="block-admin-desc">${escapeGlobalBlock(block.description || 'Aucune description.')}</p>
                <div class="block-admin-meta">
                    <span class="block-admin-pill">${escapeGlobalBlock(block.section_name || 'Sans section')}</span>
                    <span class="block-admin-pill">${escapeGlobalBlock(block.website_type || 'General')}</span>
                    ${block.is_responsive ? '<span class="block-admin-pill">Responsive</span>' : ''}
                </div>
                <div class="block-admin-actions">
                    <button type="button" class="block-admin-btn edit" onclick="openGlobalBlockForm(${block.id})"><i class="fas fa-edit"></i></button>
                    <a class="block-admin-btn preview" href="${escapeGlobalBlock(block.preview_url)}" target="_blank" title="Preview"><i class="fas fa-eye"></i></a>
                    <button type="button" class="block-admin-btn delete" onclick="deleteGlobalBlock(${block.id}, this)"><i class="fas fa-trash"></i></button>
                </div>
            </div>
        </div>
    `).join('');

    hydrateGlobalBlockPreviewFrames(blocks);
}

function renderGlobalBlockPreviewSlot(block) {
    if (String(block.html_content || '').trim() !== '') {
        return `
            <iframe class="block-preview-design"
                    data-block-preview-id="${Number(block.id)}"
                    title="${escapeGlobalBlock(block.name)}"
                    loading="lazy"
                    sandbox=""></iframe>
            <span class="block-preview-design-overlay"></span>
        `;
    }

    if (block.thumbnail_url) {
        return `<img src="${escapeGlobalBlock(block.thumbnail_url)}" alt="${escapeGlobalBlock(block.name)}">`;
    }

    return `<div class="block-admin-placeholder"><i class="fas ${escapeGlobalBlock(block.icon || 'fa-cube')}"></i></div>`;
}

function hydrateGlobalBlockPreviewFrames(blocks) {
    const blockMap = new Map(blocks.map(block => [String(block.id), block]));

    document.querySelectorAll('.block-preview-design[data-block-preview-id]').forEach(frame => {
        const block = blockMap.get(frame.dataset.blockPreviewId);
        if (!block) return;
        frame.srcdoc = buildGlobalBlockPreviewSrcdoc(block);
    });
}

function buildGlobalBlockPreviewSrcdoc(block) {
    return `<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        * { box-sizing: border-box; }
        html, body { margin: 0; min-height: 100%; background: #fff; overflow: hidden; }
        body { font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        img, video { max-width: 100%; height: auto; }
        .block-preview-stage { width: 1000px; min-height: 520px; background: #fff; overflow: hidden; }
        ${block.css_content || ''}
    </style>
</head>
<body>
    <div class="block-preview-stage">${block.html_content || ''}</div>
</body>
</html>`;
}

function filterGlobalBlocks() {
    clearTimeout(globalBlocksSearchTimer);
    globalBlocksSearchTimer = setTimeout(() => loadGlobalBlocks(1), 250);
}

function renderGlobalBlocksPagination() {
    const host = document.getElementById('globalBlocksPagination');
    if (!host) return;

    const pagination = globalBlocksPagination;
    if (!pagination || pagination.last_page <= 1) {
        host.innerHTML = pagination?.total
            ? `<div class="blocks-pagination-info">${pagination.total} bloc${pagination.total > 1 ? 's' : ''}</div>`
            : '';
        return;
    }

    const pages = paginationWindow(pagination.current_page, pagination.last_page);
    host.innerHTML = `
        <div class="blocks-pagination-info">
            ${pagination.from || 0}-${pagination.to || 0} sur ${pagination.total} blocs
        </div>
        <div class="blocks-pagination-actions">
            <button type="button" class="blocks-page-btn" ${pagination.current_page <= 1 ? 'disabled' : ''} onclick="loadGlobalBlocks(${pagination.current_page - 1})">
                <i class="fas fa-chevron-left"></i>
            </button>
            ${pages.map(page => page === '...'
                ? '<span class="blocks-page-btn disabled">...</span>'
                : `<button type="button" class="blocks-page-btn ${page === pagination.current_page ? 'active' : ''}" onclick="loadGlobalBlocks(${page})">${page}</button>`
            ).join('')}
            <button type="button" class="blocks-page-btn" ${pagination.current_page >= pagination.last_page ? 'disabled' : ''} onclick="loadGlobalBlocks(${pagination.current_page + 1})">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    `;
}

function paginationWindow(current, last) {
    const pages = new Set([1, last, current - 1, current, current + 1]);
    const sorted = [...pages].filter(page => page >= 1 && page <= last).sort((a, b) => a - b);
    const result = [];

    sorted.forEach(page => {
        if (result.length && page - result[result.length - 1] > 1) {
            result.push('...');
        }
        result.push(page);
    });

    return result;
}

function openGlobalBlockForm(blockId = null) {
    const form = document.getElementById('globalBlockForm');
    const title = document.getElementById('globalBlockFormTitle');
    const errors = document.getElementById('globalBlockFormErrors');
    form.reset();
    errors.classList.add('d-none');
    errors.innerHTML = '';
    document.getElementById('globalBlockId').value = '';
    document.getElementById('globalBlockResponsive').checked = true;
    document.getElementById('globalBlockFree').checked = true;
    document.getElementById('globalBlockActive').checked = true;

    if (blockId) {
        const block = globalBlocksItems.find(item => Number(item.id) === Number(blockId));
        if (!block) return;
        title.textContent = 'Modifier le bloc';
        document.getElementById('globalBlockId').value = block.id;
        document.getElementById('globalBlockName').value = block.name || '';
        document.getElementById('globalBlockIcon').value = block.icon || 'fa-cube';
        document.getElementById('globalBlockOrder').value = block.order || 0;
        document.getElementById('globalBlockSection').value = block.section_id || '';
        document.getElementById('globalBlockCategory').value = block.category || 'Basic';
        document.getElementById('globalBlockWebsiteType').value = block.website_type || 'General';
        document.getElementById('globalBlockWidth').value = block.width || '';
        document.getElementById('globalBlockHeight').value = block.height || '';
        document.getElementById('globalBlockDescription').value = block.description || '';
        document.getElementById('globalBlockTags').value = Array.isArray(block.tags) ? block.tags.join(', ') : '';
        document.getElementById('globalBlockResponsive').checked = !!block.is_responsive;
        document.getElementById('globalBlockFree').checked = !!block.is_free;
        document.getElementById('globalBlockActive').checked = !!block.is_active;
        document.getElementById('globalBlockHtml').value = block.html_content || '';
        document.getElementById('globalBlockCss').value = block.css_content || '';
        document.getElementById('globalBlockJs').value = block.js_content || '';
    } else {
        title.textContent = 'Nouveau bloc';
        document.getElementById('globalBlockIcon').value = 'fa-cube';
        document.getElementById('globalBlockCategory').value = 'Basic';
        document.getElementById('globalBlockWebsiteType').value = 'General';
        document.getElementById('globalBlockOrder').value = '0';
    }

    new bootstrap.Modal(document.getElementById('globalBlockFormModal')).show();
}

document.getElementById('globalBlockForm')?.addEventListener('submit', async function (event) {
    event.preventDefault();

    const blockId = document.getElementById('globalBlockId').value;
    const errors = document.getElementById('globalBlockFormErrors');
    const button = document.getElementById('globalBlockFormSubmit');
    const formData = new FormData(this);

    if (!document.getElementById('globalBlockResponsive').checked) formData.set('is_responsive', '0');
    if (!document.getElementById('globalBlockFree').checked) formData.set('is_free', '0');
    if (!document.getElementById('globalBlockActive').checked) formData.set('is_active', '0');
    if (blockId) formData.append('_method', 'PUT');

    errors.classList.add('d-none');
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Sauvegarde...';

    try {
        const response = await fetch(blockId ? globalBlocksRoutes.update.replace('__ID__', blockId) : globalBlocksRoutes.store, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: formData,
        });
        const data = await response.json();

        if (!response.ok || !data.success) {
            errors.innerHTML = data.errors ? Object.values(data.errors).flat().join('<br>') : (data.message || 'Erreur');
            errors.classList.remove('d-none');
            return;
        }

        bootstrap.Modal.getInstance(document.getElementById('globalBlockFormModal'))?.hide();
        notifyGlobalBlock(data.message || 'Bloc sauvegarde', 'success');
        loadGlobalBlocks(blockId ? globalBlocksCurrentPage : 1);
    } catch (error) {
        errors.textContent = 'Erreur reseau pendant la sauvegarde.';
        errors.classList.remove('d-none');
    } finally {
        button.disabled = false;
        button.innerHTML = '<i class="fas fa-save me-1"></i>Sauvegarder';
    }
});

function deleteGlobalBlock(blockId, btn) {
    if (!confirm('Supprimer ce bloc ?')) return;

    btn.disabled = true;
    fetch(globalBlocksRoutes.destroy.replace('__ID__', blockId), {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            notifyGlobalBlock('Bloc supprime', 'success');
            globalBlocksItems = globalBlocksItems.filter(block => Number(block.id) !== Number(blockId));
            const nextPage = globalBlocksItems.length || globalBlocksCurrentPage <= 1
                ? globalBlocksCurrentPage
                : globalBlocksCurrentPage - 1;
            loadGlobalBlocks(nextPage);
        } else {
            notifyGlobalBlock(data.message || 'Erreur', 'error');
            btn.disabled = false;
        }
    })
    .catch(() => {
        notifyGlobalBlock('Erreur reseau', 'error');
        btn.disabled = false;
    });
}

function escapeGlobalBlock(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function notifyGlobalBlock(message, type) {
    if (typeof showToast === 'function') {
        showToast(message, type);
        return;
    }

    console[type === 'error' ? 'error' : 'log'](message);
}
</script>
@endsection
