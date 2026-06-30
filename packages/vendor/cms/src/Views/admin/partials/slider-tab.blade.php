{{-- slider-tab.blade.php --}}
@php
    $sliderEnabled = $stats['etablissement']->getSetting('slider_enabled', false);
@endphp
<div
    class="tab-pane fade"
    id="v-pills-slider"
    role="tabpanel"
    data-etablissement-id="{{ $stats['etablissement']->id ?? '' }}"
    data-slider-enabled="{{ $sliderEnabled ? '1' : '0' }}">
    <div class="tab-content-header">
        <h3 class="tab-title">
            <i class="fas fa-sliders-h me-2" style="color: #45b7d1;"></i>
            Gestion du Slider
        </h3>
        <button type="button" class="btn btn-primary" id="addSlideBtn" onclick="openSlideModal()">
            <i class="fas fa-plus-circle me-2"></i>Créer un slider
        </button>
    </div>

    <div class="slider-status-card" id="sliderStatusCard">
        <div>
            <label class="slider-status-title" for="sliderEnabledSwitch">Activation du slider</label>
            <p class="slider-status-text mb-0" id="sliderStatusText">
                {{ $sliderEnabled ? 'Le slider est affiché sur le site public.' : 'Le slider est masqué sur le site public.' }}
            </p>
        </div>
        <div class="form-check form-switch slider-status-switch">
            <input
                class="form-check-input"
                type="checkbox"
                role="switch"
                id="sliderEnabledSwitch"
                {{ $sliderEnabled ? 'checked' : '' }}>
            <label class="form-check-label" for="sliderEnabledSwitch" id="sliderEnabledLabel">
                {{ $sliderEnabled ? 'Actif' : 'Inactif' }}
            </label>
        </div>
    </div>

    <div class="slider-stats mb-4">
        <div class="row">
            <div class="col-md-3">
                <div class="stat-mini-card">
                    <div class="stat-mini-value" id="totalSlides">0</div>
                    <div class="stat-mini-label">Total slides</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-mini-card">
                    <div class="stat-mini-value" id="activeSlides">0</div>
                    <div class="stat-mini-label">Slides actifs</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-mini-card">
                    <div class="stat-mini-value" id="imageSlides">0</div>
                    <div class="stat-mini-label">Images</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-mini-card">
                    <div class="stat-mini-value" id="videoSlides">0</div>
                    <div class="stat-mini-label">Vidéos</div>
                </div>
            </div>
        </div>
    </div>

    <div class="slider-filters mb-3">
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-outline-secondary active" data-filter="all">Tous</button>
            <button type="button" class="btn btn-outline-secondary" data-filter="image">Images</button>
            <button type="button" class="btn btn-outline-secondary" data-filter="video">Vidéos</button>
            <button type="button" class="btn btn-outline-secondary" data-filter="active">Actifs</button>
            <button type="button" class="btn btn-outline-secondary" data-filter="inactive">Inactifs</button>
        </div>
        <div class="float-end">
            <button type="button" class="btn btn-outline-secondary btn-sm" id="refreshSliderBtn">
                <i class="fas fa-sync-alt me-1"></i>Rafraîchir
            </button>
        </div>
        <div class="clearfix"></div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th style="width: 40px"><i class="fas fa-grip-vertical"></i></th>
                    <th style="width: 120px">Aperçu</th>
                    <th>Titre</th>
                    <th>Sous-titre</th>
                    <th style="width: 80px">Type</th>
                    <th style="width: 80px">Statut</th>
                    <th style="width: 100px">Bouton</th>
                    <th style="width: 120px">Actions</th>
                </tr>
            </thead>
            <tbody id="sliderTableBody">
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                        <p class="mt-2">Chargement des slides...</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Slide Modal -->
<div class="modal fade" id="slideModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable media-upload-modal slider-upload-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="slideModalTitle">
                    <i class="fas fa-plus-circle me-2"></i>Ajouter un slide
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="slideForm" class="media-upload-form" method="post" enctype="multipart/form-data" onsubmit="saveSlide(event); return false;">
                @csrf
                <input type="hidden" name="slide_id" id="slideId">
                <input type="hidden" name="slide_source_kind" id="slideSourceKind" value="setting">
                <div class="modal-body">
                    <div class="alert alert-info d-none" id="linkedMediaNotice">
                        <i class="fas fa-link me-2"></i>
                        Ce slide provient de la médiathèque. Ici, vous modifiez seulement son affichage dans le slider.
                    </div>
                    <div class="slider-form-grid">
                        <section class="slider-form-section">
                            <div class="slider-form-section-header">
                                <span class="slider-form-step">1</span>
                                <div>
                                    <h6>Média du slider</h6>
                                    <p>Choisissez le type de média puis sa source.</p>
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-lg-5">
                                    <label class="form-label">Type de média</label>
                                    <div class="upload-source-switch slider-type-switch">
                                        <label class="upload-source-option" for="typeImage">
                                            <input type="radio" name="type" id="typeImage" value="image" checked>
                                            <span>
                                                <i class="fas fa-image me-2"></i>Image
                                            </span>
                                        </label>
                                        <label class="upload-source-option" for="typeVideo">
                                            <input type="radio" name="type" id="typeVideo" value="video">
                                            <span>
                                                <i class="fas fa-video me-2"></i>Vidéo
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-lg-7">
                                    <label class="form-label">Source</label>
                                    <div class="upload-source-switch slider-source-switch">
                                        <label class="upload-source-option" for="sourceUpload">
                                            <input type="radio" name="source" id="sourceUpload" value="upload" checked>
                                            <span>
                                                <i class="fas fa-upload me-2"></i>Upload
                                            </span>
                                        </label>
                                        <label class="upload-source-option" for="sourceUrl">
                                            <input type="radio" name="source" id="sourceUrl" value="url">
                                            <span>
                                                <i class="fas fa-link me-2"></i>Via URL
                                            </span>
                                        </label>
                                        <label class="upload-source-option" for="sourceMedia">
                                            <input type="radio" name="source" id="sourceMedia" value="media">
                                            <span>
                                                <i class="fas fa-images me-2"></i>Médiathèque
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div id="uploadSection" class="mt-3">
                                <div class="upload-area" id="uploadArea">
                                    <div class="upload-icon">
                                        <i class="fas fa-cloud-upload-alt fa-3x"></i>
                                    </div>
                                    <h4>Glissez votre fichier ici</h4>
                                    <p>ou cliquez pour sélectionner</p>
                                    <input type="file" name="media_file" id="mediaFileInput" accept="image/*,video/mp4,video/webm,video/ogg" hidden>
                                    <button type="button" class="btn btn-outline-primary" onclick="getSlideField('mediaFileInput')?.click()">
                                        Sélectionner un fichier
                                    </button>
                                </div>
                                <div id="filePreview" class="mt-3" style="display: none;">
                                    <div class="preview-container text-center">
                                        <img id="imagePreview" src="" style="max-width: 100%; max-height: 200px; display: none; border-radius: 8px;">
                                        <video id="videoPreview" controls style="max-width: 100%; max-height: 200px; display: none;"></video>
                                        <div class="mt-2">
                                            <button type="button" class="btn btn-sm btn-danger" id="removeFileBtn">
                                                <i class="fas fa-trash"></i> Supprimer
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="mediaSection" class="mt-3" style="display: none;">
                                <label class="form-label">Sélectionner depuis la médiathèque</label>
                                <div class="media-selector">
                                    <div class="media-grid" id="mediaGrid">
                                        <div class="text-center py-3">
                                            <div class="spinner-border spinner-border-sm"></div> Chargement...
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary mt-2 w-100" id="openMediaLibraryBtn">
                                        <i class="fas fa-folder-open"></i> Parcourir la médiathèque
                                    </button>
                                </div>
                                <input type="hidden" name="media_id" id="selectedMediaId">
                                <div id="selectedMediaPreview" class="mt-3" style="display: none;">
                                    <div class="alert alert-info mb-0">
                                        <i class="fas fa-check-circle me-2"></i>
                                        <span id="selectedMediaName"></span>
                                    </div>
                                </div>
                            </div>

                            <div id="remoteUrlSection" class="mt-3" style="display: none;">
                                <label class="form-label" id="remoteUrlLabel">URL du média</label>
                                <input type="url" class="form-control" name="external_url" id="externalUrl" placeholder="https://example.com/media.jpg">
                                <small class="text-muted" id="remoteUrlHelp">Collez l'URL directe de l'image ou de la vidéo.</small>
                            </div>
                        </section>

                        <section class="slider-form-section">
                            <div class="slider-form-section-header">
                                <span class="slider-form-step">2</span>
                                <div>
                                    <h6>Contenu affiché</h6>
                                    <p>Texte visible dans le slider public.</p>
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Titre</label>
                                    <input type="text" class="form-control" name="title" id="slideTitle" placeholder="Titre du slide">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="subtitle" id="slideSubtitle" rows="3" placeholder="Sous-titre ou description"></textarea>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">Texte du bouton</label>
                                    <input type="text" class="form-control" name="button_text" id="slideButtonText" placeholder="Ex: En savoir plus">
                                </div>
                                <div class="col-md-7">
                                    <label class="form-label">Lien du bouton</label>
                                    <input type="text" class="form-control" name="button_link" id="slideButtonLink" placeholder="/page ou https://...">
                                </div>
                            </div>
                        </section>

                        <section class="slider-form-section">
                            <div class="slider-form-section-header">
                                <span class="slider-form-step">3</span>
                                <div>
                                    <h6>Design du titre et de la description</h6>
                                    <p>Ces règles sont sauvegardées dans le JSON du slider.</p>
                                </div>
                            </div>
                            <div class="slider-design-grid">
                                <div>
                                    <label class="form-label">Taille du titre</label>
                                    <input type="text" class="form-control" name="title_size" id="slideTitleSize" value="48px" placeholder="48px">
                                </div>
                                <div>
                                    <label class="form-label">Couleur du titre</label>
                                    <input type="color" class="form-control form-control-color slider-color-input" name="title_color" id="slideTitleColor" value="#ffffff">
                                </div>
                                <div>
                                    <label class="form-label">Police du titre</label>
                                    <select class="form-select" name="title_font" id="slideTitleFont">
                                        <option value="inherit">Par défaut</option>
                                        <option value="Arial, sans-serif">Arial</option>
                                        <option value="Inter, sans-serif">Inter</option>
                                        <option value="Roboto, sans-serif">Roboto</option>
                                        <option value="Montserrat, sans-serif">Montserrat</option>
                                        <option value="Poppins, sans-serif">Poppins</option>
                                        <option value="Georgia, serif">Georgia</option>
                                        <option value="Times New Roman, serif">Times New Roman</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label">Taille description</label>
                                    <input type="text" class="form-control" name="description_size" id="slideDescriptionSize" value="19px" placeholder="19px">
                                </div>
                                <div>
                                    <label class="form-label">Couleur description</label>
                                    <input type="color" class="form-control form-control-color slider-color-input" name="description_color" id="slideDescriptionColor" value="#ffffff">
                                </div>
                                <div>
                                    <label class="form-label">Police description</label>
                                    <select class="form-select" name="description_font" id="slideDescriptionFont">
                                        <option value="inherit">Par défaut</option>
                                        <option value="Arial, sans-serif">Arial</option>
                                        <option value="Inter, sans-serif">Inter</option>
                                        <option value="Roboto, sans-serif">Roboto</option>
                                        <option value="Montserrat, sans-serif">Montserrat</option>
                                        <option value="Poppins, sans-serif">Poppins</option>
                                        <option value="Georgia, serif">Georgia</option>
                                        <option value="Times New Roman, serif">Times New Roman</option>
                                    </select>
                                </div>
                            </div>
                        </section>

                        <section class="slider-form-section">
                            <div class="slider-form-section-header">
                                <span class="slider-form-step">4</span>
                                <div>
                                    <h6>Design du bouton</h6>
                                    <p>Taille, couleurs et police du bouton public.</p>
                                </div>
                            </div>
                            <div class="slider-design-grid">
                                <div>
                                    <label class="form-label">Taille du texte</label>
                                    <input type="text" class="form-control" name="button_size" id="slideButtonSize" value="16px" placeholder="16px">
                                </div>
                                <div>
                                    <label class="form-label">Couleur texte</label>
                                    <input type="color" class="form-control form-control-color slider-color-input" name="button_color" id="slideButtonColor" value="#ffffff">
                                </div>
                                <div>
                                    <label class="form-label">Couleur fond</label>
                                    <input type="color" class="form-control form-control-color slider-color-input" name="button_bg_color" id="slideButtonBgColor" value="#2563eb">
                                </div>
                                <div>
                                    <label class="form-label">Police bouton</label>
                                    <select class="form-select" name="button_font" id="slideButtonFont">
                                        <option value="inherit">Par défaut</option>
                                        <option value="Arial, sans-serif">Arial</option>
                                        <option value="Inter, sans-serif">Inter</option>
                                        <option value="Roboto, sans-serif">Roboto</option>
                                        <option value="Montserrat, sans-serif">Montserrat</option>
                                        <option value="Poppins, sans-serif">Poppins</option>
                                        <option value="Georgia, serif">Georgia</option>
                                        <option value="Times New Roman, serif">Times New Roman</option>
                                    </select>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" id="saveSlideBtn" onclick="saveSlide(event)">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteSlideModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="delete-icon">
                    <i class="fas fa-exclamation-triangle fa-3x text-danger"></i>
                </div>
                <h4>Supprimer le slide</h4>
                <p class="text-muted">Êtes-vous sûr de vouloir supprimer ce slide ?<br>Cette action est irréversible.</p>
                <div class="mt-4">
                    <button class="btn btn-secondary me-2" data-bs-dismiss="modal">Annuler</button>
                    <button class="btn btn-danger" id="confirmDeleteSlideBtn">Supprimer</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.slider-status-card {
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
.slider-status-card.is-disabled {
    background: #fff7ed;
    border-color: #fed7aa;
}
.slider-status-title {
    color: #1e293b;
    display: block;
    font-size: 14px;
    font-weight: 700;
    margin-bottom: 4px;
}
.slider-status-text {
    color: #64748b;
    font-size: 13px;
}
.slider-status-switch {
    align-items: center;
    display: flex;
    gap: 10px;
    margin: 0;
    min-width: 128px;
}
.slider-status-switch .form-check-input {
    cursor: pointer;
    height: 1.35rem;
    margin: 0;
    width: 2.5rem;
}
.slider-status-switch .form-check-label {
    color: #334155;
    cursor: pointer;
    font-size: 13px;
    font-weight: 700;
    margin: 0;
}
.slider-upload-modal .modal-header {
    background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%);
    border-bottom: 0;
    border-radius: 18px 18px 0 0;
    color: #fff;
}
.slider-upload-modal .modal-header .btn-close {
    filter: brightness(0) invert(1);
}
.slider-upload-modal .modal-content {
    border: 0;
    border-radius: 18px;
    overflow: hidden;
}
.slider-upload-modal .modal-body {
    background: #f8fafc;
    flex: 1 1 auto;
    min-height: 0;
    overflow-y: auto;
    overscroll-behavior: contain;
    -webkit-overflow-scrolling: touch;
}
.slider-upload-modal .modal-footer {
    background: #fff;
    border-top: 1px solid #e2e8f0;
    flex-shrink: 0;
}
.slider-upload-modal .form-label {
    color: #0f172a;
    font-weight: 600;
}
.slider-upload-modal .form-control,
.slider-upload-modal .form-select {
    border: 1px solid #dbe4f0;
    border-radius: 10px;
}
.slider-upload-modal .form-control:focus,
.slider-upload-modal .form-select:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.15);
}
.slider-upload-modal .form-control:disabled,
.slider-upload-modal .form-select:disabled {
    background: #eef2f7;
    cursor: not-allowed;
}
.slider-upload-modal .upload-source-switch {
    display: grid;
    gap: 12px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
}
.slider-upload-modal .slider-source-switch {
    grid-template-columns: repeat(3, minmax(0, 1fr));
}
.slider-upload-modal .upload-source-option {
    cursor: pointer;
    margin: 0;
}
.slider-upload-modal .upload-source-option input {
    opacity: 0;
    pointer-events: none;
    position: absolute;
}
.slider-upload-modal .upload-source-option span {
    align-items: center;
    background: #fff;
    border: 1px solid #dbe4f0;
    border-radius: 12px;
    color: #0f172a;
    display: flex;
    font-weight: 600;
    justify-content: center;
    min-height: 52px;
    padding: 12px 14px;
    transition: all 0.2s ease;
    width: 100%;
}
.slider-upload-modal .upload-source-option input:checked + span {
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    border-color: #2563eb;
    box-shadow: 0 0 0 0.18rem rgba(37, 99, 235, 0.14);
}
.slider-upload-modal .upload-source-option input:disabled + span {
    background: #eef2f7;
    color: #94a3b8;
    cursor: not-allowed;
    transform: none;
}
.slider-upload-modal .upload-source-option:hover span {
    border-color: #93c5fd;
    transform: translateY(-1px);
}
.slider-upload-modal .upload-source-option:has(input:disabled):hover span {
    border-color: #dbe4f0;
    transform: none;
}
.slider-form-grid {
    display: grid;
    gap: 18px;
}
.slider-form-section {
    background: #fff;
    border: 1px solid #dbe4f0;
    border-radius: 14px;
    padding: 18px;
}
.slider-form-section-header {
    align-items: flex-start;
    display: flex;
    gap: 12px;
    margin-bottom: 16px;
}
.slider-form-section-header h6 {
    color: #0f172a;
    font-size: 15px;
    font-weight: 800;
    margin: 0 0 3px;
}
.slider-form-section-header p {
    color: #64748b;
    font-size: 13px;
    margin: 0;
}
.slider-form-step {
    align-items: center;
    background: #eef2ff;
    border-radius: 999px;
    color: #3730a3;
    display: inline-flex;
    flex: 0 0 30px;
    font-size: 13px;
    font-weight: 800;
    height: 30px;
    justify-content: center;
    width: 30px;
}
.slider-design-grid {
    display: grid;
    gap: 14px;
    grid-template-columns: repeat(3, minmax(0, 1fr));
}
.slider-color-input {
    height: 38px;
    max-width: 100%;
    padding: 4px;
    width: 100%;
}
.slider-stats .stat-mini-card {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 20px;
    text-align: center;
    transition: all 0.3s ease;
}
.slider-stats .stat-mini-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.slider-filters .btn-group {
    margin-bottom: 20px;
}
.slider-filters .btn {
    border-radius: 20px;
    padding: 6px 16px;
}
.table th {
    background: #f8f9fa;
    border-bottom: 2px solid #e9ecef;
    font-weight: 600;
    padding: 12px;
}
.table td {
    vertical-align: middle;
    padding: 12px;
}
.slider-preview {
    width: 100px;
    height: 60px;
    border-radius: 8px;
    overflow: hidden;
    background: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
}
.slider-preview img,
.slider-preview video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.slider-preview .video-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-size: 20px;
}
.type-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
}
.type-badge.image {
    background: #dbeafe;
    color: #1e40af;
}
.type-badge.video {
    background: #fee2e2;
    color: #991b1b;
}
.status-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
}
.status-badge.active {
    background: #d1fae5;
    color: #065f46;
}
.status-badge.inactive {
    background: #f3f4f6;
    color: #6b7280;
}
.btn-icon {
    width: 32px;
    height: 32px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    margin: 0 2px;
}
.drag-handle-cell {
    cursor: move;
    color: #9ca3af;
    font-size: 18px;
    text-align: center;
}
.drag-handle-cell i {
    cursor: grab;
}
.drag-handle-cell i:active {
    cursor: grabbing;
}
.upload-area {
    border: 2px dashed #cbd5e1;
    border-radius: 16px;
    padding: 40px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #f8fafc;
}
.upload-area:hover {
    border-color: #4361ee;
    background: #f1f5f9;
}
.upload-area.drag-over {
    border-color: #10b981;
    background: #f0fdf4;
}
#slideForm.slider-source-url #uploadSection,
#slideForm.slider-source-url #uploadArea,
#slideForm.slider-source-url #mediaSection,
#slideForm.slider-source-media #uploadSection,
#slideForm.slider-source-media #uploadArea,
#slideForm.slider-source-media #remoteUrlSection,
#slideForm.slider-source-linked #uploadSection,
#slideForm.slider-source-linked #uploadArea,
#slideForm.slider-source-linked #mediaSection,
#slideForm.slider-source-linked #remoteUrlSection {
    display: none !important;
}
#slideForm.slider-source-url #remoteUrlSection,
#slideForm.slider-source-media #mediaSection {
    display: block !important;
}
.media-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
    gap: 10px;
    max-height: 250px;
    overflow-y: auto;
    padding: 10px;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    background: #f9fafb;
}
.media-item {
    cursor: pointer;
    border-radius: 8px;
    overflow: hidden;
    transition: all 0.2s ease;
    border: 2px solid transparent;
    background: white;
}
.media-item:hover {
    transform: scale(1.02);
}
.media-item.selected {
    border-color: #4361ee;
    box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.2);
}
.media-item img,
.media-item video {
    width: 100%;
    height: 60px;
    object-fit: cover;
}
.media-item .media-info {
    padding: 4px;
    font-size: 0.65rem;
    text-align: center;
    background: white;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.delete-icon {
    margin-bottom: 20px;
}
tr.dragging {
    opacity: 0.5;
    background: #e5e7eb;
}
.youtube-thumbnail {
    position: relative;
    width: 100%;
    height: 100%;
    background-size: cover;
    background-position: center;
}
@media (max-width: 768px) {
    .slider-design-grid {
        grid-template-columns: 1fr;
    }
    .table {
        font-size: 0.85rem;
    }
    .slider-preview {
        width: 70px;
        height: 45px;
    }
    .btn-icon {
        width: 28px;
        height: 28px;
    }
}
</style>

<script>
let sliderItems = [];
let deleteId = null;
let deleteSource = 'setting';
const sliderEtablissementId = @json((string) ($stats['etablissement']->id ?? ''));

document.addEventListener('DOMContentLoaded', function() {
    moveSliderModalsToBody();
    initEventListeners();
    const etablissementId = ensureCurrentEtablissementId();

    if (!etablissementId) {
        console.error('currentEtablissementId is not defined');
        showToast('Erreur: établissement non défini', 'error');
        return;
    }

    const sliderPane = document.getElementById('v-pills-slider');
    updateSliderEnabledUi(sliderPane?.dataset?.sliderEnabled === '1');
    document.getElementById('sliderEnabledSwitch')?.addEventListener('change', saveSliderEnabled);

    loadSliders();
});

function ensureCurrentEtablissementId() {
    if (sliderEtablissementId) {
        window.currentEtablissementId = sliderEtablissementId;
        return sliderEtablissementId;
    }

    const sliderPane = document.getElementById('v-pills-slider');
    const paneId = sliderPane?.dataset?.etablissementId;
    if (paneId) {
        window.currentEtablissementId = paneId;
        return paneId;
    }

    if (window.currentEtablissementId) {
        return window.currentEtablissementId;
    }

    const meta = document.querySelector('meta[name="etablissement-id"]');
    if (meta && meta.content) {
        window.currentEtablissementId = meta.content;
        return window.currentEtablissementId;
    }

    const match = window.location.pathname.match(/\/admin\/cms\/(\d+)/);
    window.currentEtablissementId = match ? match[1] : null;
    return window.currentEtablissementId;
}

function moveSliderModalsToBody() {
    ['slideModal', 'deleteSlideModal'].forEach((id) => {
        const modal = document.getElementById(id);
        if (modal && modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }
    });
}

function getSlideForm() {
    return document.getElementById('slideForm');
}

function getSlideField(id) {
    return getSlideForm()?.querySelector(`#${id}`) || document.getElementById(id);
}

async function saveSliderEnabled(event) {
    const checkbox = event.target;
    const isEnabled = checkbox.checked;
    const formData = new FormData();

    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    formData.append('site_slider_enabled', isEnabled ? '1' : '0');

    checkbox.disabled = true;

    try {
        const response = await fetch(`/admin/cms/${ensureCurrentEtablissementId()}/settings`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        });

        const result = await response.json();
        if (!result.success) throw new Error(result.message || 'Erreur lors de la sauvegarde');

        document.getElementById('v-pills-slider').dataset.sliderEnabled = isEnabled ? '1' : '0';
        updateSliderEnabledUi(isEnabled);
        showToast(isEnabled ? 'Slider activé' : 'Slider désactivé', 'success');
    } catch (error) {
        checkbox.checked = !isEnabled;
        updateSliderEnabledUi(!isEnabled);
        showToast(error.message, 'error');
    } finally {
        checkbox.disabled = false;
    }
}

