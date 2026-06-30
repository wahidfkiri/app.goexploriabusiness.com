{{-- slideshow-tab.blade.php --}}
@php
    $slideshowEnabled = $stats['etablissement']->getSetting('slideshow_enabled', false);
    $slideshowSectionTitle = $stats['etablissement']->getSetting('slideshow_section_title', 'Vidéos à découvrir');
@endphp
<div
    class="tab-pane fade"
    id="v-pills-slideshow"
    role="tabpanel"
    data-etablissement-id="{{ $stats['etablissement']->id ?? '' }}"
    data-slideshow-enabled="{{ $slideshowEnabled ? '1' : '0' }}">
    <div class="tab-content-header">
        <h3 class="tab-title">
            <i class="fas fa-photo-video me-2" style="color: #0ea5e9;"></i>
            Slideshow
        </h3>
        <button type="button" class="btn btn-primary" onclick="openSlideshowModal()">
            <i class="fas fa-plus-circle me-2"></i>Ajouter une vidéo
        </button>
    </div>

    <div class="slideshow-status-card" id="slideshowStatusCard">
        <div>
            <label class="slideshow-status-title" for="slideshowEnabledSwitch">Activation du slideshow</label>
            <p class="slideshow-status-text mb-0" id="slideshowStatusText">
                {{ $slideshowEnabled ? 'Le slideshow est affiché sur le site public.' : 'Le slideshow est masqué sur le site public.' }}
            </p>
        </div>
        <div class="form-check form-switch slideshow-status-switch">
            <input
                class="form-check-input"
                type="checkbox"
                role="switch"
                id="slideshowEnabledSwitch"
                {{ $slideshowEnabled ? 'checked' : '' }}>
            <label class="form-check-label" for="slideshowEnabledSwitch" id="slideshowEnabledLabel">
                {{ $slideshowEnabled ? 'Actif' : 'Inactif' }}
            </label>
        </div>
    </div>

    <div class="slideshow-section-title-card">
        <div>
            <label class="slideshow-status-title" for="slideshowSectionTitleInput">Titre de section slideshow</label>
            <p class="slideshow-status-text mb-0">Ce titre peut être utilisé dans le front avant le slideshow.</p>
        </div>
        <div class="slideshow-section-title-control">
            <input type="text" class="form-control form-control-sm" id="slideshowSectionTitleInput" value="{{ $slideshowSectionTitle }}" maxlength="191" placeholder="Ex: Nos vidéos">
            <button type="button" class="btn btn-sm btn-primary" id="saveSlideshowSectionTitleBtn">
                <i class="fas fa-save me-1"></i>Enregistrer
            </button>
        </div>
    </div>

    <div class="slideshow-admin-toolbar">
        <div>
            <strong id="slideshowTotal">0</strong>
            <span>vidéos configurées</span>
        </div>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="loadSlideshows()">
            <i class="fas fa-sync-alt me-1"></i>Rafraîchir
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th style="width: 44px;"><i class="fas fa-grip-vertical"></i></th>
                    <th style="width: 120px;">Aperçu</th>
                    <th>Titre</th>
                    <th style="width: 90px;">Source</th>
                    <th style="width: 120px;">Bouton</th>
                    <th style="width: 100px;">Statut</th>
                    <th style="width: 150px;">Actions</th>
                </tr>
            </thead>
            <tbody id="slideshowTableBody">
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 mb-0">Chargement du slideshow...</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="slideshowModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="slideshowModalTitle">
                    <i class="fas fa-plus-circle me-2"></i>Ajouter une vidéo slideshow
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="slideshowForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="slideshowId" name="id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Source vidéo</label>
                            <select class="form-select" name="source" id="slideshowSource" onchange="toggleSlideshowSource()">
                                <option value="url">URL vidéo</option>
                                <option value="local">Upload local</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Statut</label>
                            <select class="form-select" name="is_active" id="slideshowIsActive">
                                <option value="1">Actif</option>
                                <option value="0">Inactif</option>
                            </select>
                        </div>
                        <div class="col-12" id="slideshowUrlGroup">
                            <label class="form-label">URL de la vidéo</label>
                            <input type="url" class="form-control" name="video_url" id="slideshowVideoUrl" placeholder="https://example.com/video.mp4">
                        </div>
                        <div class="col-12" id="slideshowFileGroup" style="display: none;">
                            <label class="form-label">Vidéo locale</label>
                            <input type="file" class="form-control" name="video_file" id="slideshowVideoFile" accept="video/mp4,video/webm,video/ogg,video/quicktime">
                            <small class="text-muted">Formats acceptés: MP4, MOV, WebM, OGG. Taille max: 100 MB.</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Poster URL</label>
                            <input type="url" class="form-control" name="poster_url" id="slideshowPosterUrl" placeholder="https://example.com/poster.jpg">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Titre</label>
                            <input type="text" class="form-control" name="title" id="slideshowTitle" maxlength="255">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Texte du bouton</label>
                            <input type="text" class="form-control" name="button_text" id="slideshowButtonText" maxlength="120" placeholder="Ex: Voir plus">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Sous-titre</label>
                            <textarea class="form-control" name="subtitle" id="slideshowSubtitle" rows="2" maxlength="1000"></textarea>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Lien du bouton</label>
                            <input type="text" class="form-control" name="button_url" id="slideshowButtonUrl" placeholder="/contact ou https://...">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Cible du bouton</label>
                            <select class="form-select" name="button_target" id="slideshowButtonTarget">
                                <option value="_self">Même page</option>
                                <option value="_blank">Nouvel onglet</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <div class="slideshow-options">
                                <label class="form-check">
                                    <input class="form-check-input" type="checkbox" name="autoplay" id="slideshowAutoplay" value="1" checked>
                                    <span class="form-check-label">Autoplay</span>
                                </label>
                                <label class="form-check">
                                    <input class="form-check-input" type="checkbox" name="muted" id="slideshowMuted" value="1" checked>
                                    <span class="form-check-label">Muet</span>
                                </label>
                                <label class="form-check">
                                    <input class="form-check-input" type="checkbox" name="loop" id="slideshowLoop" value="1" checked>
                                    <span class="form-check-label">Loop</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-danger mt-3 d-none" id="slideshowErrors"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary" id="saveSlideshowBtn">
                        <i class="fas fa-save me-1"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteSlideshowModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                <h5>Supprimer cette vidéo ?</h5>
                <p class="text-muted">Cette action supprimera la vidéo du slideshow.</p>
                <input type="hidden" id="deleteSlideshowId">
            </div>
            <div class="modal-footer justify-content-center border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" onclick="confirmDeleteSlideshow()">Supprimer</button>
            </div>
        </div>
    </div>
