@php
    $companyCountryId = (string) $stats['etablissement']->getSetting('country_id', '', 'company');
    $companyProvinceId = (string) $stats['etablissement']->getSetting('province_id', '', 'company');
    $companyRegionId = (string) $stats['etablissement']->getSetting('region_id', '', 'company');
    $companyVilleId = (string) $stats['etablissement']->getSetting('ville_id', '', 'company');
    $companyCountry = $stats['etablissement']->getSetting('country', '', 'company');
    $companyProvince = $stats['etablissement']->getSetting('province', '', 'company');
    $companyRegion = $stats['etablissement']->getSetting('region', '', 'company');
    $companyCity = $stats['etablissement']->getSetting('city', $stats['etablissement']->ville ?? '', 'company');
    $companyLatitude = $stats['etablissement']->getSetting('latitude', '', 'company');
    $companyLongitude = $stats['etablissement']->getSetting('longitude', '', 'company');
    $defaultOpeningHours = [
        'monday' => ['label' => 'Lundi', 'is_closed' => false, 'open' => '09:00', 'close' => '18:00'],
        'tuesday' => ['label' => 'Mardi', 'is_closed' => false, 'open' => '09:00', 'close' => '18:00'],
        'wednesday' => ['label' => 'Mercredi', 'is_closed' => false, 'open' => '09:00', 'close' => '18:00'],
        'thursday' => ['label' => 'Jeudi', 'is_closed' => false, 'open' => '09:00', 'close' => '18:00'],
        'friday' => ['label' => 'Vendredi', 'is_closed' => false, 'open' => '09:00', 'close' => '18:00'],
        'saturday' => ['label' => 'Samedi', 'is_closed' => true, 'open' => '09:00', 'close' => '18:00'],
        'sunday' => ['label' => 'Dimanche', 'is_closed' => true, 'open' => '09:00', 'close' => '18:00'],
    ];
    $openingHours = $stats['etablissement']->getSetting('opening_hours', $defaultOpeningHours, 'company');
    if (is_string($openingHours)) {
        $openingHours = json_decode($openingHours, true) ?: $defaultOpeningHours;
    }
    $openingHours = array_replace_recursive($defaultOpeningHours, is_array($openingHours) ? $openingHours : []);

    $holidaysEvents = $stats['etablissement']->getSetting('holidays_events', [], 'company');
    if (is_string($holidaysEvents)) {
        $holidaysEvents = json_decode($holidaysEvents, true) ?: [];
    }
    $holidaysEvents = collect(is_array($holidaysEvents) ? $holidaysEvents : [])
        ->filter(fn ($item) => is_array($item))
        ->values()
        ->all();
@endphp