function updateSliderEnabledUi(isEnabled) {
    const card = document.getElementById('sliderStatusCard');
    const label = document.getElementById('sliderEnabledLabel');
    const text = document.getElementById('sliderStatusText');
    const checkbox = document.getElementById('sliderEnabledSwitch');

    if (checkbox) checkbox.checked = isEnabled;
    if (card) card.classList.toggle('is-disabled', !isEnabled);
    if (label) label.textContent = isEnabled ? 'Actif' : 'Inactif';
    if (text) {
        text.textContent = isEnabled
            ? 'Le slider est affiché sur le site public.'
            : 'Le slider est masqué sur le site public.';
    }
}

function initEventListeners() {
    const addBtn = document.getElementById('addSlideBtn');
    if (addBtn) {
        addBtn.addEventListener('click', openSlideModal);
    }

    const refreshBtn = document.getElementById('refreshSliderBtn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', () => loadSliders());
    }

    document.querySelectorAll('#slideForm input[name="type"]').forEach(radio => {
        radio.addEventListener('change', handleTypeChange);
    });

    document.querySelectorAll('#slideForm input[name="source"]').forEach(radio => {
        radio.addEventListener('change', handleSourceChange);
    });

    const uploadArea = getSlideField('uploadArea');
    const fileInput = getSlideField('mediaFileInput');

    if (uploadArea) {
        uploadArea.addEventListener('click', () => fileInput?.click());
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('drag-over');
        });
        uploadArea.addEventListener('dragleave', () => uploadArea.classList.remove('drag-over'));
        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('drag-over');
            if (e.dataTransfer.files.length > 0) {
                handleFileSelect(e.dataTransfer.files[0]);
            }
        });
    }

    if (fileInput) {
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                handleFileSelect(e.target.files[0]);
            }
        });
    }

    const removeFileBtn = document.getElementById('removeFileBtn');
    if (removeFileBtn) {
        removeFileBtn.addEventListener('click', clearFilePreview);
    }

    const confirmDeleteBtn = document.getElementById('confirmDeleteSlideBtn');
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', confirmDelete);
    }

    document.querySelectorAll('[data-filter]').forEach(button => {
        button.addEventListener('click', function() {
            document.querySelectorAll('[data-filter]').forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            applyFilter(this.getAttribute('data-filter'));
        });
    });

    const slideModalEl = document.getElementById('slideModal');
    if (slideModalEl) {
        slideModalEl.addEventListener('hidden.bs.modal', resetForm);
    }
}

