@extends('layouts.app')

@php
    $routes = [
        'index' => route('billing.requests.index'),
        'show' => route('billing.requests.show', ['id' => '__ID__']),
        'status' => route('billing.requests.status', ['id' => '__ID__']),
        'quote' => route('billing.requests.quote', ['id' => '__ID__']),
        'invoice' => route('billing.requests.invoice', ['id' => '__ID__']),
        'sendInvoice' => route('billing.requests.send-invoice', ['id' => '__ID__']),
        'destroy' => route('billing.requests.destroy', ['id' => '__ID__']),
        'bulkDestroy' => route('billing.requests.bulk-destroy'),
        'canDelete' => route('billing.requests.can-delete', ['id' => '__ID__']),
        'services' => route('billing.request-services.index'),
        'settings' => route('billing.settings.index'),
        'invoices' => route('invoices.index'),
        'quotes' => route('quotes.index'),
    ];
@endphp

@section('content')
<main class="dashboard-content billing-requests-page">
    <div class="billing-head">
        <div>
            <p class="billing-kicker">Facturation</p>
            <h1>Demandes reçues</h1>
            <span class="billing-subtitle">Traitez les demandes du front, puis générez un devis, une facture PDF ou un envoi email.</span>
        </div>
        <div class="billing-actions">
            <button type="button" class="btn btn-outline-danger" id="bulkDeleteBtn" style="display:none;">
                <i class="fas fa-trash me-2"></i>Supprimer sélection (<span id="bulkCount">0</span>)
            </button>
            <a href="{{ $routes['services'] }}" class="btn btn-primary"><i class="fas fa-sliders-h me-2"></i>Options front</a>
            <a href="{{ $routes['quotes'] }}" class="btn btn-outline-secondary"><i class="fas fa-file-signature me-2"></i>Devis</a>
            <a href="{{ $routes['invoices'] }}" class="btn btn-outline-secondary"><i class="fas fa-file-invoice-dollar me-2"></i>Factures</a>
            <a href="{{ $routes['settings'] }}" class="btn btn-outline-secondary"><i class="fas fa-cog me-2"></i>Paramètres</a>
        </div>
    </div>

    <section class="metric-grid">
        <div class="metric"><span>Total</span><strong>{{ $stats['total'] ?? 0 }}</strong></div>
        <div class="metric info"><span>Nouvelles</span><strong>{{ $stats['new'] ?? 0 }}</strong></div>
        <div class="metric warning"><span>Devis créés</span><strong>{{ $stats['quoted'] ?? 0 }}</strong></div>
        <div class="metric success"><span>Montant total</span><strong>{{ number_format((float)($stats['total_amount'] ?? 0), 2, ',', ' ') }} $</strong></div>
    </section>

    <section class="billing-panel">
        <div class="filters-row">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="search" id="filterSearch" placeholder="Rechercher numéro, demandeur, entreprise...">
            </div>
            <select id="filterStatus">
                <option value="">Tous les statuts</option>
                <option value="new">Nouvelle</option>
                <option value="reviewed">Revue</option>
                <option value="quoted">Devis créé</option>
                <option value="invoiced">Facture créée</option>
                <option value="sent">Facture envoyée</option>
                <option value="closed">Terminée</option>
                <option value="cancelled">Annulée</option>
            </select>
            <button type="button" class="btn btn-outline-secondary" id="resetFilters"><i class="fas fa-undo me-2"></i>Réinitialiser</button>
        </div>

        <div class="table-wrap">
            <table class="billing-table">
                <thead>
                    <tr>
                        <th style="width:40px;">
                            <input type="checkbox" id="selectAllRequests" title="Sélectionner tout">
                        </th>
                        <th>Demande</th>
                        <th>Demandeur</th>
                        <th>Options</th>
                        <th>Statut</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="requestRows">
                    <tr><td colspan="7" class="empty-cell">Chargement...</td></tr>
                </tbody>
            </table>
        </div>

        <div class="pagination-row">
            <button type="button" class="btn btn-outline-secondary" id="prevPage">Précédent</button>
            <span id="pageState">Page 1</span>
            <button type="button" class="btn btn-outline-secondary" id="nextPage">Suivant</button>
        </div>
    </section>

    <div id="billingToastHost" class="billing-toast-host"></div>

    <!-- Modal de confirmation de suppression -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle text-danger me-2"></i>Confirmer la suppression</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body" id="deleteConfirmBody">
                    <p id="deleteConfirmMessage">Êtes-vous sûr de vouloir supprimer cette demande ?</p>
                    <p class="text-muted small">Cette action est irréversible et supprimera également tous les éléments associés.</p>
                    <div id="deleteWarning" class="alert alert-warning" style="display:none;">
                        <i class="fas fa-info-circle me-2"></i>
                        <span id="deleteWarningText"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                        <i class="fas fa-trash me-2"></i>Supprimer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de détail (existant) -->
    <div class="modal fade" id="requestModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content request-detail-modal">
                <div class="modal-header">
                    <div>
                        <p class="billing-kicker mb-1">Demande front</p>
                        <h5 class="modal-title" id="requestModalTitle">Détails</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body" id="requestDetailBody">
                    <div class="empty-cell">Chargement...</div>
                </div>
                <div class="modal-footer" id="requestModalActions"></div>
            </div>
        </div>
    </div>