</div>

<style>
.slideshow-admin-toolbar {
    align-items: center;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    display: flex;
    justify-content: space-between;
    margin-bottom: 16px;
    padding: 14px 16px;
}

.slideshow-status-card,
.slideshow-section-title-card {
    align-items: center;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    display: flex;
    gap: 18px;
    justify-content: space-between;
    margin-bottom: 16px;
    padding: 16px;
}

.slideshow-section-title-card {
    display: grid;
    gap: 16px;
    grid-template-columns: minmax(0, 1fr) minmax(260px, 420px);
}

.slideshow-status-card.is-disabled {
    background: #fff7ed;
    border-color: #fed7aa;
}

.slideshow-status-title {
    color: #1e293b;
    display: block;
    font-size: 14px;
    font-weight: 700;
    margin-bottom: 4px;
}

.slideshow-status-text {
    color: #64748b;
    font-size: 13px;
}

.slideshow-status-switch {
    align-items: center;
    display: flex;
    gap: 10px;
    margin: 0;
    min-width: 128px;
}

.slideshow-status-switch .form-check-input {
    cursor: pointer;
    height: 1.35rem;
    margin: 0;
    width: 2.5rem;
}

.slideshow-status-switch .form-check-label {
    color: #334155;
    cursor: pointer;
    font-size: 13px;
    font-weight: 700;
    margin: 0;
}

.slideshow-section-title-control {
    align-items: center;
    display: flex;
    gap: 10px;
}

@media (max-width: 768px) {
    .slideshow-section-title-card {
        grid-template-columns: 1fr;
    }

    .slideshow-section-title-control {
        align-items: stretch;
        flex-direction: column;
    }
}

.slideshow-preview {
    background: #0f172a;
    border-radius: 10px;
    height: 68px;
    overflow: hidden;
    position: relative;
    width: 110px;
}

.slideshow-preview video {
    height: 100%;
    object-fit: cover;
    width: 100%;
}

.slideshow-preview-placeholder {
    align-items: center;
    color: #cbd5e1;
    display: flex;
    flex-direction: column;
    gap: 4px;
    height: 100%;
    justify-content: center;
}