function loadSliders() {
    const etablissementId = ensureCurrentEtablissementId();
    if (!etablissementId) return;

    const tbody = document.getElementById('sliderTableBody');
    tbody.innerHTML = '<tr><td colspan="8" class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2">Chargement...</p></td></tr>';

    fetch(`/admin/cms/${etablissementId}/api/slider`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'include'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        return response.json();
    })
    .then(result => {
        if (result.success && Array.isArray(result.data)) {
            sliderItems = result.data;
            renderSliderTable(result.data);
            updateStats(result.data);
        } else {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center py-5"><i class="fas fa-sliders-h fa-3x text-muted mb-3"></i><p>Aucun slide</p><button class="btn btn-primary btn-sm" onclick="document.getElementById(\'addSlideBtn\').click()">Ajouter</button></td></tr>';
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        tbody.innerHTML = `<tr><td colspan="8" class="text-center py-5 text-danger"><i class="fas fa-exclamation-triangle fa-2x mb-2"></i><p>Erreur: ${escapeHtml(error.message)}</p><button class="btn btn-sm btn-outline-primary mt-2" onclick="loadSliders()">Réessayer</button></td></tr>`;
        showToast('Erreur de chargement', 'error');
    });
}

function extractYoutubeId(url) {
    if (!url) return null;
    const match = String(url).match(/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\s]{11})/i);
    return match ? match[1] : null;
}

