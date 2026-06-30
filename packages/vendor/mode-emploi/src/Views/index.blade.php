@extends('layouts.app')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/services.css') }}">
    <main class="dashboard-content">
        <div class="page-header">
            <h1 class="page-title">
                <span class="page-title-icon"><i class="fas fa-graduation-cap"></i></span>
                Mode d'Emploi
            </h1>
            <div class="page-actions">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModuleModal">
                    <i class="fas fa-plus-circle me-2"></i>Nouveau Module
                </button>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stats-card-modern">
                <div class="stats-header-modern">
                    <div>
                        <div class="stats-value-modern" id="totalModules">0</div>
                        <div class="stats-label-modern">Total Modules</div>
                    </div>
                    <div class="stats-icon-modern" style="background: linear-gradient(135deg, var(--primary-color), #3a56e4);">
                        <i class="fas fa-folder"></i>
                    </div>
                </div>
            </div>
            <div class="stats-card-modern">
                <div class="stats-header-modern">
                    <div>
                        <div class="stats-value-modern" id="totalLessons">0</div>
                        <div class="stats-label-modern">Total Leçons</div>
                    </div>
                    <div class="stats-icon-modern" style="background: linear-gradient(135deg, var(--accent-color), #06b48a);">
                        <i class="fas fa-video"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="main-card-modern">
            <div class="card-header-modern">
                <h3 class="card-title-modern">Modules et Leçons</h3>
            </div>
            <div class="card-body-modern">
                <div class="spinner-container" id="loadingSpinner">
                    <div class="spinner-border text-primary spinner" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                </div>

                <div id="modulesContainer" style="display: none;">
                    <div class="modules-grid" id="modulesList"></div>
                </div>

                <div class="empty-state-modern" id="emptyState" style="display: none;">
                    <div class="empty-icon-modern">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h3 class="empty-title-modern">Aucun module</h3>
                    <p class="empty-text-modern">Créez votre premier module pour commencer.</p>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModuleModal">
                        <i class="fas fa-plus-circle me-2"></i>Créer un module
                    </button>
                </div>
            </div>
        </div>

        <button class="fab-modern" data-bs-toggle="modal" data-bs-target="#createModuleModal">
            <i class="fas fa-plus"></i>
        </button>
    </main>

    {{-- CREATE MODULE MODAL --}}
    <div class="modal fade" id="createModuleModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content modal-content-modern">
                <div class="modal-header modal-header-modern">
                    <h5 class="modal-title modal-title-modern"><i class="fas fa-plus-circle me-2"></i>Nouveau Module</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body modal-body-modern">
                    <form id="createModuleForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label-modern">Titre *</label>
                            <input type="text" class="form-control-modern" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-modern">Description</label>
                            <textarea class="form-control-modern" name="description" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label-modern">Icône</label>
                                <div class="icon-picker-container">
                                    <div class="icon-picker-input">
                                        <span class="icon-picker-preview" id="createIconPreview"><i class="fas fa-icons"></i></span>
                                        <input type="text" class="form-control-modern" name="icon" id="createModuleIcon" readonly style="cursor:pointer">
                                        <button type="button" class="icon-picker-toggle" id="createIconToggle"><i class="fas fa-chevron-down"></i></button>
                                    </div>
                                    <div class="icon-picker-dropdown" id="createIconDropdown">
                                        <input type="text" class="icon-picker-search" id="createIconSearch" placeholder="Rechercher une icône...">
                                        <div class="icon-picker-categories" id="createIconCategories"></div>
                                        <div class="icon-picker-grid" id="createIconGrid"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label-modern">Ordre</label>
                                <input type="number" class="form-control-modern" name="order" min="0" value="0">
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="createModuleActive" checked>
                                    <label class="form-check-label" for="createModuleActive">Actif</label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer modal-footer-modern">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary btn-pulse" id="submitModuleBtn">
                        <span class="btn-text"><i class="fas fa-save me-2"></i>Créer le module</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    {{-- EDIT MODULE MODAL --}}
    <div class="modal fade" id="editModuleModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content modal-content-modern">
                <div class="modal-header modal-header-modern">
                    <h5 class="modal-title modal-title-modern"><i class="fas fa-edit me-2"></i>Modifier le module</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body modal-body-modern">
                    <form id="editModuleForm">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="editModuleId" name="id">
                        <div class="mb-3">
                            <label class="form-label-modern">Titre *</label>
                            <input type="text" class="form-control-modern" id="editModuleTitle" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-modern">Description</label>
                            <textarea class="form-control-modern" id="editModuleDescription" name="description" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label-modern">Icône</label>
                                <div class="icon-picker-container">
                                    <div class="icon-picker-input">
                                        <span class="icon-picker-preview" id="editIconPreview"><i class="fas fa-icons"></i></span>
                                        <input type="text" class="form-control-modern" name="icon" id="editModuleIcon" readonly style="cursor:pointer">
                                        <button type="button" class="icon-picker-toggle" id="editIconToggle"><i class="fas fa-chevron-down"></i></button>
                                    </div>
                                    <div class="icon-picker-dropdown" id="editIconDropdown">
                                        <input type="text" class="icon-picker-search" id="editIconSearch" placeholder="Rechercher une icône...">
                                        <div class="icon-picker-categories" id="editIconCategories"></div>
                                        <div class="icon-picker-grid" id="editIconGrid"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label-modern">Ordre</label>
                                <input type="number" class="form-control-modern" id="editModuleOrder" name="order" min="0">
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="editModuleActive">
                                    <label class="form-check-label" for="editModuleActive">Actif</label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer modal-footer-modern">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" id="updateModuleBtn">
                        <span class="btn-text"><i class="fas fa-save me-2"></i>Enregistrer</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- CREATE LESSON MODAL --}}
    <div class="modal fade" id="createLessonModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content modal-content-modern">
                <div class="modal-header modal-header-modern">
                    <h5 class="modal-title modal-title-modern"><i class="fas fa-plus-circle me-2"></i>Nouvelle Leçon</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body modal-body-modern">
                    <form id="createLessonForm" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" id="createLessonModuleId" name="module_id">
                        <div class="mb-3">
                            <label class="form-label-modern">Titre *</label>
                            <input type="text" class="form-control-modern" id="createLessonTitle" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-modern">Description</label>
                            <textarea class="form-control-modern" id="createLessonDescription" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-modern">Source vidéo</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="video_source" id="createVideoSourceUrl" value="url" checked autocomplete="off">
                                <label class="btn btn-outline-primary" for="createVideoSourceUrl"><i class="fas fa-link me-1"></i>URL</label>
                                <input type="radio" class="btn-check" name="video_source" id="createVideoSourceFile" value="file" autocomplete="off">
                                <label class="btn btn-outline-primary" for="createVideoSourceFile"><i class="fas fa-upload me-1"></i>Fichier local</label>
                            </div>
                        </div>
                        <div class="row" id="createVideoUrlGroup">
                            <div class="col-md-6 mb-3">
                                <label class="form-label-modern">URL de la vidéo</label>
                                <input type="text" class="form-control-modern" name="video_url" id="createVideoUrl" placeholder="https://www.youtube.com/watch?v=...">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label-modern">Plateforme</label>
                                <select class="form-select-modern" name="video_provider" id="createVideoProvider">
                                    <option value="youtube">YouTube</option>
                                    <option value="local">Local</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label-modern">Durée (minutes)</label>
                                <input type="number" class="form-control-modern" name="duration_minutes" min="0" value="0">
                            </div>
                        </div>
                        <div class="mb-3" id="createVideoFileGroup" style="display:none;">
                            <label class="form-label-modern">Fichier vidéo</label>
                            <input type="file" class="form-control-modern" name="video_file" id="createVideoFile" accept="video/mp4,video/mov,video/avi,video/wmv,video/webm">
                            <div class="form-text-modern">MP4, MOV, AVI, WMV, WebM. Max: 200MB</div>
                            <div class="progress mt-2" id="createVideoProgress" style="display:none; height:24px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" id="createVideoProgressBar" style="width:0%">0%</div>
                            </div>
                            <div class="mt-2">
                                <label class="form-label-modern">Ou URL directe du fichier</label>
                                <input type="text" class="form-control-modern" name="video_path" id="createVideoPath" placeholder="https://example.com/videos/lesson.mp4">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label-modern">Ordre</label>
                                <input type="number" class="form-control-modern" name="order" min="0" value="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="createLessonActive" checked>
                                    <label class="form-check-label" for="createLessonActive">Actif</label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer modal-footer-modern">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary btn-pulse" id="submitLessonBtn">
                        <span class="btn-text"><i class="fas fa-save me-2"></i>Créer la leçon</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- EDIT LESSON MODAL --}}
    <div class="modal fade" id="editLessonModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content modal-content-modern">
                <div class="modal-header modal-header-modern">
                    <h5 class="modal-title modal-title-modern"><i class="fas fa-edit me-2"></i>Modifier la leçon</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body modal-body-modern">
                    <form id="editLessonForm" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="editLessonId" name="id">
                        <div class="mb-3">
                            <label class="form-label-modern">Module</label>
                            <select class="form-select-modern" id="editLessonModuleId" name="module_id">
                                @foreach($modules ?? [] as $m)
                                    <option value="{{ $m->id }}">{{ $m->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-modern">Titre *</label>
                            <input type="text" class="form-control-modern" id="editLessonTitle" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-modern">Description</label>
                            <textarea class="form-control-modern" id="editLessonDescription" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-modern">Source vidéo</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="video_source" id="editVideoSourceUrl" value="url" autocomplete="off">
                                <label class="btn btn-outline-primary" for="editVideoSourceUrl"><i class="fas fa-link me-1"></i>URL</label>
                                <input type="radio" class="btn-check" name="video_source" id="editVideoSourceFile" value="file" autocomplete="off">
                                <label class="btn btn-outline-primary" for="editVideoSourceFile"><i class="fas fa-upload me-1"></i>Fichier local</label>
                            </div>
                        </div>
                        <div class="row" id="editVideoUrlGroup">
                            <div class="col-md-6 mb-3">
                                <label class="form-label-modern">URL de la vidéo</label>
                                <input type="text" class="form-control-modern" id="editLessonVideoUrl" name="video_url">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label-modern">Plateforme</label>
                                <select class="form-select-modern" id="editLessonProvider" name="video_provider">
                                    <option value="youtube">YouTube</option>
                                    <option value="local">Local</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label-modern">Durée (minutes)</label>
                                <input type="number" class="form-control-modern" id="editLessonDuration" name="duration_minutes" min="0">
                            </div>
                        </div>
                        <div class="mb-3" id="editVideoFileGroup" style="display:none;">
                            <label class="form-label-modern">Fichier vidéo</label>
                            <input type="file" class="form-control-modern" name="video_file" id="editVideoFile" accept="video/mp4,video/mov,video/avi,video/wmv,video/webm">
                            <div class="form-text-modern" id="editVideoFileInfo">MP4, MOV, AVI, WMV, WebM. Max: 200MB</div>
                            <div class="progress mt-2" id="editVideoProgress" style="display:none; height:24px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" id="editVideoProgressBar" style="width:0%">0%</div>
                            </div>
                            <div class="mt-2">
                                <label class="form-label-modern">Ou URL directe du fichier</label>
                                <input type="text" class="form-control-modern" name="video_path" id="editVideoPath" placeholder="https://example.com/videos/lesson.mp4">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label-modern">Ordre</label>
                                <input type="number" class="form-control-modern" id="editLessonOrder" name="order" min="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="editLessonActive">
                                    <label class="form-check-label" for="editLessonActive">Actif</label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer modal-footer-modern">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" id="updateLessonBtn">
                        <span class="btn-text"><i class="fas fa-save me-2"></i>Enregistrer</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- DELETE CONFIRMATION MODAL --}}
    <div class="modal fade delete-confirm-modal" id="deleteConfirmModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="delete-icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <h4 class="delete-title" id="deleteTitle">Confirmer la suppression</h4>
                    <p class="delete-message" id="deleteMessage">Êtes-vous sûr de vouloir supprimer cet élément ?</p>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>Attention :</strong> Cette action est irréversible.
                    </div>
                </div>
                <div class="modal-footer border-0 justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                        <span class="btn-text"><i class="fas fa-trash me-2"></i>Supprimer</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