.slideshow-preview-youtube {
    background-position: center;
    background-size: cover;
    height: 100%;
    position: relative;
    width: 100%;
}

.slideshow-preview-youtube::after {
    background: rgba(15, 23, 42, 0.35);
    content: '';
    inset: 0;
    position: absolute;
}

.slideshow-preview-youtube .youtube-play {
    align-items: center;
    background: #ef4444;
    border-radius: 999px;
    color: #fff;
    display: flex;
    height: 34px;
    justify-content: center;
    left: 50%;
    position: absolute;
    top: 50%;
    transform: translate(-50%, -50%);
    width: 34px;
    z-index: 1;
}

.slideshow-drag-handle {
    align-items: center;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    color: #64748b;
    cursor: grab;
    display: inline-flex;
    height: 34px;
    justify-content: center;
    width: 34px;
}

.slideshow-drag-handle:active {
    cursor: grabbing;
}

.slideshow-row.is-dragging {
    opacity: 0.45;
}

.slideshow-row.drag-over {
    box-shadow: inset 0 2px 0 #0ea5e9;
}

.slideshow-options {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    display: flex;
    flex-wrap: wrap;
    gap: 18px;
    padding: 12px 14px;
}

.slideshow-options .form-check {
    align-items: center;
    display: flex;
    gap: 8px;
    margin: 0;
}

.slideshow-toast {
    background: #fff;
    border-left: 4px solid #10b981;
    border-radius: 10px;
    bottom: 24px;
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.16);
    color: #0f172a;
    max-width: 360px;
    padding: 14px 16px;
    position: fixed;
    right: 24px;
    transform: translateX(420px);
    transition: transform 0.25s ease;
    z-index: 11000;
}

.slideshow-toast.show {
    transform: translateX(0);
}

.slideshow-toast.error {
    border-left-color: #ef4444;
}
</style>

<script>
let slideshowItems = [];

document.addEventListener('DOMContentLoaded', function() {
    const slideshowPane = document.getElementById('v-pills-slideshow');
    if (slideshowPane) {
        updateSlideshowEnabledUi(slideshowPane.dataset.slideshowEnabled === '1');
        document.getElementById('slideshowEnabledSwitch')?.addEventListener('change', saveSlideshowEnabled);
        document.getElementById('saveSlideshowSectionTitleBtn')?.addEventListener('click', saveSlideshowSectionTitle);
        loadSlideshows();
    }

    document.getElementById('slideshowForm')?.addEventListener('submit', saveSlideshow);
});

function getSlideshowEtablissementId() {
    return document.getElementById('v-pills-slideshow')?.dataset?.etablissementId || window.currentEtablissementId;
}

async function loadSlideshows() {
    const tbody = document.getElementById('slideshowTableBody');
    if (!tbody) return;

    tbody.innerHTML = `<tr><td colspan="7" class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2 mb-0">Chargement...</p></td></tr>`;

    try {
        const response = await fetch(`/admin/cms/${getSlideshowEtablissementId()}/api/slideshow`, {
            headers: { 'Accept': 'application/json' }
        });
        const result = await response.json();

        if (!result.success) throw new Error(result.message || 'Erreur de chargement');

        slideshowItems = result.data || [];
        renderSlideshows();
    } catch (error) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger py-5">${escapeSlideshowHtml(error.message)}</td></tr>`;
    }
}

async function saveSlideshowEnabled(event) {
    const checkbox = event.target;
    const isEnabled = checkbox.checked;
    const formData = new FormData();

    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    formData.append('site_slideshow_enabled', isEnabled ? '1' : '0');

    checkbox.disabled = true;

    try {
        const response = await fetch(`/admin/cms/${getSlideshowEtablissementId()}/settings`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        });

        const result = await response.json();
        if (!result.success) throw new Error(result.message || 'Erreur lors de la sauvegarde');

        document.getElementById('v-pills-slideshow').dataset.slideshowEnabled = isEnabled ? '1' : '0';
        updateSlideshowEnabledUi(isEnabled);
        showSlideshowToast(isEnabled ? 'Slideshow activé' : 'Slideshow désactivé', 'success');
    } catch (error) {
        checkbox.checked = !isEnabled;
        updateSlideshowEnabledUi(!isEnabled);
        showSlideshowToast(error.message, 'error');
    } finally {
        checkbox.disabled = false;
    }
}

