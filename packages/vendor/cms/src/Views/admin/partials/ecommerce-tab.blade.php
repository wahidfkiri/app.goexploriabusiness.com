@php
    $ecommerceSectionTitle = $stats['etablissement']->getSetting('ecommerce_section_title', 'Boutique en ligne');
@endphp

<div class="tab-pane fade" id="v-pills-ecommerce" role="tabpanel" data-etablissement-id="{{ $stats['etablissement']->id ?? '' }}">
    <div class="tab-content-header">
        <h3 class="tab-title">
            <i class="fas fa-shopping-cart me-2" style="color: #10b981;"></i>
            E-commerce: Produits et Commandes
        </h3>
        <div>
            <a href="{{ route('products.index', ['etablissement_id' => $stats['etablissement']->id ?? null]) }}" class="btn btn-outline-primary btn-sm me-2">
                <i class="fas fa-box me-1"></i>Gerer les produits
            </a>
            <a href="{{ route('invoices.index', ['etablissement_id' => $stats['etablissement']->id ?? null]) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-file-invoice me-1"></i>Gerer les commandes
            </a>
        </div>
    </div>

    <div class="ecommerce-section-title-card">
        <div>
            <label class="ecommerce-section-title-label" for="ecommerceSectionTitleInput">Titre de section e-commerce</label>
            <p class="ecommerce-section-title-text mb-0">Ce titre peut être utilisé dans le front avant les produits ou commandes.</p>
        </div>
        <div class="ecommerce-section-title-control">
            <input type="text" class="form-control form-control-sm" id="ecommerceSectionTitleInput" value="{{ $ecommerceSectionTitle }}" maxlength="191" placeholder="Ex: Nos produits">
            <button type="button" class="btn btn-sm btn-primary" id="saveEcommerceSectionTitleBtn">
                <i class="fas fa-save me-1"></i>Enregistrer
            </button>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="stat-mini-card">
                <div class="stat-mini-value" id="ecomProductsCount">{{ $stats['products_for_sale_count'] ?? 0 }}</div>
                <div class="stat-mini-label">Produits a vendre</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-mini-card">
                <div class="stat-mini-value" id="ecomOrdersCount">{{ $stats['total_orders_count'] ?? 0 }}</div>
                <div class="stat-mini-label">Commandes totales</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-mini-card">
                <div class="stat-mini-value" id="ecomActiveOrders">{{ $stats['active_orders_count'] ?? 0 }}</div>
                <div class="stat-mini-label">Commandes actives</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-mini-card">
                <div class="stat-mini-value" id="ecomRevenuePaid">{{ number_format((float) ($stats['ecommerce_revenue'] ?? 0), 2, ',', ' ') }} EUR</div>
                <div class="stat-mini-label">CA encaisse</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="info-card">
                <div class="info-card-header">
                    <i class="fas fa-tags text-success"></i>
                    <h5>Produits vendables</h5>
                </div>
                <div class="table-container-modern">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th>Prix</th>
                                <th>Stock</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="cmsEcomProductsBody">
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">Chargement...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="info-card">
                <div class="info-card-header">
                    <i class="fas fa-receipt text-primary"></i>
                    <h5>Commandes recentes</h5>
                </div>
                <div class="table-container-modern">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Commande</th>
                                <th>Client</th>
                                <th>Statut</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="cmsEcomOrdersBody">
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">Chargement...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.ecommerce-section-title-card {
    align-items: center;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    display: grid;
    gap: 16px;
    grid-template-columns: minmax(0, 1fr) minmax(260px, 420px);
    margin-bottom: 16px;
    padding: 16px;
}
.ecommerce-section-title-label {
    color: #1e293b;
    display: block;
    font-size: 14px;
    font-weight: 700;
    margin-bottom: 4px;
}
.ecommerce-section-title-text {
    color: #64748b;
    font-size: 13px;
}
.ecommerce-section-title-control {
    align-items: center;
    display: flex;
    gap: 10px;
}
@media (max-width: 768px) {
    .ecommerce-section-title-card {
        grid-template-columns: 1fr;
    }

    .ecommerce-section-title-control {
        align-items: stretch;
        flex-direction: column;
    }
}
</style>