<div class="modal fade video-player-modal" id="videoPlayerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <button type="button" class="video-modal-close" data-bs-dismiss="modal" aria-label="Fermer">
                <i class="fas fa-xmark"></i>
            </button>
            <div class="video-player-wrapper" id="videoPlayerContainer">
                <div class="video-player-placeholder">
                    <div class="spinner-border text-light" role="status"></div>
                </div>
            </div>
            <div class="video-modal-header">
                <div class="video-modal-title" id="videoModalTitle">Titre de la leçon</div>
                <span class="video-modal-duration" id="videoModalDuration"></span>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('js/icon_picker.js') }}"></script>
<script>
let allModules = [];
let deleteTarget = null;
let createIconPicker, editIconPicker;

const extractYoutubeId = (url) => {
    if (!url) return null;
    const m = url.match(/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/);
    return m ? m[1] : null;
};

const buildEmbedUrl = (lesson) => {
    if (lesson.video_url) {
        const ytId = extractYoutubeId(lesson.video_url);
        if (ytId) return `https://www.youtube.com/embed/${ytId}?autoplay=1&rel=0`;
    }
    return null;
};

const playLessonVideo = (lessonId) => {
    for (const mod of allModules) {
        const lesson = (mod.lessons || []).find(l => l.id == lessonId);
        if (!lesson) continue;
        const container = document.getElementById('videoPlayerContainer');
        if (!container) return;
        document.getElementById('videoModalTitle').textContent = lesson.title || '';
        document.getElementById('videoModalDuration').textContent = lesson.duration_minutes ? lesson.duration_minutes + ' min' : '';

        const embedUrl = buildEmbedUrl(lesson);
        if (embedUrl) {
            container.innerHTML = `<iframe src="${embedUrl}" style="width:100%;height:100%;border:0;" allow="autoplay; fullscreen" allowfullscreen></iframe>`;
        } else if (lesson.video_url_display) {
            const isMp4 = lesson.video_url_display.match(/\.(mp4|mov|avi|wmv|webm)(\?|$)/i);
            const type = isMp4 ? `video/${isMp4[1] === 'mov' ? 'quicktime' : isMp4[1] === 'avi' ? 'x-msvideo' : isMp4[1] === 'wmv' ? 'x-ms-wmv' : isMp4[1] === 'webm' ? 'webm' : 'mp4'}` : 'video/mp4';
            container.innerHTML = `<video controls autoplay style="width:100%;height:100%;background:#000;object-fit:contain;"><source src="${lesson.video_url_display}" type="${type}">Votre navigateur ne supporte pas la vidéo.</video>`;
        } else {
            showAlert('warning', 'Aucune vidéo disponible pour cette leçon.');
            return;
        }
        const modal = new bootstrap.Modal(document.getElementById('videoPlayerModal'));
        modal.show();
        document.getElementById('videoPlayerModal').addEventListener('hidden.bs.modal', function() {
            container.innerHTML = '<div class="video-player-placeholder"><div class="spinner-border text-light" role="status"></div></div>';
        }, { once: true });
        return;
    }
};