async function saveSlideshowSectionTitle() {
    const input = document.getElementById('slideshowSectionTitleInput');
    const button = document.getElementById('saveSlideshowSectionTitleBtn');
    const formData = new FormData();

    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    formData.append('site_slideshow_section_title', input?.value?.trim() || '');
    if (button) button.disabled = true;

    try {
        const response = await fetch(`/admin/cms/${getSlideshowEtablissementId()}/settings`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        });

        const result = await response.json();
        if (!result.success) throw new Error(result.message || 'Erreur lors de la sauvegarde');
        showSlideshowToast('Titre de section slideshow sauvegardé', 'success');
    } catch (error) {
        showSlideshowToast(error.message || 'Erreur lors de la sauvegarde', 'error');
    } finally {
        if (button) button.disabled = false;
    }
}

function updateSlideshowEnabledUi(isEnabled) {
    const card = document.getElementById('slideshowStatusCard');
    const label = document.getElementById('slideshowEnabledLabel');
    const text = document.getElementById('slideshowStatusText');
    const checkbox = document.getElementById('slideshowEnabledSwitch');

    if (checkbox) checkbox.checked = isEnabled;
    if (card) card.classList.toggle('is-disabled', !isEnabled);
    if (label) label.textContent = isEnabled ? 'Actif' : 'Inactif';
    if (text) {
        text.textContent = isEnabled
            ? 'Le slideshow est affiché sur le site public.'
            : 'Le slideshow est masqué sur le site public.';
    }
}

function renderSlideshows() {
    const tbody = document.getElementById('slideshowTableBody');
    const total = document.getElementById('slideshowTotal');
    if (!tbody) return;

    if (total) total.textContent = slideshowItems.length;

    if (!slideshowItems.length) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center py-5"><i class="fas fa-photo-video fa-3x text-muted mb-3"></i><p>Aucune vidéo slideshow.</p><button class="btn btn-primary btn-sm" onclick="openSlideshowModal()">Ajouter une vidéo</button></td></tr>`;
        return;
    }

    tbody.innerHTML = slideshowItems.map(item => `
        <tr class="slideshow-row" draggable="true" data-slideshow-id="${item.id}">
            <td>
                <span class="slideshow-drag-handle" title="Déplacer">
                    <i class="fas fa-grip-vertical"></i>
                </span>
            </td>
            <td>${renderSlideshowPreview(item)}</td>
            <td>
                <strong>${escapeSlideshowHtml(item.title || 'Sans titre')}</strong>
                <div class="text-muted small">${escapeSlideshowHtml(item.subtitle || '')}</div>
                <div class="text-muted small text-truncate" style="max-width: 360px;">${escapeSlideshowHtml(item.video_url || '')}</div>
            </td>
            <td>${renderSlideshowSourceBadge(item)}</td>
            <td>${item.button_text ? `<span class="badge bg-secondary">${escapeSlideshowHtml(item.button_text)}</span>` : '<span class="text-muted">-</span>'}</td>
            <td><button type="button" class="btn btn-sm ${item.is_active ? 'btn-success' : 'btn-outline-secondary'}" onclick="toggleSlideshow(${item.id})">${item.is_active ? 'Actif' : 'Inactif'}</button></td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-primary" title="Modifier" onclick="openSlideshowModal(${item.id})"><i class="fas fa-edit"></i></button>
                    <button type="button" class="btn btn-outline-danger" title="Supprimer" onclick="openDeleteSlideshowModal(${item.id})"><i class="fas fa-trash"></i></button>
                </div>
            </td>
        </tr>
    `).join('');

    initSlideshowDragDrop();
}

function renderSlideshowPreview(item) {
    if (!item.video_url) {
        return `<div class="slideshow-preview"><div class="slideshow-preview-placeholder"><i class="fas fa-video"></i></div></div>`;
    }

    const youtubeId = getYoutubeVideoId(item.video_url);
    if (youtubeId) {
        const thumbnail = item.poster_url || `https://img.youtube.com/vi/${youtubeId}/mqdefault.jpg`;
        return `
            <div class="slideshow-preview">
                <div class="slideshow-preview-youtube" style="background-image: url('${escapeSlideshowHtml(thumbnail)}')">
                    <span class="youtube-play"><i class="fab fa-youtube"></i></span>
                </div>
            </div>
        `;
    }

    return `<div class="slideshow-preview"><video src="${escapeSlideshowHtml(item.video_url)}" ${item.poster_url ? `poster="${escapeSlideshowHtml(item.poster_url)}"` : ''} muted></video></div>`;
}

