{{-- media-tab.blade.php --}}
<div class="tab-pane fade" id="v-pills-media" role="tabpanel" data-etablissement-id="{{ $stats['etablissement']->id ?? request()->route('etablissementId') ?? '' }}">
    <div class="tab-content-header">
        <h3 class="tab-title">
            <i class="fas fa-images me-2" style="color: #45b7d1;"></i>
            M&eacute;diath&egrave;que
        </h3>
        <button class="btn btn-primary btn-sm" id="uploadMediaBtn">
            <i class="fas fa-upload me-1"></i>Uploader
        </button>
    </div>

    <div class="media-stats mb-4">
        <div class="row">
            <div class="col-md-3">
                <div class="stat-mini-card">
                    <div class="stat-mini-value" id="totalMedia">0</div>
                    <div class="stat-mini-label">Total fichiers</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-mini-card">
                    <div class="stat-mini-value" id="totalImages">0</div>
                    <div class="stat-mini-label">Images</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-mini-card">
                    <div class="stat-mini-value" id="totalVideos">0</div>
                    <div class="stat-mini-label">Vid&eacute;os</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-mini-card">
                    <div class="stat-mini-value" id="totalDocuments">0</div>
                    <div class="stat-mini-label">Documents</div>
                </div>
            </div>
        </div>
    </div>

    <div class="media-filters mb-3">
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-outline-secondary active" data-filter="all">Tous</button>
            <button type="button" class="btn btn-outline-secondary" data-filter="image">Images</button>
            <button type="button" class="btn btn-outline-secondary" data-filter="video">Vid&eacute;os</button>
            <button type="button" class="btn btn-outline-secondary" data-filter="document">Documents</button>
        </div>
        <div class="float-end">
            <div class="input-group" style="width: 250px;">
                <input type="text" class="form-control form-control-sm" id="searchMedia" placeholder="Rechercher...">
                <button class="btn btn-outline-secondary btn-sm" type="button" id="searchMediaBtn">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th style="width: 40px">
                        <input type="checkbox" id="selectAllMedia">
                    </th>
                    <th style="width: 80px">Aper&ccedil;u</th>
                    <th>Nom</th>
                    <th>Type</th>
                    <th>Taille</th>
                    <th>Dimensions</th>
                    <th>Date</th>
                    <th style="width: 120px">Actions</th>
                </tr>
            </thead>
            <tbody id="mediaTableBody">
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                        <p class="mt-2">Chargement des m&eacute;dias...</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        <div class="d-flex justify-content-between align-items-center">
            <div id="bulkActions" style="display: none;">
                <button class="btn btn-sm btn-danger" id="bulkDeleteBtn">
                    <i class="fas fa-trash me-1"></i>Supprimer s&eacute;lectionn&eacute;s
                </button>
                <span id="selectedCount" class="ms-2 text-muted"></span>
            </div>
            <nav id="paginationNav" class="d-flex justify-content-end">
                <!-- Pagination will be inserted here -->
            </nav>
        </div>
    </div>
</div>

<!-- Upload Media Modal -->
<div class="modal fade" id="uploadMediaModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable media-upload-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-upload me-2"></i>Uploader un fichier
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="uploadMediaForm" class="media-upload-form" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="form-label">Mode d'ajout</label>
                        <div class="upload-source-switch" id="uploadSourceSwitch">
                            <label class="upload-source-option">
                                <input type="radio" name="upload_source" value="local" id="uploadSourceLocal">
                                <span>
                                    <i class="fas fa-cloud-upload-alt me-2"></i>Upload local
                                </span>
                            </label>
                            <label class="upload-source-option">
                                <input type="radio" name="upload_source" value="url" id="uploadSourceUrl">
                                <span>
                                    <i class="fas fa-link me-2"></i>Via URL
                                </span>
                            </label>
                        </div>
                    </div>

                    <div id="uploadLocalSection" class="d-none">
                        <div class="upload-area" id="uploadArea">
                            <div class="upload-icon">
                                <i class="fas fa-cloud-upload-alt fa-3x"></i>
                            </div>
                            <h4>Glissez votre fichier ici</h4>
                            <p>ou cliquez pour s&eacute;lectionner</p>
                            <input type="file" name="file" id="fileInput" accept="image/*,video/*,application/pdf,.doc,.docx,.xls,.xlsx" hidden>
                            <button type="button" class="btn btn-outline-primary" id="selectMediaFileBtn">
                                S&eacute;lectionner un fichier
                            </button>
                        </div>
                    </div>

                    <div id="uploadUrlSection" class="d-none">
                        <div class="alert alert-primary mb-3">
                            <i class="fas fa-info-circle me-2"></i>
                            Les URLs externes sont utilis&eacute;es pour les vid&eacute;os YouTube, Vimeo et liens similaires.
                        </div>
                        <div class="mb-3">
                            <label class="form-label">URL du m&eacute;dia</label>
                            <input type="url" class="form-control" name="video_url" id="mediaVideoUrl" placeholder="https://www.youtube.com/watch?v=...">
                        </div>
                    </div>

                    <div class="mt-3 d-none" id="fileInfo">
                        <div class="alert alert-info mb-3">
                            <i class="fas fa-file me-2"></i>
                            <span id="fileName"></span>
                        </div>
                        <div class="media-preview-box mb-3" id="mediaPreviewBox" style="display:none;">
                            <img id="mediaPreviewImage" alt="preview image" style="display:none;">
                            <video id="mediaPreviewVideo" controls muted playsinline style="display:none;"></video>
                        </div>
                    </div>

                    <div id="mediaCommonDetails" class="d-none">
                        <div class="mb-3">
                            <label class="form-label">Nom personnalis&eacute; (optionnel)</label>
                            <input type="text" class="form-control" name="name" id="mediaName" placeholder="Nom du fichier">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Dossier</label>
                            <select class="form-control" name="folder" id="mediaFolder">
                                <option value="/">Racine</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Type de m&eacute;dia</label>
                            <select class="form-control" name="type" id="mediaType">
                                <option value="image">Image</option>
                                <option value="video">Vid&eacute;o</option>
                            </select>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Afficher dans slider</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_slider" id="mediaIsSlider" value="1">
                                    <label class="form-check-label" for="mediaIsSlider">Oui</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Ordre</label>
                                <input type="number" min="0" class="form-control" name="order" id="mediaOrder" value="0">
                            </div>
                        </div>
                        <div class="slider-button-panel row g-3 mt-1 d-none" id="mediaSliderButtonFields">
                            <div class="col-md-6">
                                <label class="form-label">Titre du bouton</label>
                                <input type="text" class="form-control" name="button_text" id="mediaButtonText" placeholder="Ex: D&eacute;couvrir">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">URL du bouton</label>
                                <input type="url" class="form-control" name="button_url" id="mediaButtonUrl" placeholder="https://...">
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="form-label">Affectation galeries</label>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_main_gallery" id="mediaIsMainGallery" value="1">
                                        <label class="form-check-label" for="mediaIsMainGallery">Galerie principale</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_facebook_gallery" id="mediaIsFacebookGallery" value="1">
                                        <label class="form-check-label" for="mediaIsFacebookGallery">Galerie Facebook</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_instagram_gallery" id="mediaIsInstagramGallery" value="1">
                                        <label class="form-check-label" for="mediaIsInstagramGallery">Galerie Instagram</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_pinterest_gallery" id="mediaIsPinterestGallery" value="1">
                                        <label class="form-check-label" for="mediaIsPinterestGallery">Galerie Pinterest</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Continent</label>
                                <select class="form-control geo-search-select" name="continent_id" id="mediaContinentId" data-placeholder="Choisir un continent"><option value="">--</option></select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Pays</label>
                                <select class="form-control geo-search-select" name="country_id" id="mediaCountryId" data-placeholder="Choisir un pays"><option value="">--</option></select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Province</label>
                                <select class="form-control geo-search-select" name="province_id" id="mediaProvinceId" data-placeholder="Choisir une province"><option value="">--</option></select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">R&eacute;gion</label>
                                <select class="form-control geo-search-select" name="region_id" id="mediaRegionId" data-placeholder="Choisir une r&eacute;gion"><option value="">--</option></select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Ville</label>
                                <select class="form-control geo-search-select" name="ville_id" id="mediaVilleId" data-placeholder="Choisir une ville"><option value="">--</option></select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Secteur</label>
                                <select class="form-control geo-search-select" name="secteur_id" id="mediaSecteurId" data-placeholder="Choisir un secteur"><option value="">--</option></select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary" id="uploadSubmitBtn" disabled>Uploader</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Media Modal -->