document.addEventListener('DOMContentLoaded', function() {
    setupAjax();
    loadModules();
    initIconPickers();
    document.getElementById('submitModuleBtn')?.addEventListener('click', storeModule);
    document.getElementById('updateModuleBtn')?.addEventListener('click', updateModule);
    document.getElementById('submitLessonBtn')?.addEventListener('click', storeLesson);
    document.getElementById('updateLessonBtn')?.addEventListener('click', updateLesson);
    document.getElementById('createVideoSourceUrl')?.addEventListener('change', () => toggleVideoSource('create'));
    document.getElementById('createVideoSourceFile')?.addEventListener('change', () => toggleVideoSource('create'));
    document.getElementById('editVideoSourceUrl')?.addEventListener('change', () => toggleVideoSource('edit'));
    document.getElementById('editVideoSourceFile')?.addEventListener('change', () => toggleVideoSource('edit'));
    document.getElementById('confirmDeleteBtn')?.addEventListener('click', function() {
        if (!deleteTarget) return;
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<div class="spinner-border spinner-border-sm text-light"></div> Suppression...';
        const { type, id } = deleteTarget;
        const url = type === 'module'
            ? `{{ url('mode-emploi/modules') }}/${id}`
            : `{{ url('mode-emploi/lessons') }}/${id}`;
        $.ajax({
            url: url,
            type: 'DELETE',
            success: function(resp) {
                if (resp.success) {
                    bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal')).hide();
                    loadModules();
                    showAlert('success', resp.message);
                }
            },
            error: handleAjaxError,
            complete: function() {
                btn.disabled = false;
                btn.innerHTML = '<span class="btn-text"><i class="fas fa-trash me-2"></i>Supprimer</span>';
                deleteTarget = null;
            }
        });
    });
});

