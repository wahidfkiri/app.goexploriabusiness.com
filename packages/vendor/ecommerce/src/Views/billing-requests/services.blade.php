@extends('layouts.app')

@php
    $routes = [
        'index' => route('billing.request-services.index'),
        'store' => route('billing.request-services.store'),
        'update' => route('billing.request-services.update', ['id' => '__ID__']),
        'destroy' => route('billing.request-services.destroy', ['id' => '__ID__']),
        'requests' => route('billing.requests.index'),
        'settings' => route('billing.settings.index'),
        'public' => route('billing.requests.public.form', ['etablissementId' => $etablissementId]),
    ];
@endphp

@section('content')
<main class="dashboard-content request-service-page">
    <div class="request-head">
        <div>
            <p class="request-kicker">Facturation</p>
            <h1>Options de demande</h1>
            <span class="request-subtitle">Configurez les services visibles sur le front. La TVA et la remise globales viennent des parametres de facturation.</span>
        </div>
        <div class="request-actions">
            <a href="{{ $routes['requests'] }}" class="btn btn-outline-secondary"><i class="fas fa-inbox me-2"></i>Demandes recues</a>
            <a href="{{ $routes['public'] }}" target="_blank" class="btn btn-outline-secondary"><i class="fas fa-external-link-alt me-2"></i>Page front</a>
            <a href="{{ $routes['settings'] }}" class="btn btn-outline-secondary"><i class="fas fa-cog me-2"></i>Parametres</a>
            <button type="button" class="btn btn-primary" id="addServiceBtn"><i class="fas fa-plus me-2"></i>Ajouter une option</button>
        </div>
    </div>

    <section class="request-panel">
        <div class="panel-toolbar">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="search" id="serviceSearch" placeholder="Rechercher une option...">
            </div>
            <button type="button" class="btn btn-outline-secondary" id="reloadServices"><i class="fas fa-sync-alt me-2"></i>Actualiser</button>
        </div>

        <div class="table-wrap">
            <table class="request-table">
                <thead>
                    <tr>
                        <th>Option</th>
                        <th>Prix</th>
                        <th>TVA</th>
                        <th>Ordre</th>
                        <th>Etat</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="serviceRows">
                    <tr><td colspan="6" class="empty-cell">Chargement...</td></tr>
                </tbody>
            </table>
        </div>
    </section>

    <div id="billingToastHost" class="billing-toast-host"></div>

    <div class="modal fade" id="serviceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <form class="modal-content service-modal" id="serviceForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="serviceId">
                <input type="hidden" name="remove_image" id="removeImageInput" value="0">
                <div class="modal-header">
                    <div>
                        <p class="request-kicker mb-1">Option front</p>
                        <h5 class="modal-title" id="serviceModalTitle">Ajouter une option</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <div class="service-form-grid">
                        <section class="form-card span-2">
                            <div class="form-card-title"><i class="fas fa-pen"></i><span>Contenu</span></div>
                            <div class="form-grid two">
                                <label class="span-2">
                                    <span>Titre de l'option</span>
                                    <input type="text" name="title" id="serviceTitle" required maxlength="191" placeholder="Ex: Plan d'affichage prime time">
                                </label>
                                <label class="span-2">
                                    <span>Description</span>
                                    <textarea name="description" id="serviceDescription" class="tinymce-editor" rows="5" placeholder="Detail du service, periode, inclusions..."></textarea>
                                </label>
                            </div>
                        </section>

                        <section class="form-card">
                            <div class="form-card-title"><i class="fas fa-dollar-sign"></i><span>Prix</span></div>
                            <div class="form-grid">
                                <label>
                                    <span>Prix unitaire</span>
                                    <input type="number" name="unit_price" id="servicePrice" min="0" step="0.01" required>
                                </label>
                                <label>
                                    <span>Unite</span>
                                    <input type="text" name="billing_unit" id="serviceUnit" placeholder="forfait">
                                </label>
                                <label>
                                    <span>Taxe fallback</span>
                                    <select name="tax_id" id="serviceTaxId">
                                        <option value="">Aucune taxe</option>
                                        @foreach($taxes as $tax)
                                            <option value="{{ $tax->id }}">{{ $tax->name }} - {{ number_format((float) $tax->rate, 2, ',', ' ') }}%</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label>
                                    <span>Taux fallback (%)</span>
                                    <input type="number" name="tax_rate" id="serviceTaxRate" min="0" max="100" step="0.01">
                                </label>
                            </div>
                        </section>

                        <section class="form-card">
                            <div class="form-card-title"><i class="fas fa-image"></i><span>Image</span></div>
                            <div class="image-uploader">
                                <label class="image-drop" for="serviceImage">
                                    <input type="file" name="image" id="serviceImage" accept="image/png,image/jpeg,image/webp">
                                    <span id="imagePreview"><i class="fas fa-cloud-upload-alt"></i></span>
                                    <strong>Importer une image</strong>
                                    <small>PNG, JPG ou WebP</small>
                                </label>
                                <label>
                                    <span>Ou URL image</span>
                                    <input type="url" name="image_url" id="serviceImageUrl" placeholder="https://...">
                                </label>
                                <button type="button" class="btn btn-sm btn-outline-danger w-100" id="removeImageBtn">Retirer l'image</button>
                            </div>
                        </section>

                        <section class="form-card span-2">
                            <div class="form-card-title"><i class="fas fa-sliders-h"></i><span>Affichage</span></div>
                            <div class="form-grid three compact">
                                <label>
                                    <span>Ordre</span>
                                    <input type="number" name="sort_order" id="serviceSort" min="0" value="0">
                                </label>
                                <label class="switch-row">
                                    <input type="checkbox" name="is_active" id="serviceActive" value="1" checked>
                                    <span>Visible sur le front</span>
                                </label>
                                <label class="switch-row">
                                    <input type="checkbox" name="is_featured" id="serviceFeatured" value="1">
                                    <span>Option mise en avant</span>
                                </label>
                            </div>
                        </section>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary" id="saveServiceBtn"><i class="fas fa-save me-2"></i>Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js"></script>