<div class="modal fade" id="editMediaModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable media-upload-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>Modifier le m&eacute;dia
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editMediaForm" class="media-upload-form">
                @csrf
                @method('PUT')
                <input type="hidden" name="media_id" id="editMediaId">
                <div class="modal-body">
                    <div class="media-preview-box mb-3" id="editMediaPreviewBox" style="display:none;">
                        <img id="editMediaPreviewImage" alt="preview image" style="display:none;">
                        <video id="editMediaPreviewVideo" controls muted playsinline style="display:none;"></video>
                        <iframe id="editMediaPreviewFrame" title="preview video" style="display:none; width:100%; height:260px; border:0; border-radius:12px;" allow="autoplay; encrypted-media" allowfullscreen></iframe>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nom</label>
                        <input type="text" class="form-control" name="name" id="editMediaName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Texte alternatif (alt)</label>
                        <input type="text" class="form-control" name="alt" id="editMediaAlt" placeholder="Description pour le SEO">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Titre</label>
                        <input type="text" class="form-control" name="title" id="editMediaTitle" placeholder="Titre au survol">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="editMediaDescription" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Dossier</label>
                        <select class="form-control" name="folder" id="editMediaFolder">
                            <option value="/">Racine</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select class="form-control" name="type" id="editMediaType">
                            <option value="image">Image</option>
                            <option value="video">Vid&eacute;o</option>
                        </select>
                    </div>
                    <div class="mb-3 d-none" id="editMediaVideoUrlWrap">
                        <label class="form-label">URL vid&eacute;o externe</label>
                        <input type="url" class="form-control" name="video_url" id="editMediaVideoUrl" placeholder="https://...">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Afficher dans slider</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_slider" id="editMediaIsSlider" value="1">
                                <label class="form-check-label" for="editMediaIsSlider">Oui</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ordre</label>
                            <input type="number" min="0" class="form-control" name="order" id="editMediaOrder" value="0">
                        </div>
                    </div>
                    <div class="slider-button-panel row g-2 mb-3 d-none" id="editMediaSliderButtonFields">
                        <div class="col-md-6">
                            <label class="form-label">Titre du bouton</label>
                            <input type="text" class="form-control" name="button_text" id="editMediaButtonText" placeholder="Ex: D&eacute;couvrir">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">URL du bouton</label>
                            <input type="url" class="form-control" name="button_url" id="editMediaButtonUrl" placeholder="https://...">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Affectation galeries</label>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_main_gallery" id="editMediaIsMainGallery" value="1">
                                    <label class="form-check-label" for="editMediaIsMainGallery">Galerie principale</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_facebook_gallery" id="editMediaIsFacebookGallery" value="1">
                                    <label class="form-check-label" for="editMediaIsFacebookGallery">Galerie Facebook</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_instagram_gallery" id="editMediaIsInstagramGallery" value="1">
                                    <label class="form-check-label" for="editMediaIsInstagramGallery">Galerie Instagram</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_pinterest_gallery" id="editMediaIsPinterestGallery" value="1">
                                    <label class="form-check-label" for="editMediaIsPinterestGallery">Galerie Pinterest</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Continent</label>
                            <select class="form-control geo-search-select" name="continent_id" id="editMediaContinentId" data-placeholder="Choisir un continent"><option value="">--</option></select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Pays</label>
                            <select class="form-control geo-search-select" name="country_id" id="editMediaCountryId" data-placeholder="Choisir un pays"><option value="">--</option></select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Province</label>
                            <select class="form-control geo-search-select" name="province_id" id="editMediaProvinceId" data-placeholder="Choisir une province"><option value="">--</option></select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">R&eacute;gion</label>
                            <select class="form-control geo-search-select" name="region_id" id="editMediaRegionId" data-placeholder="Choisir une r&eacute;gion"><option value="">--</option></select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ville</label>
                            <select class="form-control geo-search-select" name="ville_id" id="editMediaVilleId" data-placeholder="Choisir une ville"><option value="">--</option></select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Secteur</label>
                            <select class="form-control geo-search-select" name="secteur_id" id="editMediaSecteurId" data-placeholder="Choisir un secteur"><option value="">--</option></select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Create Folder Modal -->
<div class="modal fade" id="createFolderModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-folder-plus me-2"></i>Cr&eacute;er un dossier
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createFolderForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nom du dossier</label>
                        <input type="text" class="form-control" name="folder_name" id="folderName" required placeholder="mon-dossier">
                        <small class="text-muted">Utilisez uniquement des lettres, chiffres et tirets</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Dossier parent</label>
                        <select class="form-control" name="parent" id="parentFolder">
                            <option value="/">Racine</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Cr&eacute;er</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteMediaModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="delete-icon">
                    <i class="fas fa-exclamation-triangle fa-3x text-danger"></i>
                </div>
                <h4>Supprimer le fichier</h4>
                <p class="text-muted">&Ecirc;tes-vous s&ucirc;r de vouloir supprimer ce fichier ?<br>Cette action est irr&eacute;versible.</p>
                <div class="mt-4">
                    <button class="btn btn-secondary me-2" data-bs-dismiss="modal">Annuler</button>
                    <button class="btn btn-danger" id="confirmDeleteMediaBtn">Supprimer</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.media-stats .stat-mini-card {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 20px;
    text-align: center;
    transition: all 0.3s ease;
}

.media-stats .stat-mini-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.media-filters .btn-group {
    margin-bottom: 20px;
}