const setupAjax = () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' }
    });
};

const toggleVideoSource = (prefix) => {
    const urlRadio = document.getElementById(prefix + 'VideoSourceUrl');
    const fileRadio = document.getElementById(prefix + 'VideoSourceFile');
    const urlGroup = document.getElementById(prefix + 'VideoUrlGroup');
    const fileGroup = document.getElementById(prefix + 'VideoFileGroup');
    const urlInput = document.getElementById(prefix === 'create' ? 'createVideoUrl' : 'editLessonVideoUrl');
    const fileInput = document.getElementById(prefix === 'create' ? 'createVideoFile' : 'editVideoFile');
    const pathInput = document.getElementById(prefix === 'create' ? 'createVideoPath' : 'editVideoPath');

    const showUrl = urlRadio?.checked;
    if (urlGroup) urlGroup.style.display = showUrl ? '' : 'none';
    if (fileGroup) fileGroup.style.display = showUrl ? 'none' : '';
    if (urlInput) urlInput.required = showUrl;
    if (fileInput) fileInput.required = !showUrl && !(pathInput?.value);
    if (pathInput) pathInput.disabled = showUrl;
};

const initIconPickers = () => {
    if (typeof IconPicker === 'undefined') return;
    createIconPicker = new IconPicker({
        inputId: 'createModuleIcon',
        previewId: 'createIconPreview',
        dropdownId: 'createIconDropdown',
        searchId: 'createIconSearch',
        categoriesId: 'createIconCategories',
        gridId: 'createIconGrid',
        toggleId: 'createIconToggle'
    });
    editIconPicker = new IconPicker({
        inputId: 'editModuleIcon',
        previewId: 'editIconPreview',
        dropdownId: 'editIconDropdown',
        searchId: 'editIconSearch',
        categoriesId: 'editIconCategories',
        gridId: 'editIconGrid',
        toggleId: 'editIconToggle'
    });
};

// ── LOAD ─────────────────────────────────────────────────────

const loadModules = () => {
    document.getElementById('loadingSpinner').style.display = 'flex';
    document.getElementById('modulesContainer').style.display = 'none';
    document.getElementById('emptyState').style.display = 'none';

    $.ajax({
        url: '{{ route("mode-emploi.index") }}',
        type: 'GET',
        data: { ajax: true },
        success: function(resp) {
            document.getElementById('loadingSpinner').style.display = 'none';
            if (resp.success) {
                allModules = resp.data || [];
                renderModules(allModules);
                updateStats();
            } else {
                showAlert('danger', 'Erreur lors du chargement');
            }
        },
        error: function() {
            document.getElementById('loadingSpinner').style.display = 'none';
            showAlert('danger', 'Erreur de connexion');
        }
    });
};

// ── RENDER ───────────────────────────────────────────────────

const toggleModule = (moduleId) => {
    const body = document.getElementById(`module-body-${moduleId}`);
    const chevron = document.getElementById(`chevron-${moduleId}`);
    if (!body || !chevron) return;
    const isOpen = body.classList.contains('open');
    body.classList.toggle('open');
    chevron.classList.toggle('open');
    body.style.height = isOpen ? '0px' : body.scrollHeight + 'px';
    if (!isOpen) {
        body.addEventListener('transitionend', function handler() {
            body.style.height = 'auto';
            body.removeEventListener('transitionend', handler);
        }, { once: true });
    }
};

