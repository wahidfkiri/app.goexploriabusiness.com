@php
    $contactFormTitle = $stats['etablissement']->getSetting('contact_form_title', 'Contactez-nous');
    $contactMessagesRoutes = [
        'index' => route('cms.admin.contact-messages.index', ['etablissementId' => $stats['etablissement']->id]),
        'show' => route('cms.admin.contact-messages.show', ['etablissementId' => $stats['etablissement']->id, 'id' => '__ID__']),
        'update' => route('cms.admin.contact-messages.update', ['etablissementId' => $stats['etablissement']->id, 'id' => '__ID__']),
        'destroy' => route('cms.admin.contact-messages.destroy', ['etablissementId' => $stats['etablissement']->id, 'id' => '__ID__']),
        'bulk' => route('cms.admin.contact-messages.bulk', ['etablissementId' => $stats['etablissement']->id]),
    ];
@endphp

<div
    class="tab-pane fade"
    id="v-pills-contact-messages"
    role="tabpanel"
    data-etablissement-id="{{ $stats['etablissement']->id ?? '' }}">
    <div class="tab-content-header contact-header">
        <h3 class="tab-title">
            <i class="fas fa-inbox me-2" style="color: #2563eb;"></i>
            Messages contact
        </h3>
        <div class="contact-actions">
            <button type="button" class="btn btn-outline-secondary btn-sm" id="refreshContactMessagesBtn">
                <i class="fas fa-sync-alt me-1"></i>Actualiser
            </button>
            <button type="button" class="btn btn-outline-primary btn-sm" id="markSelectedReadBtn" disabled>
                <i class="fas fa-envelope-open me-1"></i>Marquer lu
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="archiveSelectedBtn" disabled>
                <i class="fas fa-archive me-1"></i>Archiver
            </button>
        </div>
    </div>

    <div class="contact-title-card">
        <div>
            <label class="contact-title-label" for="contactFormTitleInput">Titre du formulaire de contact</label>
            <p class="contact-title-text mb-0">Ce titre peut être utilisé dans le front au-dessus du formulaire de contact.</p>
        </div>
        <div class="contact-title-control">
            <input type="text" class="form-control form-control-sm" id="contactFormTitleInput" value="{{ $contactFormTitle }}" maxlength="191" placeholder="Ex: Contactez-nous">
            <button type="button" class="btn btn-sm btn-primary" id="saveContactFormTitleBtn">
                <i class="fas fa-save me-1"></i>Enregistrer
            </button>
        </div>
    </div>

    <div class="contact-metrics">
        <div class="contact-metric">
            <span>Total</span>
            <strong id="contactMetricTotal">{{ $stats['contact_messages_count'] ?? 0 }}</strong>
        </div>
        <div class="contact-metric unread">
            <span>Nouveaux</span>
            <strong id="contactMetricNew">{{ $stats['new_contact_messages_count'] ?? 0 }}</strong>
        </div>
        <div class="contact-metric">
            <span>Répondus</span>
            <strong id="contactMetricReplied">{{ $stats['replied_contact_messages_count'] ?? 0 }}</strong>
        </div>
    </div>

    <div class="contact-toolbar">
        <div class="contact-search">
            <i class="fas fa-search"></i>
            <input type="search" id="contactSearchInput" placeholder="Rechercher nom, email, sujet, message...">
        </div>
        <select id="contactStatusFilter">
            <option value="all">Tous les statuts</option>
            <option value="new">Nouveaux</option>
            <option value="read">Lus</option>
            <option value="replied">Répondus</option>
            <option value="archived">Archivés</option>
            <option value="spam">Spam</option>
        </select>
        <select id="contactPriorityFilter">
            <option value="all">Toutes priorités</option>
            <option value="urgent">Urgente</option>
            <option value="high">Haute</option>
            <option value="normal">Normale</option>
            <option value="low">Basse</option>
        </select>
    </div>

    <div class="contact-table-wrap">
        <table class="contact-table">
            <thead>
                <tr>
                    <th><input type="checkbox" id="selectAllContactMessages"></th>
                    <th>Visiteur</th>
                    <th>Sujet</th>
                    <th>Statut</th>
                    <th>Priorité</th>
                    <th>Date</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody id="contactMessagesTableBody">
                <tr>
                    <td colspan="7" class="contact-empty">Chargement...</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="contact-pagination">
        <button type="button" class="btn btn-outline-secondary btn-sm" id="contactPrevPage">Précédent</button>
        <span id="contactPageState">Page 1</span>
        <button type="button" class="btn btn-outline-secondary btn-sm" id="contactNextPage">Suivant</button>
    </div>