.media-filters .btn {
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

.media-preview {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    overflow: hidden;
    background: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
}

.media-preview img,
.media-preview video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.media-preview .file-icon {
    font-size: 24px;
    color: #6b7280;
}

.type-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.7rem;
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

.type-badge.document {
    background: #fef3c7;
    color: #92400e;
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

.upload-source-switch {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
}

.upload-source-option {
    margin: 0;
    cursor: pointer;
}

.upload-source-option input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}

.upload-source-option span {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 52px;
    padding: 12px 16px;
    border: 1px solid #dbe4f0;
    border-radius: 12px;
    background: #fff;
    color: #0f172a;
    font-weight: 600;
    transition: all 0.2s ease;
}

.upload-source-option input:checked + span {
    border-color: #2563eb;
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    box-shadow: 0 0 0 0.18rem rgba(37, 99, 235, 0.14);
}

.upload-source-option:hover span {
    border-color: #93c5fd;
    transform: translateY(-1px);
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

.upload-icon i {
    color: #94a3b8;
    margin-bottom: 16px;
}

.delete-icon {
    margin-bottom: 20px;
}

.folder-breadcrumb {
    background: #f8f9fa;
    padding: 10px 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.folder-breadcrumb .breadcrumb-item {
    cursor: pointer;
}

.folder-breadcrumb .breadcrumb-item:hover {
    color: #4361ee;
    text-decoration: underline;
}

.current-folder {
    font-weight: 600;
    color: #4361ee;
}

.media-upload-modal .modal-content {
    display: flex;
    flex-direction: column;
    border: 0;
    border-radius: 18px;
    box-shadow: 0 20px 60px rgba(15, 23, 42, 0.2);
    max-height: calc(100vh - 2rem);
}

.media-upload-modal {
    max-width: 760px;
}

.media-upload-form {
    display: flex;
    flex: 1 1 auto;
    flex-direction: column;
    min-height: 0;
}

.media-upload-modal .modal-header {
    background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%);
    color: #fff;
    border-bottom: 0;
    border-radius: 18px 18px 0 0;
}

.media-upload-modal .modal-header .btn-close {
    filter: brightness(0) invert(1);
}

.media-upload-modal .modal-body {
    background: #f8fafc;
    flex: 1 1 auto;
    min-height: 0;
    overflow-y: auto;
    overscroll-behavior: contain;
    -webkit-overflow-scrolling: touch;
}

.media-upload-modal .modal-footer {
    flex-shrink: 0;
}

.media-upload-modal .form-label {
    font-weight: 600;
    color: #0f172a;
}

.media-upload-modal .form-control,
.media-upload-modal .form-select {
    border-radius: 10px;
    border: 1px solid #dbe4f0;
}

.media-upload-modal .form-control:disabled,
.media-upload-modal .form-select:disabled {
    background: #eef2f7;
    cursor: not-allowed;
}

.media-upload-modal .form-control:focus,
.media-upload-modal .form-select:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.15);
}

.slider-button-panel {
    background: linear-gradient(135deg, #f8fbff 0%, #eef5ff 100%);
    border: 1px solid rgba(67, 97, 238, 0.12);
    border-radius: 14px;
    padding: 16px;
}

.slider-button-panel .form-label {
    font-weight: 600;
}

.media-preview-box {
    border: 1px solid #dbe4f0;
    border-radius: 12px;
    padding: 8px;
    background: #fff;
}

.media-preview-box img,
.media-preview-box video {
    width: 100%;
    max-height: 240px;
    border-radius: 8px;
    object-fit: contain;
    background: #0f172a;
}

.select2-container--default .select2-selection--single {
    height: 38px;
    border: 1px solid #dbe4f0;
    border-radius: 10px;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 36px;
    padding-left: 12px;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px;
}
</style>

<script>
// Initialisation
const mediaEtablissementId = @json((string) ($stats['etablissement']->id ?? request()->route('etablissementId') ?? ''));
var currentEtablissementId = mediaEtablissementId || window.currentEtablissementId || null;
let currentPage = 1;
let currentFilter = 'all';
let currentSearch = '';
let currentFolder = '/';
let currentDeleteId = null;
let mediaItems = [];
let totalPages = 1;
let foldersCache = [];
let locationsCache = {
    continents: [],
    countries: [],
    provinces: [],
    regions: [],
    villes: [],
    secteurs: []
};

document.addEventListener('DOMContentLoaded', function() {
    ensureCurrentEtablissementId();
    loadMedia();
    loadFolders();
    initEventListeners();
    initLocationCascade();
    ensureSelect2Assets().then(() => {
        initGeoSelect2();
    }).catch(() => {
        console.warn('Select2 non charg\u00e9, fallback natif activ\u00e9.');
    });
    const mediaType = document.getElementById('mediaType');
    const editMediaType = document.getElementById('editMediaType');
    if (mediaType) {
        mediaType.addEventListener('change', () => {
            syncUploadSourceState();
            updateUploadSubmitState();
        });
    }
    if (editMediaType) {
        editMediaType.addEventListener('change', () => toggleVideoUrlField('editMediaType', 'editMediaVideoUrlWrap'));
        toggleVideoUrlField('editMediaType', 'editMediaVideoUrlWrap');
    }
    document.querySelectorAll('input[name="upload_source"]').forEach(input => {
        input.addEventListener('change', () => {
            syncUploadSourceState();
            updateUploadSubmitState();
        });
    });
    const mediaVideoUrl = document.getElementById('mediaVideoUrl');
    if (mediaVideoUrl) {
        mediaVideoUrl.addEventListener('input', () => {
            syncUploadSourceState();
            updateUploadSubmitState();
        });
    }
    const mediaIsSlider = document.getElementById('mediaIsSlider');
    const editMediaIsSlider = document.getElementById('editMediaIsSlider');
    if (mediaIsSlider) {
        mediaIsSlider.addEventListener('change', () => toggleSliderButtonFields('mediaIsSlider', 'mediaSliderButtonFields'));
        toggleSliderButtonFields('mediaIsSlider', 'mediaSliderButtonFields');
    }
    if (editMediaIsSlider) {
        editMediaIsSlider.addEventListener('change', () => toggleSliderButtonFields('editMediaIsSlider', 'editMediaSliderButtonFields'));
        toggleSliderButtonFields('editMediaIsSlider', 'editMediaSliderButtonFields');
    }

    // Fix modal lock issue (body remains non-clickable after close)
    const uploadModalEl = document.getElementById('uploadMediaModal');
    if (uploadModalEl) {
        uploadModalEl.addEventListener('hidden.bs.modal', () => {
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('padding-right');
            document.body.style.removeProperty('overflow');
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
                window.jQuery('.geo-search-select').select2('close');
            }
        });
    }
    const editModalEl = document.getElementById('editMediaModal');
    if (editModalEl) {
        editModalEl.addEventListener('hidden.bs.modal', () => {
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('padding-right');
            document.body.style.removeProperty('overflow');
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            resetEditPreview();
            if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
                window.jQuery('.geo-search-select').select2('close');
            }
        });
    }

    syncUploadSourceState();
    updateUploadSubmitState();
});

async function parseJsonResponse(response) {
    const contentType = response.headers.get('content-type') || '';
    if (!contentType.includes('application/json')) {
        const text = await response.text();
        throw new Error(`R\u00e9ponse non JSON (${response.status}). D\u00e9but: ${text.slice(0, 120)}`);
    }
    return response.json();
}
function ensureCurrentEtablissementId() {
    if (currentEtablissementId) {
        window.currentEtablissementId = currentEtablissementId;
        return currentEtablissementId;
    }
    const mediaPane = document.getElementById('v-pills-media');
    const paneId = mediaPane?.dataset?.etablissementId;
    if (paneId) {
        currentEtablissementId = String(paneId);
        window.currentEtablissementId = currentEtablissementId;
        return currentEtablissementId;
    }
    const meta = document.querySelector('meta[name="etablissement-id"]');
    if (meta && meta.content) {
        currentEtablissementId = String(meta.content);
        window.currentEtablissementId = currentEtablissementId;
        return currentEtablissementId;
    }
    const match = window.location.pathname.match(/\/admin\/cms\/(\d+)/);
    currentEtablissementId = match ? String(match[1]) : null;
    window.currentEtablissementId = currentEtablissementId;
    return currentEtablissementId;
}