</main>

<style>
    .billing-requests-page { padding: 28px; background: #f3f6fa; min-height: calc(100vh - 72px); color: #172033; }
    .billing-head { display: flex; justify-content: space-between; gap: 18px; align-items: center; margin-bottom: 22px; padding: 20px; border: 1px solid #dbe3ee; border-radius: 8px; background: linear-gradient(135deg, #fff 0%, #f8fbff 100%); box-shadow: 0 14px 32px rgba(15, 23, 42, .06); }
    .billing-head h1 { margin: 0; font-size: 30px; font-weight: 850; color: #0f172a; letter-spacing: 0; }
    .billing-kicker { margin: 0 0 5px; color: #2563eb; text-transform: uppercase; font-size: 12px; font-weight: 850; }
    .billing-subtitle { display: block; color: #64748b; font-weight: 650; }
    .billing-actions { display: flex; flex-wrap: wrap; gap: 10px; }
    .billing-actions .btn { min-height: 40px; border-radius: 7px; font-weight: 750; }
    .metric-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; margin-bottom: 18px; }
    .metric, .billing-panel { background: #fff; border: 1px solid #dbe3ee; border-radius: 8px; box-shadow: 0 12px 28px rgba(15, 23, 42, .05); }
    .metric { position: relative; overflow: hidden; min-height: 92px; padding: 16px; display: grid; gap: 8px; }
    .metric::before { content: ""; position: absolute; inset: 0 0 auto; height: 3px; background: #2563eb; }
    .metric.info::before { background: #0ea5e9; }
    .metric.warning::before { background: #f59e0b; }
    .metric.success::before { background: #16a34a; }
    .metric span { color: #64748b; font-size: 12px; font-weight: 800; text-transform: uppercase; }
    .metric strong { font-size: 24px; font-weight: 850; color: #0f172a; line-height: 1.1; }
    .billing-panel { overflow: hidden; }
    .filters-row { display: grid; grid-template-columns: minmax(260px, 1fr) 210px auto; gap: 10px; padding: 16px; border-bottom: 1px solid #e2e8f0; background: #fbfdff; }
    .filters-row input, .filters-row select { border: 1px solid #cbd5e1; border-radius: 7px; padding: 10px 11px; min-height: 42px; background: #fff; color: #172033; font-weight: 650; }
    .filters-row input:focus, .filters-row select:focus { outline: 0; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, .12); }
    .search-box { position: relative; }
    .search-box i { position: absolute; left: 12px; top: 13px; color: #64748b; }
    .search-box input { width: 100%; padding-left: 36px; }
    .table-wrap { overflow-x: auto; }
    .billing-table { width: 100%; border-collapse: separate; border-spacing: 0; }
    .billing-table th { color: #64748b; font-size: 11px; text-transform: uppercase; padding: 13px 14px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; white-space: nowrap; letter-spacing: .02em; }
    .billing-table td { padding: 14px; border-bottom: 1px solid #eef2f7; vertical-align: middle; color: #334155; }
    .billing-table tbody tr:hover { background: #f8fbff; }
    .billing-table tbody tr.selected { background: #dbeafe; }
    .empty-cell { text-align: center; color: #64748b; padding: 34px !important; }
    .status-pill { display: inline-flex; padding: 6px 10px; border-radius: 999px; font-weight: 850; font-size: 12px; background: #e2e8f0; color: #334155; }
    .status-new { background: #dbeafe; color: #1d4ed8; }
    .status-reviewed { background: #e0f2fe; color: #0369a1; }
    .status-quoted { background: #fef3c7; color: #92400e; }
    .status-invoiced, .status-sent { background: #dcfce7; color: #166534; }
    .status-cancelled { background: #fee2e2; color: #991b1b; }
    .row-actions { display: inline-flex; gap: 6px; justify-content: flex-end; }
    .action-btn { width: 34px; height: 34px; border: 1px solid #d6dfeb; border-radius: 7px; background: #fff; color: #475569; display: inline-flex; align-items: center; justify-content: center; transition: .16s ease; }
    .action-btn:hover { color: #2563eb; border-color: #9db7ec; transform: translateY(-1px); box-shadow: 0 8px 18px rgba(37, 99, 235, .12); }
    .action-btn.success:hover { color: #16a34a; border-color: #86efac; box-shadow: 0 8px 18px rgba(22, 163, 74, .12); }
    .action-btn.warning:hover { color: #d97706; border-color: #fcd34d; box-shadow: 0 8px 18px rgba(217, 119, 6, .12); }
    .action-btn.danger:hover { color: #dc2626; border-color: #fca5a5; box-shadow: 0 8px 18px rgba(220, 38, 38, .12); }
    .action-btn:disabled { opacity: 0.4; cursor: not-allowed; }
    .pagination-row { display: flex; justify-content: flex-end; align-items: center; gap: 12px; padding: 14px 16px; background: #fbfdff; }
    .pagination-row span { color: #64748b; font-weight: 750; }
    .detail-grid { display: grid; grid-template-columns: .9fr 1.1fr; gap: 16px; }
    .detail-card { border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; background: #fff; }
    .detail-card h6 { margin: 0 0 12px; font-weight: 850; color: #0f172a; }
    .detail-list { display: grid; gap: 8px; margin: 0; }
    .detail-list div { display: grid; grid-template-columns: 130px 1fr; gap: 10px; color: #334155; }
    .detail-list dt { color: #64748b; font-weight: 850; }
    .items-table { width: 100%; border-collapse: collapse; }
    .items-table th, .items-table td { padding: 10px; border-bottom: 1px solid #eef2f7; vertical-align: top; }
    .items-table th { font-size: 11px; text-transform: uppercase; color: #64748b; background: #f8fafc; }
    .totals-box { margin-top: 14px; display: grid; justify-content: end; gap: 6px; }
    .totals-box div { min-width: 260px; display: flex; justify-content: space-between; gap: 20px; }
    .totals-box strong { color: #0f172a; }
    .billing-toast-host { position: fixed; right: 20px; bottom: 20px; z-index: 1080; display: grid; gap: 10px; }
    .billing-toast { padding: 12px 14px; border-radius: 8px; color: #fff; background: #16a34a; box-shadow: 0 15px 30px rgba(15, 23, 42, .18); min-width: 240px; font-weight: 750; animation: slideIn .3s ease; }
    .billing-toast.error { background: #dc2626; }
    .billing-toast.warning { background: #f59e0b; }
    .checkbox-cell { text-align: center; }
    .checkbox-cell input[type="checkbox"] { width: 16px; height: 16px; cursor: pointer; }
    #selectAllRequests { width: 16px; height: 16px; cursor: pointer; }
    @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
    @media (max-width: 1000px) { .metric-grid { grid-template-columns: repeat(2, 1fr); } .filters-row, .detail-grid { grid-template-columns: 1fr; } }
    @media (max-width: 640px) { .billing-requests-page { padding: 14px; } .billing-head { display: grid; padding: 16px; } .metric-grid { grid-template-columns: 1fr; } }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const routes = @json($routes);
    const csrf = @json(csrf_token());
    const rows = document.getElementById('requestRows');
    const modalEl = document.getElementById('requestModal');
    const modal = new bootstrap.Modal(modalEl);
    const deleteModalEl = document.getElementById('deleteConfirmModal');
    const deleteModal = new bootstrap.Modal(deleteModalEl);
    const state = { 
        page: 1, 
        lastPage: 1, 
        selectedIds: new Set(),
        deleteTarget: null, // { type: 'single', id: 123 } ou { type: 'bulk', ids: [1,2,3] }
    };
    const money = new Intl.NumberFormat('fr-CA', { style: 'currency', currency: 'CAD' });
    const route = (name, id) => routes[name].replace('__ID__', id);
    const escapeHtml = (value = '') => String(value).replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]));

    // Toast notifications
    const toast = (message, type = 'success') => {
        const host = document.getElementById('billingToastHost');
        const item = document.createElement('div');
        item.className = `billing-toast ${type}`;
        item.textContent = message;
        host.appendChild(item);
        setTimeout(() => item.remove(), 4200);
    };

    // Filters
    const filters = () => ({
        ajax: 1,
        search: document.getElementById('filterSearch').value,
        status: document.getElementById('filterStatus').value,
    });

    // Load requests
    const loadRequests = async (page = 1) => {
        state.page = page;
        rows.innerHTML = '<tr><td colspan="7" class="empty-cell">Chargement...</td></tr>';
        state.selectedIds.clear();
        updateBulkDeleteButton();
        document.getElementById('selectAllRequests').checked = false;
        
        const params = new URLSearchParams({ ...filters(), page });
        const response = await fetch(`${routes.index}?${params}`, { 
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } 
        });
        const payload = await response.json();
        state.lastPage = payload.last_page || 1;
        renderRequests(payload.data || []);
        document.getElementById('pageState').textContent = `Page ${state.page} / ${state.lastPage}`;
        document.getElementById('prevPage').disabled = state.page <= 1;
        document.getElementById('nextPage').disabled = state.page >= state.lastPage;
    };

    // Render requests
    const renderRequests = (items) => {
        if (!items.length) {
            rows.innerHTML = '<tr><td colspan="7" class="empty-cell">Aucune demande trouvée.</td></tr>';
            return;
        }

        rows.innerHTML = items.map(item => {
            const hasInvoice = item.invoice_id !== null && item.invoice_id !== undefined;
            const hasQuote = item.quote_id !== null && item.quote_id !== undefined;
            const canDelete = !hasInvoice && !hasQuote && !['closed', 'sent', 'invoiced'].includes(item.status);
            const isSelected = state.selectedIds.has(item.id);
            
            return `
                <tr class="${isSelected ? 'selected' : ''}" data-id="${item.id}">
                    <td class="checkbox-cell">
                        <input type="checkbox" class="row-checkbox" data-id="${item.id}" ${isSelected ? 'checked' : ''} ${canDelete ? '' : 'disabled'}>
                    </td>
                    <td>
                        <strong>${escapeHtml(item.request_number)}</strong>
                        <div class="text-muted small">${escapeHtml(item.submitted_at || '')}</div>
                    </td>
                    <td>
                        <strong>${escapeHtml(item.company || item.name || '-')}</strong>
                        <div class="text-muted small">${escapeHtml(item.email || '')}${item.phone ? ' - ' + escapeHtml(item.phone) : ''}</div>
                    </td>
                    <td>${Number(item.items_count || 0)} option(s)</td>
                    <td><span class="status-pill status-${escapeHtml(item.status)}">${escapeHtml(item.status_label || item.status)}</span></td>
                    <td class="text-end"><strong>${money.format(Number(item.total || 0))}</strong></td>
                    <td class="text-end">
                        <span class="row-actions">
                            <button type="button" class="action-btn" data-view="${item.id}" title="Voir"><i class="fas fa-eye"></i></button>
                            <button type="button" class="action-btn warning" data-quote="${item.id}" title="Créer devis" ${hasQuote ? 'disabled' : ''}><i class="fas fa-file-signature"></i></button>
                            <button type="button" class="action-btn success" data-invoice="${item.id}" title="Créer facture" ${hasInvoice ? 'disabled' : ''}><i class="fas fa-file-invoice-dollar"></i></button>
                            <button type="button" class="action-btn success" data-send="${item.id}" title="Envoyer facture" ${!hasInvoice ? 'disabled' : ''}><i class="fas fa-paper-plane"></i></button>
                            ${canDelete ? `
                                <button type="button" class="action-btn danger" data-delete="${item.id}" title="Supprimer"><i class="fas fa-trash"></i></button>
                            ` : `
                                <button type="button" class="action-btn" disabled title="Non supprimable"><i class="fas fa-trash" style="opacity:0.3;"></i></button>
                            `}
                        </span>
                    </td>
                </tr>
            `;
        }).join('');
    };

    // Render detail
    const renderDetail = (item) => {
        const hasInvoice = item.invoice_id !== null && item.invoice_id !== undefined;
        const hasQuote = item.quote_id !== null && item.quote_id !== undefined;
        const canDelete = !hasInvoice && !hasQuote && !['closed', 'sent', 'invoiced'].includes(item.status);
        
        document.getElementById('requestModalTitle').textContent = item.request_number || 'Détails demande';
        document.getElementById('requestDetailBody').innerHTML = `
            <div class="detail-grid">
                <section class="detail-card">
                    <h6>Demandeur</h6>
                    <dl class="detail-list">
                        <div><dt>Nom</dt><dd>${escapeHtml(item.name || '-')}</dd></div>
                        <div><dt>Entreprise</dt><dd>${escapeHtml(item.company || '-')}</dd></div>
                        <div><dt>Email</dt><dd>${escapeHtml(item.email || '-')}</dd></div>
                        <div><dt>Téléphone</dt><dd>${escapeHtml(item.phone || '-')}</dd></div>
                        <div><dt>Adresse</dt><dd>${escapeHtml([item.address, item.city, item.zipcode, item.country].filter(Boolean).join(', ') || '-')}</dd></div>
                        <div><dt>Message</dt><dd>${escapeHtml(item.message || '-')}</dd></div>
                        ${item.invoice_id ? `<div><dt>Facture</dt><dd>#${escapeHtml(item.invoice_number || item.invoice_id)}</dd></div>` : ''}
                        ${item.quote_id ? `<div><dt>Devis</dt><dd>#${escapeHtml(item.quote_number || item.quote_id)}</dd></div>` : ''}
                    </dl>
                </section>
                <section class="detail-card">
                    <h6>Options sélectionnées</h6>
                    <table class="items-table">
                        <thead><tr><th>Option</th><th>Qt</th><th class="text-end">Prix</th><th class="text-end">Total</th></tr></thead>
                        <tbody>
                            ${(item.items || []).map(line => `
                                <tr>
                                    <td><strong>${escapeHtml(line.title)}</strong><div class="text-muted small">${escapeHtml(line.description || '')}</div></td>
                                    <td>${Number(line.quantity || 1)}</td>
                                    <td class="text-end">${money.format(Number(line.unit_price || 0))}</td>
                                    <td class="text-end">${money.format(Number(line.total || 0))}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                    <div class="totals-box">
                        <div><span>Sous-total</span><strong>${money.format(Number(item.subtotal || 0))}</strong></div>
                        <div><span>Taxes</span><strong>${money.format(Number(item.tax_total || 0))}</strong></div>
                        <div><span>Total</span><strong>${money.format(Number(item.total || 0))}</strong></div>
                    </div>
                </section>
            </div>
        `;
        
        let buttons = `
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fermer</button>
        `;
        
        if (!hasQuote) {
            buttons += `<button type="button" class="btn btn-warning" data-modal-quote="${item.id}"><i class="fas fa-file-signature me-2"></i>Générer devis</button>`;
        }
        if (!hasInvoice) {
            buttons += `<button type="button" class="btn btn-success" data-modal-invoice="${item.id}"><i class="fas fa-file-invoice-dollar me-2"></i>Générer facture</button>`;
        }
        if (hasInvoice) {
            buttons += `<button type="button" class="btn btn-primary" data-modal-send="${item.id}"><i class="fas fa-paper-plane me-2"></i>Envoyer facture</button>`;
        }
        if (canDelete) {
            buttons += `<button type="button" class="btn btn-danger" data-modal-delete="${item.id}"><i class="fas fa-trash me-2"></i>Supprimer</button>`;
        }
        
        document.getElementById('requestModalActions').innerHTML = buttons;
        modal.show();
    };

    // Get detail
    const getDetail = async (id) => {
        document.getElementById('requestDetailBody').innerHTML = '<div class="empty-cell">Chargement...</div>';
        modal.show();
        const response = await fetch(route('show', id), { 
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } 
        });
        const payload = await response.json();
        if (!response.ok || !payload.success) {
            toast(payload.message || 'Demande introuvable.', 'error');
            modal.hide();
            return;
        }
        renderDetail(payload.data);
    };

    // Perform action (quote, invoice, send)
    const action = async (name, id) => {
        const response = await fetch(route(name, id), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        const payload = await response.json();
        toast(payload.message || 'Opération terminée.', response.ok && payload.success ? 'success' : 'error');
        if (payload.redirect_url) {
            window.open(payload.redirect_url, '_blank');
        }
        await loadRequests(state.page);
    };

    // Delete single request
    const deleteRequest = async (id) => {
        try {
            const response = await fetch(route('destroy', id), {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const payload = await response.json();
            
            if (response.ok && payload.success) {
                toast(payload.message, 'success');
                await loadRequests(state.page);
                deleteModal.hide();
            } else {
                toast(payload.message || 'Erreur lors de la suppression.', 'error');
            }
        } catch (error) {
            toast('Une erreur est survenue lors de la suppression.', 'error');
            console.error(error);
        }
    };

    // Bulk delete
    const bulkDelete = async (ids) => {
        try {
            const response = await fetch(routes.bulkDestroy, {
                method: 'POST',
                headers: { 
                    'X-CSRF-TOKEN': csrf, 
                    'Accept': 'application/json', 
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ ids: Array.from(ids) }),
            });
            const payload = await response.json();
            
            if (response.ok && payload.success) {
                toast(payload.message, 'success');
                state.selectedIds.clear();
                updateBulkDeleteButton();
                await loadRequests(state.page);
                deleteModal.hide();
            } else {
                toast(payload.message || 'Erreur lors de la suppression en masse.', 'error');
            }
        } catch (error) {
            toast('Une erreur est survenue lors de la suppression en masse.', 'error');
            console.error(error);
        }
    };

    // Show delete confirmation modal
    const showDeleteConfirmation = (type, id = null) => {
        if (type === 'single') {
            state.deleteTarget = { type: 'single', id: id };
            document.getElementById('deleteConfirmMessage').textContent = 'Êtes-vous sûr de vouloir supprimer cette demande ?';
            document.getElementById('deleteWarning').style.display = 'none';
        } else if (type === 'bulk') {
            const count = state.selectedIds.size;
            state.deleteTarget = { type: 'bulk', ids: new Set(state.selectedIds) };
            document.getElementById('deleteConfirmMessage').textContent = `Êtes-vous sûr de vouloir supprimer ${count} demande(s) ?`;
            document.getElementById('deleteWarning').style.display = 'none';
        }
        deleteModal.show();
    };

    // Update bulk delete button
    const updateBulkDeleteButton = () => {
        const count = state.selectedIds.size;
        const btn = document.getElementById('bulkDeleteBtn');
        const countSpan = document.getElementById('bulkCount');
        if (count > 0) {
            btn.style.display = 'inline-flex';
            countSpan.textContent = count;
        } else {
            btn.style.display = 'none';
        }
    };

    // Event listeners for row actions
    rows.addEventListener('click', event => {
        const viewBtn = event.target.closest('[data-view]');
        const quoteBtn = event.target.closest('[data-quote]');
        const invoiceBtn = event.target.closest('[data-invoice]');
        const sendBtn = event.target.closest('[data-send]');
        const deleteBtn = event.target.closest('[data-delete]');
        
        if (viewBtn) getDetail(viewBtn.dataset.view);
        if (quoteBtn) action('quote', quoteBtn.dataset.quote);
        if (invoiceBtn) action('invoice', invoiceBtn.dataset.invoice);
        if (sendBtn) action('sendInvoice', sendBtn.dataset.send);
        if (deleteBtn) showDeleteConfirmation('single', parseInt(deleteBtn.dataset.delete));
    });

    // Event listeners for modal actions
    document.getElementById('requestModalActions').addEventListener('click', event => {
        const quoteBtn = event.target.closest('[data-modal-quote]');
        const invoiceBtn = event.target.closest('[data-modal-invoice]');
        const sendBtn = event.target.closest('[data-modal-send]');
        const deleteBtn = event.target.closest('[data-modal-delete]');
        
        if (quoteBtn) { modal.hide(); action('quote', quoteBtn.dataset.modalQuote); }
        if (invoiceBtn) { modal.hide(); action('invoice', invoiceBtn.dataset.modalInvoice); }
        if (sendBtn) { modal.hide(); action('sendInvoice', sendBtn.dataset.modalSend); }
        if (deleteBtn) { modal.hide(); showDeleteConfirmation('single', parseInt(deleteBtn.dataset.modalDelete)); }
    });

    // Confirm delete button
    document.getElementById('confirmDeleteBtn').addEventListener('click', async () => {
        const target = state.deleteTarget;
        if (!target) return;
        
        if (target.type === 'single') {
            await deleteRequest(target.id);
        } else if (target.type === 'bulk') {
            await bulkDelete(target.ids);
        }
        state.deleteTarget = null;
    });

    // Select all checkbox
    document.getElementById('selectAllRequests').addEventListener('change', (event) => {
        const checked = event.target.checked;
        const checkboxes = document.querySelectorAll('.row-checkbox:not(:disabled)');
        checkboxes.forEach(cb => {
            cb.checked = checked;
            const id = parseInt(cb.dataset.id);
            if (checked) {
                state.selectedIds.add(id);
            } else {
                state.selectedIds.delete(id);
            }
        });
        updateBulkDeleteButton();
        // Update row highlighting
        document.querySelectorAll('tbody tr').forEach(tr => {
            const cb = tr.querySelector('.row-checkbox');
            if (cb && !cb.disabled) {
                tr.classList.toggle('selected', cb.checked);
            }
        });
    });

    // Individual row checkbox
    rows.addEventListener('change', (event) => {
        const cb = event.target.closest('.row-checkbox');
        if (!cb) return;
        
        const id = parseInt(cb.dataset.id);
        if (cb.checked) {
            state.selectedIds.add(id);
        } else {
            state.selectedIds.delete(id);
        }
        updateBulkDeleteButton();
        // Update row highlighting
        const tr = cb.closest('tr');
        if (tr) tr.classList.toggle('selected', cb.checked);
        // Update select all checkbox
        const allCheckboxes = document.querySelectorAll('.row-checkbox:not(:disabled)');
        const checkedCheckboxes = document.querySelectorAll('.row-checkbox:not(:disabled):checked');
        document.getElementById('selectAllRequests').checked = allCheckboxes.length > 0 && allCheckboxes.length === checkedCheckboxes.length;
    });

    // Bulk delete button
    document.getElementById('bulkDeleteBtn').addEventListener('click', () => {
        if (state.selectedIds.size > 0) {
            showDeleteConfirmation('bulk');
        }
    });

    // Filter events
    document.getElementById('filterSearch').addEventListener('input', () => loadRequests(1));
    document.getElementById('filterStatus').addEventListener('change', () => loadRequests(1));
    document.getElementById('resetFilters').addEventListener('click', () => {
        document.getElementById('filterSearch').value = '';
        document.getElementById('filterStatus').value = '';
        loadRequests(1);
    });
    
    // Pagination
    document.getElementById('prevPage').addEventListener('click', () => loadRequests(Math.max(1, state.page - 1)));
    document.getElementById('nextPage').addEventListener('click', () => loadRequests(Math.min(state.lastPage, state.page + 1)));

    // Initial load
    loadRequests();
});
</script>
@endsection