<style>
    .request-service-page { padding: 28px; background: #f3f6fa; min-height: calc(100vh - 72px); color: #172033; }
    .request-head { display: flex; align-items: center; justify-content: space-between; gap: 18px; margin-bottom: 20px; padding: 20px; background: linear-gradient(135deg, #fff 0%, #f8fbff 100%); border: 1px solid #dbe3ee; border-radius: 8px; box-shadow: 0 14px 32px rgba(15, 23, 42, .06); }
    .request-head h1 { margin: 0; color: #0f172a; font-size: 30px; font-weight: 850; letter-spacing: 0; }
    .request-kicker { margin: 0 0 5px; color: #2563eb; text-transform: uppercase; font-size: 12px; font-weight: 850; }
    .request-subtitle { display: block; color: #64748b; font-weight: 650; }
    .request-actions { display: flex; flex-wrap: wrap; gap: 10px; }
    .request-actions .btn, .panel-toolbar .btn { min-height: 40px; border-radius: 7px; font-weight: 750; }
    .request-panel { background: #fff; border: 1px solid #dbe3ee; border-radius: 8px; box-shadow: 0 12px 28px rgba(15, 23, 42, .05); overflow: hidden; }
    .panel-toolbar { display: flex; justify-content: space-between; gap: 12px; padding: 16px; background: #fbfdff; border-bottom: 1px solid #e2e8f0; }
    .search-box { position: relative; flex: 1; max-width: 480px; }
    .search-box i { position: absolute; top: 13px; left: 12px; color: #64748b; }
    .search-box input, .form-grid input, .form-grid select, .form-grid textarea, .image-uploader input, .image-uploader textarea, .image-uploader select { width: 100%; border: 1px solid #cbd5e1; border-radius: 7px; padding: 10px 11px; min-height: 42px; background: #fff; color: #172033; font-weight: 650; }
    .search-box input { padding-left: 36px; }
    .search-box input:focus, .form-grid input:focus, .form-grid select:focus, .form-grid textarea:focus, .image-uploader input:focus { outline: 0; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, .12); }
    .table-wrap { overflow-x: auto; }
    .request-table { width: 100%; border-collapse: separate; border-spacing: 0; }
    .request-table th { color: #64748b; font-size: 11px; text-transform: uppercase; padding: 13px 14px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; white-space: nowrap; letter-spacing: .02em; }
    .request-table td { padding: 14px; border-bottom: 1px solid #eef2f7; vertical-align: middle; color: #334155; }
    .request-table tbody tr:hover { background: #f8fbff; }
    .option-cell { display: flex; gap: 12px; align-items: center; min-width: 280px; }
    .option-thumb { width: 54px; height: 54px; border-radius: 8px; background: #e2e8f0 center/cover no-repeat; display: grid; place-items: center; color: #64748b; flex: 0 0 auto; }
    .option-cell strong { display: block; color: #0f172a; font-size: 15px; }
    .option-cell small { display: block; max-width: 460px; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .status-pill { display: inline-flex; align-items: center; gap: 6px; padding: 6px 10px; border-radius: 999px; font-weight: 850; font-size: 12px; background: #e2e8f0; color: #334155; }
    .status-pill.active { background: #dcfce7; color: #166534; }
    .status-pill.featured { background: #fef3c7; color: #92400e; margin-left: 6px; }
    .empty-cell { text-align: center; color: #64748b; padding: 34px !important; }
    .row-actions { display: inline-flex; justify-content: flex-end; gap: 6px; }
    .action-btn { width: 34px; height: 34px; border: 1px solid #d6dfeb; border-radius: 7px; background: #fff; color: #475569; display: inline-flex; align-items: center; justify-content: center; transition: .16s ease; }
    .action-btn:hover { color: #2563eb; border-color: #9db7ec; transform: translateY(-1px); box-shadow: 0 8px 18px rgba(37, 99, 235, .12); }
    .action-btn.danger:hover { color: #dc2626; border-color: #fca5a5; box-shadow: 0 8px 18px rgba(220, 38, 38, .12); }
    .service-modal .modal-header, .service-modal .modal-footer { background: #f8fafc; border-color: #e2e8f0; }
    .service-modal .modal-title { margin: 0; font-size: 22px; font-weight: 850; color: #0f172a; }
    .service-form-grid { display: grid; grid-template-columns: 1.2fr .8fr; gap: 14px; }
    .form-card { border: 1px solid #e2e8f0; border-radius: 8px; background: #fff; padding: 16px; }
    .form-card.span-2 { grid-column: 1 / -1; }
    .form-card-title { display: flex; align-items: center; gap: 9px; margin-bottom: 14px; font-weight: 850; color: #0f172a; }
    .form-card-title i { width: 28px; height: 28px; display: inline-grid; place-items: center; border-radius: 7px; background: #e0edff; color: #2563eb; }
    .form-grid { display: grid; grid-template-columns: 1fr; gap: 12px; }
    .form-grid.two { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .form-grid.three { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .form-grid .span-2 { grid-column: 1 / -1; }
    .form-grid label, .image-uploader label { display: grid; gap: 6px; color: #475569; font-weight: 800; }
    .form-grid label span, .image-uploader label span { font-size: 12px; text-transform: uppercase; color: #64748b; }
    .switch-row { display: flex !important; flex-direction: row; align-items: center; gap: 10px; padding: 12px; border: 1px solid #dbe3ee; border-radius: 8px; background: #fbfdff; min-height: 62px; }
    .switch-row input { width: 18px; min-height: 18px; }
    .image-uploader { display: grid; gap: 12px; }
    .image-drop { border: 1px dashed #9db7ec; border-radius: 8px; min-height: 180px; padding: 16px; text-align: center; align-content: center; cursor: pointer; background: #f8fbff; }
    .image-drop input { display: none; }
    .image-drop span { width: 78px; height: 78px; display: inline-grid; place-items: center; margin: 0 auto 10px; border-radius: 8px; background: #e0edff center/cover no-repeat; color: #2563eb; font-size: 26px; }
    .image-drop strong, .image-drop small { display: block; color: #334155; }
    .image-drop small { color: #64748b; }
    .billing-toast-host { position: fixed; right: 20px; bottom: 20px; z-index: 1080; display: grid; gap: 10px; }
    .billing-toast { padding: 12px 14px; border-radius: 8px; color: #fff; background: #16a34a; box-shadow: 0 15px 30px rgba(15, 23, 42, .18); min-width: 240px; font-weight: 750; }
    .billing-toast.error { background: #dc2626; }
    @media (max-width: 1000px) { .request-head, .panel-toolbar { display: grid; } .service-form-grid, .form-grid.two, .form-grid.three { grid-template-columns: 1fr; } }
    @media (max-width: 640px) { .request-service-page { padding: 14px; } .request-head { padding: 16px; } }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const routes = @json($routes);
    const csrf = @json(csrf_token());
    const rows = document.getElementById('serviceRows');
    const initialServices = @json($servicesPayload ?? []);
    const modalEl = document.getElementById('serviceModal');
    const modal = new bootstrap.Modal(modalEl);
    const form = document.getElementById('serviceForm');
    const services = new Map();
    const money = new Intl.NumberFormat('fr-CA', { style: 'currency', currency: 'CAD' });
    const route = (name, id) => routes[name].replace('__ID__', id);

    // ============================================
    // TINYMCE INITIALIZATION
    // ============================================
    function initTinyMCE(selector, height = 400) {
        if (typeof tinymce !== 'undefined') {
            tinymce.init({
                selector: selector,
                height: height,
                menubar: true,
                plugins: [
                    'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                    'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                    'insertdatetime', 'media', 'table', 'help', 'wordcount', 'emoticons',
                    'code', 'save', 'directionality', 'template', 'codesample'
                ],
                toolbar: 'undo redo | blocks | ' +
                    'bold italic backcolor | alignleft aligncenter ' +
                    'alignright alignjustify | bullist numlist outdent indent | ' +
                    'removeformat | help | fullscreen | code | table | link image media',
                content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px; line-height:1.6; }',
                branding: false,
                promotion: false,
                statusbar: true,
                relative_urls: false,
                remove_script_host: false,
                convert_urls: true,
                file_picker_types: 'image media',
                image_advtab: true,
                setup: function(editor) {
                    editor.on('change', function() {
                        tinymce.triggerSave();
                    });
                }
            });
        }
    }

    function initAllEditors() {
        if (typeof tinymce !== 'undefined') {
            tinymce.remove('.tinymce-editor');
            initTinyMCE('.tinymce-editor', 400);
        }
    }

    initAllEditors();

    const toast = (message, type = 'success') => {
        const host = document.getElementById('billingToastHost');
        const item = document.createElement('div');
        item.className = `billing-toast ${type}`;
        item.textContent = message;
        host.appendChild(item);
        setTimeout(() => item.remove(), 4200);
    };

    const escapeHtml = (value = '') => String(value).replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]));

    const loadServices = async () => {
        rows.innerHTML = '<tr><td colspan="6" class="empty-cell">Chargement...</td></tr>';
        const response = await fetch(`${routes.index}?ajax=1`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
        const payload = await response.json();
        services.clear();
        (payload.data || []).forEach(service => services.set(Number(service.id), service));
        renderServices();
    };

    const renderServices = () => {
        const search = document.getElementById('serviceSearch').value.toLowerCase().trim();
        const items = Array.from(services.values()).filter(service => {
            return !search || `${service.title} ${service.description || ''}`.toLowerCase().includes(search);
        });

        if (!items.length) {
            rows.innerHTML = '<tr><td colspan="6" class="empty-cell">Aucune option trouvee.</td></tr>';
            return;
        }

        rows.innerHTML = items.map(service => {
            const thumb = service.image_url
                ? `<span class="option-thumb" style="background-image:url('${escapeHtml(service.image_url)}')"></span>`
                : '<span class="option-thumb"><i class="fas fa-image"></i></span>';
            const desc = escapeHtml(stripTags(service.description || 'Aucune description'));
            return `
                <tr>
                    <td>
                        <div class="option-cell">
                            ${thumb}
                            <div>
                                <strong>${escapeHtml(service.title)}</strong>
                                <small>${desc}</small>
                            </div>
                        </div>
                    </td>
                    <td><strong>${money.format(Number(service.unit_price || 0))}</strong><div class="text-muted small">${escapeHtml(service.billing_unit || 'forfait')}</div></td>
                    <td>${Number(service.tax_rate || 0).toFixed(2)}%</td>
                    <td>${Number(service.sort_order || 0)}</td>
                    <td>
                        <span class="status-pill ${service.is_active ? 'active' : ''}">${service.is_active ? 'Visible' : 'Masquee'}</span>
                        ${service.is_featured ? '<span class="status-pill featured">Mise en avant</span>' : ''}
                    </td>
                    <td class="text-end">
                        <span class="row-actions">
                            <button type="button" class="action-btn" data-edit="${service.id}" title="Modifier"><i class="fas fa-edit"></i></button>
                            <button type="button" class="action-btn danger" data-delete="${service.id}" title="Supprimer"><i class="fas fa-trash"></i></button>
                        </span>
                    </td>
                </tr>
            `;
        }).join('');
    };

    const stripTags = (html = '') => {
        const doc = new DOMParser().parseFromString(html, 'text/html');
        return doc.body.textContent || '';
    };

    const resetForm = () => {
        form.reset();
        document.getElementById('serviceId').value = '';
        document.getElementById('removeImageInput').value = '0';
        document.getElementById('serviceActive').checked = true;
        document.getElementById('serviceFeatured').checked = false;
        document.getElementById('serviceSort').value = '0';
        const preview = document.getElementById('imagePreview');
        preview.style.backgroundImage = '';
        preview.innerHTML = '<i class="fas fa-cloud-upload-alt"></i>';
        if (typeof tinymce !== 'undefined') {
            const editor = tinymce.get('serviceDescription');
            if (editor) {
                editor.setContent('');
            }
        }
    };

    const openForm = (service = null) => {
        resetForm();
        document.getElementById('serviceModalTitle').textContent = service ? 'Modifier une option' : 'Ajouter une option';
        if (service) {
            document.getElementById('serviceId').value = service.id;
            document.getElementById('serviceTitle').value = service.title || '';
            document.getElementById('servicePrice').value = service.unit_price || 0;
            document.getElementById('serviceUnit').value = service.billing_unit || 'forfait';
            document.getElementById('serviceTaxId').value = service.tax_id || '';
            document.getElementById('serviceTaxRate').value = service.tax_rate || 0;
            document.getElementById('serviceSort').value = service.sort_order || 0;
            document.getElementById('serviceActive').checked = Boolean(service.is_active);
            document.getElementById('serviceFeatured').checked = Boolean(service.is_featured);
            document.getElementById('serviceImageUrl').value = service.image_url || '';
            if (typeof tinymce !== 'undefined') {
                const editor = tinymce.get('serviceDescription');
                if (editor) {
                    editor.setContent(service.description || '');
                }
            } else {
                document.getElementById('serviceDescription').value = service.description || '';
            }
            if (service.image_url) {
                const preview = document.getElementById('imagePreview');
                preview.style.backgroundImage = `url('${service.image_url}')`;
                preview.innerHTML = '';
            }
        }
        modal.show();
    };

    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (typeof tinymce !== 'undefined') {
            tinymce.triggerSave();
        }
        const id = document.getElementById('serviceId').value;
        const formData = new FormData(form);
        if (id) {
            formData.append('_method', 'PUT');
        }
        if (!document.getElementById('serviceActive').checked) formData.set('is_active', '0');
        if (!document.getElementById('serviceFeatured').checked) formData.set('is_featured', '0');

        const response = await fetch(id ? route('update', id) : routes.store, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: formData,
        });
        const payload = await response.json();
        if (!response.ok || !payload.success) {
            toast(payload.message || 'Erreur lors de la sauvegarde.', 'error');
            return;
        }
        modal.hide();
        toast(payload.message || 'Option sauvegardee.');
        await loadServices();
    });

    rows.addEventListener('click', async event => {
        const editBtn = event.target.closest('[data-edit]');
        const deleteBtn = event.target.closest('[data-delete]');
        if (editBtn) {
            openForm(services.get(Number(editBtn.dataset.edit)));
        }
        if (deleteBtn) {
            if (!confirm('Supprimer cette option de demande ?')) return;
            const response = await fetch(route('destroy', deleteBtn.dataset.delete), {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const payload = await response.json();
            toast(payload.message || 'Option supprimee.', response.ok ? 'success' : 'error');
            await loadServices();
        }
    });

    document.getElementById('addServiceBtn').addEventListener('click', () => openForm());
    document.getElementById('reloadServices').addEventListener('click', loadServices);
    document.getElementById('serviceSearch').addEventListener('input', renderServices);
    document.getElementById('removeImageBtn').addEventListener('click', () => {
        document.getElementById('removeImageInput').value = '1';
        document.getElementById('serviceImageUrl').value = '';
        const preview = document.getElementById('imagePreview');
        preview.style.backgroundImage = '';
        preview.innerHTML = '<i class="fas fa-cloud-upload-alt"></i>';
    });
    document.getElementById('serviceImage').addEventListener('change', event => {
        const file = event.target.files[0];
        if (!file) return;
        const preview = document.getElementById('imagePreview');
        preview.style.backgroundImage = `url('${URL.createObjectURL(file)}')`;
        preview.innerHTML = '';
    });

    // Re-init TinyMCE after modal is shown (since modal might move DOM)
    modalEl.addEventListener('shown.bs.modal', () => {
        if (typeof tinymce !== 'undefined') {
            const editor = tinymce.get('serviceDescription');
            if (!editor) {
                initTinyMCE('#serviceDescription', 400);
            }
        }
    });

    if (initialServices && initialServices.length) {
        initialServices.forEach(service => services.set(Number(service.id), service));
        renderServices();
    } else {
        loadServices();
    }
});
</script>
@endsection