function getCurrentEtablissementId() {
    return ensureCurrentEtablissementId();
}

function requireCurrentEtablissementId() {
    const etablissementId = getCurrentEtablissementId();
    if (!etablissementId) {
        showToast('Erreur: établissement non défini', 'error');
        throw new Error('currentEtablissementId is not defined');
    }
    return etablissementId;
}

function toggleVideoUrlField(typeFieldId, wrapId) {
    const typeEl = document.getElementById(typeFieldId);
    const wrap = document.getElementById(wrapId);
    if (!typeEl || !wrap) return;
    wrap.classList.toggle('d-none', typeEl.value !== 'video');
}

function toggleSliderButtonFields(checkboxId, wrapId) {
    const checkbox = document.getElementById(checkboxId);
    const wrap = document.getElementById(wrapId);
    if (!checkbox || !wrap) return;
    wrap.classList.toggle('d-none', !checkbox.checked);
}

function getSelectedUploadSource() {
    return document.querySelector('input[name="upload_source"]:checked')?.value || '';
}

function syncUploadSourceState() {
    const source = getSelectedUploadSource();
    const localSection = document.getElementById('uploadLocalSection');
    const urlSection = document.getElementById('uploadUrlSection');
    const detailsSection = document.getElementById('mediaCommonDetails');
    const fileInfo = document.getElementById('fileInfo');
    const mediaType = document.getElementById('mediaType');
    const file = document.getElementById('fileInput')?.files?.[0];

    if (localSection) {
        localSection.classList.toggle('d-none', source !== 'local');
    }
    if (urlSection) {
        urlSection.classList.toggle('d-none', source !== 'url');
    }
    if (detailsSection) {
        detailsSection.classList.toggle('d-none', !source);
    }

    if (mediaType) {
        if (source === 'url') {
            mediaType.value = 'video';
            mediaType.setAttribute('disabled', 'disabled');
        } else {
            mediaType.removeAttribute('disabled');
        }
    }

    if (fileInfo) {
        if (source === 'local' && file) {
            fileInfo.style.display = 'block';
        } else if (source !== 'local') {
            fileInfo.style.display = 'none';
        }
    }
}

function updateUploadSubmitState() {
    const submitBtn = document.getElementById('uploadSubmitBtn');
    if (!submitBtn) return;

    const source = getSelectedUploadSource();
    const file = document.getElementById('fileInput')?.files?.[0];
    const videoUrl = (document.getElementById('mediaVideoUrl')?.value || '').trim();

    if (source === 'local') {
        submitBtn.disabled = !file;
        return;
    }

    if (source === 'url') {
        submitBtn.disabled = videoUrl === '';
        return;
    }

    submitBtn.disabled = true;
}

function initEventListeners() {
    // Upload button
    const uploadBtn = document.getElementById('uploadMediaBtn');
    if (uploadBtn) {
        uploadBtn.addEventListener('click', () => {
            resetUploadForm();
            new bootstrap.Modal(document.getElementById('uploadMediaModal')).show();
        });
    }
    
    // File upload handling
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('fileInput');
    const selectFileBtn = document.getElementById('selectMediaFileBtn');
    
    if (uploadArea) {
        uploadArea.addEventListener('click', (e) => {
            if (e.target.closest('button, input')) return;
            fileInput?.click();
        });
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('drag-over');
        });
        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('drag-over');
        });
        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('drag-over');
            if (e.dataTransfer.files.length > 0) {
                handleFileSelect(e.dataTransfer.files[0]);
            }
        });
    }

    if (selectFileBtn) {
        selectFileBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            fileInput?.click();
        });
    }
    
    if (fileInput) {
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                handleFileSelect(e.target.files[0]);
            }
        });
    }
    
    // Upload form submission
    const uploadForm = document.getElementById('uploadMediaForm');
    if (uploadForm) {
        uploadForm.addEventListener('submit', uploadMedia);
    }
    
    // Edit form submission
    const editForm = document.getElementById('editMediaForm');
    if (editForm) {
        editForm.addEventListener('submit', updateMedia);
    }
    
    // Create folder form
    const folderForm = document.getElementById('createFolderForm');
    if (folderForm) {
        folderForm.addEventListener('submit', createFolder);
    }
    
    // Delete confirmation
    const confirmDeleteBtn = document.getElementById('confirmDeleteMediaBtn');
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', confirmDeleteMedia);
    }
    
    // Filter buttons
    document.querySelectorAll('[data-filter]').forEach(button => {
        button.addEventListener('click', function() {
            document.querySelectorAll('[data-filter]').forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            currentFilter = this.getAttribute('data-filter');
            currentPage = 1;
            loadMedia();
        });
    });
    
    // Search
    const searchBtn = document.getElementById('searchMediaBtn');
    if (searchBtn) {
        searchBtn.addEventListener('click', () => {
            currentSearch = document.getElementById('searchMedia').value;
            currentPage = 1;
            loadMedia();
        });
    }
    
    const searchInput = document.getElementById('searchMedia');
    if (searchInput) {
        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                currentSearch = searchInput.value;
                currentPage = 1;
                loadMedia();
            }
        });
    }
    
    // Select all
    const selectAll = document.getElementById('selectAllMedia');
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            document.querySelectorAll('.media-checkbox').forEach(cb => {
                cb.checked = this.checked;
            });
            updateBulkActions();
        });
    }
    
    // Bulk delete
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    if (bulkDeleteBtn) {
        bulkDeleteBtn.addEventListener('click', bulkDeleteMedia);
    }
}

function loadMedia() {
    const tbody = document.getElementById('mediaTableBody');
    tbody.innerHTML = '<tr><td colspan="8" class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2">Chargement...</p></td></tr>';

    let etablissementId;
    try {
        etablissementId = requireCurrentEtablissementId();
    } catch (error) {
        console.error('Media load error:', error);
        tbody.innerHTML = '<tr><td colspan="8" class="text-center py-5 text-danger"><i class="fas fa-exclamation-circle fa-2x"></i><p>Établissement non défini</p></td></tr>';
        return;
    }

    let url = `/admin/cms/${etablissementId}/media?page=${currentPage}&folder=${encodeURIComponent(currentFolder)}`;
    if (currentFilter !== 'all') url += `&type=${currentFilter}`;
    if (currentSearch) url += `&search=${encodeURIComponent(currentSearch)}`;
    
    fetch(url, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(parseJsonResponse)
        .then(result => {
            if (result.success) {
                mediaItems = result.data.data || result.data || [];
                totalPages = result.data.last_page || result.last_page || 1;
                foldersCache = (result.stats && result.stats.folders) ? result.stats.folders : [];
                setLocationsCache(result.locations || {});
                syncRootLocationSelects();
                renderMediaTable(mediaItems);
                updateStats(result.stats || {});
                renderPagination();
                syncFoldersSelects();
            } else {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center py-5 text-danger"><i class="fas fa-exclamation-circle fa-2x"></i><p>Erreur de chargement</p></td></tr>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            tbody.innerHTML = '<tr><td colspan="8" class="text-center py-5 text-danger"><i class="fas fa-exclamation-circle fa-2x"></i><p>Erreur de chargement</p></td></tr>';
        });
}

function loadFolders() {
    syncFoldersSelects();
}

function syncFoldersSelects() {
    const folderSelects = document.querySelectorAll('#mediaFolder, #editMediaFolder, #parentFolder');
    folderSelects.forEach(select => {
        const currentValue = select.value;
        select.innerHTML = '<option value="/">Racine</option>' + 
            foldersCache.map(f => `<option value="${escapeHtml(f)}">${escapeHtml(f)}</option>`).join('');
        select.value = currentValue || '/';
    });
}

async function fetchLocationLevel(level, params = {}) {
    const etablissementId = requireCurrentEtablissementId();
    const query = new URLSearchParams(params).toString();
    const url = `/admin/cms/${etablissementId}/media/locations/${level}${query ? `?${query}` : ''}`;
    const response = await fetch(url, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    });
    const result = await parseJsonResponse(response);
    if (!result.success) return [];
    return result.data || [];
}

function fillSelect(selectId, items, placeholder = '--') {
    const el = document.getElementById(selectId);
    if (!el) return;
    el.innerHTML = `<option value="">${placeholder}</option>` + items.map(item =>
        `<option value="${item.id}">${escapeHtml(item.name || '')}</option>`
    ).join('');
    refreshSelect2Element(el);
}

function ensureSelect2Assets() {
    return new Promise((resolve, reject) => {
        if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
            resolve();
            return;
        }

        const cssId = 'select2-css-cdn';
        if (!document.getElementById(cssId)) {
            const link = document.createElement('link');
            link.id = cssId;
            link.rel = 'stylesheet';
            link.href = 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css';
            document.head.appendChild(link);
        }

        const scriptId = 'select2-js-cdn';
        if (document.getElementById(scriptId)) {
            setTimeout(() => {
                if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) resolve();
                else reject();
            }, 400);
            return;
        }

        const script = document.createElement('script');
        script.id = scriptId;
        script.src = 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js';
        script.onload = () => resolve();
        script.onerror = () => reject();
        document.body.appendChild(script);
    });
}