<div class="tab-pane fade" id="v-pills-config" role="tabpanel">
    <div class="tab-content-header">
        <h3 class="tab-title">
            <i class="fas fa-cog me-2" style="color: #6c757d;"></i>
            Configuration générale
        </h3>
    </div>
    
    <form id="configForm">
        @csrf
        <input type="hidden" name="_method" value="POST">
        
        <div class="config-sections">
            <div class="config-group">
                <h4>Informations générales</h4>
                <div class="config-item">
                    <label class="config-label">Nom du site</label>
                    <input type="text" class="form-control" name="site_name" value="{{ $stats['etablissement']->getSetting('name', $stats['etablissement']->name ?? 'Mon site') }}">
                </div>
                <div class="config-item mt-3">
                    <label class="config-label">Slogan</label>
                    <input type="text" class="form-control" name="site_slogan" value="{{ $stats['etablissement']->getSetting('slogan', '') }}">
                </div>
                <div class="config-item mt-3">
                    <label class="config-label">Description</label>
                    <textarea class="form-control" name="site_description" rows="3">{{ $stats['etablissement']->getSetting('description', '') }}</textarea>
                </div>
            </div>

            <div class="config-group">
                <h4>Identité visuelle</h4>
                
                <!-- Logo Section -->
                <div class="config-item">
                    <label class="required">Logo du site</label>
                    
                    <div class="upload-area" data-target="logo">
                        <i class="fas fa-cloud-upload-alt upload-icon"></i>
                        <div class="upload-title">Télécharger le logo</div>
                        <div class="upload-subtitle">PNG, JPG, SVG jusqu'à 2MB</div>
                        <button type="button" class="upload-button" data-type="logo">
                            <i class="fas fa-folder-open"></i>
                            Parcourir
                        </button>
                        <input type="file" class="file-input-hidden" data-type="logo" accept="image/png,image/jpeg,image/jpg,image/svg+xml">
                    </div>
                    
                    <div class="preview-container logo-preview" data-preview="logo" 
                         style="{{ $logo = $stats['etablissement']->getSetting('site_logo') ? 'display: block;' : 'display: none;' }}">
                        <div class="preview-header">
                            <span class="preview-title">Logo actuel</span>
                            <button type="button" class="remove-image" data-type="logo">
                                <i class="fas fa-trash-alt"></i>
                                Supprimer
                            </button>
                        </div>
                        <div class="preview-image">
                            @if($logo = $stats['etablissement']->getSetting('site_logo'))
                                <img src="{{ $logo }}" alt="Logo" data-logo-preview>
                            @endif
                        </div>
                    </div>
                    
                    <div class="upload-progress" data-progress="logo">
                        <div class="progress-bar">
                            <div class="progress-fill"></div>
                        </div>
                        <div class="progress-text">Téléchargement en cours...</div>
                    </div>
                </div>
                
                <!-- Favicon Section -->
                <div class="config-item mt-4">
                    <label>Favicon</label>
                    
                    <div class="upload-area" data-target="favicon">
                        <i class="fas fa-image upload-icon"></i>
                        <div class="upload-title">Télécharger le favicon</div>
                        <div class="upload-subtitle">ICO, PNG, SVG - 32x32 ou 64x64 pixels</div>
                        <button type="button" class="upload-button" data-type="favicon">
                            <i class="fas fa-folder-open"></i>
                            Parcourir
                        </button>
                        <input type="file" class="file-input-hidden" data-type="favicon" accept="image/x-icon,image/png,image/svg+xml">
                    </div>
                    
                    <div class="preview-container favicon-preview" data-preview="favicon"
                         style="{{ $favicon = $stats['etablissement']->getSetting('site_favicon') ? 'display: block;' : 'display: none;' }}">
                        <div class="preview-header">
                            <span class="preview-title">Favicon actuel</span>
                            <button type="button" class="remove-image" data-type="favicon">
                                <i class="fas fa-trash-alt"></i>
                                Supprimer
                            </button>
                        </div>
                        <div class="preview-image">
                            @if($favicon = $stats['etablissement']->getSetting('site_favicon'))
                                <img src="{{ $favicon }}" alt="Favicon" data-favicon-preview>
                            @endif
                        </div>
                        <div class="favicon-sizes">
                            <div class="favicon-size">
                                <i class="fas fa-desktop"></i>
                                <span>Ordinateur</span>
                                <small>32x32</small>
                            </div>
                            <div class="favicon-size">
                                <i class="fas fa-mobile-alt"></i>
                                <span>Mobile</span>
                                <small>64x64</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="upload-progress" data-progress="favicon">
                        <div class="progress-bar">
                            <div class="progress-fill"></div>
                        </div>
                        <div class="progress-text">Téléchargement en cours...</div>
                    </div>
                </div>
            </div>
            
            <div class="config-group mt-4">
                <h4>Email et notifications</h4>
                <div class="config-item">
                    <label class="config-label">Email de contact</label>
                    <input type="email" class="form-control" name="email" value="{{ $stats['etablissement']->getSetting('email', $stats['etablissement']->email_contact ?? '') }}">
                </div>
                <div class="config-item mt-3">
                    <label class="config-label">Email de notification</label>
                    <input type="email" class="form-control" name="notification_email" value="{{ $stats['etablissement']->getSetting('notification_email', '') }}">
                </div>
            </div>
            
            <div class="config-group mt-4">
                <h4>Localisation</h4>
                <div class="config-item">
                    <label class="config-label">Adresse</label>
                    <input type="text" class="form-control" name="address" id="configAddress" value="{{ $stats['etablissement']->getSetting('address', $stats['etablissement']->adresse ?? '') }}">
                </div>
                <div class="config-item mt-3">
                    <label class="config-label">Code postal</label>
                    <input type="text" class="form-control" name="zip_code" id="configZipCode" value="{{ $stats['etablissement']->getSetting('zip_code', $stats['etablissement']->zip_code ?? '') }}">
                </div>
                <div class="config-item mt-3">
                    <label class="config-label">Pays</label>
                    <select class="form-control" name="country_id" id="configCountryId" data-selected="{{ $companyCountryId }}" data-selected-label="{{ $companyCountry }}">
                        <option value="">Choisir un pays</option>
                    </select>
                    <input type="hidden" name="country" id="configCountryName" value="{{ $companyCountry }}">
                </div>
                <div class="config-item mt-3">
                    <label class="config-label">Province</label>
                    <select class="form-control" name="province_id" id="configProvinceId" data-selected="{{ $companyProvinceId }}" data-selected-label="{{ $companyProvince }}" disabled>
                        <option value="">Choisir une province</option>
                    </select>
                    <input type="hidden" name="province" id="configProvinceName" value="{{ $companyProvince }}">
                </div>
                <div class="config-item mt-3">
                    <label class="config-label">Région</label>
                    <select class="form-control" name="region_id" id="configRegionId" data-selected="{{ $companyRegionId }}" data-selected-label="{{ $companyRegion }}" disabled>
                        <option value="">Choisir une région</option>
                    </select>
                    <input type="hidden" name="region" id="configRegionName" value="{{ $companyRegion }}">
                </div>
                <div class="config-item mt-3">
                    <label class="config-label">Ville</label>
                    <select class="form-control" name="ville_id" id="configVilleId" data-selected="{{ $companyVilleId }}" data-selected-label="{{ $companyCity }}" disabled>
                        <option value="">Choisir une ville</option>
                    </select>
                    <input type="hidden" name="city" id="configCityName" value="{{ $companyCity }}">
                </div>
                <div class="config-item mt-3">
                    <div class="location-map-header">
                        <label class="config-label mb-0">Position sur la carte</label>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="configMapSearchBtn">
                            <i class="fas fa-search-location me-1"></i>
                            Rechercher
                        </button>
                    </div>
                    <div id="configLocationMap" class="config-location-map" data-lat="{{ $companyLatitude }}" data-lng="{{ $companyLongitude }}"></div>
                    <div class="location-map-footer">
                        <span id="configMapStatus">Sélectionnez une adresse ou déplacez le marqueur.</span>
                        <span id="configMapCoordinates"></span>
                    </div>
                    <input type="hidden" name="latitude" id="configLatitude" value="{{ $companyLatitude }}">
                    <input type="hidden" name="longitude" id="configLongitude" value="{{ $companyLongitude }}">
                </div>
                <div class="config-item mt-3">
                    <label class="config-label">Téléphone</label>
                    <input type="text" class="form-control" name="phone" value="{{ $stats['etablissement']->getSetting('phone', $stats['etablissement']->phone ?? '', 'company') }}">
                </div>
            </div>

            <div class="config-group mt-4">
                <h4>Horaires d'ouverture</h4>
                <input type="hidden" name="opening_hours" id="configOpeningHours" value='@json($openingHours)'>

                <div class="opening-hours-list">
                    @foreach($openingHours as $dayKey => $day)
                        <div class="opening-hours-row" data-day="{{ $dayKey }}">
                            <div class="opening-day">{{ $day['label'] }}</div>
                            <div class="opening-closed">
                                <div class="form-check">
                                    <input class="form-check-input opening-closed-input" type="checkbox" id="openingClosed{{ $dayKey }}" data-day="{{ $dayKey }}" {{ !empty($day['is_closed']) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="openingClosed{{ $dayKey }}">Ferm&eacute;</label>
                                </div>
                            </div>
                            <div class="opening-time">
                                <label>Ouverture</label>
                                <input type="time" class="form-control opening-time-input" data-day="{{ $dayKey }}" data-field="open" value="{{ $day['open'] ?? '09:00' }}">
                            </div>
                            <div class="opening-time">
                                <label>Fermeture</label>
                                <input type="time" class="form-control opening-time-input" data-day="{{ $dayKey }}" data-field="close" value="{{ $day['close'] ?? '18:00' }}">
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="config-group mt-4">
                <div class="holiday-section-header">
                    <div>
                        <h4 class="mb-1">Jours f&eacute;ri&eacute;s et f&ecirc;tes</h4>
                        <p class="holiday-section-help mb-0">Ajoutez les fermetures exceptionnelles, jours f&eacute;ri&eacute;s et &eacute;v&eacute;nements visibles sur le site.</p>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="addHolidayEventBtn">
                        <i class="fas fa-plus me-1"></i>Ajouter
                    </button>
                </div>
                <input type="hidden" name="holidays_events" id="configHolidaysEvents" value='@json($holidaysEvents)'>

                <div class="holidays-events-list" id="holidaysEventsList"></div>
                <div class="holidays-empty-state" id="holidaysEmptyState">
                    <i class="fas fa-calendar-day"></i>
                    <span>Aucun jour f&eacute;ri&eacute; ou &eacute;v&eacute;nement configur&eacute;.</span>
                </div>
            </div>
        </div>
        
        <div class="form-actions mt-4">
            <button type="submit" class="btn btn-primary" id="saveConfigBtn">
                <i class="fas fa-save me-2"></i>Sauvegarder la configuration
            </button>
            <div class="spinner-border spinner-border-sm text-primary ms-2" id="configLoading" style="display: none;" role="status">
                <span class="visually-hidden">Chargement...</span>
            </div>
        </div>
    </form>
</div>

<style>
    /* ============================================
   CONFIGURATION VISUELLE - STYLES MODERNES
   ============================================ */

.config-group {
    background: #ffffff;
    border-radius: 20px;
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05), 0 1px 2px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    border: 1px solid #eef2f6;
}

.config-group:hover {
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
    border-color: #e2e8f0;
}

.config-group h4 {
    font-size: 18px;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 2px solid #f1f5f9;
    display: flex;
    align-items: center;
    gap: 10px;
}

.config-group h4::before {
    content: '';
    width: 4px;
    height: 20px;
    background: linear-gradient(135deg, #3b82f6, #06d6a0);
    border-radius: 2px;
}

.config-item {
    margin-bottom: 20px;
}

.config-item label {
    display: block;
    font-size: 14px;
    font-weight: 500;
    color: #334155;
    margin-bottom: 8px;
    transition: color 0.2s ease;
}

.config-item.required label::after {
    content: '*';
    color: #ef4444;
    margin-left: 4px;
}

/* Upload Area Styles */
.upload-area {
    position: relative;
    border: 2px dashed #e2e8f0;
    border-radius: 16px;
    padding: 32px 24px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #fafbfc;
    margin-bottom: 16px;
}

.upload-area:hover {
    border-color: #3b82f6;
    background: #f0f9ff;
    transform: translateY(-2px);
}

.upload-area.drag-over {
    border-color: #06d6a0;
    background: #f0fdf4;
    transform: scale(0.98);
}

.upload-icon {
    font-size: 48px;
    color: #94a3b8;
    margin-bottom: 16px;
    transition: all 0.3s ease;
}

.upload-area:hover .upload-icon {
    color: #3b82f6;
    transform: translateY(-4px);
}

.upload-title {
    font-size: 16px;
    font-weight: 500;
    color: #1e293b;
    margin-bottom: 8px;
}

.upload-subtitle {
    font-size: 13px;
    color: #64748b;
    margin-bottom: 16px;
}

.upload-button {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: white;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
}

.upload-button:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.upload-button:active {
    transform: translateY(0);
}

.file-input-hidden {
    display: none;
}

.preview-container {
    margin-top: 20px;
    padding: 16px;
    background: #f8fafc;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    display: none;
}

.preview-container.active {
    display: block;
    animation: fadeInUp 0.4s ease;
}

.preview-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.preview-title {
    font-size: 13px;
    font-weight: 600;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.remove-image {
    background: #fee2e2;
    color: #ef4444;
    border: none;
    border-radius: 8px;
    padding: 6px 12px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.remove-image:hover {
    background: #fecaca;
    transform: scale(1.05);
}

.preview-image {
    display: flex;
    justify-content: center;
    align-items: center;
    background: white;
    border-radius: 12px;
    padding: 16px;
    min-height: 120px;
}

.preview-image img {
    max-width: 100%;
    max-height: 150px;
    object-fit: contain;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.logo-preview img {
    max-height: 80px;
}

.favicon-preview img {
    width: 64px;
    height: 64px;
    border-radius: 16px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.favicon-sizes {
    display: flex;
    gap: 16px;
    justify-content: center;
    margin-top: 12px;
}

.favicon-size {
    text-align: center;
    font-size: 11px;
    color: #64748b;
}

.upload-progress {
    margin-top: 12px;
    display: none;
}

.upload-progress.active {
    display: block;
}

.progress-bar {
    height: 4px;
    background: #e2e8f0;
    border-radius: 2px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #3b82f6, #06d6a0);
    width: 0%;
    transition: width 0.3s ease;
    border-radius: 2px;
}

.progress-text {
    font-size: 12px;
    color: #64748b;
    margin-top: 8px;
    text-align: center;
}

.form-actions {
    display: flex;
    align-items: center;
    gap: 12px;
}

.opening-hours-list {
    display: grid;
    gap: 12px;
}

.opening-hours-row {
    display: grid;
    grid-template-columns: minmax(120px, 1fr) minmax(110px, .75fr) minmax(130px, 1fr) minmax(130px, 1fr);
    gap: 12px;
    align-items: center;
    padding: 12px;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    background: #f8fafc;
}

.opening-day {
    font-weight: 600;
    color: #1e293b;
}

.opening-time label {
    font-size: 12px;
    color: #64748b;
    margin-bottom: 4px;
}

.opening-hours-row.is-closed .opening-time {
    opacity: .5;
}

.holiday-section-header {
    align-items: flex-start;
    display: flex;
    gap: 16px;
    justify-content: space-between;
    margin-bottom: 16px;
}

.holiday-section-help {
    color: #64748b;
    font-size: 13px;
}

.holidays-events-list {
    display: grid;
    gap: 12px;
}

.holiday-event-row {
    align-items: end;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    display: grid;
    gap: 12px;
    grid-template-columns: minmax(135px, .8fr) minmax(170px, 1.2fr) minmax(140px, .8fr) minmax(150px, .8fr) minmax(170px, 1fr) 42px;
    padding: 12px;
}

.holiday-field label {
    color: #64748b;
    font-size: 12px;
    margin-bottom: 5px;
}

.holiday-checks {
    display: grid;
    gap: 7px;
    padding-bottom: 7px;
}

.holiday-checks .form-check {
    margin: 0;
}

.holiday-remove-btn {
    align-items: center;
    background: #fee2e2;
    border: 0;
    border-radius: 10px;
    color: #991b1b;
    display: inline-flex;
    height: 38px;
    justify-content: center;
    width: 38px;
}

.holidays-empty-state {
    align-items: center;
    background: #f8fafc;
    border: 1px dashed #cbd5e1;
    border-radius: 14px;
    color: #64748b;
    display: flex;
    gap: 10px;
    justify-content: center;
    padding: 18px;
}

.location-map-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    margin-bottom: 10px;
}

.config-location-map {
    width: 100%;
    min-width: 0;
    height: 340px;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    overflow: hidden;
    background: #f8fafc;
}

.config-location-map.leaflet-container,
.config-location-map .leaflet-container {
    width: 100%;
    height: 100%;
    border-radius: 16px;
}

.location-map-footer {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    margin-top: 8px;
    color: #64748b;
    font-size: 12px;
}

#configMapCoordinates {
    font-family: monospace;
    color: #334155;
    white-space: nowrap;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 768px) {
    .config-group {
        padding: 16px;
    }
    
    .upload-area {
        padding: 20px 16px;
    }
    
    .upload-icon {
        font-size: 36px;
    }
    
    .preview-image img {
        max-height: 100px;
    }

    .opening-hours-row {
        grid-template-columns: 1fr;
    }

    .holiday-section-header {
        flex-direction: column;
    }

    .holiday-event-row {
        grid-template-columns: 1fr;
    }

    .location-map-header,
    .location-map-footer {
        align-items: flex-start;
        flex-direction: column;
    }

    .config-location-map {
        height: 280px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const etablissementId = {{ $stats['etablissement']->id }};
    let locationMapController = null;
    
    // Configuration des uploads
    const uploadConfigs = {
        logo: {
            accept: ['image/png', 'image/jpeg', 'image/jpg', 'image/svg+xml'],
            maxSize: 2 * 1024 * 1024,
            field: 'site_logo',
            preview: 'logo',
            endpoint: `/admin/cms/${etablissementId}/settings/upload`
        },
        favicon: {
            accept: ['image/x-icon', 'image/png', 'image/svg+xml'],
            maxSize: 1 * 1024 * 1024,
            field: 'site_favicon',
            preview: 'favicon',
            endpoint: `/admin/cms/${etablissementId}/settings/upload`
        }
    };
    
    // Initialiser les uploads
    Object.keys(uploadConfigs).forEach(type => {
        initUpload(type, uploadConfigs[type]);
    });

    initConfigLocationCascade();
    initOpeningHours();
    initHolidaysEvents();
    initLocationMap();
    
    // Formulaire de configuration
    const configForm = document.getElementById('configForm');
    if (configForm) {
        configForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            syncConfigLocationNames();
            syncOpeningHours();
            syncHolidaysEvents();

            const saveBtn = document.getElementById('saveConfigBtn');
            const loading = document.getElementById('configLoading');
            const formData = new FormData(this);
            
            saveBtn.disabled = true;
            loading.style.display = 'inline-block';
            
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
                
                if (result.success) {
                    showToast(result.message, 'success');
                } else {
                    showToast(result.message || 'Erreur lors de la sauvegarde', 'error');
                }
            } catch (error) {
                console.error('Save error:', error);
                showToast('Erreur lors de la sauvegarde', 'error');
            } finally {
                saveBtn.disabled = false;
                loading.style.display = 'none';
            }
        });
    }

    function initConfigLocationCascade() {
        const countrySelect = document.getElementById('configCountryId');
        const provinceSelect = document.getElementById('configProvinceId');
        const regionSelect = document.getElementById('configRegionId');
        const villeSelect = document.getElementById('configVilleId');

        if (!countrySelect || !provinceSelect || !regionSelect || !villeSelect) return;

        countrySelect.addEventListener('change', async () => {
            syncConfigLocationNames();
            resetSelect(provinceSelect, 'Choisir une province');
            resetSelect(regionSelect, 'Choisir une région');
            resetSelect(villeSelect, 'Choisir une ville');
            clearConfigLocationNames(['province', 'region', 'city']);

            if (countrySelect.value) {
                await loadConfigLocationOptions('provinces', provinceSelect, { country_id: countrySelect.value }, 'Choisir une province');
            }

            locationMapController?.scheduleGeocode();
        });

        provinceSelect.addEventListener('change', async () => {
            syncConfigLocationNames();
            resetSelect(regionSelect, 'Choisir une région');
            resetSelect(villeSelect, 'Choisir une ville');
            clearConfigLocationNames(['region', 'city']);

            if (provinceSelect.value) {
                await loadConfigLocationOptions('regions', regionSelect, { province_id: provinceSelect.value }, 'Choisir une région');
            }

            locationMapController?.scheduleGeocode();
        });

        regionSelect.addEventListener('change', async () => {
            syncConfigLocationNames();
            resetSelect(villeSelect, 'Choisir une ville');
            clearConfigLocationNames(['city']);

            if (regionSelect.value) {
                await loadConfigLocationOptions('villes', villeSelect, { region_id: regionSelect.value }, 'Choisir une ville');
            }

            locationMapController?.scheduleGeocode();
        });

        villeSelect.addEventListener('change', () => {
            syncConfigLocationNames();
            locationMapController?.scheduleGeocode();
        });

        (async () => {
            await loadConfigLocationOptions('countries', countrySelect, {}, 'Choisir un pays');

            if (countrySelect.value) {
                await loadConfigLocationOptions('provinces', provinceSelect, { country_id: countrySelect.value }, 'Choisir une province');
            }

            if (provinceSelect.value) {
                await loadConfigLocationOptions('regions', regionSelect, { province_id: provinceSelect.value }, 'Choisir une région');
            }

            if (regionSelect.value) {
                await loadConfigLocationOptions('villes', villeSelect, { region_id: regionSelect.value }, 'Choisir une ville');
            }

            syncConfigLocationNames();
            locationMapController?.scheduleGeocode(400);
        })();
    }

    async function loadConfigLocationOptions(level, select, params, placeholder) {
        const selectedValue = select.dataset.selected || '';
        const selectedLabel = select.dataset.selectedLabel || '';
        const query = new URLSearchParams(params);
        const url = `/admin/cms/${etablissementId}/settings/locations/${level}${query.toString() ? `?${query.toString()}` : ''}`;

        select.disabled = true;
        select.innerHTML = `<option value="">Chargement...</option>`;

        try {
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Erreur de chargement');
            }

            fillLocationSelect(select, result.data || [], placeholder, selectedValue, selectedLabel);
        } catch (error) {
            console.error('Location load error:', error);
            select.innerHTML = `<option value="">${placeholder}</option>`;
            showToast('Erreur lors du chargement des localisations', 'error');
        } finally {
            select.disabled = false;
            select.dataset.selected = '';
        }
    }

    function fillLocationSelect(select, items, placeholder, selectedValue, selectedLabel) {
        select.innerHTML = `<option value="">${placeholder}</option>`;

        items.forEach(item => {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = item.name;
            if (selectedValue && String(item.id) === String(selectedValue)) {
                option.selected = true;
            }
            select.appendChild(option);
        });

        if (selectedValue && !select.value && selectedLabel) {
            const option = document.createElement('option');
            option.value = selectedValue;
            option.textContent = selectedLabel;
            option.selected = true;
            select.appendChild(option);
        }
    }

    function resetSelect(select, placeholder) {
        select.innerHTML = `<option value="">${placeholder}</option>`;
        select.disabled = true;
        select.dataset.selected = '';
    }

    function syncConfigLocationNames() {
        setHiddenLocationName('configCountryId', 'configCountryName');
        setHiddenLocationName('configProvinceId', 'configProvinceName');
        setHiddenLocationName('configRegionId', 'configRegionName');
        setHiddenLocationName('configVilleId', 'configCityName');
    }

    function setHiddenLocationName(selectId, inputId) {
        const select = document.getElementById(selectId);
        const input = document.getElementById(inputId);
        if (!select || !input) return;

        const option = select.options[select.selectedIndex];
        input.value = select.value && option ? option.textContent.trim() : '';
    }

    function clearConfigLocationNames(keys) {
        const inputs = {
            province: document.getElementById('configProvinceName'),
            region: document.getElementById('configRegionName'),
            city: document.getElementById('configCityName')
        };

        keys.forEach(key => {
            if (inputs[key]) inputs[key].value = '';
        });
    }

    async function initLocationMap() {
        const mapEl = document.getElementById('configLocationMap');
        if (!mapEl) return;

        try {
            await loadLeaflet();
        } catch (error) {
            console.error('Leaflet load error:', error);
            const statusEl = document.getElementById('configMapStatus');
            if (statusEl) statusEl.textContent = 'Impossible de charger la carte.';
            return;
        }

        const latInput = document.getElementById('configLatitude');
        const lngInput = document.getElementById('configLongitude');
        const statusEl = document.getElementById('configMapStatus');
        const coordinatesEl = document.getElementById('configMapCoordinates');
        const initialLat = parseFloat(latInput?.value || mapEl.dataset.lat);
        const initialLng = parseFloat(lngInput?.value || mapEl.dataset.lng);
        const hasInitialPosition = Number.isFinite(initialLat) && Number.isFinite(initialLng);
        const defaultPosition = hasInitialPosition ? [initialLat, initialLng] : [45.5017, -73.5673];
        const map = L.map(mapEl, { scrollWheelZoom: false }).setView(defaultPosition, hasInitialPosition ? 15 : 5);
        const marker = L.marker(defaultPosition, { draggable: true }).addTo(map);
        let geocodeTimer = null;

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        setMapPosition(defaultPosition[0], defaultPosition[1], false);

        marker.on('dragend', () => {
            const position = marker.getLatLng();
            setMapPosition(position.lat, position.lng, false);
            setMapStatus('Position ajustée manuellement.');
        });

        document.getElementById('configMapSearchBtn')?.addEventListener('click', () => geocodeCurrentAddress());
        document.getElementById('configAddress')?.addEventListener('input', () => scheduleGeocode(900));
        document.getElementById('configZipCode')?.addEventListener('input', () => scheduleGeocode(900));

        if (!hasInitialPosition) {
            scheduleGeocode(800);
        }

        refreshMapSize();
        [80, 250, 600, 1200].forEach(delay => setTimeout(refreshMapSize, delay));

        window.addEventListener('resize', () => refreshMapSize());

        if (window.ResizeObserver) {
            const resizeObserver = new ResizeObserver(() => refreshMapSize());
            resizeObserver.observe(mapEl);
            const pane = document.getElementById('v-pills-config');
            if (pane) resizeObserver.observe(pane);
        }

        if (window.MutationObserver) {
            const pane = document.getElementById('v-pills-config');
            if (pane) {
                const mutationObserver = new MutationObserver(() => refreshMapSize());
                mutationObserver.observe(pane, { attributes: true, attributeFilter: ['class', 'style'] });
            }
        }

        document.querySelectorAll('.nav-link-modern[data-section="v-pills-config"]').forEach(link => {
            link.addEventListener('click', () => {
                [0, 120, 350].forEach(delay => setTimeout(refreshMapSize, delay));
            });
        });

        document.querySelectorAll('[data-bs-toggle="pill"], [data-bs-toggle="tab"]').forEach(tab => {
            tab.addEventListener('shown.bs.tab', event => {
                const target = event.target?.getAttribute('data-bs-target') || event.target?.getAttribute('href');
                if (target === '#v-pills-config') {
                    setTimeout(refreshMapSize, 120);
                }
            });
        });

        locationMapController = {
            scheduleGeocode,
            geocodeCurrentAddress,
            invalidate: refreshMapSize
        };

        function scheduleGeocode(delay = 650) {
            clearTimeout(geocodeTimer);
            geocodeTimer = setTimeout(geocodeCurrentAddress, delay);
        }

        async function geocodeCurrentAddress() {
            const query = buildLocationQuery();
            if (!query) {
                setMapStatus('Ajoutez une adresse ou choisissez une ville pour positionner la carte.');
                return;
            }

            setMapStatus('Recherche de la position...');

            try {
                const url = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(query);
                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const results = await response.json();

                if (!response.ok || !Array.isArray(results) || results.length === 0) {
                    setMapStatus('Adresse introuvable. Déplacez le marqueur manuellement.');
                    return;
                }

                const result = results[0];
                const lat = parseFloat(result.lat);
                const lng = parseFloat(result.lon);

                if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
                    setMapStatus('Coordonnées invalides. Déplacez le marqueur manuellement.');
                    return;
                }

                setMapPosition(lat, lng, true);
                setMapStatus(result.display_name || 'Position trouvée.');
            } catch (error) {
                console.error('Map geocode error:', error);
                setMapStatus('Impossible de rechercher la position pour le moment.');
            }
        }

        function buildLocationQuery() {
            return [
                document.getElementById('configAddress')?.value,
                document.getElementById('configZipCode')?.value,
                document.getElementById('configCityName')?.value,
                document.getElementById('configRegionName')?.value,
                document.getElementById('configProvinceName')?.value,
                document.getElementById('configCountryName')?.value
            ].map(value => (value || '').trim()).filter(Boolean).join(', ');
        }

        function setMapPosition(lat, lng, shouldZoom) {
            const roundedLat = Number(lat).toFixed(7);
            const roundedLng = Number(lng).toFixed(7);
            latInput.value = roundedLat;
            lngInput.value = roundedLng;
            coordinatesEl.textContent = `${roundedLat}, ${roundedLng}`;
            marker.setLatLng([lat, lng]);

            if (shouldZoom) {
                map.setView([lat, lng], 16);
            }
        }

        function setMapStatus(message) {
            if (statusEl) statusEl.textContent = message;
        }

        function refreshMapSize() {
            if (!mapEl.offsetParent && !mapEl.classList.contains('leaflet-container')) {
                return;
            }

            requestAnimationFrame(() => {
                map.invalidateSize({ pan: false });
                const position = marker.getLatLng();
                map.setView(position, map.getZoom(), { animate: false });
            });
        }
    }

    function loadLeaflet() {
        if (window.L) {
            return Promise.resolve();
        }

        const cssHref = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
        if (!document.querySelector(`link[href="${cssHref}"]`)) {
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = cssHref;
            document.head.appendChild(link);
        }

        return new Promise((resolve, reject) => {
            const existingScript = document.querySelector('script[data-leaflet-loader]');
            if (existingScript) {
                existingScript.addEventListener('load', resolve, { once: true });
                existingScript.addEventListener('error', reject, { once: true });
                return;
            }

            const script = document.createElement('script');
            script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
            script.async = true;
            script.dataset.leafletLoader = 'true';
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    function initOpeningHours() {
        document.querySelectorAll('.opening-hours-row').forEach(row => {
            const closedInput = row.querySelector('.opening-closed-input');
            if (!closedInput) return;

            closedInput.addEventListener('change', () => updateOpeningHoursRow(row));
            updateOpeningHoursRow(row);
        });

        document.querySelectorAll('.opening-time-input').forEach(input => {
            input.addEventListener('change', syncOpeningHours);
        });

        syncOpeningHours();
    }

    function updateOpeningHoursRow(row) {
        const closedInput = row.querySelector('.opening-closed-input');
        const isClosed = !!closedInput?.checked;

        row.classList.toggle('is-closed', isClosed);
        row.querySelectorAll('.opening-time-input').forEach(input => {
            input.disabled = isClosed;
        });

        syncOpeningHours();
    }

    function syncOpeningHours() {
        const target = document.getElementById('configOpeningHours');
        if (!target) return;

        const hours = {};
        document.querySelectorAll('.opening-hours-row').forEach(row => {
            const day = row.dataset.day;
            const closedInput = row.querySelector('.opening-closed-input');
            const openInput = row.querySelector('.opening-time-input[data-field="open"]');
            const closeInput = row.querySelector('.opening-time-input[data-field="close"]');

            hours[day] = {
                label: row.querySelector('.opening-day')?.textContent.trim() || day,
                is_closed: !!closedInput?.checked,
                open: openInput?.value || '',
                close: closeInput?.value || ''
            };
        });

        target.value = JSON.stringify(hours);
    }

    function initHolidaysEvents() {
        const addButton = document.getElementById('addHolidayEventBtn');
        const list = document.getElementById('holidaysEventsList');
        const source = document.getElementById('configHolidaysEvents');
        if (!list || !source) return;

        let items = [];
        try {
            items = JSON.parse(source.value || '[]');
        } catch (error) {
            items = [];
        }

        if (Array.isArray(items)) {
            items
                .filter(item => item && typeof item === 'object')
                .forEach(item => addHolidayEventRow(item));
        }

        addButton?.addEventListener('click', () => {
            addHolidayEventRow({
                date: new Date().toISOString().slice(0, 10),
                name: '',
                type: 'holiday',
                is_closed: true,
                is_recurring: false,
                note: ''
            });
        });

        list.addEventListener('input', syncHolidaysEvents);
        list.addEventListener('change', syncHolidaysEvents);
        list.addEventListener('click', event => {
            const removeButton = event.target.closest('.holiday-remove-btn');
            if (!removeButton) return;

            removeButton.closest('.holiday-event-row')?.remove();
            syncHolidaysEvents();
        });

        syncHolidaysEvents();
    }

    function addHolidayEventRow(item = {}) {
        const list = document.getElementById('holidaysEventsList');
        if (!list) return;

        const row = document.createElement('div');
        row.className = 'holiday-event-row';
        row.innerHTML = `
            <div class="holiday-field">
                <label>Date</label>
                <input type="date" class="form-control holiday-input" data-field="date" value="${escapeConfigHtml(item.date || '')}">
            </div>
            <div class="holiday-field">
                <label>Nom</label>
                <input type="text" class="form-control holiday-input" data-field="name" value="${escapeConfigHtml(item.name || '')}" placeholder="Ex: Jour de l'An">
            </div>
            <div class="holiday-field">
                <label>Type</label>
                <select class="form-control holiday-input" data-field="type">
                    <option value="holiday" ${item.type === 'holiday' ? 'selected' : ''}>Jour f&eacute;ri&eacute;</option>
                    <option value="celebration" ${item.type === 'celebration' ? 'selected' : ''}>F&ecirc;te</option>
                    <option value="exception" ${item.type === 'exception' ? 'selected' : ''}>Exception</option>
                    <option value="event" ${item.type === 'event' ? 'selected' : ''}>&Eacute;v&eacute;nement</option>
                </select>
            </div>
            <div class="holiday-checks">
                <div class="form-check">
                    <input class="form-check-input holiday-input" type="checkbox" data-field="is_closed" ${item.is_closed === false ? '' : 'checked'}>
                    <label class="form-check-label">Ferm&eacute; ce jour</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input holiday-input" type="checkbox" data-field="is_recurring" ${item.is_recurring ? 'checked' : ''}>
                    <label class="form-check-label">Chaque ann&eacute;e</label>
                </div>
            </div>
            <div class="holiday-field">
                <label>Note</label>
                <input type="text" class="form-control holiday-input" data-field="note" value="${escapeConfigHtml(item.note || '')}" placeholder="Message optionnel">
            </div>
            <button type="button" class="holiday-remove-btn" title="Supprimer">
                <i class="fas fa-trash-alt"></i>
            </button>
        `;
        list.appendChild(row);
        syncHolidaysEvents();
    }

    function syncHolidaysEvents() {
        const target = document.getElementById('configHolidaysEvents');
        const emptyState = document.getElementById('holidaysEmptyState');
        const rows = Array.from(document.querySelectorAll('.holiday-event-row'));
        if (!target) return;

        const items = rows.map(row => {
            const getField = field => row.querySelector(`.holiday-input[data-field="${field}"]`);
            return {
                date: getField('date')?.value || '',
                name: getField('name')?.value.trim() || '',
                type: getField('type')?.value || 'holiday',
                is_closed: !!getField('is_closed')?.checked,
                is_recurring: !!getField('is_recurring')?.checked,
                note: getField('note')?.value.trim() || ''
            };
        }).filter(item => item.date || item.name || item.note);

        items.sort((a, b) => (a.date || '').localeCompare(b.date || ''));
        target.value = JSON.stringify(items);
        emptyState?.classList.toggle('d-none', items.length > 0);
    }

    function escapeConfigHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }
    
    function initUpload(type, config) {
        const uploadArea = document.querySelector(`.upload-area[data-target="${type}"]`);
        const fileInput = document.querySelector(`.file-input-hidden[data-type="${type}"]`);
        const uploadBtn = document.querySelector(`.upload-button[data-type="${type}"]`);
        const previewContainer = document.querySelector(`[data-preview="${type}"]`);
        const progressContainer = document.querySelector(`[data-progress="${type}"]`);
        const removeBtn = document.querySelector(`.remove-image[data-type="${type}"]`);
        
        if (uploadBtn) {
            uploadBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                fileInput.click();
            });
        }
        
        if (uploadArea) {
            uploadArea.addEventListener('click', () => {
                fileInput.click();
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
                const files = e.dataTransfer.files;
                if (files.length) {
                    handleFileUpload(files[0], type, config);
                }
            });
        }
        
        if (fileInput) {
            fileInput.addEventListener('change', (e) => {
                if (e.target.files.length) {
                    handleFileUpload(e.target.files[0], type, config);
                }
            });
        }
        
        if (removeBtn) {
            removeBtn.addEventListener('click', () => {
                removeFile(type, config);
            });
        }
    }
    
    async function handleFileUpload(file, type, config) {
        if (!config.accept.includes(file.type)) {
            showToast(`Format non supporté. Formats acceptés: ${config.accept.join(', ')}`, 'error');
            return;
        }
        
        if (file.size > config.maxSize) {
            showToast(`Fichier trop volumineux. Maximum: ${config.maxSize / 1024 / 1024}MB`, 'error');
            return;
        }
        
        const progressContainer = document.querySelector(`[data-progress="${type}"]`);
        if (progressContainer) {
            progressContainer.classList.add('active');
            updateProgress(progressContainer, 0);
        }
        
        const formData = new FormData();
        formData.append('file', file);
        formData.append('field', config.field);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
        
        // Simulate progress
        let progress = 0;
        const interval = setInterval(() => {
            progress += 10;
            if (progress <= 90 && progressContainer) {
                updateProgress(progressContainer, progress);
            }
        }, 100);
        
        try {
            const response = await fetch(config.endpoint, {
                method: 'POST',
                body: formData
            });
            
            clearInterval(interval);
            
            const result = await response.json();
            
            if (progressContainer) {
                updateProgress(progressContainer, 100);
                setTimeout(() => {
                    progressContainer.classList.remove('active');
                }, 500);
            }
            
            if (result.success) {
                updatePreview(type, result.path || result.stored_path);
                showToast('Fichier téléchargé avec succès', 'success');
            } else {
                throw new Error(result.message || 'Erreur lors du téléchargement');
            }
        } catch (error) {
            clearInterval(interval);
            if (progressContainer) {
                progressContainer.classList.remove('active');
            }
            showToast(error.message, 'error');
        }
    }
    
    function updatePreview(type, path) {
        const previewContainer = document.querySelector(`[data-preview="${type}"]`);
        const previewImg = document.querySelector(`[data-${type}-preview]`);
        
        if (previewContainer) {
            previewContainer.style.display = 'block';
        }
        
        if (previewImg) {
            previewImg.src = path;
        } else if (previewContainer) {
            const previewImage = previewContainer.querySelector('.preview-image');
            if (previewImage) {
                previewImage.innerHTML = `<img src="${path}" alt="${type}">`;
            }
        }
    }
    
    async function removeFile(type, config) {
        if (!confirm('Êtes-vous sûr de vouloir supprimer ce fichier ?')) {
            return;
        }
        
        try {
            const response = await fetch(`/admin/cms/${etablissementId}/settings/remove-file`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ field: config.field })
            });
            
            const result = await response.json();
            
            if (result.success) {
                const previewContainer = document.querySelector(`[data-preview="${type}"]`);
                if (previewContainer) {
                    previewContainer.style.display = 'none';
                    const previewImg = previewContainer.querySelector('img');
                    if (previewImg) {
                        previewImg.src = '';
                    }
                }
                showToast('Fichier supprimé avec succès', 'success');
            } else {
                throw new Error(result.message || 'Erreur lors de la suppression');
            }
        } catch (error) {
            showToast(error.message, 'error');
        }
    }
    
    function updateProgress(container, percent) {
        if (!container) return;
        const fill = container.querySelector('.progress-fill');
        if (fill) {
            fill.style.width = `${percent}%`;
        }
    }
    
    function showToast(message, type = 'success') {
        const existingToasts = document.querySelectorAll('.toast-notification');
        existingToasts.forEach(toast => toast.remove());
        
        const toast = document.createElement('div');
        toast.className = `toast-notification ${type}`;
        toast.innerHTML = `
            <div class="toast-content">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(toast);
        setTimeout(() => toast.classList.add('show'), 100);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
    
    // Styles for toast
    if (!document.querySelector('#config-toast-styles')) {
        const style = document.createElement('style');
        style.id = 'config-toast-styles';
        style.textContent = `
            .toast-notification {
                position: fixed;
                bottom: 20px;
                right: 20px;
                background: white;
                border-radius: 12px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                transform: translateX(400px);
                transition: transform 0.3s ease;
                z-index: 10000;
                min-width: 280px;
            }
            .toast-notification.show {
                transform: translateX(0);
            }
            .toast-content {
                padding: 16px 20px;
                display: flex;
                align-items: center;
                gap: 12px;
                border-left: 4px solid;
                border-radius: 12px;
            }
            .toast-notification.success .toast-content {
                border-left-color: #10b981;
            }
            .toast-notification.success i {
                color: #10b981;
            }
            .toast-notification.error .toast-content {
                border-left-color: #ef4444;
            }
            .toast-notification.error i {
                color: #ef4444;
            }
        `;
        document.head.appendChild(style);
    }
});
</script>
