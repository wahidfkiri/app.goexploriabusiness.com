{{-- map-videos-tab.blade.php --}}
@php
    $mapsEnabled = $stats['etablissement']->getSetting('maps_enabled', false);
    $mapsSectionTitle = $stats['etablissement']->getSetting('maps_section_title', 'Carte interactive');
    $defaultMapLat = $stats['etablissement']->getSetting('latitude', 45.5017, 'company');
    $defaultMapLng = $stats['etablissement']->getSetting('longitude', -73.5673, 'company');
    $defaultMapCountry = optional($stats['etablissement']->country)->name;
@endphp

<div
    class="tab-pane fade"
    id="v-pills-map-videos"
    role="tabpanel"
    data-etablissement-id="{{ $stats['etablissement']->id ?? '' }}"
    data-maps-enabled="{{ $mapsEnabled ? '1' : '0' }}"
    data-default-lat="{{ $defaultMapLat }}"
    data-default-lng="{{ $defaultMapLng }}"
    data-default-country="{{ $defaultMapCountry }}">
    <div class="tab-content-header">
        <h3 class="tab-title">
            <i class="fas fa-map-marked-alt me-2" style="color: #16a34a;"></i>
            Vidéos maps
        </h3>
        <button type="button" class="btn btn-primary" onclick="openMapVideoModal()">
            <i class="fas fa-plus-circle me-2"></i>Créer un point vidéo
        </button>
    </div>

    <div class="map-videos-status-card" id="mapVideosStatusCard">
        <div>
            <label class="map-videos-status-title" for="mapsEnabledSwitch">Activation des maps</label>
            <p class="map-videos-status-text mb-0" id="mapsStatusText">
                {{ $mapsEnabled ? 'Les points maps sont affichés sur le site public.' : 'Les points maps sont masqués sur le site public.' }}
            </p>
        </div>
        <div class="form-check form-switch map-videos-status-switch">
            <input class="form-check-input" type="checkbox" role="switch" id="mapsEnabledSwitch" {{ $mapsEnabled ? 'checked' : '' }}>
            <label class="form-check-label" for="mapsEnabledSwitch" id="mapsEnabledLabel">{{ $mapsEnabled ? 'Actif' : 'Inactif' }}</label>
        </div>
    </div>

    <div class="map-videos-title-card">
        <div>
            <label class="map-videos-status-title" for="mapsSectionTitleInput">Titre de section carte map</label>
            <p class="map-videos-status-text mb-0">Ce titre peut être utilisé dans le front avant l'affichage de la carte.</p>
        </div>
        <div class="map-videos-title-control">
            <input type="text" class="form-control form-control-sm" id="mapsSectionTitleInput" value="{{ $mapsSectionTitle }}" maxlength="191" placeholder="Ex: Notre carte interactive">
            <button type="button" class="btn btn-sm btn-primary" id="saveMapsSectionTitleBtn">
                <i class="fas fa-save me-1"></i>Enregistrer
            </button>
        </div>
    </div>

    <div class="map-videos-toolbar">
        <div class="map-video-stat">
            <strong id="mapVideosTotal">0</strong>
            <span>Total points</span>
        </div>
        <div class="map-video-stat">
            <strong id="mapVideosActive">0</strong>
            <span>Actifs</span>
        </div>
        <div class="map-video-stat">
            <strong id="mapVideosWithVideo">0</strong>
            <span>Avec vidéo</span>
        </div>
        <button type="button" class="btn btn-outline-secondary btn-sm ms-auto" onclick="loadMapVideos()">
            <i class="fas fa-sync-alt me-1"></i>Rafraîchir
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th style="width: 120px;">Vidéo</th>
                    <th>Titre</th>
                    <th style="width: 160px;">Position</th>
                    <th style="width: 110px;">Statut</th>
                    <th style="width: 140px;">Actions</th>
                </tr>
            </thead>
            <tbody id="mapVideosTableBody">
                <tr>
                    <td colspan="5" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 mb-0">Chargement des points vidéos...</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="mapVideoModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mapVideoModalTitle">
                    <i class="fas fa-map-pin me-2"></i>Créer un point vidéo map
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="mapVideoForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="mapVideoId" name="id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-lg-6">
                            <label class="form-label">Titre du point <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="title" id="mapVideoTitle" maxlength="191" required>
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label">Titre vidéo</label>
                            <input type="text" class="form-control" name="video_title" id="mapVideoVideoTitle" maxlength="191">
                        </div>
                        <div class="col-12">
                            <label class="form-label">URL YouTube <span class="text-danger">*</span></label>
                            <input type="url" class="form-control" name="youtube_url" id="mapVideoYoutubeUrl" placeholder="https://www.youtube.com/watch?v=..." required>
                            <small class="text-muted">Formats acceptés: youtube.com/watch, youtube.com/shorts, youtube.com/embed, youtu.be.</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="mapVideoDescription" rows="2" maxlength="500"></textarea>
                        </div>
                        <div class="col-12">
                            <div class="map-video-media-panel">
                                <div class="map-video-extra-title mb-3">
                                    <i class="fas fa-images me-2"></i>Médias du point
                                </div>
                                <div class="row g-3">
                                    <div class="col-lg-6">
                                        <label class="form-label">Image principale</label>
                                        <input type="file" class="form-control" name="main_image" id="mapVideoMainImage" accept="image/png,image/jpg,image/jpeg,image/webp">
                                        <small class="text-muted">PNG, JPG, JPEG ou WebP jusqu'à 2MB.</small>
                                        <div class="map-video-current-media mt-2" id="mapVideoMainImagePreview">
                                            <span>Aucune image principale</span>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <label class="form-label">Galerie d'images</label>
                                        <input type="file" class="form-control" name="additional_images[]" id="mapVideoAdditionalImages" accept="image/png,image/jpg,image/jpeg,image/webp" multiple>
                                        <small class="text-muted">Ajoutez une ou plusieurs images de galerie.</small>
                                        <div class="map-video-gallery-preview mt-2" id="mapVideoGalleryPreview"></div>
                                        <div class="map-video-gallery-preview mt-2" id="mapVideoSelectedGalleryPreview"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Catégorie</label>
                            <select class="form-select" name="category" id="mapVideoCategory">
                                <option value="video_map">Vidéo map</option>
                                <option value="tourism">Tourisme</option>
                                <option value="culture">Culture</option>
                                <option value="history">Histoire</option>
                                <option value="nature">Nature</option>
                                <option value="adventure">Aventure</option>
                                <option value="shopping">Shopping</option>
                                <option value="science">Science</option>
                                <option value="beach">Plage</option>
                                <option value="family">Famille</option>
                                <option value="restaurant">Restaurant</option>
                                <option value="hotel">Hôtel</option>
                                <option value="commerce">Commerce</option>
                                <option value="chalet">Chalet</option>
                                <option value="commercial">Commercial</option>
                                <option value="domaine">Domaine</option>
                                <option value="residence">Résidence</option>
                                <option value="condo">Condo</option>
                                <option value="maison">Maison</option>
                                <option value="terrain">Terrain</option>
                                <option value="sante">Santé</option>
                                <option value="education">Éducation</option>
                                <option value="sport">Sport</option>
                                <option value="loisirs">Loisirs</option>
                                <option value="transport">Transport</option>
                                <option value="immobilier">Immobilier</option>
                                <option value="service">Service</option>
                                <option value="autre">Autre</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Statut</label>
                            <select class="form-select" name="is_active" id="mapVideoIsActive">
                                <option value="1">Actif</option>
                                <option value="0">Inactif</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Mis en avant</label>
                            <select class="form-select" name="is_featured" id="mapVideoIsFeatured">
                                <option value="0">Non</option>
                                <option value="1">Oui</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Adresse</label>
                            <div class="input-group">
                                <input type="text" class="form-control map-video-geocode-input" name="adresse" id="mapVideoAdresse" placeholder="Numéro, rue, quartier...">
                                <button type="button" class="btn btn-outline-primary" onclick="geocodeMapVideoAddress()">
                                    <i class="fas fa-search-location me-1"></i>Rechercher
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Ville</label>
                            <input type="text" class="form-control map-video-geocode-input" name="ville" id="mapVideoVille" maxlength="191" placeholder="Ville">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Code postal</label>
                            <input type="text" class="form-control map-video-geocode-input" name="code_postal" id="mapVideoCodePostal" maxlength="20">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Latitude <span class="text-danger">*</span></label>
                            <input type="number" step="0.00000001" class="form-control" name="latitude" id="mapVideoLatitude" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Longitude <span class="text-danger">*</span></label>
                            <input type="number" step="0.00000001" class="form-control" name="longitude" id="mapVideoLongitude" required>
                        </div>
                        <div class="col-12">
                            <div class="map-video-picker-header">
                                <div>
                                    <label class="form-label mb-0">Position sur la carte</label>
                                    <small class="text-muted d-block">Saisissez une adresse ou une ville, cliquez Rechercher, puis ajustez le marker si besoin.</small>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="geocodeMapVideoAddress()">
                                    <i class="fas fa-location-crosshairs me-1"></i>Placer le marker
                                </button>
                            </div>
                            <div id="mapVideoPicker" class="map-video-picker"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Lien détails</label>
                            <input type="text" class="form-control" name="details_url" id="mapVideoDetailsUrl" maxlength="191" placeholder="/page ou https://...">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" value="1" name="has_details_page" id="mapVideoHasDetailsPage">
                                <label class="form-check-label fw-semibold" for="mapVideoHasDetailsPage">Activer la page de détails</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="map-video-extra-panel">
                                <div class="map-video-extra-title">
                                    <i class="fas fa-address-card me-2"></i>Autres options du point
                                </div>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label">Description longue</label>
                                        <textarea class="form-control" name="details[long_description]" id="mapVideoLongDescription" rows="3"></textarea>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Téléphone</label>
                                        <input type="text" class="form-control" name="details[phone]" id="mapVideoDetailPhone" maxlength="50">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" name="details[email]" id="mapVideoDetailEmail" maxlength="191">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Site web</label>
                                        <input type="url" class="form-control" name="details[website]" id="mapVideoDetailWebsite" maxlength="191">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Personne contact</label>
                                        <input type="text" class="form-control" name="details[contact_person]" id="mapVideoDetailContactPerson" maxlength="191">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Note</label>
                                        <input type="number" class="form-control" name="details[rating]" id="mapVideoDetailRating" min="0" max="5" step="0.1">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Nombre d'avis</label>
                                        <input type="number" class="form-control" name="details[reviews_count]" id="mapVideoDetailReviewsCount" min="0" step="1">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Facebook</label>
                                        <input type="url" class="form-control" name="details[facebook]" id="mapVideoDetailFacebook" maxlength="191">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Instagram</label>
                                        <input type="url" class="form-control" name="details[instagram]" id="mapVideoDetailInstagram" maxlength="191">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">LinkedIn</label>
                                        <input type="url" class="form-control" name="details[linkedin]" id="mapVideoDetailLinkedin" maxlength="191">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">TikTok</label>
                                        <input type="url" class="form-control" name="details[tiktok]" id="mapVideoDetailTiktok" maxlength="191">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Pinterest</label>
                                        <input type="url" class="form-control" name="details[pinterest]" id="mapVideoDetailPinterest" maxlength="191">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Google Maps</label>
                                        <input type="url" class="form-control" name="details[google_maps]" id="mapVideoDetailGoogleMaps" maxlength="191">
                                    </div>
                                    <div class="col-12">
                                        <div class="map-video-extra-subtitle">Réseaux complémentaires</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">X / Twitter</label>
                                        <input type="url" class="form-control" name="details[twitter]" id="mapVideoDetailTwitter" maxlength="191">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Snapchat</label>
                                        <input type="text" class="form-control" name="details[snapchat]" id="mapVideoDetailSnapchat" maxlength="191">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">WhatsApp</label>
                                        <input type="text" class="form-control" name="details[whatsapp]" id="mapVideoDetailWhatsapp" maxlength="50">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Telegram</label>
                                        <input type="text" class="form-control" name="details[telegram]" id="mapVideoDetailTelegram" maxlength="191">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Discord</label>
                                        <input type="text" class="form-control" name="details[discord]" id="mapVideoDetailDiscord" maxlength="191">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Twitch</label>
                                        <input type="url" class="form-control" name="details[twitch]" id="mapVideoDetailTwitch" maxlength="191">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Reddit</label>
                                        <input type="url" class="form-control" name="details[reddit]" id="mapVideoDetailReddit" maxlength="191">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">GitHub</label>
                                        <input type="url" class="form-control" name="details[github]" id="mapVideoDetailGithub" maxlength="191">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Medium</label>
                                        <input type="url" class="form-control" name="details[medium]" id="mapVideoDetailMedium" maxlength="191">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Tumblr</label>
                                        <input type="url" class="form-control" name="details[tumblr]" id="mapVideoDetailTumblr" maxlength="191">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Vimeo</label>
                                        <input type="url" class="form-control" name="details[vimeo]" id="mapVideoDetailVimeo" maxlength="191">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Dribbble</label>
                                        <input type="url" class="form-control" name="details[dribbble]" id="mapVideoDetailDribbble" maxlength="191">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Behance</label>
                                        <input type="url" class="form-control" name="details[behance]" id="mapVideoDetailBehance" maxlength="191">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">SoundCloud</label>
                                        <input type="url" class="form-control" name="details[soundcloud]" id="mapVideoDetailSoundcloud" maxlength="191">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Spotify</label>
                                        <input type="url" class="form-control" name="details[spotify]" id="mapVideoDetailSpotify" maxlength="191">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">TripAdvisor</label>
                                        <input type="url" class="form-control" name="details[tripadvisor]" id="mapVideoDetailTripadvisor" maxlength="191">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Foursquare</label>
                                        <input type="url" class="form-control" name="details[foursquare]" id="mapVideoDetailFoursquare" maxlength="191">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Yelp</label>
                                        <input type="url" class="form-control" name="details[yelp]" id="mapVideoDetailYelp" maxlength="191">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-danger mt-3 d-none" id="mapVideoErrors"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary" id="saveMapVideoBtn">
                        <i class="fas fa-save me-1"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteMapVideoModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                <h5>Supprimer ce point vidéo ?</h5>
                <p class="text-muted">Cette action supprimera le point map et sa vidéo.</p>
                <input type="hidden" id="deleteMapVideoId">
            </div>
            <div class="modal-footer justify-content-center border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" onclick="confirmDeleteMapVideo()">Supprimer</button>
            </div>
        </div>
    </div>