const renderModules = (modules) => {
    const container = document.getElementById('modulesList');
    container.innerHTML = '';

    if (!modules || modules.length === 0) {
        document.getElementById('emptyState').style.display = 'block';
        document.getElementById('modulesContainer').style.display = 'none';
        return;
    }

    document.getElementById('emptyState').style.display = 'none';
    document.getElementById('modulesContainer').style.display = 'block';

    modules.forEach((mod, idx) => {
        const icon = mod.icon || 'fas fa-folder';
        const lessons = mod.lessons || [];

        let lessonsHtml = '';
        if (lessons.length === 0) {
            lessonsHtml = '<div class="lesson-empty"><i class="fas fa-info-circle me-1"></i>Aucune leçon</div>';
        } else {
            lessons.forEach((l) => {
                const ytId = l.video_url ? extractYoutubeId(l.video_url) : null;
                const thumbStyle = ytId
                    ? `background-image:url(https://img.youtube.com/vi/${ytId}/hqdefault.jpg); background-size:cover; background-position:center;`
                    : '';
                let thumbInner;
                if (ytId) {
                    thumbInner = '<div class="lesson-thumb-play"><i class="fas fa-play"></i></div>';
                } else if (l.video_provider === 'local' && l.video_url_display) {
                    thumbInner = `
                        <video muted playsinline preload="metadata" src="${l.video_url_display}" style="width:100%;height:100%;object-fit:cover;position:absolute;inset:0;"></video>
                        <div class="lesson-thumb-play"><i class="fas fa-play"></i></div>`;
                } else {
                    const pi = l.video_provider === 'local' ? 'fas fa-video' : 'fab fa-youtube';
                    thumbInner = `<div class="lesson-thumb-play"><i class="fas fa-play"></i></div><div class="lesson-thumb-bg" style="color:#4361ee"><i class="${pi}"></i></div>`;
                }
                lessonsHtml += `
                    <div class="lesson-item" data-id="${l.id}">
                        <div class="lesson-thumb" style="${thumbStyle}" onclick="playLessonVideo('${l.id}')">
                            ${thumbInner}
                        </div>
                        <div class="lesson-body" onclick="playLessonVideo('${l.id}')">
                            <div class="lesson-title">${escHtml(l.title)}</div>
                        </div>
                        <div class="lesson-actions">
                            <button class="lesson-action-btn" title="Modifier" onclick="event.stopPropagation(); openEditLesson(${l.id})">
                                <i class="fas fa-pen"></i>
                            </button>
                            <button class="lesson-action-btn lesson-action-delete" title="Supprimer" onclick="event.stopPropagation(); confirmDelete('lesson', ${l.id}, '${l.title.replace(/'/g, "\\'")}')">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                `;
            });
        }

        const card = document.createElement('div');
        card.className = 'module-card';
        card.id = `module-card-${mod.id}`;
        card.style.animationDelay = `${idx * 0.1}s`;
        card.innerHTML = `
            <div class="module-header" onclick="toggleModule(${mod.id})">
                <div class="module-toggle ${lessons.length > 0 ? '' : 'invisible'}">
                    <i class="fas fa-chevron-right" id="chevron-${mod.id}"></i>
                </div>
                <div class="module-icon-circle"><i class="${icon}"></i></div>
                <div class="module-info">
                    <div class="module-name">${escHtml(mod.title)}</div>
                    <div class="module-meta">${lessons.length} leçon${lessons.length > 1 ? 's' : ''} · Ordre: ${mod.order || 0}</div>
                </div>
                <div class="module-actions" onclick="event.stopPropagation();">
                    <button class="module-action-btn" title="Ajouter une leçon" onclick="openCreateLesson(${mod.id})">
                        <i class="fas fa-plus"></i>
                    </button>
                    <button class="module-action-btn" title="Modifier le module" onclick="openEditModule(${mod.id})">
                        <i class="fas fa-pen"></i>
                    </button>
                    <button class="module-action-btn module-action-delete" title="Supprimer le module" onclick="confirmDelete('module', ${mod.id}, '${mod.title.replace(/'/g, "\\'")}')">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                    <span class="module-status ${mod.is_active ? 'active' : 'inactive'}">${mod.is_active ? 'Actif' : 'Inactif'}</span>
                </div>
            </div>
            ${mod.description ? `<div class="module-desc">${escHtml(mod.description)}</div>` : ''}
            <div class="module-body" id="module-body-${mod.id}">
                <div class="lessons-list">${lessonsHtml}</div>
                <div class="module-footer">
                    <button class="btn-add-lesson" onclick="openCreateLesson(${mod.id})">
                        <i class="fas fa-plus-circle me-1"></i>Ajouter une leçon
                    </button>
                </div>
            </div>
        `;
        container.appendChild(card);
    });
};

// ── STATS ────────────────────────────────────────────────────

const updateStats = () => {
    const totalModules = allModules.length;
    const totalLessons = allModules.reduce((sum, m) => sum + (m.lessons?.length || 0), 0);
    document.getElementById('totalModules').textContent = totalModules;
    document.getElementById('totalLessons').textContent = totalLessons;
};

// ── MODULE CRUD ──────────────────────────────────────────────

const storeModule = () => {
    const form = document.getElementById('createModuleForm');
    if (!form.checkValidity()) { form.reportValidity(); return; }

    const btn = document.getElementById('submitModuleBtn');
    btn.disabled = true;
    btn.innerHTML = '<div class="spinner-border spinner-border-sm text-light"></div> Création...';

    $.ajax({
        url: '{{ route("mode-emploi.modules.store") }}',
        type: 'POST',
        data: $(form).serialize(),
        success: function(resp) {
            if (resp.success) {
                bootstrap.Modal.getInstance(document.getElementById('createModuleModal')).hide();
                form.reset();
                document.getElementById('createModuleActive').checked = true;
                if (createIconPicker) createIconPicker.setValue('');
                loadModules();
                showAlert('success', resp.message);
            }
        },
        error: handleAjaxError,
        complete: function() {
            btn.disabled = false;
            btn.innerHTML = '<span class="btn-text"><i class="fas fa-save me-2"></i>Créer le module</span>';
        }
    });
};

const openEditModule = (id) => {
    const mod = allModules.find(m => m.id === id);
    if (!mod) return;

    document.getElementById('editModuleId').value = mod.id;
    document.getElementById('editModuleTitle').value = mod.title;
    document.getElementById('editModuleDescription').value = mod.description || '';
    if (editIconPicker) editIconPicker.setValue(mod.icon || '');
    document.getElementById('editModuleOrder').value = mod.order || 0;
    document.getElementById('editModuleActive').checked = !!mod.is_active;

    new bootstrap.Modal(document.getElementById('editModuleModal')).show();
};