function isVimeoUrl(url) {
    return /vimeo\.com/i.test(String(url || ''));
}

function isExternalVideoUrl(url) {
    return !!extractYoutubeId(url) || isVimeoUrl(url);
}

function isManagedStorageUrl(url) {
    if (!url) return false;
    const value = String(url);
    return value.includes('/storage/') || value.startsWith('/storage/');
}

function isExternalImageUrl(url) {
    if (!url) return false;
    const value = String(url);
    return /^https?:\/\//i.test(value) && !isManagedStorageUrl(value) && !isExternalVideoUrl(value);
}

function getPreviewHtml(item) {
    if (item.type === 'video') {
        const videoUrl = item.video_url || item.url || '';
        const youtubeId = extractYoutubeId(videoUrl);

        if (youtubeId) {
            return `<div class="slider-preview"><div class="youtube-thumbnail" style="background-image:url('https://img.youtube.com/vi/${youtubeId}/mqdefault.jpg');background-size:cover;background-position:center;"><div style="display:flex;align-items:center;justify-content:center;height:100%;background:rgba(0,0,0,0.3);"><i class="fab fa-youtube" style="color:red;font-size:24px;"></i></div></div></div>`;
        }

        if (isVimeoUrl(videoUrl)) {
            return `<div class="slider-preview"><div class="video-placeholder"><i class="fab fa-vimeo-v fa-2x"></i></div></div>`;
        }

        if (item.url) {
            return `<div class="slider-preview"><video src="${escapeHtml(item.url)}"></video></div>`;
        }

        return `<div class="slider-preview"><div class="video-placeholder"><i class="fas fa-video fa-2x"></i></div></div>`;
    }

    if (item.type === 'image' && item.url) {
        return `<div class="slider-preview"><img src="${escapeHtml(item.url)}" alt="${escapeHtml(item.title || '')}"></div>`;
    }

    return `<div class="slider-preview"><div class="video-placeholder"><i class="fas fa-image fa-2x"></i></div></div>`;
}