function initGeoSelect2() {
    if (!(window.jQuery && window.jQuery.fn && window.jQuery.fn.select2)) return;
    window.jQuery('.geo-search-select').each(function() {
        refreshSelect2Element(this);
    });
}

function refreshSelect2Element(el) {
    if (!(window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) || !el) return;
    const placeholder = el.dataset.placeholder || 'Rechercher...';
    const $el = window.jQuery(el);
    const $modal = $el.closest('.modal');
    if ($el.hasClass('select2-hidden-accessible')) {
        $el.select2('destroy');
    }
    $el.select2({
        placeholder,
        allowClear: true,
        width: '100%',
        dropdownParent: $modal.length ? $modal : window.jQuery(document.body)
    });
}

function setLocationsCache(locations = {}) {
    locationsCache = {
        continents: Array.isArray(locations.continents) ? locations.continents : [],
        countries: Array.isArray(locations.countries) ? locations.countries : [],
        provinces: Array.isArray(locations.provinces) ? locations.provinces : [],
        regions: Array.isArray(locations.regions) ? locations.regions : [],
        villes: Array.isArray(locations.villes) ? locations.villes : [],
        secteurs: Array.isArray(locations.secteurs) ? locations.secteurs : [],
    };
}

async function getLocationItems(level, params = {}) {
    const cached = Array.isArray(locationsCache[level]) ? locationsCache[level] : [];
    if (cached.length > 0) {
        switch (level) {
            case 'countries':
                return params.continent_id ? cached.filter(item => String(item.continent_id) === String(params.continent_id)) : cached;
            case 'provinces':
                return params.country_id ? cached.filter(item => String(item.country_id) === String(params.country_id)) : cached;
            case 'regions':
                return params.province_id ? cached.filter(item => String(item.province_id) === String(params.province_id)) : cached;
            case 'villes':
                return params.region_id ? cached.filter(item => String(item.region_id) === String(params.region_id)) : cached;
            case 'secteurs':
                return params.region_id ? cached.filter(item => String(item.region_id) === String(params.region_id)) : cached;
            default:
                return cached;
        }
    }

    return fetchLocationLevel(level, params);
}

function clearLocationChain(fromLevel, map) {
    const order = ['continent', 'country', 'province', 'region', 'ville', 'secteur'];
    const start = order.indexOf(fromLevel);
    for (let i = start + 1; i < order.length; i++) {
        fillSelect(map[order[i]], []);
    }
}

function getLocationMap(mode) {
    if (mode === 'edit') {
        return {
            continent: 'editMediaContinentId',
            country: 'editMediaCountryId',
            province: 'editMediaProvinceId',
            region: 'editMediaRegionId',
            ville: 'editMediaVilleId',
            secteur: 'editMediaSecteurId',
        };
    }

    return {
        continent: 'mediaContinentId',
        country: 'mediaCountryId',
        province: 'mediaProvinceId',
        region: 'mediaRegionId',
        ville: 'mediaVilleId',
        secteur: 'mediaSecteurId',
    };
}

function syncRootLocationSelects() {
    if (!locationsCache.continents.length) return;
    ['create', 'edit'].forEach(mode => {
        const map = getLocationMap(mode);
        const continentEl = document.getElementById(map.continent);
        if (!continentEl) return;
        const currentValue = continentEl.value || '';
        fillSelect(map.continent, locationsCache.continents);
        continentEl.value = currentValue;
        if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
            window.jQuery(`#${map.continent}`).trigger('change.select2');
        }
    });
}

function initSingleLocationCascade(map) {
    const continentEl = document.getElementById(map.continent);
    const countryEl = document.getElementById(map.country);
    const provinceEl = document.getElementById(map.province);
    const regionEl = document.getElementById(map.region);

    if (!continentEl) return;

    getLocationItems('continents').then(items => fillSelect(map.continent, items));
    fillSelect(map.country, []);
    fillSelect(map.province, []);
    fillSelect(map.region, []);
    fillSelect(map.ville, []);
    fillSelect(map.secteur, []);

    continentEl.addEventListener('change', async () => {
        clearLocationChain('continent', map);
        if (!continentEl.value) return;
        const countries = await getLocationItems('countries', { continent_id: continentEl.value });
        fillSelect(map.country, countries);
    });

    countryEl?.addEventListener('change', async () => {
        clearLocationChain('country', map);
        if (!countryEl.value) return;
        const provinces = await getLocationItems('provinces', { country_id: countryEl.value });
        fillSelect(map.province, provinces);
    });

    provinceEl?.addEventListener('change', async () => {
        clearLocationChain('province', map);
        if (!provinceEl.value) return;
        const regions = await getLocationItems('regions', { province_id: provinceEl.value });
        fillSelect(map.region, regions);
    });

    regionEl?.addEventListener('change', async () => {
        clearLocationChain('region', map);
        if (!regionEl.value) return;
        const [villes, secteurs] = await Promise.all([
            getLocationItems('villes', { region_id: regionEl.value }),
            getLocationItems('secteurs', { region_id: regionEl.value }),
        ]);
        fillSelect(map.ville, villes);
        fillSelect(map.secteur, secteurs);
    });
}

function initLocationCascade() {
    initSingleLocationCascade(getLocationMap('create'));
    initSingleLocationCascade(getLocationMap('edit'));
}