function renderSlideshowSourceBadge(item) {
    if (item.source === 'local') {
        return '<span class="badge bg-primary">Local</span>';
    }

    if (getYoutubeVideoId(item.video_url)) {
        return '<span class="badge bg-danger"><i class="fab fa-youtube me-1"></i>YouTube</span>';
    }

    return '<span class="badge bg-info">URL</span>';
}

function initSlideshowDragDrop() {
    const tbody = document.getElementById('slideshowTableBody');
    if (!tbody) return;

    let draggedRow = null;

    tbody.querySelectorAll('.slideshow-row').forEach(row => {
        row.addEventListener('dragstart', event => {
            draggedRow = row;
            row.classList.add('is-dragging');
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', row.dataset.slideshowId);
        });

        row.addEventListener('dragend', () => {
            row.classList.remove('is-dragging');
            tbody.querySelectorAll('.slideshow-row').forEach(item => item.classList.remove('drag-over'));
            draggedRow = null;
            saveSlideshowOrderFromDom();
        });

        row.addEventListener('dragover', event => {
            event.preventDefault();
            if (!draggedRow || draggedRow === row) return;

            row.classList.add('drag-over');
            const rect = row.getBoundingClientRect();
            const insertAfter = event.clientY > rect.top + rect.height / 2;

            if (insertAfter) {
                row.after(draggedRow);
            } else {
                row.before(draggedRow);
            }
        });

        row.addEventListener('dragleave', () => {
            row.classList.remove('drag-over');
        });

        row.addEventListener('drop', event => {
            event.preventDefault();
            row.classList.remove('drag-over');
        });
    });
}

async function saveSlideshowOrderFromDom() {
    const rows = [...document.querySelectorAll('#slideshowTableBody .slideshow-row')];
    const orders = rows.map((row, index) => ({
        id: Number(row.dataset.slideshowId),
        sort_order: index + 1
    })).filter(item => Number.isFinite(item.id));

    if (!orders.length) return;

    const currentOrder = slideshowItems.map(item => Number(item.id)).join(',');
    const nextOrder = orders.map(item => item.id).join(',');

    if (currentOrder === nextOrder) return;

    slideshowItems = orders
        .map(order => {
            const item = slideshowItems.find(slide => Number(slide.id) === order.id);
            return item ? { ...item, sort_order: order.sort_order } : null;
        })
        .filter(Boolean);

    try {
        const response = await fetch(`/admin/cms/${getSlideshowEtablissementId()}/api/slideshow/reorder`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ orders })
        });

        const result = await response.json();
        if (!result.success) throw new Error(result.message || 'Erreur lors de la réorganisation');

        showSlideshowToast('Ordre des vidéos mis à jour', 'success');
    } catch (error) {
        showSlideshowToast(error.message, 'error');
        await loadSlideshows();
    }
}

function getYoutubeVideoId(url) {
    const value = String(url || '').trim();
    if (!value) return null;

    const match = value.match(/(?:youtube\.com\/(?:watch\?.*v=|embed\/|shorts\/)|youtu\.be\/)([A-Za-z0-9_-]{11})/);
    return match ? match[1] : null;
}

function openSlideshowModal(id = null) {
    const form = document.getElementById('slideshowForm');
    const errors = document.getElementById('slideshowErrors');
    form?.reset();
    if (errors) {
        errors.classList.add('d-none');
        errors.innerHTML = '';
    }

    document.getElementById('slideshowId').value = id || '';
    document.getElementById('slideshowModalTitle').innerHTML = id
        ? '<i class="fas fa-edit me-2"></i>Modifier une vidéo slideshow'
        : '<i class="fas fa-plus-circle me-2"></i>Ajouter une vidéo slideshow';

    if (id) {
        const item = slideshowItems.find(slide => Number(slide.id) === Number(id));
        if (item) {
            document.getElementById('slideshowSource').value = item.source || 'url';
            document.getElementById('slideshowVideoUrl').value = item.source === 'url' ? (item.video_url || '') : '';
            document.getElementById('slideshowPosterUrl').value = item.poster_url || '';
            document.getElementById('slideshowTitle').value = item.title || '';
            document.getElementById('slideshowSubtitle').value = item.subtitle || '';
            document.getElementById('slideshowButtonText').value = item.button_text || '';
            document.getElementById('slideshowButtonUrl').value = item.button_url || '';
            document.getElementById('slideshowButtonTarget').value = item.button_target || '_self';
            document.getElementById('slideshowIsActive').value = item.is_active ? '1' : '0';
            document.getElementById('slideshowAutoplay').checked = item.options?.autoplay !== false;
            document.getElementById('slideshowMuted').checked = item.options?.muted !== false;
            document.getElementById('slideshowLoop').checked = item.options?.loop !== false;
        }
    } else {
        document.getElementById('slideshowSource').value = 'url';
        document.getElementById('slideshowIsActive').value = '1';
        document.getElementById('slideshowButtonTarget').value = '_self';
        document.getElementById('slideshowAutoplay').checked = true;
        document.getElementById('slideshowMuted').checked = true;
        document.getElementById('slideshowLoop').checked = true;
    }

    toggleSlideshowSource();
    bootstrap.Modal.getOrCreateInstance(document.getElementById('slideshowModal')).show();
}