function renderSliderTable(items) {
    const tbody = document.getElementById('sliderTableBody');
    const sortedItems = [...items].sort((a, b) => (a.order || 0) - (b.order || 0));

    if (sortedItems.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center py-5"><i class="fas fa-sliders-h fa-3x text-muted mb-3"></i><p>Aucun slide</p><button class="btn btn-primary btn-sm" onclick="document.getElementById(\'addSlideBtn\').click()">Ajouter</button></td></tr>';
        return;
    }

    tbody.innerHTML = sortedItems.map((item, index) => {
        const source = item.source || 'setting';
        const sourceId = item.source_id || item.id;
        const toggleTitle = source === 'media' ? 'Retirer du slider' : (item.is_active ? 'Désactiver' : 'Activer');
        const deleteTitle = source === 'media' ? 'Retirer du slider' : 'Supprimer';
        const sourceBadge = source === 'media'
            ? '<span class="badge bg-info-subtle text-info-emphasis">Médiathèque</span>'
            : '<span class="badge bg-light text-dark">Setting</span>';

        return `
            <tr data-id="${sourceId}" data-source="${source}" data-order="${item.order || index + 1}">
                <td class="drag-handle-cell"><i class="fas fa-grip-vertical"></i></td>
                <td>${getPreviewHtml(item)}</td>
                <td><strong>${escapeHtml(item.title) || '<span class="text-muted">-</span>'}</strong><div class="mt-1">${sourceBadge}</div></td>
                <td><small class="text-muted">${escapeHtml(item.subtitle) || '-'}</small></td>
                <td><span class="type-badge ${item.type}"><i class="fas fa-${item.type === 'video' ? 'video' : 'image'} me-1"></i>${item.type === 'video' ? 'Vidéo' : 'Image'}</span></td>
                <td><span class="status-badge ${item.is_active ? 'active' : 'inactive'}"><i class="fas fa-${item.is_active ? 'check-circle' : 'circle'} me-1"></i>${item.is_active ? 'Actif' : 'Inactif'}</span></td>
                <td>${item.button_text ? `<span class="badge bg-secondary">${escapeHtml(item.button_text)}</span>` : '-'}</td>
                <td>
                    <button class="btn btn-sm btn-outline-primary btn-icon edit-slide" data-id="${sourceId}" data-source="${source}" title="Modifier"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-sm btn-outline-secondary btn-icon toggle-active" data-id="${sourceId}" data-source="${source}" title="${toggleTitle}"><i class="fas fa-${item.is_active ? 'eye-slash' : 'eye'}"></i></button>
                    <button class="btn btn-sm btn-outline-danger btn-icon delete-slide" data-id="${sourceId}" data-source="${source}" title="${deleteTitle}"><i class="fas fa-trash"></i></button>
                </td>
            </tr>`;
    }).join('');

    document.querySelectorAll('.edit-slide').forEach(btn => btn.addEventListener('click', () => editSlide(btn.dataset.id, btn.dataset.source)));
    document.querySelectorAll('.delete-slide').forEach(btn => btn.addEventListener('click', () => {
        deleteId = btn.dataset.id;
        deleteSource = btn.dataset.source || 'setting';
        new bootstrap.Modal(document.getElementById('deleteSlideModal')).show();
    }));
    document.querySelectorAll('.toggle-active').forEach(btn => btn.addEventListener('click', () => toggleActive(btn.dataset.id, btn.dataset.source)));

    initDragAndDrop();
}

function initDragAndDrop() {
    const tbody = document.getElementById('sliderTableBody');
    let dragSrcRow = null;

    tbody.querySelectorAll('tr[data-id]').forEach(row => {
        row.setAttribute('draggable', 'true');
        row.addEventListener('dragstart', (e) => {
            dragSrcRow = row;
            e.dataTransfer.effectAllowed = 'move';
            row.classList.add('dragging');
        });
        row.addEventListener('dragend', () => row.classList.remove('dragging'));
        row.addEventListener('dragover', (e) => e.preventDefault());
        row.addEventListener('drop', (e) => {
            e.preventDefault();
            if (dragSrcRow && dragSrcRow !== row) {
                if (Array.from(tbody.children).indexOf(dragSrcRow) < Array.from(tbody.children).indexOf(row)) {
                    row.parentNode.insertBefore(dragSrcRow, row.nextSibling);
                } else {
                    row.parentNode.insertBefore(dragSrcRow, row);
                }
                saveNewOrder();
            }
        });
    });
}

function saveNewOrder() {
    const etablissementId = ensureCurrentEtablissementId();
    if (!etablissementId) return;

    const rows = document.querySelectorAll('#sliderTableBody tr[data-id]');
    const orders = [];

    rows.forEach((row, index) => {
        orders.push({
            item_id: parseInt(row.dataset.id, 10),
            source: row.dataset.source || 'setting',
            order: index + 1
        });
    });

    fetch(`/admin/cms/${etablissementId}/api/slider/reorder`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ orders })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            loadSliders();
        } else {
            showToast(result.message || 'Erreur de tri', 'error');
        }
    })
    .catch(error => {
        console.error('Reorder error:', error);
        showToast('Erreur de tri', 'error');
    });
}