const updateModule = () => {
    const form = document.getElementById('editModuleForm');
    const id = document.getElementById('editModuleId').value;
    if (!form.checkValidity()) { form.reportValidity(); return; }

    const btn = document.getElementById('updateModuleBtn');
    btn.disabled = true;
    btn.innerHTML = '<div class="spinner-border spinner-border-sm text-light"></div> Enregistrement...';

    $.ajax({
        url: `{{ url('mode-emploi/modules') }}/${id}`,
        type: 'POST',
        data: $(form).serialize() + '&_method=PUT',
        success: function(resp) {
            if (resp.success) {
                bootstrap.Modal.getInstance(document.getElementById('editModuleModal')).hide();
                loadModules();
                showAlert('success', resp.message);
            }
        },
        error: handleAjaxError,
        complete: function() {
            btn.disabled = false;
            btn.innerHTML = '<span class="btn-text"><i class="fas fa-save me-2"></i>Enregistrer</span>';
        }
    });
};

// ── LESSON CRUD ──────────────────────────────────────────────

const openCreateLesson = (moduleId) => {
    document.getElementById('createLessonModuleId').value = moduleId;
    document.getElementById('createLessonTitle').value = '';
    document.getElementById('createLessonDescription').value = '';
    document.getElementById('createLessonActive').checked = true;
    new bootstrap.Modal(document.getElementById('createLessonModal')).show();
};

const storeLesson = () => {
    const form = document.getElementById('createLessonForm');
    if (!form.checkValidity()) { form.reportValidity(); return; }

    const btn = document.getElementById('submitLessonBtn');
    btn.disabled = true;
    btn.innerHTML = '<div class="spinner-border spinner-border-sm text-light"></div> Création...';

    const formData = new FormData(form);
    const progressBar = document.getElementById('createVideoProgress');
    const progressBarInner = document.getElementById('createVideoProgressBar');

    $.ajax({
        url: '{{ route("mode-emploi.lessons.store") }}',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        xhr: function() {
            const xhr = new window.XMLHttpRequest();
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const pct = Math.round((e.loaded / e.total) * 100);
                    if (progressBar) progressBar.style.display = 'flex';
                    if (progressBarInner) {
                        progressBarInner.style.width = pct + '%';
                        progressBarInner.textContent = pct + '%';
                    }
                }
            }, false);
            return xhr;
        },
        success: function(resp) {
            if (resp.success) {
                bootstrap.Modal.getInstance(document.getElementById('createLessonModal')).hide();
                form.reset();
                toggleVideoSource('create');
                loadModules();
                showAlert('success', resp.message);
            } else {
                showAlert('danger', resp.message || 'Erreur');
            }
        },
        error: handleAjaxError,
        complete: function() {
            btn.disabled = false;
            btn.innerHTML = '<span class="btn-text"><i class="fas fa-save me-2"></i>Créer la leçon</span>';
            if (progressBar) progressBar.style.display = 'none';
            if (progressBarInner) { progressBarInner.style.width = '0%'; progressBarInner.textContent = '0%'; }
        }
    });
};

const openEditLesson = (id) => {
    for (const mod of allModules) {
        const lesson = (mod.lessons || []).find(l => l.id === id);
        if (lesson) {
            document.getElementById('editLessonId').value = lesson.id;
            document.getElementById('editLessonModuleId').value = lesson.module_id;
            document.getElementById('editLessonTitle').value = lesson.title;
            document.getElementById('editLessonDescription').value = lesson.description || '';
            document.getElementById('editLessonVideoUrl').value = lesson.video_url || '';
            document.getElementById('editLessonProvider').value = lesson.video_provider || 'local';
            document.getElementById('editLessonDuration').value = lesson.duration_minutes || '';
            document.getElementById('editLessonOrder').value = lesson.order || 0;
            document.getElementById('editLessonActive').checked = !!lesson.is_active;
            document.getElementById('editVideoPath').value = (lesson.video_path && lesson.video_path.match(/^https?:\/\//)) ? lesson.video_path : '';

            const hasUrl = !!lesson.video_url;
            document.getElementById('editVideoSourceUrl').checked = hasUrl;
            document.getElementById('editVideoSourceFile').checked = !hasUrl;
            toggleVideoSource('edit');

            new bootstrap.Modal(document.getElementById('editLessonModal')).show();
            return;
        }
    }
};

const updateLesson = () => {
    const form = document.getElementById('editLessonForm');
    const id = document.getElementById('editLessonId').value;
    if (!form.checkValidity()) { form.reportValidity(); return; }

    const btn = document.getElementById('updateLessonBtn');
    btn.disabled = true;
    btn.innerHTML = '<div class="spinner-border spinner-border-sm text-light"></div> Enregistrement...';

    const formData = new FormData(form);
    formData.append('_method', 'PUT');
    const progressBar = document.getElementById('editVideoProgress');
    const progressBarInner = document.getElementById('editVideoProgressBar');

    $.ajax({
        url: `{{ url('mode-emploi/lessons') }}/${id}`,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        xhr: function() {
            const xhr = new window.XMLHttpRequest();
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const pct = Math.round((e.loaded / e.total) * 100);
                    if (progressBar) progressBar.style.display = 'flex';
                    if (progressBarInner) {
                        progressBarInner.style.width = pct + '%';
                        progressBarInner.textContent = pct + '%';
                    }
                }
            }, false);
            return xhr;
        },
        success: function(resp) {
            if (resp.success) {
                bootstrap.Modal.getInstance(document.getElementById('editLessonModal')).hide();
                loadModules();
                showAlert('success', resp.message);
            } else {
                showAlert('danger', resp.message || 'Erreur');
            }
        },
        error: handleAjaxError,
        complete: function() {
            btn.disabled = false;
            btn.innerHTML = '<span class="btn-text"><i class="fas fa-save me-2"></i>Enregistrer</span>';
            if (progressBar) progressBar.style.display = 'none';
            if (progressBarInner) { progressBarInner.style.width = '0%'; progressBarInner.textContent = '0%'; }
        }
    });
};