function toggleSlideshowSource() {
    const source = document.getElementById('slideshowSource')?.value || 'url';
    document.getElementById('slideshowUrlGroup').style.display = source === 'url' ? 'block' : 'none';
    document.getElementById('slideshowFileGroup').style.display = source === 'local' ? 'block' : 'none';
}

async function saveSlideshow(event) {
    event.preventDefault();
    const form = document.getElementById('slideshowForm');
    const saveBtn = document.getElementById('saveSlideshowBtn');
    const id = document.getElementById('slideshowId').value;
    const formData = new FormData(form);

    formData.set('is_active', document.getElementById('slideshowIsActive').value);
    formData.set('autoplay', document.getElementById('slideshowAutoplay').checked ? '1' : '0');
    formData.set('muted', document.getElementById('slideshowMuted').checked ? '1' : '0');
    formData.set('loop', document.getElementById('slideshowLoop').checked ? '1' : '0');

    if (id) formData.append('_method', 'PUT');

    saveBtn.disabled = true;

    try {
        const response = await fetch(`/admin/cms/${getSlideshowEtablissementId()}/api/slideshow${id ? `/${id}` : ''}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        });

        const result = await response.json();
        if (!result.success) {
            showSlideshowErrors(result);
            return;
        }

        bootstrap.Modal.getInstance(document.getElementById('slideshowModal'))?.hide();
        await loadSlideshows();
        showSlideshowToast(result.message || 'Slideshow sauvegardé', 'success');
    } catch (error) {
        showSlideshowErrors({ message: error.message });
    } finally {
        saveBtn.disabled = false;
    }
}

async function toggleSlideshow(id) {
    try {
        const response = await fetch(`/admin/cms/${getSlideshowEtablissementId()}/api/slideshow/${id}/toggle`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        const result = await response.json();
        if (!result.success) throw new Error(result.message || 'Erreur statut');
        await loadSlideshows();
        showSlideshowToast(result.message || 'Statut mis à jour', 'success');
    } catch (error) {
        showSlideshowToast(error.message, 'error');
    }
}

function openDeleteSlideshowModal(id) {
    document.getElementById('deleteSlideshowId').value = id;
    bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteSlideshowModal')).show();
}

async function confirmDeleteSlideshow() {
    const id = document.getElementById('deleteSlideshowId').value;
    if (!id) return;

    try {
        const response = await fetch(`/admin/cms/${getSlideshowEtablissementId()}/api/slideshow/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        const result = await response.json();
        if (!result.success) throw new Error(result.message || 'Erreur suppression');
        bootstrap.Modal.getInstance(document.getElementById('deleteSlideshowModal'))?.hide();
        await loadSlideshows();
        showSlideshowToast(result.message || 'Vidéo supprimée', 'success');
    } catch (error) {
        showSlideshowToast(error.message, 'error');
    }
}

function showSlideshowErrors(result) {
    const errors = document.getElementById('slideshowErrors');
    if (!errors) return;

    const messages = [];
    if (result.errors) {
        Object.values(result.errors).forEach(items => items.forEach(message => messages.push(message)));
    }
    if (!messages.length && result.message) messages.push(result.message);

    errors.innerHTML = messages.map(message => `<div>${escapeSlideshowHtml(message)}</div>`).join('');
    errors.classList.remove('d-none');
}

function showSlideshowToast(message, type = 'success') {
    if (typeof showToast === 'function') {
        showToast(message, type);
        return;
    }

    document.querySelectorAll('.slideshow-toast').forEach(toast => toast.remove());
    const toast = document.createElement('div');
    toast.className = `slideshow-toast ${type === 'error' ? 'error' : 'success'}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    requestAnimationFrame(() => toast.classList.add('show'));
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 250);
    }, 3000);
}

function escapeSlideshowHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}
</script>