</div>

<style>
.map-videos-status-card,
.map-videos-title-card,
.map-videos-toolbar {
    align-items: center;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    display: flex;
    gap: 18px;
    margin-bottom: 16px;
    padding: 16px;
}
.map-videos-status-card {
    justify-content: space-between;
}
.map-videos-title-card {
    display: grid;
    gap: 16px;
    grid-template-columns: minmax(0, 1fr) minmax(260px, 420px);
}
.map-videos-status-card.is-disabled {
    background: #fff7ed;
    border-color: #fed7aa;
}
.map-videos-status-title {
    color: #1e293b;
    display: block;
    font-size: 14px;
    font-weight: 700;
    margin-bottom: 4px;
}
.map-videos-status-text {
    color: #64748b;
    font-size: 13px;
}
.map-videos-status-switch {
    align-items: center;
    display: flex;
    gap: 10px;
    margin: 0;
    min-width: 128px;
}
.map-videos-status-switch .form-check-input {
    cursor: pointer;
    height: 1.35rem;
    margin: 0;
    width: 2.5rem;
}
.map-videos-status-switch .form-check-label {
    color: #334155;
    cursor: pointer;
    font-size: 13px;
    font-weight: 700;
    margin: 0;
}
.map-videos-title-control {
    align-items: center;
    display: flex;
    gap: 10px;
}
@media (max-width: 768px) {
    .map-videos-title-card {
        grid-template-columns: 1fr;
    }

    .map-videos-title-control {
        align-items: stretch;
        flex-direction: column;
    }
}
.map-video-stat {
    display: flex;
    flex-direction: column;
}
.map-video-stat strong {
    color: #0f172a;
    font-size: 22px;
    line-height: 1;
}
.map-video-stat span {
    color: #64748b;
    font-size: 12px;
}
.map-video-thumb {
    background: #0f172a;
    background-position: center;
    background-size: cover;
    border-radius: 10px;
    height: 68px;
    overflow: hidden;
    position: relative;
    width: 110px;
}
.map-video-thumb::after {
    background: rgba(15, 23, 42, 0.35);
    content: '';
    inset: 0;
    position: absolute;
}
.map-video-thumb .play {
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
.map-video-picker-header {
    align-items: center;
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
}
.map-video-picker {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    height: 320px;
    overflow: hidden;
}
.map-video-extra-panel {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 16px;
}
.map-video-media-panel {
    background: #fff;
    border: 1px solid #dbeafe;
    border-radius: 14px;
    padding: 16px;
}
.map-video-current-media,
.map-video-gallery-preview {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    min-height: 54px;
}
.map-video-current-media span,
.map-video-gallery-preview span {
    color: #64748b;
    font-size: 13px;
}
.map-video-media-thumb {
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    height: 72px;
    object-fit: cover;
    width: 96px;
}
.map-video-extra-title {
    color: #0f172a;
    font-size: 0.95rem;
    font-weight: 800;
    margin-bottom: 14px;
}
.map-video-extra-subtitle {
    border-top: 1px solid #e2e8f0;
    color: #475569;
    font-size: 0.82rem;
    font-weight: 800;
    letter-spacing: 0.03em;
    padding-top: 14px;
    text-transform: uppercase;
}
.map-video-toast {
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
.map-video-toast.show {
    transform: translateX(0);
}
.map-video-toast.error {
    border-left-color: #ef4444;
}
</style>

<script>
let mapVideoItems = [];
let mapVideoMap = null;
let mapVideoMarker = null;

document.addEventListener('DOMContentLoaded', function() {
    const pane = document.getElementById('v-pills-map-videos');
    if (!pane) return;

    updateMapsEnabledUi(pane.dataset.mapsEnabled === '1');
    document.getElementById('mapsEnabledSwitch')?.addEventListener('change', saveMapsEnabled);
    document.getElementById('saveMapsSectionTitleBtn')?.addEventListener('click', saveMapsSectionTitle);
    document.getElementById('mapVideoForm')?.addEventListener('submit', saveMapVideo);
    document.getElementById('mapVideoMainImage')?.addEventListener('change', previewMapVideoMainImage);
    document.getElementById('mapVideoAdditionalImages')?.addEventListener('change', previewMapVideoGalleryImages);
    document.querySelectorAll('.map-video-geocode-input').forEach(input => {
        input.addEventListener('keydown', event => {
            if (event.key === 'Enter') {
                event.preventDefault();
                geocodeMapVideoAddress();
            }
        });
    });
    document.getElementById('mapVideoModal')?.addEventListener('shown.bs.modal', () => {
        initMapVideoPicker();
        setTimeout(() => mapVideoMap?.invalidateSize(), 150);
    });
    loadMapVideos();
});

function getMapVideosEtablissementId() {
    return document.getElementById('v-pills-map-videos')?.dataset?.etablissementId || window.currentEtablissementId;
}

async function loadMapVideos() {
    const tbody = document.getElementById('mapVideosTableBody');
    if (!tbody) return;

    tbody.innerHTML = `<tr><td colspan="5" class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2 mb-0">Chargement...</p></td></tr>`;

    try {
        const response = await fetch(`/admin/cms/${getMapVideosEtablissementId()}/api/map-videos`, {
            headers: { 'Accept': 'application/json' }
        });
        const result = await response.json();
        if (!result.success) throw new Error(result.message || 'Erreur de chargement');

        mapVideoItems = result.data || [];
        renderMapVideos(result.stats || {});
    } catch (error) {
        tbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger py-5">${escapeMapVideoHtml(error.message)}</td></tr>`;
    }
}

function renderMapVideos(stats = {}) {
    const tbody = document.getElementById('mapVideosTableBody');
    if (!tbody) return;

    document.getElementById('mapVideosTotal').textContent = stats.total ?? mapVideoItems.length;
    document.getElementById('mapVideosActive').textContent = stats.active ?? mapVideoItems.filter(item => item.is_active).length;
    document.getElementById('mapVideosWithVideo').textContent = stats.with_video ?? mapVideoItems.filter(item => item.has_video).length;

    if (!mapVideoItems.length) {
        tbody.innerHTML = `<tr><td colspan="5" class="text-center py-5"><i class="fas fa-map-marked-alt fa-3x text-muted mb-3"></i><p>Aucun point vidéo map.</p><button class="btn btn-primary btn-sm" onclick="openMapVideoModal()">Créer un point vidéo</button></td></tr>`;
        return;
    }

    tbody.innerHTML = mapVideoItems.map(item => `
        <tr>
            <td>${renderMapVideoThumb(item)}</td>
            <td>
                <strong>${escapeMapVideoHtml(item.title || 'Sans titre')}</strong>
                <div class="text-muted small">${escapeMapVideoHtml(item.description || '')}</div>
                <div class="text-muted small text-truncate" style="max-width: 360px;">${escapeMapVideoHtml(item.youtube_url || '')}</div>
            </td>
            <td>
                <div class="small"><i class="fas fa-location-dot me-1"></i>${Number(item.latitude).toFixed(6)}, ${Number(item.longitude).toFixed(6)}</div>
                <div class="text-muted small">${escapeMapVideoHtml([item.adresse, item.ville].filter(Boolean).join(', '))}</div>
            </td>
            <td>
                <button type="button" class="btn btn-sm ${item.is_active ? 'btn-success' : 'btn-outline-secondary'}" onclick="toggleMapVideo(${item.id})">${item.is_active ? 'Actif' : 'Inactif'}</button>
            </td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-primary" title="Modifier" onclick="openMapVideoModal(${item.id})"><i class="fas fa-edit"></i></button>
                    <button type="button" class="btn btn-outline-danger" title="Supprimer" onclick="openDeleteMapVideoModal(${item.id})"><i class="fas fa-trash"></i></button>
                </div>
            </td>
        </tr>
    `).join('');
}

function renderMapVideoThumb(item) {
    const bg = item.thumbnail ? ` style="background-image:url('${escapeMapVideoHtml(item.thumbnail)}')"` : '';
    return `<div class="map-video-thumb"${bg}><span class="play"><i class="fab fa-youtube"></i></span></div>`;
}

function setMapVideoInputValue(id, value) {
    const input = document.getElementById(id);
    if (input) input.value = value ?? '';
}

function renderMapVideoMediaPreview(item = null) {
    const mainPreview = document.getElementById('mapVideoMainImagePreview');
    const galleryPreview = document.getElementById('mapVideoGalleryPreview');
    const selectedGalleryPreview = document.getElementById('mapVideoSelectedGalleryPreview');

    if (mainPreview) {
        mainPreview.innerHTML = item?.main_image_url
            ? `<img src="${escapeMapVideoHtml(item.main_image_url)}" alt="Image principale" class="map-video-media-thumb"><span>Image principale actuelle</span>`
            : '<span>Aucune image principale</span>';
    }

    const images = Array.isArray(item?.images) ? item.images : [];
    if (galleryPreview) {
        galleryPreview.innerHTML = images.length
            ? images.map(image => `<img src="${escapeMapVideoHtml(image.thumbnail || image.url)}" alt="Image galerie" class="map-video-media-thumb">`).join('')
            : '<span>Aucune image dans la galerie</span>';
    }

    if (selectedGalleryPreview) {
        selectedGalleryPreview.innerHTML = '';
    }
}

function previewMapVideoMainImage(event) {
    const file = event.target.files?.[0];
    const preview = document.getElementById('mapVideoMainImagePreview');
    if (!file || !preview) return;

    const url = URL.createObjectURL(file);
    preview.innerHTML = `<img src="${url}" alt="Nouvelle image principale" class="map-video-media-thumb"><span>Nouvelle image sélectionnée</span>`;
}

function previewMapVideoGalleryImages(event) {
    const files = Array.from(event.target.files || []);
    const preview = document.getElementById('mapVideoSelectedGalleryPreview');
    if (!preview) return;

    preview.innerHTML = files.length
        ? files.map(file => `<img src="${URL.createObjectURL(file)}" alt="Image galerie" class="map-video-media-thumb">`).join('')
        : '';
}

function fillMapVideoDetails(details = {}) {
    setMapVideoInputValue('mapVideoLongDescription', details.long_description);
    setMapVideoInputValue('mapVideoDetailPhone', details.phone);
    setMapVideoInputValue('mapVideoDetailEmail', details.email);
    setMapVideoInputValue('mapVideoDetailWebsite', details.website);
    setMapVideoInputValue('mapVideoDetailContactPerson', details.contact_person);
    setMapVideoInputValue('mapVideoDetailRating', details.rating);
    setMapVideoInputValue('mapVideoDetailReviewsCount', details.reviews_count);
    setMapVideoInputValue('mapVideoDetailFacebook', details.facebook);
    setMapVideoInputValue('mapVideoDetailInstagram', details.instagram);
    setMapVideoInputValue('mapVideoDetailTwitter', details.twitter);
    setMapVideoInputValue('mapVideoDetailLinkedin', details.linkedin);
    setMapVideoInputValue('mapVideoDetailTiktok', details.tiktok);
    setMapVideoInputValue('mapVideoDetailPinterest', details.pinterest);
    setMapVideoInputValue('mapVideoDetailSnapchat', details.snapchat);
    setMapVideoInputValue('mapVideoDetailWhatsapp', details.whatsapp);
    setMapVideoInputValue('mapVideoDetailTelegram', details.telegram);
    setMapVideoInputValue('mapVideoDetailDiscord', details.discord);
    setMapVideoInputValue('mapVideoDetailTwitch', details.twitch);
    setMapVideoInputValue('mapVideoDetailReddit', details.reddit);
    setMapVideoInputValue('mapVideoDetailGithub', details.github);
    setMapVideoInputValue('mapVideoDetailMedium', details.medium);
    setMapVideoInputValue('mapVideoDetailTumblr', details.tumblr);
    setMapVideoInputValue('mapVideoDetailVimeo', details.vimeo);
    setMapVideoInputValue('mapVideoDetailDribbble', details.dribbble);
    setMapVideoInputValue('mapVideoDetailBehance', details.behance);
    setMapVideoInputValue('mapVideoDetailSoundcloud', details.soundcloud);
    setMapVideoInputValue('mapVideoDetailSpotify', details.spotify);
    setMapVideoInputValue('mapVideoDetailTripadvisor', details.tripadvisor);
    setMapVideoInputValue('mapVideoDetailFoursquare', details.foursquare);
    setMapVideoInputValue('mapVideoDetailYelp', details.yelp);
    setMapVideoInputValue('mapVideoDetailGoogleMaps', details.google_maps);
}

function openMapVideoModal(id = null) {
    const form = document.getElementById('mapVideoForm');
    const errors = document.getElementById('mapVideoErrors');
    const pane = document.getElementById('v-pills-map-videos');
    form?.reset();
    errors?.classList.add('d-none');
    if (errors) errors.innerHTML = '';

    const defaultLat = parseFloat(pane?.dataset?.defaultLat || '45.5017');
    const defaultLng = parseFloat(pane?.dataset?.defaultLng || '-73.5673');

    document.getElementById('mapVideoId').value = id || '';
    document.getElementById('mapVideoCategory').value = 'video_map';
    document.getElementById('mapVideoLatitude').value = defaultLat.toFixed(8);
    document.getElementById('mapVideoLongitude').value = defaultLng.toFixed(8);
    document.getElementById('mapVideoIsActive').value = '1';
    document.getElementById('mapVideoIsFeatured').value = '0';
    document.getElementById('mapVideoHasDetailsPage').checked = false;
    fillMapVideoDetails({});
    renderMapVideoMediaPreview(null);
    document.getElementById('mapVideoModalTitle').innerHTML = id
        ? '<i class="fas fa-edit me-2"></i>Modifier un point vidéo map'
        : '<i class="fas fa-map-pin me-2"></i>Créer un point vidéo map';

    if (id) {
        const item = mapVideoItems.find(point => Number(point.id) === Number(id));
        if (item) {
            document.getElementById('mapVideoTitle').value = item.title || '';
            document.getElementById('mapVideoVideoTitle').value = item.video_title || '';
            document.getElementById('mapVideoYoutubeUrl').value = item.youtube_url || '';
            document.getElementById('mapVideoDescription').value = item.description || '';
            document.getElementById('mapVideoCategory').value = item.category || 'video_map';
            document.getElementById('mapVideoLatitude').value = Number(item.latitude).toFixed(8);
            document.getElementById('mapVideoLongitude').value = Number(item.longitude).toFixed(8);
            document.getElementById('mapVideoAdresse').value = item.adresse || '';
            document.getElementById('mapVideoVille').value = item.ville || '';
            document.getElementById('mapVideoCodePostal').value = item.code_postal || '';
            document.getElementById('mapVideoDetailsUrl').value = item.details_url || '';
            document.getElementById('mapVideoIsActive').value = item.is_active ? '1' : '0';
            document.getElementById('mapVideoIsFeatured').value = item.is_featured ? '1' : '0';
            document.getElementById('mapVideoHasDetailsPage').checked = Boolean(item.has_details_page);
            fillMapVideoDetails(item.details || {});
            renderMapVideoMediaPreview(item);
        }
    }

    bootstrap.Modal.getOrCreateInstance(document.getElementById('mapVideoModal')).show();
}

async function saveMapVideo(event) {
    event.preventDefault();
    const id = document.getElementById('mapVideoId').value;
    const form = document.getElementById('mapVideoForm');
    const saveBtn = document.getElementById('saveMapVideoBtn');
    const formData = new FormData(form);

    if (id) formData.append('_method', 'PUT');
    saveBtn.disabled = true;

    try {
        const response = await fetch(`/admin/cms/${getMapVideosEtablissementId()}/api/map-videos${id ? `/${id}` : ''}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        });
        const result = await response.json();

        if (!result.success) {
            showMapVideoErrors(result);
            return;
        }

        bootstrap.Modal.getInstance(document.getElementById('mapVideoModal'))?.hide();
        await loadMapVideos();
        showMapVideoToast(result.message || 'Point sauvegardé', 'success');
    } catch (error) {
        showMapVideoErrors({ message: error.message });
    } finally {
        saveBtn.disabled = false;
    }
}

async function toggleMapVideo(id) {
    try {
        const response = await fetch(`/admin/cms/${getMapVideosEtablissementId()}/api/map-videos/${id}/toggle`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        const result = await response.json();
        if (!result.success) throw new Error(result.message || 'Erreur statut');
        await loadMapVideos();
        showMapVideoToast(result.message || 'Statut mis à jour', 'success');
    } catch (error) {
        showMapVideoToast(error.message, 'error');
    }
}

function openDeleteMapVideoModal(id) {
    document.getElementById('deleteMapVideoId').value = id;
    bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteMapVideoModal')).show();
}

async function confirmDeleteMapVideo() {
    const id = document.getElementById('deleteMapVideoId').value;
    if (!id) return;

    try {
        const response = await fetch(`/admin/cms/${getMapVideosEtablissementId()}/api/map-videos/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        const result = await response.json();
        if (!result.success) throw new Error(result.message || 'Erreur suppression');
        bootstrap.Modal.getInstance(document.getElementById('deleteMapVideoModal'))?.hide();
        await loadMapVideos();
        showMapVideoToast(result.message || 'Point supprimé', 'success');
    } catch (error) {
        showMapVideoToast(error.message, 'error');
    }
}

async function saveMapsEnabled(event) {
    const checkbox = event.target;
    const isEnabled = checkbox.checked;
    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    formData.append('site_maps_enabled', isEnabled ? '1' : '0');
    checkbox.disabled = true;

    try {
        const response = await fetch(`/admin/cms/${getMapVideosEtablissementId()}/settings`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        });
        const result = await response.json();
        if (!result.success) throw new Error(result.message || 'Erreur sauvegarde');
        document.getElementById('v-pills-map-videos').dataset.mapsEnabled = isEnabled ? '1' : '0';
        updateMapsEnabledUi(isEnabled);
        showMapVideoToast(isEnabled ? 'Maps activées' : 'Maps désactivées', 'success');
    } catch (error) {
        checkbox.checked = !isEnabled;
        updateMapsEnabledUi(!isEnabled);
        showMapVideoToast(error.message, 'error');
    } finally {
        checkbox.disabled = false;
    }
}

async function saveMapsSectionTitle() {
    const input = document.getElementById('mapsSectionTitleInput');
    const button = document.getElementById('saveMapsSectionTitleBtn');
    const formData = new FormData();

    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    formData.append('site_maps_section_title', input?.value?.trim() || '');
    if (button) button.disabled = true;

    try {
        const response = await fetch(`/admin/cms/${getMapVideosEtablissementId()}/settings`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        });
        const result = await response.json();
        if (!result.success) throw new Error(result.message || 'Erreur sauvegarde');
        showMapVideoToast('Titre de section carte sauvegardé', 'success');
    } catch (error) {
        showMapVideoToast(error.message || 'Erreur lors de la sauvegarde', 'error');
    } finally {
        if (button) button.disabled = false;
    }
}

function updateMapsEnabledUi(isEnabled) {
    const card = document.getElementById('mapVideosStatusCard');
    const label = document.getElementById('mapsEnabledLabel');
    const text = document.getElementById('mapsStatusText');
    const checkbox = document.getElementById('mapsEnabledSwitch');
    if (checkbox) checkbox.checked = isEnabled;
    if (card) card.classList.toggle('is-disabled', !isEnabled);
    if (label) label.textContent = isEnabled ? 'Actif' : 'Inactif';
    if (text) {
        text.textContent = isEnabled
            ? 'Les points maps sont affichés sur le site public.'
            : 'Les points maps sont masqués sur le site public.';
    }
}

async function initMapVideoPicker() {
    await loadMapVideoLeaflet();
    const picker = document.getElementById('mapVideoPicker');
    if (!picker || !window.L) return;

    const lat = parseFloat(document.getElementById('mapVideoLatitude').value || '45.5017');
    const lng = parseFloat(document.getElementById('mapVideoLongitude').value || '-73.5673');

    if (!mapVideoMap) {
        mapVideoMap = L.map(picker, { scrollWheelZoom: false }).setView([lat, lng], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(mapVideoMap);
        mapVideoMarker = L.marker([lat, lng], { draggable: true }).addTo(mapVideoMap);
        mapVideoMarker.on('dragend', () => {
            const pos = mapVideoMarker.getLatLng();
            setMapVideoPosition(pos.lat, pos.lng, false);
        });
        mapVideoMap.on('click', event => setMapVideoPosition(event.latlng.lat, event.latlng.lng, true));
    } else {
        setMapVideoPosition(lat, lng, true);
    }
}

function setMapVideoPosition(lat, lng, moveMap) {
    const fixedLat = Number(lat).toFixed(8);
    const fixedLng = Number(lng).toFixed(8);
    document.getElementById('mapVideoLatitude').value = fixedLat;
    document.getElementById('mapVideoLongitude').value = fixedLng;
    mapVideoMarker?.setLatLng([lat, lng]);
    if (moveMap) mapVideoMap?.setView([lat, lng], Math.max(mapVideoMap.getZoom(), 13));
}

async function geocodeMapVideoAddress() {
    const defaultCountry = document.getElementById('v-pills-map-videos')?.dataset?.defaultCountry || '';
    const userQueryParts = [
        document.getElementById('mapVideoAdresse')?.value,
        document.getElementById('mapVideoVille')?.value,
        document.getElementById('mapVideoCodePostal')?.value
    ].map(value => (value || '').trim()).filter(Boolean);
    const queryParts = [
        ...userQueryParts,
        defaultCountry
    ].map(value => (value || '').trim()).filter(Boolean);
    const query = queryParts.join(', ');

    if (!userQueryParts.length) {
        showMapVideoToast('Ajoutez une adresse ou une ville à rechercher.', 'error');
        return;
    }

    const buttons = document.querySelectorAll('#mapVideoModal button[onclick="geocodeMapVideoAddress()"]');
    buttons.forEach(button => {
        button.disabled = true;
        button.dataset.originalHtml = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Recherche...';
    });

    try {
        const params = new URLSearchParams({
            format: 'json',
            limit: '1',
            addressdetails: '1',
            q: query
        });

        const response = await fetch('https://nominatim.openstreetmap.org/search?' + params.toString(), {
            headers: {
                'Accept': 'application/json',
                'Accept-Language': 'fr'
            }
        });
        if (!response.ok) throw new Error('Service de recherche indisponible');
        const results = await response.json();
        if (!Array.isArray(results) || !results.length) {
            throw new Error(`Adresse introuvable: ${queryParts.filter(part => part !== defaultCountry).join(', ')}`);
        }
        setMapVideoPosition(parseFloat(results[0].lat), parseFloat(results[0].lon), true);
        mapVideoMap?.setZoom(Math.max(mapVideoMap.getZoom(), 15));
        showMapVideoToast('Marker placé sur la carte', 'success');
    } catch (error) {
        showMapVideoToast(error.message || 'Recherche impossible', 'error');
    } finally {
        buttons.forEach(button => {
            button.disabled = false;
            button.innerHTML = button.dataset.originalHtml || '<i class="fas fa-search-location me-1"></i>Rechercher';
            delete button.dataset.originalHtml;
        });
    }
}

function loadMapVideoLeaflet() {
    if (window.L) return Promise.resolve();
    const cssHref = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
    if (!document.querySelector(`link[href="${cssHref}"]`)) {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = cssHref;
        document.head.appendChild(link);
    }
    return new Promise((resolve, reject) => {
        const existing = document.querySelector('script[data-map-video-leaflet]');
        if (existing) {
            existing.addEventListener('load', resolve, { once: true });
            existing.addEventListener('error', reject, { once: true });
            return;
        }
        const script = document.createElement('script');
        script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
        script.async = true;
        script.dataset.mapVideoLeaflet = 'true';
        script.onload = resolve;
        script.onerror = reject;
        document.head.appendChild(script);
    });
}

function showMapVideoErrors(result) {
    const errors = document.getElementById('mapVideoErrors');
    if (!errors) return;
    const messages = [];
    if (result.errors) {
        Object.values(result.errors).forEach(items => items.forEach(message => messages.push(message)));
    }
    if (!messages.length && result.message) messages.push(result.message);
    errors.innerHTML = messages.map(message => `<div>${escapeMapVideoHtml(message)}</div>`).join('');
    errors.classList.remove('d-none');
}

function showMapVideoToast(message, type = 'success') {
    document.querySelectorAll('.map-video-toast').forEach(toast => toast.remove());
    const toast = document.createElement('div');
    toast.className = `map-video-toast ${type === 'error' ? 'error' : 'success'}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    requestAnimationFrame(() => toast.classList.add('show'));
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 250);
    }, 3000);
}

function escapeMapVideoHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}
</script>