async function populateLocationFields(mode, media) {
    const map = getLocationMap(mode);
    const values = {
        continent: media.continent_id || '',
        country: media.country_id || '',
        province: media.province_id || '',
        region: media.region_id || '',
        ville: media.ville_id || '',
        secteur: media.secteur_id || '',
    };

    const continents = await getLocationItems('continents');
    fillSelect(map.continent, continents);
    document.getElementById(map.continent).value = values.continent;
    if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) window.jQuery(`#${map.continent}`).trigger('change.select2');

    if (values.continent) {
        const countries = await getLocationItems('countries', { continent_id: values.continent });
        fillSelect(map.country, countries);
        document.getElementById(map.country).value = values.country;
        if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) window.jQuery(`#${map.country}`).trigger('change.select2');
    } else {
        clearLocationChain('continent', map);
        return;
    }

    if (values.country) {
        const provinces = await getLocationItems('provinces', { country_id: values.country });
        fillSelect(map.province, provinces);
        document.getElementById(map.province).value = values.province;
        if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) window.jQuery(`#${map.province}`).trigger('change.select2');
    } else {
        clearLocationChain('country', map);
        return;
    }

    if (values.province) {
        const regions = await getLocationItems('regions', { province_id: values.province });
        fillSelect(map.region, regions);
        document.getElementById(map.region).value = values.region;
        if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) window.jQuery(`#${map.region}`).trigger('change.select2');
    } else {
        clearLocationChain('province', map);
        return;
    }

    if (values.region) {
        const [villes, secteurs] = await Promise.all([
            getLocationItems('villes', { region_id: values.region }),
            getLocationItems('secteurs', { region_id: values.region }),
        ]);
        fillSelect(map.ville, villes);
        fillSelect(map.secteur, secteurs);
        document.getElementById(map.ville).value = values.ville;
        document.getElementById(map.secteur).value = values.secteur;
        if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
            window.jQuery(`#${map.ville}`).trigger('change.select2');
            window.jQuery(`#${map.secteur}`).trigger('change.select2');
        }
    } else {
        clearLocationChain('region', map);
    }
}

function renderMediaTable(items) {
    const tbody = document.getElementById('mediaTableBody');
    
    if (!items || items.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center py-5"><i class="fas fa-folder-open fa-3x text-muted mb-3"></i><p>Aucun m&eacute;dia trouv&eacute;</p><button class="btn btn-primary btn-sm" onclick="document.getElementById(\'uploadMediaBtn\').click()">Uploader</button></td></tr>';
        return;
    }
    
    tbody.innerHTML = items.map(media => {
        const assetUrl = getMediaAssetUrl(media);
        const previewHtml = media.type === 'image'
            ? (assetUrl ? `<img src="${escapeHtml(assetUrl)}" alt="${escapeHtml(media.name)}">` : `<div class="file-icon"><i class="fas fa-image fa-2x"></i></div>`)
            : media.type === 'video'
                ? (media.video_url ? `<i class="fab fa-youtube fa-2x text-danger"></i>` : (assetUrl ? `<video src="${escapeHtml(assetUrl)}"></video>` : `<div class="file-icon"><i class="fas fa-video fa-2x"></i></div>`))
                : `<div class="file-icon"><i class="fas ${media.icon || 'fa-file'} fa-2x"></i></div>`;

        return `
        <tr data-id="${media.id}">
            <td>
                <input type="checkbox" class="media-checkbox" data-id="${media.id}">
            </td>
            <td>
                <div class="media-preview">
                    ${previewHtml}
                </div>
            </td>
            <td>
                <strong>${escapeHtml(media.name)}</strong>
                ${media.alt ? `<br><small class="text-muted">Alt: ${escapeHtml(media.alt)}</small>` : ''}
                ${media.is_slider ? `<br><small class="text-success">Slider #${media.order ?? 0}</small>` : ''}
                ${media.is_slider && media.button_text ? `<br><small class="text-primary">Bouton: ${escapeHtml(media.button_text)}</small>` : ''}
                ${media.is_main_gallery ? `<br><small class="text-info">Galerie principale</small>` : ''}
                ${media.is_facebook_gallery ? `<br><small class="text-info">Galerie Facebook</small>` : ''}
                ${media.is_instagram_gallery ? `<br><small class="text-info">Galerie Instagram</small>` : ''}
                ${media.is_pinterest_gallery ? `<br><small class="text-info">Galerie Pinterest</small>` : ''}
            </td>
            <td><span class="type-badge ${media.type}">${media.type === 'image' ? 'Image' : media.type === 'video' ? 'Vid&eacute;o' : 'Document'}</span></td>
            <td>${media.formatted_size || media.size}</td>
            <td>${media.width && media.height ? `${media.width}x${media.height}` : '-'}</td>
            <td><small>${new Date(media.created_at).toLocaleDateString('fr-FR')}</small></td>
            <td>
                <button class="btn btn-sm btn-outline-primary btn-icon copy-url" data-url="${escapeHtml(assetUrl)}" title="Copier l'URL"><i class="fas fa-copy"></i></button>
                <button class="btn btn-sm btn-outline-secondary btn-icon edit-media" data-id="${media.id}" title="Modifier"><i class="fas fa-edit"></i></button>
                <button class="btn btn-sm btn-outline-danger btn-icon delete-media" data-id="${media.id}" title="Supprimer"><i class="fas fa-trash"></i></button>
            </td>
        </tr>
    `;
    }).join('');
    
    // Attach events
    document.querySelectorAll('.copy-url').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            navigator.clipboard.writeText(btn.dataset.url);
            showToast('URL copi\u00e9e', 'success');
        });
    });
    
    document.querySelectorAll('.edit-media').forEach(btn => {
        btn.addEventListener('click', () => editMedia(btn.dataset.id));
    });
    
    document.querySelectorAll('.delete-media').forEach(btn => {
        btn.addEventListener('click', () => {
            currentDeleteId = btn.dataset.id;
            new bootstrap.Modal(document.getElementById('deleteMediaModal')).show();
        });
    });
    
    document.querySelectorAll('.media-checkbox').forEach(cb => {
        cb.addEventListener('change', updateBulkActions);
    });
    
    updateBulkActions();
}

function renderPagination() {
    const paginationNav = document.getElementById('paginationNav');
    if (!paginationNav || totalPages <= 1) {
        paginationNav.innerHTML = '';
        return;
    }
    
    let html = '<ul class="pagination pagination-sm">';
    
    // Previous
    html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
        <a class="page-link" href="#" data-page="${currentPage - 1}">&laquo;</a>
    </li>`;
    
    // Page numbers
    let startPage = Math.max(1, currentPage - 2);
    let endPage = Math.min(totalPages, currentPage + 2);
    
    if (startPage > 1) {
        html += `<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`;
        if (startPage > 2) html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
    }
    
    for (let i = startPage; i <= endPage; i++) {
        html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
            <a class="page-link" href="#" data-page="${i}">${i}</a>
        </li>`;
    }
    
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        html += `<li class="page-item"><a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a></li>`;
    }
    
    // Next
    html += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
        <a class="page-link" href="#" data-page="${currentPage + 1}">&raquo;</a>
    </li>`;
    
    html += '</ul>';
    paginationNav.innerHTML = html;
    
    paginationNav.querySelectorAll('.page-link').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const page = parseInt(link.dataset.page);
            if (page && !isNaN(page) && page !== currentPage && page >= 1 && page <= totalPages) {
                currentPage = page;
                loadMedia();
            }
        });
    });
}

function updateStats(stats) {
    document.getElementById('totalMedia').textContent = stats.total || stats.media_count || 0;
    document.getElementById('totalImages').textContent = stats.images || stats.images_count || 0;
    document.getElementById('totalVideos').textContent = stats.videos || 0;
    document.getElementById('totalDocuments').textContent = stats.documents || stats.documents_count || 0;
}