</div>

<div class="modal fade" id="contactMessageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content contact-modal">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="contactModalSubject">Message contact</h5>
                    <small class="text-muted" id="contactModalMeta"></small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <div class="contact-detail-grid">
                    <div>
                        <span>Nom</span>
                        <strong id="contactModalName">-</strong>
                    </div>
                    <div>
                        <span>Email</span>
                        <strong id="contactModalEmail">-</strong>
                    </div>
                    <div>
                        <span>Téléphone</span>
                        <strong id="contactModalPhone">-</strong>
                    </div>
                    <div>
                        <span>Société</span>
                        <strong id="contactModalCompany">-</strong>
                    </div>
                </div>
                <div class="contact-message-box" id="contactModalMessage"></div>
                <div class="contact-tech-meta" id="contactModalTechnical"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-outline-primary" data-contact-status="read">Marquer lu</button>
                <button type="button" class="btn btn-success" data-contact-status="replied">Répondu</button>
                <button type="button" class="btn btn-outline-secondary" data-contact-status="archived">Archiver</button>
                <button type="button" class="btn btn-outline-warning" data-contact-status="spam">Spam</button>
            </div>
        </div>
    </div>
</div>

<style>
    .contact-header { gap: 16px; align-items: flex-start; }
    .contact-actions { display: flex; gap: 8px; flex-wrap: wrap; justify-content: flex-end; }
    .contact-title-card {
        align-items: center;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        display: grid;
        gap: 16px;
        grid-template-columns: minmax(0, 1fr) minmax(260px, 420px);
        margin: 18px 0;
        padding: 16px;
    }
    .contact-title-label {
        color: #1e293b;
        display: block;
        font-size: 14px;
        font-weight: 700;
        margin-bottom: 4px;
    }
    .contact-title-text {
        color: #64748b;
        font-size: 13px;
    }
    .contact-title-control {
        align-items: center;
        display: flex;
        gap: 10px;
    }
    .contact-metrics { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; margin: 18px 0; }
    .contact-metric { border: 1px solid #dfe5ee; border-radius: 8px; padding: 14px; background: #fff; display: grid; gap: 4px; }
    .contact-metric span { color: #64748b; font-size: 13px; font-weight: 700; }
    .contact-metric strong { color: #0f766e; font-size: 24px; font-weight: 800; }
    .contact-metric.unread strong { color: #2563eb; }
    .contact-toolbar { display: grid; grid-template-columns: minmax(260px, 1fr) 170px 170px; gap: 10px; margin-bottom: 14px; }
    .contact-search { position: relative; }
    .contact-search i { position: absolute; left: 12px; top: 12px; color: #64748b; }
    .contact-search input, .contact-toolbar select { width: 100%; border: 1px solid #cbd5e1; border-radius: 7px; min-height: 40px; padding: 9px 11px; background: #fff; }
    .contact-search input { padding-left: 36px; }
    .contact-table-wrap { overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 8px; background: #fff; }
    .contact-table { width: 100%; border-collapse: collapse; }
    .contact-table th { padding: 12px; color: #64748b; font-size: 12px; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; white-space: nowrap; }
    .contact-table td { padding: 12px; border-bottom: 1px solid #eef2f7; vertical-align: top; }
    .contact-empty { text-align: center; padding: 32px !important; color: #64748b; }
    .contact-person { display: grid; gap: 2px; }
    .contact-person strong { color: #172033; }
    .contact-person span, .contact-preview, .contact-date { color: #64748b; font-size: 12px; }
    .contact-subject { display: grid; gap: 4px; max-width: 420px; }
    .contact-pill { display: inline-flex; width: fit-content; padding: 5px 8px; border-radius: 999px; font-size: 12px; font-weight: 800; background: #e2e8f0; color: #334155; }
    .contact-status-new { background: #dbeafe; color: #1d4ed8; }
    .contact-status-replied { background: #dcfce7; color: #166534; }
    .contact-status-spam { background: #fef3c7; color: #92400e; }
    .contact-status-archived { background: #e5e7eb; color: #374151; }
    .contact-priority-urgent, .contact-priority-high { background: #fee2e2; color: #991b1b; }
    .contact-row-new { background: #f8fbff; }
    .contact-row-actions { display: inline-flex; gap: 6px; justify-content: flex-end; }
    .contact-icon-btn { width: 32px; height: 32px; border: 1px solid #d6dfeb; border-radius: 7px; background: #fff; color: #1f2937; display: inline-flex; align-items: center; justify-content: center; }
    .contact-icon-btn:hover { color: #2563eb; border-color: #9db7ec; }
    .contact-pagination { display: flex; justify-content: flex-end; align-items: center; gap: 10px; padding-top: 14px; }
    .contact-detail-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; margin-bottom: 16px; }
    .contact-detail-grid div { background: #f8fafc; border-radius: 8px; padding: 12px; display: grid; gap: 3px; }
    .contact-detail-grid span, .contact-tech-meta { color: #64748b; font-size: 12px; }
    .contact-message-box { white-space: pre-line; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; background: #fff; color: #172033; line-height: 1.55; }
    .contact-tech-meta { margin-top: 12px; display: grid; gap: 4px; }
    @media (max-width: 900px) {
        .contact-title-card,
        .contact-metrics,
        .contact-toolbar,
        .contact-detail-grid {
            grid-template-columns: 1fr;
        }

        .contact-title-control {
            align-items: stretch;
            flex-direction: column;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const routes = @json($contactMessagesRoutes);
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const tbody = document.getElementById('contactMessagesTableBody');
    if (!tbody) return;

    const state = { page: 1, lastPage: 1, selectedId: null, loaded: false };
    const route = (name, id) => routes[name].replace('__ID__', id);
    const modalElement = document.getElementById('contactMessageModal');
    const modal = modalElement && window.bootstrap ? new bootstrap.Modal(modalElement) : null;
    const escapeHtml = value => String(value ?? '').replace(/[&<>"']/g, char => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[char]));
    const toast = (message, type = 'success') => {
        if (typeof showToast === 'function') {
            showToast(message, type);
            return;
        }
        alert(message);
    };

    async function saveContactFormTitle() {
        const pane = document.getElementById('v-pills-contact-messages');
        const input = document.getElementById('contactFormTitleInput');
        const button = document.getElementById('saveContactFormTitleBtn');
        const etablissementId = pane?.dataset?.etablissementId;
        const formData = new FormData();

        if (!etablissementId) {
            toast('Erreur: etablissement non defini', 'error');
            return;
        }

        formData.append('_token', csrf);
        formData.append('site_contact_form_title', input?.value?.trim() || '');
        if (button) button.disabled = true;

        try {
            const response = await fetch(`/admin/cms/${etablissementId}/settings`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData,
            });
            const payload = await response.json();
            if (!response.ok || !payload.success) throw new Error(payload.message || 'Sauvegarde impossible.');
            toast('Titre du formulaire de contact sauvegarde.');
        } catch (error) {
            toast(error.message || 'Erreur lors de la sauvegarde', 'error');
        } finally {
            if (button) button.disabled = false;
        }
    }

    function selectedIds() {
        return Array.from(document.querySelectorAll('.contact-message-checkbox:checked')).map(input => Number(input.value));
    }

    function syncBulkButtons() {
        const hasSelection = selectedIds().length > 0;
        document.getElementById('markSelectedReadBtn').disabled = !hasSelection;
        document.getElementById('archiveSelectedBtn').disabled = !hasSelection;
    }

    function updateMetrics(stats = {}) {
        document.getElementById('contactMetricTotal').textContent = stats.total ?? 0;
        document.getElementById('contactMetricNew').textContent = stats.new ?? 0;
        document.getElementById('contactMetricReplied').textContent = stats.replied ?? 0;
    }

    function filters() {
        return {
            search: document.getElementById('contactSearchInput').value,
            status: document.getElementById('contactStatusFilter').value,
            priority: document.getElementById('contactPriorityFilter').value,
        };
    }

    async function loadContactMessages(page = 1) {
        state.page = page;
        tbody.innerHTML = '<tr><td colspan="7" class="contact-empty">Chargement...</td></tr>';
        const params = new URLSearchParams({ ...filters(), page });
        const response = await fetch(`${routes.index}?${params}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        const payload = await response.json();
        if (!response.ok || !payload.success) throw new Error(payload.message || 'Chargement impossible.');
        state.lastPage = payload.last_page || 1;
        updateMetrics(payload.stats || {});
        renderRows(payload.data || []);
        document.getElementById('contactPageState').textContent = `Page ${state.page} / ${state.lastPage}`;
        document.getElementById('contactPrevPage').disabled = state.page <= 1;
        document.getElementById('contactNextPage').disabled = state.page >= state.lastPage;
        document.getElementById('selectAllContactMessages').checked = false;
        syncBulkButtons();
        state.loaded = true;
    }

    function renderRows(messages) {
        if (!messages.length) {
            tbody.innerHTML = '<tr><td colspan="7" class="contact-empty">Aucun message contact pour le moment.</td></tr>';
            return;
        }

        tbody.innerHTML = messages.map(message => `
            <tr class="${message.status === 'new' ? 'contact-row-new' : ''}" data-message-id="${message.id}">
                <td><input type="checkbox" class="contact-message-checkbox" value="${message.id}"></td>
                <td>
                    <div class="contact-person">
                        <strong>${escapeHtml(message.name)}</strong>
                        <span>${escapeHtml(message.email || message.phone || '-')}</span>
                    </div>
                </td>
                <td>
                    <div class="contact-subject">
                        <strong>${escapeHtml(message.subject)}</strong>
                        <span class="contact-preview">${escapeHtml(message.preview)}</span>
                    </div>
                </td>
                <td><span class="contact-pill contact-status-${message.status}">${escapeHtml(message.status_label)}</span></td>
                <td><span class="contact-pill contact-priority-${message.priority}">${escapeHtml(message.priority_label)}</span></td>
                <td><span class="contact-date">${escapeHtml(message.created_at_human || message.created_at || '-')}</span></td>
                <td class="text-end">
                    <span class="contact-row-actions">
                        <button type="button" class="contact-icon-btn" title="Voir" data-show-message="${message.id}"><i class="fas fa-eye"></i></button>
                        <button type="button" class="contact-icon-btn" title="Répondu" data-status-message="${message.id}" data-status="replied"><i class="fas fa-reply"></i></button>
                        <button type="button" class="contact-icon-btn" title="Archiver" data-status-message="${message.id}" data-status="archived"><i class="fas fa-archive"></i></button>
                        <button type="button" class="contact-icon-btn" title="Supprimer" data-delete-message="${message.id}"><i class="fas fa-trash"></i></button>
                    </span>
                </td>
            </tr>
        `).join('');
    }

    async function showMessage(id) {
        const response = await fetch(route('show', id), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        const payload = await response.json();
        if (!response.ok || !payload.success) throw new Error(payload.message || 'Message introuvable.');
        const message = payload.data;
        state.selectedId = message.id;
        updateMetrics(payload.stats || {});
        document.getElementById('contactModalSubject').textContent = message.subject || 'Sans objet';
        document.getElementById('contactModalMeta').textContent = `${message.created_at_human || ''} · ${message.form_name || 'contact'}`;
        document.getElementById('contactModalName').textContent = message.name || '-';
        document.getElementById('contactModalEmail').innerHTML = message.email ? `<a href="mailto:${escapeHtml(message.email)}">${escapeHtml(message.email)}</a>` : '-';
        document.getElementById('contactModalPhone').innerHTML = message.phone ? `<a href="tel:${escapeHtml(message.phone)}">${escapeHtml(message.phone)}</a>` : '-';
        document.getElementById('contactModalCompany').textContent = message.company || '-';
        document.getElementById('contactModalMessage').textContent = message.message || '';
        document.getElementById('contactModalTechnical').innerHTML = [
            message.source_url ? `Page source: ${escapeHtml(message.source_url)}` : '',
            message.ip_address ? `IP: ${escapeHtml(message.ip_address)}` : '',
            message.utm_source ? `UTM: ${escapeHtml(message.utm_source)} / ${escapeHtml(message.utm_medium || '-')}` : '',
        ].filter(Boolean).join('<br>');
        modal?.show();
        loadContactMessages(state.page).catch(() => {});
    }

    async function updateMessageStatus(id, status) {
        const response = await fetch(route('update', id), {
            method: 'PUT',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ status }),
        });
        const payload = await response.json();
        if (!response.ok || !payload.success) throw new Error(payload.message || 'Mise à jour impossible.');
        updateMetrics(payload.stats || {});
        toast(payload.message || 'Message mis à jour.');
        await loadContactMessages(state.page);
    }

    async function deleteMessage(id) {
        if (!confirm('Supprimer ce message contact ?')) return;
        const response = await fetch(route('destroy', id), {
            method: 'DELETE',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf },
        });
        const payload = await response.json();
        if (!response.ok || !payload.success) throw new Error(payload.message || 'Suppression impossible.');
        updateMetrics(payload.stats || {});
        toast(payload.message || 'Message supprimé.');
        await loadContactMessages(state.page);
    }

    async function bulkStatus(status) {
        const ids = selectedIds();
        if (!ids.length) return;
        const response = await fetch(routes.bulk, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ ids, status }),
        });
        const payload = await response.json();
        if (!response.ok || !payload.success) throw new Error(payload.message || 'Action impossible.');
        updateMetrics(payload.stats || {});
        toast(payload.message || 'Messages mis à jour.');
        await loadContactMessages(state.page);
    }

    let filterTimer;
    ['contactSearchInput', 'contactStatusFilter', 'contactPriorityFilter'].forEach(id => {
        const element = document.getElementById(id);
        element.addEventListener(id === 'contactSearchInput' ? 'input' : 'change', () => {
            clearTimeout(filterTimer);
            filterTimer = setTimeout(() => loadContactMessages(1).catch(error => toast(error.message, 'error')), 250);
        });
    });

    document.getElementById('refreshContactMessagesBtn').addEventListener('click', () => loadContactMessages(state.page).catch(error => toast(error.message, 'error')));
    document.getElementById('contactPrevPage').addEventListener('click', () => loadContactMessages(Math.max(1, state.page - 1)).catch(error => toast(error.message, 'error')));
    document.getElementById('contactNextPage').addEventListener('click', () => loadContactMessages(Math.min(state.lastPage, state.page + 1)).catch(error => toast(error.message, 'error')));
    document.getElementById('selectAllContactMessages').addEventListener('change', event => {
        document.querySelectorAll('.contact-message-checkbox').forEach(input => input.checked = event.target.checked);
        syncBulkButtons();
    });
    document.getElementById('markSelectedReadBtn').addEventListener('click', () => bulkStatus('read').catch(error => toast(error.message, 'error')));
    document.getElementById('archiveSelectedBtn').addEventListener('click', () => bulkStatus('archived').catch(error => toast(error.message, 'error')));
    document.getElementById('saveContactFormTitleBtn')?.addEventListener('click', () => saveContactFormTitle());

    tbody.addEventListener('change', event => {
        if (event.target.classList.contains('contact-message-checkbox')) syncBulkButtons();
    });
    tbody.addEventListener('click', event => {
        const showButton = event.target.closest('[data-show-message]');
        const statusButton = event.target.closest('[data-status-message]');
        const deleteButton = event.target.closest('[data-delete-message]');
        if (showButton) showMessage(showButton.dataset.showMessage).catch(error => toast(error.message, 'error'));
        if (statusButton) updateMessageStatus(statusButton.dataset.statusMessage, statusButton.dataset.status).catch(error => toast(error.message, 'error'));
        if (deleteButton) deleteMessage(deleteButton.dataset.deleteMessage).catch(error => toast(error.message, 'error'));
    });
    modalElement?.addEventListener('click', event => {
        const button = event.target.closest('[data-contact-status]');
        if (!button || !state.selectedId) return;
        updateMessageStatus(state.selectedId, button.dataset.contactStatus)
            .then(() => modal?.hide())
            .catch(error => toast(error.message, 'error'));
    });

    document.querySelector('[data-section="v-pills-contact-messages"]')?.addEventListener('click', () => {
        if (!state.loaded) loadContactMessages().catch(error => toast(error.message, 'error'));
    });

    if (new URLSearchParams(window.location.search).get('section') === 'contact-messages') {
        loadContactMessages().catch(error => toast(error.message, 'error'));
    }
});
</script>