<script>
(function () {
    let loadedOnce = false;
    const etablissementId = window.currentEtablissementId || {{ (int) ($stats['etablissement']->id ?? 0) }};
    const productsIndexUrl = @json(route('products.index'));
    const ordersIndexUrl = @json(route('invoices.index'));

    function csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    function showEcommerceToast(message, type = 'success') {
        if (typeof showToast === 'function') {
            showToast(message, type);
            return;
        }

        console[type === 'error' ? 'error' : 'log'](message);
    }

    async function saveEcommerceSectionTitle() {
        const input = document.getElementById('ecommerceSectionTitleInput');
        const button = document.getElementById('saveEcommerceSectionTitleBtn');
        const formData = new FormData();

        formData.append('_token', csrfToken());
        formData.append('site_ecommerce_section_title', input?.value?.trim() || '');
        if (button) button.disabled = true;

        try {
            const response = await fetch(`/admin/cms/${etablissementId}/settings`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken(),
                    'Accept': 'application/json'
                },
                body: formData
            });
            const data = await response.json();
            if (!response.ok || !data.success) throw new Error(data.message || 'Erreur de sauvegarde');
            showEcommerceToast('Titre de section e-commerce sauvegardé', 'success');
        } catch (error) {
            showEcommerceToast(error.message || 'Erreur de sauvegarde', 'error');
        } finally {
            if (button) button.disabled = false;
        }
    }

    function statusBadge(status) {
        const map = {
            payee: 'success',
            envoyee: 'info',
            brouillon: 'secondary',
            en_attente: 'warning',
            partiellement_payee: 'primary',
            annulee: 'danger'
        };
        const labelMap = {
            payee: 'Payee',
            envoyee: 'Envoyee',
            brouillon: 'Brouillon',
            en_attente: 'En attente',
            partiellement_payee: 'Partielle',
            annulee: 'Annulee'
        };
        const cls = map[status] || 'secondary';
        const label = labelMap[status] || status;
        return `<span class="badge bg-${cls}">${label}</span>`;
    }

    function formatMoney(value) {
        const number = Number(value || 0);
        return `${number.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} EUR`;
    }

    function renderProducts(items) {
        const tbody = document.getElementById('cmsEcomProductsBody');
        if (!tbody) return;

        if (!Array.isArray(items) || items.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">Aucun produit disponible.</td></tr>';
            return;
        }

        tbody.innerHTML = items.map((product) => {
            const stock = product.stock_management === 'non' ? 'Non gere' : (product.current_stock ?? 0);
            const showUrl = `${productsIndexUrl}/${product.id}`;
            return `
                <tr>
                    <td>
                        <strong>${product.name || '-'}</strong><br>
                        <small class="text-muted">${product.reference || '-'}</small>
                    </td>
                    <td>${formatMoney(product.price_ttc)}</td>
                    <td>${stock}</td>
                    <td>
                        <a href="${showUrl}" class="btn btn-sm btn-outline-primary">Voir</a>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function renderOrders(items) {
        const tbody = document.getElementById('cmsEcomOrdersBody');
        if (!tbody) return;

        if (!Array.isArray(items) || items.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">Aucune commande.</td></tr>';
            return;
        }

        tbody.innerHTML = items.map((order) => {
            const client = order.client?.nom_complet || order.client?.name || order.client_name || '-';
            const showUrl = `${ordersIndexUrl}/${order.id}`;
            const canMarkPaid = Number(order.remaining_amount || 0) > 0 && order.status !== 'annulee';

            return `
                <tr>
                    <td>
                        <strong>${order.invoice_number || '#' + order.id}</strong><br>
                        <small class="text-muted">${order.invoice_date || ''}</small>
                    </td>
                    <td>${client}</td>
                    <td>${statusBadge(order.status)}</td>
                    <td class="d-flex gap-1">
                        <a href="${showUrl}" class="btn btn-sm btn-outline-primary">Voir</a>
                        ${canMarkPaid ? `<button class="btn btn-sm btn-success js-mark-order-paid" data-id="${order.id}">Payee</button>` : ''}
                    </td>
                </tr>
            `;
        }).join('');
    }

    async function loadProducts() {
        const response = await fetch(`/admin/cms/${etablissementId}/api/ecommerce/products`, {
            headers: { 'Accept': 'application/json' }
        });
        const data = await response.json();
        if (!response.ok || !data.success) throw new Error(data.message || 'Erreur produits');
        renderProducts(data.data || []);
        document.getElementById('ecomProductsCount').textContent = (data.data || []).length;
    }

    async function loadOrders() {
        const response = await fetch(`/admin/cms/${etablissementId}/api/ecommerce/orders`, {
            headers: { 'Accept': 'application/json' }
        });
        const data = await response.json();
        if (!response.ok || !data.success) throw new Error(data.message || 'Erreur commandes');

        renderOrders(data.data || []);

        const stats = data.stats || {};
        if (document.getElementById('ecomOrdersCount')) document.getElementById('ecomOrdersCount').textContent = stats.total ?? 0;
        if (document.getElementById('ecomActiveOrders')) document.getElementById('ecomActiveOrders').textContent = stats.pending ?? 0;
        if (document.getElementById('ecomRevenuePaid')) document.getElementById('ecomRevenuePaid').textContent = formatMoney(stats.revenue_paid ?? 0);
    }

    async function markOrderPaid(invoiceId) {
        const response = await fetch(`/admin/cms/${etablissementId}/api/ecommerce/orders/${invoiceId}/mark-paid`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken(),
                'Accept': 'application/json'
            }
        });
        const data = await response.json();
        if (!response.ok || !data.success) throw new Error(data.message || 'Erreur de mise a jour');
        if (typeof showToast === 'function') showToast(data.message || 'Commande marquee payee.');
        await loadOrders();
    }

    async function loadCmsEcommerceData() {
        if (!etablissementId) return;
        try {
            await Promise.all([loadProducts(), loadOrders()]);
            loadedOnce = true;
        } catch (error) {
            if (typeof showToast === 'function') showToast(error.message || 'Erreur e-commerce', 'error');
        }
    }

    document.addEventListener('click', async function (event) {
        const btn = event.target.closest('.js-mark-order-paid');
        if (!btn) return;
        const invoiceId = btn.getAttribute('data-id');
        if (!invoiceId) return;
        if (!confirm('Marquer cette commande comme payee ?')) return;
        btn.disabled = true;
        try {
            await markOrderPaid(invoiceId);
        } catch (error) {
            if (typeof showToast === 'function') showToast(error.message || 'Erreur', 'error');
        } finally {
            btn.disabled = false;
        }
    });

    document.addEventListener('click', function (event) {
        const nav = event.target.closest('.nav-link-modern[data-section="v-pills-ecommerce"]');
        if (nav) {
            setTimeout(loadCmsEcommerceData, 100);
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('saveEcommerceSectionTitleBtn')?.addEventListener('click', saveEcommerceSectionTitle);
        const section = new URLSearchParams(window.location.search).get('section');
        if (section === 'ecommerce' || !loadedOnce) {
            loadCmsEcommerceData();
        }
    });
})();
</script>