function updateBulkActions() {
    const checked = document.querySelectorAll('.media-checkbox:checked').length;
    const bulkActions = document.getElementById('bulkActions');
    const selectedCount = document.getElementById('selectedCount');
    
    if (checked > 0) {
        bulkActions.style.display = 'block';
        selectedCount.textContent = `${checked} s\u00e9lectionn\u00e9(s)`;
    } else {
        bulkActions.style.display = 'none';
    }
}

function handleFileSelect(file) {
    document.getElementById('fileName').textContent = file.name;
    document.getElementById('fileInfo').style.display = 'block';
    updateUploadSubmitState();
    
    const name = file.name.replace(/\.[^/.]+$/, '');
    document.getElementById('mediaName').value = name;

    const box = document.getElementById('mediaPreviewBox');
    const img = document.getElementById('mediaPreviewImage');
    const vid = document.getElementById('mediaPreviewVideo');
    if (!box || !img || !vid) return;

    img.style.display = 'none';
    vid.style.display = 'none';
    img.removeAttribute('src');
    vid.removeAttribute('src');
    box.style.display = 'none';

    const objectUrl = URL.createObjectURL(file);
    if (file.type.startsWith('image/')) {
        img.src = objectUrl;
        img.style.display = 'block';
        box.style.display = 'block';
    } else if (file.type.startsWith('video/')) {
        vid.src = objectUrl;
        vid.style.display = 'block';
        box.style.display = 'block';
    }
}

function resetUploadForm() {
    document.getElementById('uploadMediaForm').reset();
    document.getElementById('uploadSourceLocal').checked = false;
    document.getElementById('uploadSourceUrl').checked = false;
    document.getElementById('fileInfo').style.display = 'none';
    document.getElementById('uploadSubmitBtn').disabled = true;
    document.getElementById('fileInput').value = '';
    const box = document.getElementById('mediaPreviewBox');
    const img = document.getElementById('mediaPreviewImage');
    const vid = document.getElementById('mediaPreviewVideo');
    if (img) img.removeAttribute('src');
    if (vid) {
        vid.pause();
        vid.removeAttribute('src');
        vid.load();
    }
    if (box) box.style.display = 'none';
    syncUploadSourceState();
    toggleSliderButtonFields('mediaIsSlider', 'mediaSliderButtonFields');
    updateUploadSubmitState();
}

function resetEditPreview() {
    const box = document.getElementById('editMediaPreviewBox');
    const img = document.getElementById('editMediaPreviewImage');
    const video = document.getElementById('editMediaPreviewVideo');
    const frame = document.getElementById('editMediaPreviewFrame');

    if (img) {
        img.removeAttribute('src');
        img.style.display = 'none';
    }
    if (video) {
        video.pause();
        video.removeAttribute('src');
        video.load();
        video.style.display = 'none';
    }
    if (frame) {
        frame.removeAttribute('src');
        frame.style.display = 'none';
    }
    if (box) {
        box.style.display = 'none';
    }
}

function getVideoEmbedUrl(url) {
    if (!url) return '';
    try {
        const parsed = new URL(url);
        if (parsed.hostname.includes('youtube.com')) {
            const id = parsed.searchParams.get('v');
            return id ? `https://www.youtube.com/embed/${id}` : '';
        }
        if (parsed.hostname.includes('youtu.be')) {
            const id = parsed.pathname.replace('/', '').trim();
            return id ? `https://www.youtube.com/embed/${id}` : '';
        }
        if (parsed.hostname.includes('vimeo.com')) {
            const id = parsed.pathname.split('/').filter(Boolean).pop();
            return id ? `https://player.vimeo.com/video/${id}` : '';
        }
    } catch (error) {
        console.warn('URL vid\u00e9o invalide pour preview', error);
    }
    return '';
}

function getMediaAssetUrl(media) {
    if (!media) return '';

    const candidate = String(media.path || media.url || '').trim();
    if (!candidate) return '';

    if (/^https?:\/\//i.test(candidate)) {
        return candidate;
    }

    if (candidate.startsWith('/')) {
        return candidate;
    }

    if (candidate.startsWith('storage/')) {
        return `/${candidate}`;
    }

    return `/storage/${candidate.replace(/^\/+/, '')}`;
}

function setEditPreview(media) {
    const box = document.getElementById('editMediaPreviewBox');
    const img = document.getElementById('editMediaPreviewImage');
    const video = document.getElementById('editMediaPreviewVideo');
    const frame = document.getElementById('editMediaPreviewFrame');

    resetEditPreview();
    if (!box || !media) return;

    const assetUrl = getMediaAssetUrl(media);

    if (media.type === 'image' && assetUrl) {
        img.src = assetUrl;
        img.style.display = 'block';
        box.style.display = 'block';
        return;
    }

    if (media.type === 'video') {
        const embedUrl = getVideoEmbedUrl(media.video_url || '');
        if (embedUrl) {
            frame.src = `${embedUrl}${embedUrl.includes('?') ? '&' : '?'}autoplay=0&mute=1&controls=1&rel=0`;
            frame.style.display = 'block';
            box.style.display = 'block';
            return;
        }

        if (assetUrl) {
            video.src = assetUrl;
            video.style.display = 'block';
            box.style.display = 'block';
        }
    }
}

function uploadMedia(e) {
    e.preventDefault();

    let etablissementId;
    try {
        etablissementId = requireCurrentEtablissementId();
    } catch (error) {
        console.error('Upload init error:', error);
        return;
    }

    const uploadSource = getSelectedUploadSource();
    const fileInput = document.getElementById('fileInput');
    const file = fileInput.files[0];

    if (!uploadSource) {
        showToast('Veuillez choisir le mode d\'ajout', 'error');
        return;
    }

    const mediaVideoUrlValue = document.getElementById('mediaVideoUrl')?.value?.trim() || '';
    if (uploadSource === 'local' && !file) {
        showToast('Veuillez s\u00e9lectionner un fichier', 'error');
        return;
    }

    if (uploadSource === 'url' && mediaVideoUrlValue === '') {
        showToast('Veuillez saisir une URL', 'error');
        return;
    }

    const formData = new FormData();
    if (file) {
        formData.append('file', file);
    }
    formData.append('name', document.getElementById('mediaName')?.value || '');
    formData.append('folder', document.getElementById('mediaFolder')?.value || '/');
    formData.append('type', uploadSource === 'url' ? 'video' : (document.getElementById('mediaType')?.value || 'image'));
    formData.append('video_url', uploadSource === 'url' ? mediaVideoUrlValue : '');
    formData.append('is_slider', document.getElementById('mediaIsSlider')?.checked ? '1' : '0');
    formData.append('is_main_gallery', document.getElementById('mediaIsMainGallery')?.checked ? '1' : '0');
    formData.append('is_facebook_gallery', document.getElementById('mediaIsFacebookGallery')?.checked ? '1' : '0');
    formData.append('is_instagram_gallery', document.getElementById('mediaIsInstagramGallery')?.checked ? '1' : '0');
    formData.append('is_pinterest_gallery', document.getElementById('mediaIsPinterestGallery')?.checked ? '1' : '0');
    formData.append('order', document.getElementById('mediaOrder')?.value || '0');
    formData.append('button_text', document.getElementById('mediaButtonText')?.value || '');
    formData.append('button_url', document.getElementById('mediaButtonUrl')?.value || '');
    formData.append('continent_id', document.getElementById('mediaContinentId')?.value || '');
    formData.append('country_id', document.getElementById('mediaCountryId')?.value || '');
    formData.append('province_id', document.getElementById('mediaProvinceId')?.value || '');
    formData.append('region_id', document.getElementById('mediaRegionId')?.value || '');
    formData.append('ville_id', document.getElementById('mediaVilleId')?.value || '');
    formData.append('secteur_id', document.getElementById('mediaSecteurId')?.value || '');
    
    const submitBtn = document.getElementById('uploadSubmitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Upload...';
    
    fetch(`/admin/cms/${etablissementId}/media/upload`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(parseJsonResponse)
    .then(result => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Uploader';
        
        if (result.success) {
            bootstrap.Modal.getInstance(document.getElementById('uploadMediaModal')).hide();
            showToast('Fichier upload\u00e9 avec succ\u00e8s', 'success');
            loadMedia();
            loadFolders();
            resetUploadForm();
        } else {
            showToast(result.message || 'Erreur lors de l\'upload', 'error');
        }
    })
    .catch(error => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Uploader';
        console.error('Upload error:', error);
        showToast(error?.message || 'Erreur lors de l\'upload', 'error');
    });
}