function openSlideModal() {
    const modalEl = document.getElementById('slideModal');
    if (!modalEl) {
        console.error('slideModal introuvable');
        showToast('Erreur: modal introuvable', 'error');
        return;
    }

    try {
        resetForm();
        const titleEl = document.getElementById('slideModalTitle');
        if (titleEl) {
            titleEl.innerHTML = '<i class="fas fa-plus-circle me-2"></i>Ajouter un slide';
        }
    } catch (error) {
        console.error('Reset slider form error:', error);
    }

    try {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    } catch (error) {
        console.error('Open slider modal error:', error);
        showToast('Erreur lors de l\'ouverture du modal', 'error');
    }
}

function updateStats(items) {
    document.getElementById('totalSlides').textContent = items.length;
    document.getElementById('activeSlides').textContent = items.filter(i => i.is_active).length;
    document.getElementById('imageSlides').textContent = items.filter(i => i.type === 'image').length;
    document.getElementById('videoSlides').textContent = items.filter(i => i.type === 'video').length;
}

function applyFilter(filter) {
    document.querySelectorAll('#sliderTableBody tr').forEach(row => {
        if (!row.dataset.id) return;
        const type = row.querySelector('.type-badge')?.classList.contains('image') ? 'image' : 'video';
        const status = row.querySelector('.status-badge')?.classList.contains('active') ? 'active' : 'inactive';
        let show = true;
        if (filter === 'image') show = type === 'image';
        else if (filter === 'video') show = type === 'video';
        else if (filter === 'active') show = status === 'active';
        else if (filter === 'inactive') show = status === 'inactive';
        row.style.display = show ? '' : 'none';
    });
}

function handleTypeChange() {
    const checked = document.querySelector('#slideForm input[name="type"]:checked');
    const isVideo = checked && checked.value === 'video';
    const fileInput = getSlideField('mediaFileInput');
    const remoteUrlLabel = document.getElementById('remoteUrlLabel');
    const remoteUrlHelp = document.getElementById('remoteUrlHelp');
    const externalUrl = getSlideField('externalUrl');

    if (fileInput) {
        fileInput.setAttribute('accept', isVideo ? 'video/mp4,video/webm,video/ogg' : 'image/*');
    }
    if (remoteUrlLabel) {
        remoteUrlLabel.textContent = isVideo ? 'URL de la vidéo externe' : 'URL de l’image externe';
    }
    if (remoteUrlHelp) {
        remoteUrlHelp.textContent = isVideo
            ? 'Collez une URL YouTube, Vimeo ou une URL directe de vidéo.'
            : 'Collez l’URL directe d’une image.';
    }
    if (externalUrl) {
        externalUrl.placeholder = isVideo
            ? 'https://www.youtube.com/watch?v=... ou https://vimeo.com/...'
            : 'https://example.com/image.jpg';
    }
}

function handleSourceChange() {
    const source = getSelectedSlideSource();

    syncSliderAssetSections(source);

    if (source !== 'upload') {
        clearFilePreview(false);
    }

    if (source === 'media') {
        loadMediaLibrary();
    }
}

function getSelectedSlideSource() {
    return document.querySelector('#slideForm input[name="source"]:checked')?.value || 'upload';
}

function syncSliderAssetSections(source = getSelectedSlideSource()) {
    const form = getSlideForm();

    if (form) {
        form.classList.remove('slider-source-upload', 'slider-source-url', 'slider-source-media', 'slider-source-linked');
        form.classList.add(`slider-source-${source}`);
    }

    setSliderSectionVisible('remoteUrlSection', source === 'url');
    setSliderSectionVisible('uploadSection', source === 'upload');
    setSliderSectionVisible('mediaSection', source === 'media');

    const uploadArea = getSlideField('uploadArea');
    const filePreview = getSlideField('filePreview');

    if (uploadArea) {
        const shouldShowUploadArea = source === 'upload' && (!filePreview || filePreview.style.display === 'none');
        uploadArea.style.display = shouldShowUploadArea ? 'block' : 'none';
    }

    if (source === 'url') {
        getSlideField('externalUrl')?.focus();
    }
}

function setSliderSectionVisible(sectionId, isVisible) {
    const section = getSlideField(sectionId);
    if (!section) return;

    section.classList.toggle('d-none', !isVisible);
    section.style.display = isVisible ? 'block' : 'none';
}

function handleFileSelect(file) {
    const isVideo = file.type.startsWith('video/');
    const imgPreview = document.getElementById('imagePreview');
    const vidPreview = document.getElementById('videoPreview');

    const reader = new FileReader();
    reader.onload = function(e) {
        if (isVideo) {
            imgPreview.style.display = 'none';
            vidPreview.style.display = 'block';
            vidPreview.src = e.target.result;
        } else {
            vidPreview.style.display = 'none';
            imgPreview.style.display = 'block';
            imgPreview.src = e.target.result;
        }
        getSlideField('filePreview').style.display = 'block';
        getSlideField('uploadArea').style.display = 'none';
    };
    reader.readAsDataURL(file);
}

function clearFilePreview(showUploadArea = true) {
    getSlideField('filePreview').style.display = 'none';
    getSlideField('uploadArea').style.display = showUploadArea && getSelectedSlideSource() === 'upload' ? 'block' : 'none';
    document.getElementById('imagePreview').src = '';
    document.getElementById('imagePreview').style.display = 'none';
    document.getElementById('videoPreview').src = '';
    document.getElementById('videoPreview').style.display = 'none';
    getSlideField('mediaFileInput').value = '';
}

