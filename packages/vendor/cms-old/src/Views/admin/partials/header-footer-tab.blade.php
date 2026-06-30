@php
    $etablissement = $stats['etablissement'];
    $headerEnabled = $etablissement->getSetting('header_enabled', false);
    $footerEnabled = $etablissement->getSetting('footer_enabled', false);
    $hasHeaderFooterTable = \Illuminate\Support\Facades\Schema::connection('cms')->hasTable('cms_header_footers');
    $headerItem = $hasHeaderFooterTable ? \Vendor\Cms\Models\HeaderFooter::where('etablissement_id', $etablissement->id)->where('type', 'header')->first() : null;
    $footerItem = $hasHeaderFooterTable ? \Vendor\Cms\Models\HeaderFooter::where('etablissement_id', $etablissement->id)->where('type', 'footer')->first() : null;
@endphp

<div
    class="tab-pane fade"
    id="v-pills-header-footer"
    role="tabpanel"
    data-etablissement-id="{{ $etablissement->id }}"
    data-header-enabled="{{ $headerEnabled ? '1' : '0' }}"
    data-footer-enabled="{{ $footerEnabled ? '1' : '0' }}">
    <div class="tab-content-header">
        <h3 class="tab-title">
            <i class="fas fa-layer-group me-2" style="color: #0ea5e9;"></i>
            Header & Footer
        </h3>
    </div>

    <div class="header-footer-grid">
        <div class="header-footer-card" id="headerBuilderCard">
            <div class="header-footer-icon">
                <i class="fas fa-window-maximize"></i>
            </div>
            <div class="header-footer-content">
                <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                    <div>
                        <h4>Header du site</h4>
                        <p>Créez l'en-tête global de cet établissement avec GrapesJS.</p>
                    </div>
                    <div class="form-check form-switch header-footer-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="headerEnabledSwitch" {{ $headerEnabled ? 'checked' : '' }}>
                        <label class="form-check-label" id="headerEnabledLabel" for="headerEnabledSwitch">{{ $headerEnabled ? 'Actif' : 'Inactif' }}</label>
                    </div>
                </div>

                <div class="header-footer-meta">
                    <span><i class="fas fa-database me-1"></i>{{ $headerItem ? 'Créé' : 'Création automatique à l’ouverture' }}</span>
                    @if($headerItem?->updated_at)
                        <span><i class="fas fa-clock me-1"></i>{{ $headerItem->updated_at->format('d/m/Y H:i') }}</span>
                    @endif
                </div>

                <div class="header-footer-actions">
                    <a
                        href="{{ route('cms.admin.header-footer.edit', ['etablissementId' => $etablissement->id, 'type' => 'header']) }}"
                        class="btn btn-primary"
                        target="_blank"
                        rel="noopener noreferrer">
                        <i class="fas fa-edit me-2"></i>Éditer le header
                    </a>
                </div>
            </div>
        </div>

        <div class="header-footer-card" id="footerBuilderCard">
            <div class="header-footer-icon footer">
                <i class="fas fa-window-minimize"></i>
            </div>
            <div class="header-footer-content">
                <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                    <div>
                        <h4>Footer du site</h4>
                        <p>Créez le pied de page global de cet établissement avec GrapesJS.</p>
                    </div>
                    <div class="form-check form-switch header-footer-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="footerEnabledSwitch" {{ $footerEnabled ? 'checked' : '' }}>
                        <label class="form-check-label" id="footerEnabledLabel" for="footerEnabledSwitch">{{ $footerEnabled ? 'Actif' : 'Inactif' }}</label>
                    </div>
                </div>

                <div class="header-footer-meta">
                    <span><i class="fas fa-database me-1"></i>{{ $footerItem ? 'Créé' : 'Création automatique à l’ouverture' }}</span>
                    @if($footerItem?->updated_at)
                        <span><i class="fas fa-clock me-1"></i>{{ $footerItem->updated_at->format('d/m/Y H:i') }}</span>
                    @endif
                </div>

                <div class="header-footer-actions">
                    <a
                        href="{{ route('cms.admin.header-footer.edit', ['etablissementId' => $etablissement->id, 'type' => 'footer']) }}"
                        class="btn btn-primary"
                        target="_blank"
                        rel="noopener noreferrer">
                        <i class="fas fa-edit me-2"></i>Éditer le footer
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.header-footer-grid {
    display: grid;
    gap: 18px;
}
.header-footer-card {
    align-items: flex-start;
    background: #fff;
    border: 1px solid #dbe4f0;
    border-radius: 14px;
    display: flex;
    gap: 18px;
    padding: 18px;
}
.header-footer-card.is-disabled {
    background: #fff7ed;
    border-color: #fed7aa;
}
.header-footer-icon {
    align-items: center;
    background: #e0f2fe;
    border-radius: 14px;
    color: #0369a1;
    display: inline-flex;
    flex: 0 0 52px;
    font-size: 22px;
    height: 52px;
    justify-content: center;
    width: 52px;
}
.header-footer-icon.footer {
    background: #eef2ff;
    color: #4338ca;
}
.header-footer-content {
    flex: 1;
}
.header-footer-content h4 {
    color: #0f172a;
    font-size: 18px;
    font-weight: 800;
    margin: 0 0 6px;
}
.header-footer-content p {
    color: #64748b;
    margin: 0;
}
.header-footer-switch {
    align-items: center;
    display: flex;
    gap: 10px;
    margin: 0;
}
.header-footer-switch .form-check-input {
    cursor: pointer;
    height: 1.35rem;
    margin: 0;
    width: 2.5rem;
}
.header-footer-switch .form-check-label {
    color: #334155;
    cursor: pointer;
    font-size: 13px;
    font-weight: 800;
}
.header-footer-meta {
    color: #64748b;
    display: flex;
    flex-wrap: wrap;
    gap: 14px;
    margin-top: 16px;
    font-size: 13px;
}
.header-footer-actions {
    margin-top: 18px;
}
@media (max-width: 768px) {
    .header-footer-card {
        flex-direction: column;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const pane = document.getElementById('v-pills-header-footer');
    if (!pane) return;

    updateHeaderFooterUi('header', pane.dataset.headerEnabled === '1');
    updateHeaderFooterUi('footer', pane.dataset.footerEnabled === '1');

    document.getElementById('headerEnabledSwitch')?.addEventListener('change', event => saveHeaderFooterEnabled('header', event.target.checked));
    document.getElementById('footerEnabledSwitch')?.addEventListener('change', event => saveHeaderFooterEnabled('footer', event.target.checked));
});