// ── DELETE ───────────────────────────────────────────────────

const confirmDelete = (type, id, name) => {
    deleteTarget = { type, id };
    document.getElementById('deleteTitle').textContent = `Supprimer ${type === 'module' ? 'le module' : 'la leçon'}`;
    document.getElementById('deleteMessage').textContent = `Êtes-vous sûr de vouloir supprimer "${name}" ?`;
    new bootstrap.Modal(document.getElementById('deleteConfirmModal')).show();
};

// ── HELPERS ──────────────────────────────────────────────────

const escHtml = (str) => {
    const div = document.createElement('div');
    div.textContent = str || '';
    return div.innerHTML;
};

const handleAjaxError = (xhr) => {
    if (xhr.status === 422) {
        const errors = xhr.responseJSON.errors;
        let msg = 'Veuillez corriger les erreurs suivantes:<br>';
        for (const field in errors) msg += `- ${errors[field].join('<br>')}<br>`;
        showAlert('danger', msg);
    } else {
        showAlert('danger', 'Erreur: ' + (xhr.responseJSON?.message || xhr.statusText));
    }
};

const showAlert = (type, message) => {
    const container = document.querySelector('.dashboard-content');
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
    alert.style.zIndex = '9999';
    alert.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
    container.prepend(alert);
    setTimeout(() => alert.remove(), 5000);
};
</script>

<style>
/* ── Modules Grid ────────────────────────────────────── */
.modules-grid { display: flex; flex-direction: column; gap: 1rem; }

