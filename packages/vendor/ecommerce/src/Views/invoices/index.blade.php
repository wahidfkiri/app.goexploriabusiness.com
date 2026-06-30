@extends('layouts.app')

@php
    $routes = [
        'index' => route('invoices.index'),
        'create' => route('invoices.create'),
        'statistics' => route('invoices.statistics'),
        'show' => route('invoices.show', ['invoice' => '__ID__']),
        'edit' => route('invoices.edit', ['invoice' => '__ID__']),
        'pdf' => route('invoices.pdf', ['invoice' => '__ID__']),
        'send' => route('invoices.send', ['invoice' => '__ID__']),
        'paid' => route('invoices.mark-paid', ['invoice' => '__ID__']),
        'quotes' => route('quotes.index'),
    ];
@endphp

@section('content')
<main class="dashboard-content billing-index">
    <div class="billing-head">
        <div>
            <p class="billing-kicker">Facturation</p>
            <h1>Factures</h1>
        </div>
        <div class="billing-actions">
            <a href="{{ $routes['quotes'] }}" class="btn btn-outline-secondary"><i class="fas fa-file-signature me-2"></i>Devis</a>
            <a href="{{ $routes['create'] }}" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Nouvelle facture</a>
        </div>
    </div>

    <section class="metric-grid">
        <div class="metric"><span>Total</span><strong id="metricCount">{{ $stats['total'] ?? 0 }}</strong></div>
        <div class="metric"><span>Montant facturé</span><strong id="metricTotal">{{ number_format((float)($stats['total_amount'] ?? 0), 2, ',', ' ') }} $</strong></div>
        <div class="metric"><span>Payé</span><strong id="metricPaid">{{ number_format((float)($stats['paid_amount'] ?? 0), 2, ',', ' ') }} $</strong></div>
        <div class="metric danger"><span>En retard</span><strong id="metricLate">{{ $stats['overdue_count'] ?? 0 }}</strong></div>
    </section>

    <section class="billing-panel">
        <div class="filters-row">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="search" id="filterSearch" placeholder="Rechercher numéro, client...">
            </div>
            <select id="filterStatus">
                <option value="">Tous les statuts</option>
                <option value="brouillon">Brouillon</option>
                <option value="envoyee">Envoyée</option>
                <option value="en_attente">En attente</option>
                <option value="payee">Payée</option>
                <option value="partiellement_payee">Partiellement payée</option>
                <option value="en_retard">En retard</option>
                <option value="annulee">Annulée</option>
            </select>
            <select id="filterClient">
                <option value="">Tous les clients</option>
                @foreach($clients as $client)
                    @php($clientName = $client->name ?: $client->lname ?: 'Établissement #' . $client->id)
                    <option value="{{ $client->id }}">{{ $clientName }}</option>
                @endforeach
            </select>
            <button type="button" class="btn btn-outline-secondary" id="resetFilters"><i class="fas fa-undo me-2"></i>Réinitialiser</button>
        </div>

        <div class="table-wrap">
            <table class="billing-table">
                <thead>
                    <tr>
                        <th>Facture</th>
                        <th>Client</th>
                        <th>Date</th>
                        <th>Échéance</th>
                        <th>Statut</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="invoiceRows">
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
</main>