function loadMediaLibrary() {
    const etablissementId = ensureCurrentEtablissementId();
    if (!etablissementId) return;

    const grid = document.getElementById('mediaGrid');
    grid.innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm"></div> Chargement...</div>';
    const checked = document.querySelector('#slideForm input[name="type"]:checked');
    const type = checked ? checked.value : 'image';

    fetch(`/admin/cms/${etablissementId}/media?type=${type === 'video' ? 'video' : 'image'}&limit=20`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(result => {
        const mediaItems = Array.isArray(result.data)
            ? result.data
            : (Array.isArray(result.data?.data) ? result.data.data : []);
        const selectedMediaId = document.getElementById('selectedMediaId').value || '';

        if (result.success && mediaItems.length) {
            grid.innerHTML = mediaItems.map(media => `
                <div class="media-item ${String(selectedMediaId) === String(media.id) ? 'selected' : ''}" data-id="${media.id}" data-url="${escapeHtml(getSliderMediaAssetUrl(media))}" data-name="${escapeHtml(media.name || '')}">
                    ${media.type === 'video' ? `<video src="${escapeHtml(getSliderMediaAssetUrl(media))}"></video>` : `<img src="${escapeHtml(getSliderMediaAssetUrl(media))}" alt="${escapeHtml(media.name || '')}">`}
                    <div class="media-info">${escapeHtml((media.name || '').substring(0, 15))}</div>
                </div>`).join('');

            grid.querySelectorAll('.media-item').forEach(item => item.addEventListener('click', () => {
                grid.querySelectorAll('.media-item').forEach(i => i.classList.remove('selected'));
                item.classList.add('selected');
                document.getElementById('selectedMediaId').value = item.dataset.id;
                document.getElementById('selectedMediaName').textContent = item.dataset.name || '';
                document.getElementById('selectedMediaPreview').style.display = 'block';
            }));

            if (selectedMediaId) {
                const selectedCard = grid.querySelector(`.media-item[data-id="${selectedMediaId}"]`);
                if (selectedCard) {
                    document.getElementById('selectedMediaName').textContent = selectedCard.dataset.name || 'Média sélectionné';
                    document.getElementById('selectedMediaPreview').style.display = 'block';
                }
            }
        } else {
            grid.innerHTML = '<div class="text-center py-4"><p class="text-muted">Aucun média trouvé</p></div>';
        }
    })
    .catch(error => {
        console.error('Error loading media:', error);
        grid.innerHTML = '<div class="text-center py-4 text-danger"><p>Erreur de chargement</p></div>';
    });
}

function findSliderItem(id, source = 'setting') {
    return sliderItems.find(item => String(item.source_id || item.id) === String(id) && String(item.source || 'setting') === String(source || 'setting'));
}

function setModalSourceMode(source) {
    const isMediaSource = source === 'media';
    const notice = document.getElementById('linkedMediaNotice');

    if (notice) {
        notice.classList.toggle('d-none', !isMediaSource);
    }

    document.querySelectorAll('#slideForm input[name="source"]').forEach(input => input.disabled = isMediaSource);
    document.querySelectorAll('#slideForm input[name="type"]').forEach(input => input.disabled = isMediaSource);

    if (isMediaSource) {
        getSlideForm()?.classList.remove('slider-source-upload', 'slider-source-url', 'slider-source-media');
        getSlideForm()?.classList.add('slider-source-linked');
        setSliderSectionVisible('uploadSection', false);
        setSliderSectionVisible('mediaSection', false);
        setSliderSectionVisible('remoteUrlSection', false);
    } else {
        syncSliderAssetSections();
    }
}

function setExistingPreview(item) {
    clearFilePreview();
    if (!item) return;

    const imgPreview = document.getElementById('imagePreview');
    const vidPreview = document.getElementById('videoPreview');
    const filePreview = getSlideField('filePreview');
    const uploadArea = getSlideField('uploadArea');
    const mediaUrl = item.video_url || item.url || '';

    if (item.type === 'image' && item.url) {
        imgPreview.src = item.url;
        imgPreview.style.display = 'block';
        filePreview.style.display = 'block';
        uploadArea.style.display = 'none';
        return;
    }

    if (item.type === 'video') {
        if (extractYoutubeId(mediaUrl)) {
            imgPreview.src = `https://img.youtube.com/vi/${extractYoutubeId(mediaUrl)}/mqdefault.jpg`;
            imgPreview.style.display = 'block';
            filePreview.style.display = 'block';
            uploadArea.style.display = 'none';
            return;
        }

        if (isVimeoUrl(mediaUrl)) {
            return;
        }

        if (item.url) {
            vidPreview.src = item.url;
            vidPreview.style.display = 'block';
            filePreview.style.display = 'block';
            uploadArea.style.display = 'none';
            return;
        }
    }
}

function getSliderMediaAssetUrl(media) {
    const candidate = String(media?.path || media?.url || '').trim();
    if (!candidate) return '';
    if (/^https?:\/\//i.test(candidate) || candidate.startsWith('/')) {
        return candidate;
    }
    if (candidate.startsWith('storage/')) {
        return `/${candidate}`;
    }
    return `/storage/${candidate.replace(/^\/+/, '')}`;
}

function saveSlide(e) {
    if (e && typeof e.preventDefault === 'function') {
        e.preventDefault();
    }
    const etablissementId = ensureCurrentEtablissementId();
    if (!etablissementId) {
        showToast('Erreur: établissement non défini', 'error');
        return;
    }

    const slideId = document.getElementById('slideId').value;
    const isEdit = slideId !== '';
    const selectedType = document.querySelector('#slideForm input[name="type"]:checked')?.value || 'image';
    const sourceKind = document.getElementById('slideSourceKind').value || 'setting';
    const assetSource = sourceKind === 'media'
        ? 'media'
        : getSelectedSlideSource();

    const formData = new FormData();
    formData.append('type', selectedType);
    formData.append('source', sourceKind);
    formData.append('slide_source_kind', sourceKind);
    formData.append('asset_source', assetSource);
    formData.append('title', document.getElementById('slideTitle').value || '');
    formData.append('subtitle', document.getElementById('slideSubtitle').value || '');
    formData.append('button_text', document.getElementById('slideButtonText').value || '');
    formData.append('button_link', document.getElementById('slideButtonLink').value || '');
    appendSliderDesignFields(formData);
    const externalUrl = getSlideField('externalUrl').value || '';

    if (!(isEdit && sourceKind === 'media')) {
        if (assetSource === 'upload') {
            const file = getSlideField('mediaFileInput')?.files[0];
            if (!file && !isEdit) {
                showToast(selectedType === 'video' ? 'Veuillez sélectionner une vidéo' : 'Veuillez sélectionner une image', 'error');
                return;
            }

            if (file) {
                formData.append(selectedType === 'video' ? 'video_file' : 'image_file', file);
            }
        } else if (assetSource === 'url') {
            if (!externalUrl) {
                showToast(selectedType === 'video' ? 'Veuillez saisir une URL vidéo' : 'Veuillez saisir une URL image', 'error');
                return;
            }
            formData.append('external_url', externalUrl);
            if (selectedType === 'video') {
                formData.append('video_url', externalUrl);
            }
        } else {
            const mediaId = document.getElementById('selectedMediaId').value;
            if (!mediaId && !isEdit) {
                showToast('Veuillez sélectionner un média', 'error');
                return;
            }
            if (mediaId) {
                formData.append('media_id', mediaId);
            }
        }
    }

    const saveBtn = document.getElementById('saveSlideBtn');
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enregistrement...';

    const url = isEdit
        ? `/admin/cms/${etablissementId}/api/slider/${slideId}`
        : `/admin/cms/${etablissementId}/api/slider`;

    if (isEdit) {
        formData.append('_method', 'PUT');
    }

    fetch(url, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    })
    .then(async response => {
        const result = await response.json().catch(() => ({ success: false, message: 'Réponse invalide' }));
        if (!response.ok) {
            throw new Error(result.message || `HTTP ${response.status}`);
        }
        return result;
    })
    .then(result => {
        saveBtn.disabled = false;
        saveBtn.innerHTML = 'Enregistrer';
        if (result.success) {
            bootstrap.Modal.getInstance(document.getElementById('slideModal')).hide();
            showToast(result.message || 'Enregistré', 'success');
            loadSliders();
        } else {
            showToast(result.message || 'Erreur', 'error');
        }
    })
    .catch(error => {
        saveBtn.disabled = false;
        saveBtn.innerHTML = 'Enregistrer';
        console.error('Save error:', error);
        showToast(error.message || 'Erreur lors de l\'enregistrement', 'error');
    });
}