async function saveHeaderFooterEnabled(type, isEnabled) {
    const pane = document.getElementById('v-pills-header-footer');
    const etablissementId = pane?.dataset?.etablissementId;
    const checkbox = document.getElementById(`${type}EnabledSwitch`);
    const formData = new FormData();

    if (!etablissementId) {
        showHeaderFooterToast('Erreur: établissement non défini', 'error');
        return;
    }

    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    formData.append(type === 'header' ? 'site_header_enabled' : 'site_footer_enabled', isEnabled ? '1' : '0');

    if (checkbox) checkbox.disabled = true;

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

        pane.dataset[`${type}Enabled`] = isEnabled ? '1' : '0';
        updateHeaderFooterUi(type, isEnabled);
        showHeaderFooterToast(`${type === 'header' ? 'Header' : 'Footer'} ${isEnabled ? 'activé' : 'désactivé'}`, 'success');
    } catch (error) {
        if (checkbox) checkbox.checked = !isEnabled;
        updateHeaderFooterUi(type, !isEnabled);
        showHeaderFooterToast(error.message || 'Erreur lors de la sauvegarde', 'error');
    } finally {
        if (checkbox) checkbox.disabled = false;
    }
}

function updateHeaderFooterUi(type, isEnabled) {
    const card = document.getElementById(type === 'header' ? 'headerBuilderCard' : 'footerBuilderCard');
    const label = document.getElementById(`${type}EnabledLabel`);
    const checkbox = document.getElementById(`${type}EnabledSwitch`);

    if (checkbox) checkbox.checked = isEnabled;
    if (label) label.textContent = isEnabled ? 'Actif' : 'Inactif';
    if (card) card.classList.toggle('is-disabled', !isEnabled);
}

function showHeaderFooterToast(message, type = 'success') {
    if (typeof showToast === 'function') {
        showToast(message, type);
        return;
    }

    console[type === 'error' ? 'error' : 'log'](message);
}
</script>