<style>
    .billing-index { padding: 28px; background: #f3f6fa; min-height: calc(100vh - 72px); color: #172033; }
    .billing-head { display: flex; justify-content: space-between; gap: 18px; align-items: center; margin-bottom: 22px; padding: 20px; border: 1px solid #dbe3ee; border-radius: 8px; background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%); box-shadow: 0 14px 32px rgba(15, 23, 42, .06); }
    .billing-head h1 { margin: 0; font-size: 30px; font-weight: 850; letter-spacing: 0; color: #0f172a; }
    .billing-kicker { margin: 0 0 5px; color: #2563eb; text-transform: uppercase; font-size: 12px; font-weight: 850; }
    .billing-actions { display: flex; gap: 10px; flex-wrap: wrap; }
    .billing-actions .btn { min-height: 40px; border-radius: 7px; font-weight: 750; }
    .metric-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; margin-bottom: 18px; }
    .metric, .billing-panel { background: #fff; border: 1px solid #dbe3ee; border-radius: 8px; box-shadow: 0 12px 28px rgba(15, 23, 42, .05); }
    .metric { padding: 16px; display: grid; gap: 8px; position: relative; overflow: hidden; min-height: 92px; }
    .metric::before { content: ""; position: absolute; inset: 0 0 auto; height: 3px; background: #2563eb; }
    .metric:nth-child(2)::before { background: #0f766e; }
    .metric:nth-child(3)::before { background: #16a34a; }
    .metric.danger::before { background: #dc2626; }
    .metric span { color: #64748b; font-size: 12px; font-weight: 800; text-transform: uppercase; }
    .metric strong { font-size: 24px; font-weight: 850; color: #0f172a; line-height: 1.1; }
    .metric.danger strong { color: #dc2626; }
    .billing-panel { padding: 0; overflow: hidden; }
    .filters-row { display: grid; grid-template-columns: minmax(260px, 1fr) 190px 240px auto; gap: 10px; padding: 16px; border-bottom: 1px solid #e2e8f0; background: #fbfdff; }
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
    .empty-cell { text-align: center; color: #64748b; padding: 34px !important; }
    .status-pill { display: inline-flex; padding: 6px 10px; border-radius: 999px; font-weight: 850; font-size: 12px; background: #e2e8f0; color: #334155; }
    .status-payee { background: #dcfce7; color: #166534; }
    .status-envoyee, .status-en_attente { background: #dbeafe; color: #1d4ed8; }
    .status-annulee { background: #e5e7eb; color: #374151; }
    .status-en_retard { background: #fee2e2; color: #991b1b; }
    .row-actions { display: inline-flex; gap: 6px; justify-content: flex-end; }
    .action-btn { width: 34px; height: 34px; border: 1px solid #d6dfeb; border-radius: 7px; background: #fff; color: #475569; display: inline-flex; align-items: center; justify-content: center; transition: .16s ease; }
    .action-btn:hover { color: #2563eb; border-color: #9db7ec; transform: translateY(-1px); box-shadow: 0 8px 18px rgba(37, 99, 235, .12); }
    .pagination-row { display: flex; justify-content: flex-end; align-items: center; gap: 12px; padding: 14px 16px; background: #fbfdff; }
    .pagination-row span { color: #64748b; font-weight: 750; }
    .billing-toast-host { position: fixed; right: 20px; bottom: 20px; z-index: 1080; display: grid; gap: 10px; }
    .billing-toast { padding: 12px 14px; border-radius: 8px; color: #fff; background: #16a34a; box-shadow: 0 15px 30px rgba(15, 23, 42, .18); min-width: 240px; font-weight: 750; }
    .billing-toast.error { background: #dc2626; }
    @media (max-width: 1000px) { .metric-grid { grid-template-columns: repeat(2, 1fr); } .filters-row { grid-template-columns: 1fr 1fr; } }
    @media (max-width: 640px) { .billing-index { padding: 14px; } .billing-head { display: grid; padding: 16px; } .metric-grid, .filters-row { grid-template-columns: 1fr; } }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const routes = @json($routes);
    const csrf = @json(csrf_token());
    const rows = document.getElementById('invoiceRows');
    const state = { page: 1, lastPage: 1 };
    const money = new Intl.NumberFormat('fr-CA', { style: 'currency', currency: 'CAD' });
    const route = (name, id) => routes[name].replace('__ID__', id);
    const toast = (message, type = 'success') => {
        const host = document.getElementById('billingToastHost');
        const item = document.createElement('div');
        item.className = `billing-toast ${type}`;
        item.textContent = message;
        host.appendChild(item);
        setTimeout(() => item.remove(), 4200);
    };
    const filters = () => ({
        ajax: 1,
        search: document.getElementById('filterSearch').value,
        status: document.getElementById('filterStatus').value,
        client_id: document.getElementById('filterClient').value,
    });
    const load = async (page = 1) => {
        state.page = page;
        rows.innerHTML = '<tr><td colspan="7" class="empty-cell">Chargement...</td></tr>';
        const params = new URLSearchParams({ ...filters(), page });
        const response = await fetch(`${routes.index}?${params}`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
        const payload = await response.json();
        state.lastPage = payload.last_page || 1;
        render(payload.data || []);
        document.getElementById('pageState').textContent = `Page ${state.page} / ${state.lastPage}`;
        document.getElementById('prevPage').disabled = state.page <= 1;
        document.getElementById('nextPage').disabled = state.page >= state.lastPage;
    };
    const render = (items) => {
        if (!items.length) {
            rows.innerHTML = '<tr><td colspan="7" class="empty-cell">Aucune facture trouvée.</td></tr>';
            return;
        }
        rows.innerHTML = items.map(invoice => `
            <tr>
                <td><strong>${invoice.invoice_number || '-'}</strong>${invoice.is_overdue ? '<div class="text-danger small">En retard</div>' : ''}</td>
                <td>${invoice.client_name || '-'}</td>
                <td>${invoice.invoice_date || '-'}</td>
                <td>${invoice.due_date || '-'}</td>
                <td><span class="status-pill status-${invoice.status}">${invoice.status_label || invoice.status}</span></td>
                <td class="text-end"><strong>${money.format(invoice.total || 0)}</strong></td>
                <td class="text-end">
                    <span class="row-actions">
                        <a class="action-btn" title="Voir" href="${route('show', invoice.id)}"><i class="fas fa-eye"></i></a>
                        <a class="action-btn" title="Modifier" href="${route('edit', invoice.id)}"><i class="fas fa-edit"></i></a>
                        <a class="action-btn" title="PDF" href="${route('pdf', invoice.id)}"><i class="fas fa-file-pdf"></i></a>
                        <button class="action-btn" title="Envoyer" data-send="${invoice.id}"><i class="fas fa-paper-plane"></i></button>
                        <button class="action-btn" title="Payée" data-paid="${invoice.id}"><i class="fas fa-check"></i></button>
                    </span>
                </td>
            </tr>
        `).join('');
    };
    const postAction = async (url) => {
        const response = await fetch(url, { method: 'POST', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf } });
        const payload = await response.json();
        if (!response.ok || !payload.success) throw new Error(payload.message || 'Action impossible.');
        toast(payload.message || 'Action effectuée.');
        await load(state.page);
    };
    let timer;
    ['filterSearch', 'filterStatus', 'filterClient'].forEach(id => {
        document.getElementById(id).addEventListener(id === 'filterSearch' ? 'input' : 'change', () => {
            clearTimeout(timer);
            timer = setTimeout(() => load(1), 250);
        });
    });
    document.getElementById('resetFilters').addEventListener('click', () => {
        document.getElementById('filterSearch').value = '';
        document.getElementById('filterStatus').value = '';
        document.getElementById('filterClient').value = '';
        load(1);
    });
    document.getElementById('prevPage').addEventListener('click', () => load(Math.max(1, state.page - 1)));
    document.getElementById('nextPage').addEventListener('click', () => load(Math.min(state.lastPage, state.page + 1)));
    rows.addEventListener('click', async event => {
        const send = event.target.closest('[data-send]');
        const paid = event.target.closest('[data-paid]');
        if (!send && !paid) return;
        try {
            await postAction(send ? route('send', send.dataset.send) : route('paid', paid.dataset.paid));
        } catch (error) {
            toast(error.message, 'error');
        }
    });
    load();
});
</script>
@endsection