function editSlide(id, source = 'setting') {
    const item = findSliderItem(id, source);
    if (!item) return;

    resetForm();
    document.getElementById('slideId').value = id;
    document.getElementById('slideSourceKind').value = source || 'setting';
    document.getElementById('slideModalTitle').innerHTML = '<i class="fas fa-edit me-2"></i>Modifier le slide';
    document.getElementById(item.type === 'video' ? 'typeVideo' : 'typeImage').checked = true;
    document.getElementById('slideTitle').value = item.title || '';
    document.getElementById('slideSubtitle').value = item.subtitle || '';
    document.getElementById('slideButtonText').value = item.button_text || '';
    document.getElementById('slideButtonLink').value = item.button_link || '';
    setSliderStyleFields(item);
    getSlideField('externalUrl').value = item.video_url || (isExternalVideoUrl(item.url || '') || isExternalImageUrl(item.url || '') ? (item.url || '') : '');

    handleTypeChange();

    if ((source || 'setting') === 'media') {
        document.getElementById('sourceMedia').checked = true;
        setModalSourceMode('media');
    } else if ((item.asset_source || '') === 'media' || item.media_id) {
        document.getElementById('sourceMedia').checked = true;
        document.getElementById('selectedMediaId').value = item.media_id || '';
        document.getElementById('selectedMediaName').textContent = item.title || item.name || 'Média sélectionné';
        document.getElementById('selectedMediaPreview').style.display = item.media_id ? 'block' : 'none';
        setModalSourceMode('setting');
        handleSourceChange();
    } else if ((item.asset_source || '') === 'url' || item.video_url || isExternalVideoUrl(item.url || '') || isExternalImageUrl(item.url || '')) {
        document.getElementById('sourceUrl').checked = true;
        setModalSourceMode('setting');
        handleSourceChange();
    } else {
        document.getElementById('sourceUpload').checked = true;
        setModalSourceMode('setting');
        handleSourceChange();
    }

    setExistingPreview(item);
    new bootstrap.Modal(document.getElementById('slideModal')).show();
}

function toggleActive(id, source = 'setting') {
    const etablissementId = ensureCurrentEtablissementId();
    if (!etablissementId) return;

    fetch(`/admin/cms/${etablissementId}/api/slider/${id}/toggle`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ source: source || 'setting' })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showToast(result.message || 'Statut mis à jour', 'success');
            loadSliders();
        } else {
            showToast(result.message || 'Erreur', 'error');
        }
    })
    .catch(error => {
        console.error('Toggle error:', error);
        showToast('Erreur de statut', 'error');
    });
}

function confirmDelete() {
    if (!deleteId) return;
    const etablissementId = ensureCurrentEtablissementId();
    if (!etablissementId) return;

    fetch(`/admin/cms/${etablissementId}/api/slider/${deleteId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ source: deleteSource || 'setting' })
    })
    .then(response => response.json())
    .then(result => {
        bootstrap.Modal.getInstance(document.getElementById('deleteSlideModal')).hide();
        if (result.success) {
            showToast(result.message || 'Supprimé', 'success');
            loadSliders();
        } else {
            showToast(result.message || 'Erreur', 'error');
        }
        deleteId = null;
        deleteSource = 'setting';
    })
    .catch(error => {
        console.error('Delete error:', error);
        showToast('Erreur de suppression', 'error');
    });
}

function resetForm() {
    document.getElementById('slideForm').reset();
    document.getElementById('slideId').value = '';
    document.getElementById('slideSourceKind').value = 'setting';
    document.getElementById('selectedMediaId').value = '';
    document.getElementById('selectedMediaPreview').style.display = 'none';
    getSlideField('externalUrl').value = '';
    document.getElementById('linkedMediaNotice').classList.add('d-none');
    document.querySelectorAll('#slideForm input[name="source"]').forEach(input => input.disabled = false);
    document.querySelectorAll('#slideForm input[name="type"]').forEach(input => input.disabled = false);
    document.getElementById('sourceUpload').checked = true;
    document.getElementById('typeImage').checked = true;
    clearFilePreview();
    handleTypeChange();
    handleSourceChange();
    setSliderStyleFields();
    deleteId = null;
    deleteSource = 'setting';
}

function appendSliderDesignFields(formData) {
    formData.append('title_size', document.getElementById('slideTitleSize')?.value || '48px');
    formData.append('title_color', document.getElementById('slideTitleColor')?.value || '#ffffff');
    formData.append('title_font', document.getElementById('slideTitleFont')?.value || 'inherit');
    formData.append('description_size', document.getElementById('slideDescriptionSize')?.value || '19px');
    formData.append('description_color', document.getElementById('slideDescriptionColor')?.value || '#ffffff');
    formData.append('description_font', document.getElementById('slideDescriptionFont')?.value || 'inherit');
    formData.append('button_size', document.getElementById('slideButtonSize')?.value || '16px');
    formData.append('button_color', document.getElementById('slideButtonColor')?.value || '#ffffff');
    formData.append('button_bg_color', document.getElementById('slideButtonBgColor')?.value || '#2563eb');
    formData.append('button_font', document.getElementById('slideButtonFont')?.value || 'inherit');
}

function setSliderStyleFields(item = {}) {
    const titleStyle = item.title_style || {};
    const descriptionStyle = item.description_style || {};
    const buttonStyle = item.button_style || {};

    setFieldValue('slideTitleSize', titleStyle.size || '48px');
    setFieldValue('slideTitleColor', titleStyle.color || '#ffffff');
    setFieldValue('slideTitleFont', titleStyle.font || 'inherit');
    setFieldValue('slideDescriptionSize', descriptionStyle.size || '19px');
    setFieldValue('slideDescriptionColor', descriptionStyle.color || '#ffffff');
    setFieldValue('slideDescriptionFont', descriptionStyle.font || 'inherit');
    setFieldValue('slideButtonSize', buttonStyle.size || '16px');
    setFieldValue('slideButtonColor', buttonStyle.color || '#ffffff');
    setFieldValue('slideButtonBgColor', buttonStyle.background_color || '#2563eb');
    setFieldValue('slideButtonFont', buttonStyle.font || 'inherit');
}

function setFieldValue(id, value) {
    const field = document.getElementById(id);
    if (field) {
        field.value = value;
    }
}

function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function showToast(message, type = 'success') {
    document.querySelectorAll('.toast-notification').forEach(t => t.remove());
    const toast = document.createElement('div');
    toast.className = `toast-notification ${type}`;
    toast.innerHTML = `<div class="toast-content"><i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i><span>${escapeHtml(message)}</span></div>`;
    document.body.appendChild(toast);
    setTimeout(() => toast.classList.add('show'), 100);
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

if (!document.querySelector('#slider-toast-styles')) {
    const style = document.createElement('style');
    style.id = 'slider-toast-styles';
    style.textContent = `
        .toast-notification { position: fixed; bottom: 20px; right: 20px; background: white; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); transform: translateX(400px); transition: transform 0.3s ease; z-index: 10000; min-width: 280px; }
        .toast-notification.show { transform: translateX(0); }
        .toast-content { padding: 16px 20px; display: flex; align-items: center; gap: 12px; border-left: 4px solid; border-radius: 12px; }
        .toast-notification.success .toast-content { border-left-color: #10b981; }
        .toast-notification.success i { color: #10b981; }
        .toast-notification.error .toast-content { border-left-color: #ef4444; }
        .toast-notification.error i { color: #ef4444; }
    `;
    document.head.appendChild(style);
}
</script>