/* ── Module Card ─────────────────────────────────────── */
.module-card {
    background: var(--card-bg, #fff);
    border: 1px solid var(--border-color, #e9ecef);
    border-radius: 14px;
    animation: modFadeIn 0.35s ease forwards;
    opacity: 0;
    overflow: hidden;
}

.module-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 1.25rem;
    cursor: pointer;
    transition: background 0.2s;
    user-select: none;
}
.module-header:hover { background: var(--hover-bg, #f8f9fa); }

.module-toggle {
    width: 28px; height: 28px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; color: var(--text-muted, #6c757d);
    transition: transform 0.2s, background 0.2s;
}
.module-toggle i { transition: transform 0.25s ease; font-size: 0.8rem; }
.module-toggle i.open { transform: rotate(90deg); }

.module-icon-circle {
    width: 44px; height: 44px; border-radius: 12px;
    background: linear-gradient(135deg, var(--primary-color, #4361ee), #3a56e4);
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 1.1rem; flex-shrink: 0;
}

.module-info { flex: 1; min-width: 0; }
.module-name { font-weight: 600; font-size: 1rem; color: var(--text-color, #212529); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.module-meta { font-size: 0.78rem; color: var(--text-muted, #6c757d); margin-top: 1px; }

.module-actions {
    display: flex; align-items: center; gap: 0.3rem;
    flex-shrink: 0;
}

.module-action-btn {
    width: 32px; height: 32px; border-radius: 8px; border: none;
    display: inline-flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: 0.8rem;
    background: transparent; color: var(--text-muted, #6c757d);
    transition: all 0.2s;
}
.module-action-btn:hover { background: var(--hover-bg, #e9ecef); color: var(--text-color, #212529); }
.module-action-delete:hover { background: #fde8e8; color: #dc3545; }

.module-status {
    font-size: 0.7rem; font-weight: 600; padding: 3px 10px;
    border-radius: 20px; margin-left: 0.25rem;
}
.module-status.active { background: #e8f5e9; color: #2e7d32; }
.module-status.inactive { background: #f5f5f5; color: #9e9e9e; }

.module-desc {
    color: var(--text-muted, #6c757d); font-size: 0.85rem;
    padding: 0 1.25rem 0.75rem 4rem;
    border-bottom: 1px solid var(--border-color, #f0f0f0);
}

/* ── Module Body (collapse) ──────────────────────────── */
.module-body {
    height: 0; overflow: hidden;
    transition: height 0.35s ease;
    background: #fafbfc;
}
.module-body.open {
    height: auto;
}

.lessons-list { padding: 0.75rem 1.25rem 0.5rem; }

.lesson-empty {
    text-align: center; padding: 1rem; font-size: 0.85rem;
    color: var(--text-muted, #6c757d);
}

.module-footer {
    padding: 0.5rem 1.25rem 0.75rem;
    border-top: 1px solid var(--border-color, #f0f0f0);
}

.btn-add-lesson {
    width: 100%; padding: 0.5rem; border-radius: 10px; border: 1px dashed var(--border-color, #d0d0d0);
    background: transparent; color: var(--text-muted, #6c757d); font-size: 0.82rem;
    cursor: pointer; transition: all 0.2s;
}
.btn-add-lesson:hover { border-color: var(--primary-color, #4361ee); color: var(--primary-color, #4361ee); background: rgba(67,97,238,0.04); }

/* ── Lesson Item ─────────────────────────────────────── */
.lesson-item {
    display: flex; align-items: center; gap: 0.75rem;
    padding: 0.5rem 0.65rem; border-radius: 10px;
    transition: background 0.2s; cursor: pointer;
}
.lesson-item:hover { background: var(--hover-bg, #f0f1f3); }

.lesson-thumb {
    width: 80px; height: 48px; border-radius: 8px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    background: #1a1a2e; position: relative; overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
}
.lesson-thumb:hover { transform: scale(1.06); box-shadow: 0 3px 12px rgba(0,0,0,0.2); }

.lesson-thumb-play {
    width: 28px; height: 28px; border-radius: 50%;
    background: rgba(0,0,0,0.6); color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.7rem; position: relative; z-index: 2;
    transition: background 0.2s, transform 0.2s;
}
.lesson-thumb:hover .lesson-thumb-play { background: rgba(220,53,69,0.9); transform: scale(1.1); }

.lesson-thumb-bg {
    position: absolute; inset: 0; display: flex;
    align-items: center; justify-content: center;
    font-size: 1.4rem; opacity: 0.2;
}

.lesson-body { flex: 1; min-width: 0; }
.lesson-title { font-weight: 500; font-size: 0.88rem; color: var(--text-color, #212529); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.lesson-meta { font-size: 0.75rem; color: var(--text-muted, #6c757d); }
.meta-dot { margin: 0 4px; }

.lesson-actions {
    display: flex; align-items: center; gap: 0.2rem;
    opacity: 0; transition: opacity 0.2s; flex-shrink: 0;
}
.lesson-item:hover .lesson-actions { opacity: 1; }

.lesson-action-btn {
    width: 30px; height: 30px; border-radius: 8px; border: none;
    display: inline-flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: 0.75rem;
    background: transparent; color: var(--text-muted, #adb5bd);
    transition: all 0.2s;
}
.lesson-action-btn:hover { background: var(--hover-bg, #e9ecef); color: var(--text-color, #212529); }
.lesson-action-delete:hover { background: #fde8e8; color: #dc3545; }

/* ── Video Player Modal ──────────────────────────────── */
.video-player-modal .modal-dialog { max-width: 580px; }
.video-player-modal .modal-content {
    background: #fff; border: none; border-radius: 16px;
    overflow: hidden; box-shadow: 0 16px 48px rgba(0,0,0,0.2);
}

.video-modal-close {
    position: absolute; top: 10px; right: 10px; z-index: 20;
    width: 32px; height: 32px; border-radius: 50%;
    border: none; background: rgba(0,0,0,0.45); color: #fff;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: 0.85rem;
    transition: all 0.2s;
}
.video-modal-close:hover { background: #dc3545; transform: scale(1.1); }

.video-player-wrapper {
    position: relative; width: 100%; aspect-ratio: 16 / 9;
    background: #000; display: flex; align-items: center; justify-content: center;
}
.video-player-wrapper iframe,
.video-player-wrapper video {
    width: 100%; height: 100%; display: block;
}

.video-player-placeholder {
    display: flex; align-items: center; justify-content: center;
    width: 100%; height: 100%;
}

.video-modal-header {
    display: flex; align-items: center; justify-content: space-between;
    gap: 0.75rem; padding: 0.85rem 1rem;
    background: #fff;
}

.video-modal-title {
    font-size: 0.9rem; font-weight: 600; color: #1a1a1a;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    flex: 1; min-width: 0;
}

.video-modal-duration {
    font-size: 0.78rem; color: #888;
    font-weight: 400; flex-shrink: 0;
}

/* ── Icon picker fix ─────────────────────────────────── */
.icon-picker-dropdown { z-index: 9999; }
#createModuleModal .modal-body,
#editModuleModal .modal-body { overflow: visible; }

/* ── Progress Bar Upload ─────────────────────────────── */
.progress { background: var(--border-color, #e9ecef); border-radius: 12px; }
.progress-bar { background: linear-gradient(90deg, var(--primary-color, #4361ee), #6c8cff); color: #fff; font-size: 0.75rem; font-weight: 600; border-radius: 12px; display: flex; align-items: center; justify-content: center; transition: width 0.3s ease; height: auto; }

/* ── Animations ──────────────────────────────────────── */
@keyframes modFadeIn {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}

/* ── Responsive ──────────────────────────────────────── */
@media (max-width: 768px) {
    .module-header { flex-wrap: wrap; padding: 0.85rem 1rem; }
    .module-actions { width: 100%; justify-content: flex-end; margin-top: 0.25rem; }
    .lesson-item { flex-wrap: wrap; gap: 0.5rem; }
    .lesson-actions { opacity: 1; }
    .lesson-thumb { width: 64px; height: 40px; }
    .lesson-thumb-play { width: 24px; height: 24px; font-size: 0.6rem; }
    .module-desc { padding: 0 1rem 0.5rem 3.5rem; }
    .lessons-list { padding: 0.5rem 1rem; }
    .module-footer { padding: 0.5rem 1rem 0.65rem; }
    .video-player-modal .modal-dialog { max-width: calc(100vw - 2rem); margin: 0 auto; }
    .video-modal-header { flex-direction: column; align-items: flex-start; gap: 0.2rem; }
    .video-modal-title { font-size: 0.85rem; }
    .video-modal-close { top: 8px; right: 8px; width: 30px; height: 30px; font-size: 0.8rem; }
}

@media (max-width: 576px) {
    .module-icon-circle { width: 36px; height: 36px; font-size: 0.9rem; }
    .module-name { font-size: 0.9rem; }
    .module-action-btn { width: 28px; height: 28px; font-size: 0.7rem; }
    .module-status { font-size: 0.65rem; padding: 2px 8px; }
}
</style>
@endsection