async function editMedia(id) {
    const media = mediaItems.find(m => m.id == id);
    if (!media) return;
    
    document.getElementById('editMediaId').value = media.id;
    document.getElementById('editMediaName').value = media.name || '';
    document.getElementById('editMediaAlt').value = media.alt || '';
    document.getElementById('editMediaTitle').value = media.title || '';
    document.getElementById('editMediaDescription').value = media.description || '';
    document.getElementById('editMediaFolder').value = media.folder || '/';
    document.getElementById('editMediaType').value = media.type || 'image';
    document.getElementById('editMediaVideoUrl').value = media.video_url || '';
    document.getElementById('editMediaIsSlider').checked = !!media.is_slider;
    document.getElementById('editMediaIsMainGallery').checked = !!media.is_main_gallery;
    document.getElementById('editMediaIsFacebookGallery').checked = !!media.is_facebook_gallery;
    document.getElementById('editMediaIsInstagramGallery').checked = !!media.is_instagram_gallery;
    document.getElementById('editMediaIsPinterestGallery').checked = !!media.is_pinterest_gallery;
    document.getElementById('editMediaOrder').value = media.order ?? 0;
    document.getElementById('editMediaButtonText').value = media.button_text || '';
    document.getElementById('editMediaButtonUrl').value = media.button_url || '';
    toggleVideoUrlField('editMediaType', 'editMediaVideoUrlWrap');
    toggleSliderButtonFields('editMediaIsSlider', 'editMediaSliderButtonFields');
    setEditPreview(media);
    await populateLocationFields('edit', media);
    
    new bootstrap.Modal(document.getElementById('editMediaModal')).show();
}

function updateMedia(e) {
    e.preventDefault();

    let etablissementId;
    try {
        etablissementId = requireCurrentEtablissementId();
    } catch (error) {
        console.error('Update init error:', error);
        return;
    }

    const id = document.getElementById('editMediaId').value;
    const formData = new FormData(document.getElementById('editMediaForm'));
    formData.append('_method', 'PUT');

    fetch(`/admin/cms/${etablissementId}/media/${id}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(parseJsonResponse)
    .then(result => {
        if (result.success) {
            bootstrap.Modal.getInstance(document.getElementById('editMediaModal')).hide();
            showToast('M\u00e9dia modifi\u00e9 avec succ\u00e8s', 'success');
            loadMedia();
        } else {
            showToast(result.message || 'Erreur lors de la modification', 'error');
        }
    })
    .catch(error => {
        console.error('Update error:', error);
        showToast('Erreur lors de la modification', 'error');
    });
}

function createFolder(e) {
    e.preventDefault();

    let etablissementId;
    try {
        etablissementId = requireCurrentEtablissementId();
    } catch (error) {
        console.error('Create folder init error:', error);
        return;
    }

    const folderName = document.getElementById('folderName').value;
    const parent = document.getElementById('parentFolder').value;

    fetch(`/admin/cms/${etablissementId}/media/folder/create`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ folder_name: folderName, parent: parent })
    })
    .then(parseJsonResponse)
    .then(result => {
        if (result.success) {
            bootstrap.Modal.getInstance(document.getElementById('createFolderModal')).hide();
            showToast('Dossier cr\u00e9\u00e9 avec succ\u00e8s', 'success');
            loadFolders();
            document.getElementById('createFolderForm').reset();
        } else {
            showToast(result.message || 'Erreur lors de la cr\u00e9ation', 'error');
        }
    })
    .catch(error => {
        console.error('Create folder error:', error);
        showToast('Erreur lors de la cr\u00e9ation', 'error');
    });
}

function confirmDeleteMedia() {
    if (!currentDeleteId) return;

    let etablissementId;
    try {
        etablissementId = requireCurrentEtablissementId();
    } catch (error) {
        console.error('Delete init error:', error);
        return;
    }

    fetch(`/admin/cms/${etablissementId}/media/${currentDeleteId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(parseJsonResponse)
    .then(result => {
        bootstrap.Modal.getInstance(document.getElementById('deleteMediaModal')).hide();
        if (result.success) {
            showToast('Fichier supprim\u00e9 avec succ\u00e8s', 'success');
            loadMedia();
            loadFolders();
        } else {
            showToast(result.message || 'Erreur lors de la suppression', 'error');
        }
        currentDeleteId = null;
    })
    .catch(error => {
        console.error('Delete error:', error);
        showToast('Erreur lors de la suppression', 'error');
        currentDeleteId = null;
    });
}

function bulkDeleteMedia() {
    const selectedIds = [];
    document.querySelectorAll('.media-checkbox:checked').forEach(cb => {
        selectedIds.push(cb.dataset.id);
    });
    
    if (selectedIds.length === 0) return;
    
    if (!confirm(`Supprimer ${selectedIds.length} fichier(s) d\u00e9finitivement ?`)) return;
    
    let etablissementId;
    try {
        etablissementId = requireCurrentEtablissementId();
    } catch (error) {
        console.error('Bulk delete init error:', error);
        return;
    }

    fetch(`/admin/cms/${etablissementId}/media/bulk/delete`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ ids: selectedIds })
    })
    .then(parseJsonResponse)
    .then(result => {
        if (result.success) {
            showToast(`${result.deleted || selectedIds.length} fichier(s) supprim\u00e9(s)`, 'success');
            loadMedia();
            loadFolders();
        } else {
            showToast(result.message || 'Erreur lors de la suppression', 'error');
        }
    })
    .catch(error => {
        console.error('Bulk delete error:', error);
        showToast('Erreur lors de la suppression', 'error');
    });
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, m => m === '&' ? '&amp;' : m === '<' ? '&lt;' : '&gt;');
}

function showToast(message, type = 'success') {
    document.querySelectorAll('.toast-notification').forEach(t => t.remove());
    const toast = document.createElement('div');
    toast.className = `toast-notification ${type}`;
    toast.innerHTML = `<div class="toast-content"><i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i><span>${escapeHtml(message)}</span></div>`;
    document.body.appendChild(toast);
    setTimeout(() => toast.classList.add('show'), 100);
    setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 300); }, 3000);
}

// Styles for toast
if (!document.querySelector('#media-toast-styles')) {
    const style = document.createElement('style');
    style.id = 'media-toast-styles';
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
