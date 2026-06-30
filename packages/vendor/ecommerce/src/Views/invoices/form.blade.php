@extends('layouts.app')

@php
    $isQuote = $documentType === 'quote';
    $title = $isQuote ? ($mode === 'edit' ? 'Modifier le devis' : 'Créer un devis') : ($mode === 'edit' ? 'Modifier la facture' : 'Créer une facture');
    $number = $isQuote ? ($document?->quote_number ?? 'Nouveau') : ($document?->invoice_number ?? 'Nouvelle');
    $dateValue = old('invoice_date', optional($isQuote ? $document?->quote_date : $document?->invoice_date)->toDateString() ?: now()->toDateString());
    $dueValue = old('due_date', optional($isQuote ? $document?->valid_until : $document?->due_date)->toDateString() ?: now()->addDays($defaultDueDays)->toDateString());
    $statusOptions = $isQuote
        ? ['brouillon' => 'Brouillon', 'envoye' => 'Envoyé', 'en_attente' => 'En attente', 'accepte' => 'Accepté', 'refuse' => 'Refusé', 'annule' => 'Annulé']
        : ['brouillon' => 'Brouillon', 'envoyee' => 'Envoyée', 'en_attente' => 'En attente', 'annulee' => 'Annulée'];
    $defaultTaxRate = $taxes->whereIn('id', $billingSettings->default_tax_ids ?? [])->sum('rate') ?: optional($taxes->first())->rate;
    $lineItems = $document?->lines?->map(fn ($line) => [
        'product_id' => $line->product_id,
        'type' => $line->type ?: 'prestation',
        'description' => $line->description,
        'detailed_description' => $line->detailed_description,
        'quantity' => (int) $line->quantity,
        'unit_price' => (float) $line->unit_price,
        'tax_rate' => (float) ($line->tax_rate ?? 0),
    ])->values()->all() ?? [[
        'product_id' => null,
        'type' => 'prestation',
        'description' => '',
        'detailed_description' => '',
        'quantity' => 1,
        'unit_price' => 0,
        'tax_rate' => (float) $defaultTaxRate,
    ]];
    $logoUrl = data_get($document?->metadata, 'logo_url') ?: $billingSettings->billing_logo_url;
    $settingsPayload = [
        'default_shipping_fees' => (float) ($billingSettings->default_shipping_fees ?? 0),
        'default_administration_fees' => (float) ($billingSettings->default_administration_fees ?? 0),
        'default_discount_percentage' => (float) ($billingSettings->default_discount_percentage ?? 0),
        'default_note' => $billingSettings->default_note,
        'invoice_footer_note' => $billingSettings->invoice_footer_note,
        'quote_footer_note' => $billingSettings->quote_footer_note,
        'terms_and_conditions' => $billingSettings->terms_and_conditions,
        'legal_mentions' => $billingSettings->legal_mentions,
    ];
    $defaultFooter = $isQuote
        ? ($document?->conditions ?? $settingsPayload['quote_footer_note'] ?? $settingsPayload['terms_and_conditions'])
        : ($document?->footer ?? $settingsPayload['invoice_footer_note'] ?? $settingsPayload['terms_and_conditions'] ?? $settingsPayload['legal_mentions']);
    $productsPayload = $products->map(fn ($product) => [
        'id' => $product->id,
        'name' => $product->name,
        'price' => (float) ($product->price_ht ?? $product->price_ttc ?? 0),
        'tax_rate' => (float) ($product->tax_rate ?? 0),
        'type' => $product->main_type ?: 'produit',
    ])->values();
@endphp

@section('content')
<main class="dashboard-content invoice-workspace">
    <div class="invoice-page-head">
        <div>
            <p class="invoice-kicker">{{ $isQuote ? 'Devis' : 'Facture' }} / {{ $number }}</p>
            <h1>{{ $title }}</h1>
        </div>
        <div class="invoice-actions">
            <a href="{{ $backUrl }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Retour
            </a>
            <button type="submit" form="documentForm" class="btn btn-primary" id="saveDocumentBtn">
                <i class="fas fa-save me-2"></i>Enregistrer
            </button>
        </div>
    </div>

    <form id="documentForm" class="invoice-form" enctype="multipart/form-data" novalidate>
        @csrf
        @if($mode === 'edit')
            @method('PUT')
        @endif

        <section class="invoice-layout">
            <div class="invoice-main">
                <div class="invoice-panel">
                    <div class="panel-title">
                        <i class="fas fa-user-check"></i>
                        <span>Client</span>
                    </div>
                    <div class="form-grid one">
                        <label>
                            <span>Client établissement</span>
                            <select name="client_id" required>
                                <option value="">Sélectionner un établissement</option>
                                @foreach($clients as $client)
                                    @php($clientName = $client->name ?: $client->lname ?: 'Établissement #' . $client->id)
                                    <option value="{{ $client->id }}" @selected((int) old('client_id', $document?->client_id) === $client->id)>{{ $clientName }}</option>
                                @endforeach
                            </select>
                            <small class="field-error" data-error-for="client_id"></small>
                        </label>
                    </div>
                </div>

                <div class="invoice-panel">
                    <div class="panel-title with-action">
                        <div><i class="fas fa-list"></i><span>Lignes</span></div>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addLineBtn">
                            <i class="fas fa-plus me-1"></i>Ajouter
                        </button>
                    </div>
                    <div id="lineEditor" class="line-editor"></div>
                    <small class="field-error" data-error-for="lines"></small>
                </div>

                <div class="invoice-panel">
                    <div class="panel-title">
                        <i class="fas fa-align-left"></i>
                        <span>Notes et conditions</span>
                    </div>
                    <label class="full">
                        <span>Note visible</span>
                        <textarea name="notes" rows="4" placeholder="Message affiché sur le document">{{ old('notes', $document?->notes ?? $settingsPayload['default_note']) }}</textarea>
                        <small class="field-error" data-error-for="notes"></small>
                    </label>
                    <label class="full">
                        <span>Conditions / pied de page</span>
                        <textarea name="footer" rows="4" placeholder="Conditions de paiement, mentions légales...">{{ old('footer', $defaultFooter) }}</textarea>
                        <small class="field-error" data-error-for="footer"></small>
                    </label>
                    <label class="full">
                        <span>Note interne</span>
                        <textarea name="internal_notes" rows="3" placeholder="Visible uniquement par l'équipe">{{ old('internal_notes', $document?->internal_notes) }}</textarea>
                        <small class="field-error" data-error-for="internal_notes"></small>
                    </label>
                </div>
            </div>

            <aside class="invoice-side">
                <div class="invoice-panel sticky-panel">
                    <div class="panel-title">
                        <i class="fas fa-sliders-h"></i>
                        <span>Paramètres</span>
                    </div>
                    <div class="form-grid one">
                        <label>
                            <span>Statut</span>
                            <select name="status" required>
                                @foreach($statusOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(old('status', $document?->status ?? 'brouillon') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <small class="field-error" data-error-for="status"></small>
                        </label>
                        <label>
                            <span>{{ $isQuote ? 'Date du devis' : 'Date de facture' }}</span>
                            <input type="date" name="invoice_date" value="{{ $dateValue }}" required>
                            <small class="field-error" data-error-for="invoice_date"></small>
                        </label>
                        <label>
                            <span>{{ $isQuote ? ($billingSettings->quote_validity_label ?: 'Valide jusqu’au') : ($billingSettings->invoice_due_label ?: 'Échéance') }}</span>
                            <input type="date" name="due_date" value="{{ $dueValue }}" required>
                            <small class="field-error" data-error-for="due_date"></small>
                        </label>
                        <label>
                            <span>Remise globale (%)</span>
                            <input type="number" name="discount_percentage" step="0.01" min="0" max="100" value="{{ old('discount_percentage', $document?->discount_percentage ?? $settingsPayload['default_discount_percentage']) }}">
                            <small class="field-error" data-error-for="discount_percentage"></small>
                        </label>
                        <label>
                            <span>Frais livraison</span>
                            <input type="number" name="shipping_fees" step="0.01" min="0" value="{{ old('shipping_fees', $document?->shipping_fees ?? $settingsPayload['default_shipping_fees']) }}">
                        </label>
                        <label>
                            <span>Frais administratifs</span>
                            <input type="number" name="administration_fees" step="0.01" min="0" value="{{ old('administration_fees', $document?->administration_fees ?? $settingsPayload['default_administration_fees']) }}">
                        </label>
                    </div>

                    <div class="logo-uploader">
                        <input type="hidden" name="remove_logo" id="removeLogoInput" value="0">
                        <label for="logoInput" class="logo-drop">
                            <input type="file" name="logo" id="logoInput" accept="image/png,image/jpeg,image/webp,image/svg+xml">
                            <span class="logo-preview" id="logoPreview" @if($logoUrl) style="background-image:url('{{ $logoUrl }}')" @endif>
                                @unless($logoUrl)
                                    <i class="fas fa-image"></i>
                                @endunless
                            </span>
                            <strong>Logo du document</strong>
                            <small>PNG, JPG, WebP ou SVG</small>
                        </label>
                        <button type="button" class="btn btn-sm btn-outline-danger w-100" id="removeLogoBtn">
                            <i class="fas fa-trash-alt me-1"></i>Retirer le logo
                        </button>
                        <small class="field-error" data-error-for="logo"></small>
                    </div>

                    <div class="total-box">
                        <div><span>Sous-total</span><strong id="subtotalValue">0,00 $</strong></div>
                        <div><span>Remise</span><strong id="discountValue">0,00 $</strong></div>
                        <div><span>Taxes</span><strong id="taxValue">0,00 $</strong></div>
                        <div><span>Frais</span><strong id="feesValue">0,00 $</strong></div>
                        <div class="grand-total"><span>Total</span><strong id="totalValue">0,00 $</strong></div>
                    </div>
                </div>
            </aside>
        </section>
    </form>

    <div id="invoiceToastHost" class="invoice-toast-host"></div>
</main>

<template id="lineTemplate">
    <article class="line-row" data-line>
        <div class="line-head">
            <span class="line-number"></span>
            <button type="button" class="icon-btn remove-line" title="Supprimer la ligne">
                <i class="fas fa-trash-alt"></i>
            </button>
        </div>
        <div class="line-grid">
            <label class="span-2">
                <span>Produit / service</span>
                <select data-field="product_id">
                    <option value="">Ligne libre</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                <span>Type</span>
                <select data-field="type">
                    <option value="prestation">Prestation</option>
                    <option value="service">Service</option>
                    <option value="produit">Produit</option>
                    <option value="frais">Frais</option>
                    <option value="remise">Remise</option>
                </select>
            </label>
            <label class="span-3">
                <span>Description</span>
                <input type="text" data-field="description" maxlength="500" required>
                <small class="field-error" data-line-error="description"></small>
            </label>
            <label class="span-3">
                <span>Détail</span>
                <textarea data-field="detailed_description" rows="2"></textarea>
            </label>
            <label>
                <span>Qté</span>
                <input type="number" data-field="quantity" min="1" step="1" required>
                <small class="field-error" data-line-error="quantity"></small>
            </label>
            <label>
                <span>Prix unitaire</span>
                <input type="number" data-field="unit_price" min="0" step="0.01" required>
                <small class="field-error" data-line-error="unit_price"></small>
            </label>
            <label>
                <span>Taxe (%)</span>
                <input type="number" data-field="tax_rate" min="0" max="100" step="0.01">
            </label>
            <div class="line-total">
                <span>Total ligne</span>
                <strong data-line-total>0,00 $</strong>
            </div>
        </div>
    </article>
</template>

<style>
    .invoice-workspace { padding: 28px; background: #f3f6fa; min-height: calc(100vh - 72px); color: #172033; }
    .invoice-page-head { display: flex; align-items: center; justify-content: space-between; gap: 18px; margin-bottom: 22px; padding: 20px; border: 1px solid #dbe3ee; border-radius: 8px; background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%); box-shadow: 0 14px 32px rgba(15, 23, 42, .06); }
    .invoice-page-head h1 { margin: 0; font-size: 30px; font-weight: 850; letter-spacing: 0; color: #0f172a; }
    .invoice-kicker { margin: 0 0 5px; color: #2563eb; font-size: 12px; font-weight: 850; text-transform: uppercase; }
    .invoice-actions { display: flex; gap: 10px; flex-wrap: wrap; }
    .invoice-actions .btn { min-height: 40px; border-radius: 7px; font-weight: 750; }
    .invoice-layout { display: grid; grid-template-columns: minmax(0, 1fr) 380px; gap: 20px; align-items: start; }
    .invoice-main { display: grid; gap: 18px; }
    .invoice-panel { background: #fff; border: 1px solid #dbe3ee; border-radius: 8px; padding: 18px; box-shadow: 0 12px 28px rgba(15, 23, 42, .05); }
    .sticky-panel { position: sticky; top: 18px; }
    .panel-title { display: flex; align-items: center; gap: 10px; margin: -18px -18px 18px; padding: 14px 18px; border-bottom: 1px solid #e2e8f0; background: #fbfdff; font-weight: 850; color: #172033; border-radius: 8px 8px 0 0; }
    .panel-title i { color: #2563eb; }
    .panel-title.with-action { justify-content: space-between; }
    .panel-title.with-action > div { display: flex; gap: 10px; align-items: center; }
    .form-grid { display: grid; gap: 14px; }
    .form-grid.two { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .form-grid.one { grid-template-columns: 1fr; }
    .invoice-form label { display: grid; gap: 7px; margin-bottom: 14px; color: #475569; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: .02em; }
    .invoice-form label.full { margin-bottom: 16px; }
    .invoice-form input, .invoice-form select, .invoice-form textarea { width: 100%; border: 1px solid #cbd5e1; border-radius: 7px; padding: 11px 12px; color: #172033; background: #fff; font: inherit; font-size: 14px; font-weight: 600; text-transform: none; letter-spacing: 0; }
    .invoice-form textarea { resize: vertical; line-height: 1.5; }
    .invoice-form input:focus, .invoice-form select:focus, .invoice-form textarea:focus { outline: 0; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, .12); }
    .field-error { min-height: 15px; color: #dc2626; font-size: 12px; font-weight: 650; text-transform: none; letter-spacing: 0; }
    .line-editor { display: grid; gap: 14px; }
    .line-row { border: 1px solid #dbe3ee; border-radius: 8px; padding: 14px; background: #fbfdff; transition: .16s ease; }
    .line-row:hover { border-color: #b8c7da; box-shadow: 0 10px 22px rgba(15, 23, 42, .04); }
    .line-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
    .line-number { display: inline-flex; width: 30px; height: 30px; align-items: center; justify-content: center; border-radius: 7px; background: #dbeafe; color: #1d4ed8; font-weight: 850; }
    .icon-btn { width: 32px; height: 32px; border: 1px solid #fecaca; color: #dc2626; background: #fff; border-radius: 7px; display: inline-flex; align-items: center; justify-content: center; transition: .16s ease; }
    .icon-btn:hover { background: #fff5f5; transform: translateY(-1px); }
    .line-grid { display: grid; grid-template-columns: 1.35fr .9fr .9fr; gap: 12px; align-items: end; }
    .line-grid .span-2 { grid-column: span 2; }
    .line-grid .span-3 { grid-column: span 3; }
    .line-total { display: grid; gap: 7px; color: #475569; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: .02em; }
    .line-total strong { display: flex; align-items: center; min-height: 44px; padding: 10px 12px; border-radius: 7px; background: #ecfdf5; color: #0f766e; font-size: 15px; letter-spacing: 0; }
    .logo-uploader { margin: 16px 0; display: grid; gap: 10px; }
    .logo-drop { border: 1px dashed #9eb0c8; border-radius: 8px; padding: 16px; text-align: center; cursor: pointer; background: #fbfdff; transition: .16s ease; }
    .logo-drop:hover { border-color: #2563eb; background: #f8fbff; }
    .logo-drop input { display: none; }
    .logo-preview { width: 104px; height: 74px; display: inline-flex; align-items: center; justify-content: center; margin: 0 auto 10px; border-radius: 7px; border: 1px solid #e2e8f0; background: #eef2f7 center/contain no-repeat; color: #64748b; font-size: 24px; }
    .total-box { border-top: 1px solid #e2e8f0; padding-top: 14px; display: grid; gap: 10px; }
    .total-box div { display: flex; justify-content: space-between; gap: 12px; color: #526070; font-weight: 700; }
    .total-box strong { color: #172033; }
    .grand-total { margin-top: 4px; border-top: 1px solid #dbe4f0; padding-top: 14px; font-size: 20px; font-weight: 850; }
    .grand-total strong { color: #0f766e; }
    .invoice-toast-host { position: fixed; right: 20px; bottom: 20px; z-index: 1080; display: grid; gap: 10px; }
    .invoice-toast { padding: 12px 14px; border-radius: 8px; color: #fff; box-shadow: 0 15px 30px rgba(15, 23, 42, .18); min-width: 240px; font-weight: 750; }
    .invoice-toast.success { background: #16a34a; }
    .invoice-toast.error { background: #dc2626; }
    @media (max-width: 1100px) { .invoice-layout { grid-template-columns: 1fr; } .sticky-panel { position: static; } }
    @media (max-width: 760px) { .invoice-workspace { padding: 14px; } .invoice-page-head { display: grid; padding: 16px; } .form-grid.two, .line-grid { grid-template-columns: 1fr; } .line-grid .span-2, .line-grid .span-3 { grid-column: span 1; } }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('documentForm');
    const lineEditor = document.getElementById('lineEditor');
    const template = document.getElementById('lineTemplate');
    const saveBtn = document.getElementById('saveDocumentBtn');
    const products = @json($productsPayload);
    const initialLines = @json($lineItems);
    const submitUrl = @json($storeUrl);
    const documentMethod = @json($mode === 'edit' ? 'PUT' : 'POST');
    const currency = new Intl.NumberFormat('fr-CA', { style: 'currency', currency: 'CAD' });

    const toast = (message, type = 'success') => {
        if (typeof window.showToast === 'function') {
            window.showToast(message, type);
            return;
        }
        const host = document.getElementById('invoiceToastHost');
        const item = document.createElement('div');
        item.className = `invoice-toast ${type}`;
        item.textContent = message;
        host.appendChild(item);
        setTimeout(() => item.remove(), 4200);
    };

    const lineName = (index, field) => `lines[${index}][${field}]`;
    const productById = (id) => products.find(product => String(product.id) === String(id));

    const addLine = (data = {}) => {
        const row = template.content.firstElementChild.cloneNode(true);
        lineEditor.appendChild(row);
        fillLine(row, data);
        refreshLineNames();
        calculateTotals();
    };

    const fillLine = (row, data) => {
        row.querySelectorAll('[data-field]').forEach(input => {
            const field = input.dataset.field;
            input.value = data[field] ?? (field === 'quantity' ? 1 : field === 'tax_rate' || field === 'unit_price' ? 0 : '');
        });
    };

    const refreshLineNames = () => {
        lineEditor.querySelectorAll('[data-line]').forEach((row, index) => {
            row.querySelector('.line-number').textContent = index + 1;
            row.querySelectorAll('[data-field]').forEach(input => {
                input.name = lineName(index, input.dataset.field);
            });
            row.querySelectorAll('[data-line-error]').forEach(error => {
                error.dataset.errorFor = lineName(index, error.dataset.lineError);
            });
        });
    };

    const calculateTotals = () => {
        let subtotal = 0;
        let tax = 0;
        lineEditor.querySelectorAll('[data-line]').forEach(row => {
            const quantity = Number(row.querySelector('[data-field="quantity"]').value || 0);
            const price = Number(row.querySelector('[data-field="unit_price"]').value || 0);
            const rate = Number(row.querySelector('[data-field="tax_rate"]').value || 0);
            const lineSubtotal = quantity * price;
            const lineTax = lineSubtotal * rate / 100;
            subtotal += lineSubtotal;
            tax += lineTax;
            row.querySelector('[data-line-total]').textContent = currency.format(lineSubtotal + lineTax);
        });
        const discountRate = Number(form.elements.discount_percentage.value || 0);
        const shipping = Number(form.elements.shipping_fees.value || 0);
        const admin = Number(form.elements.administration_fees.value || 0);
        const discount = subtotal * discountRate / 100;
        const fees = shipping + admin;
        const total = subtotal - discount + tax + fees;

        document.getElementById('subtotalValue').textContent = currency.format(subtotal);
        document.getElementById('discountValue').textContent = currency.format(discount);
        document.getElementById('taxValue').textContent = currency.format(tax);
        document.getElementById('feesValue').textContent = currency.format(fees);
        document.getElementById('totalValue').textContent = currency.format(total);
    };

    const clearErrors = () => {
        document.querySelectorAll('.field-error').forEach(item => item.textContent = '');
        form.querySelectorAll('.is-invalid').forEach(item => item.classList.remove('is-invalid'));
    };

    const dotToName = (key) => {
        const parts = key.split('.');
        if (parts[0] === 'lines' && parts.length >= 3) {
            return `lines[${parts[1]}][${parts[2]}]`;
        }
        return key;
    };

    const showErrors = (errors = {}) => {
        Object.entries(errors).forEach(([key, messages]) => {
            const fieldName = key.startsWith('lines.') ? dotToName(key) : key;
            const field = Array.from(form.elements).find(element => element.name === fieldName);
            const error = document.querySelector(`[data-error-for="${fieldName}"]`) || document.querySelector(`[data-error-for="${key}"]`);
            if (field) field.classList.add('is-invalid');
            if (error) error.textContent = Array.isArray(messages) ? messages[0] : messages;
        });
    };

    document.getElementById('addLineBtn').addEventListener('click', () => addLine());

    lineEditor.addEventListener('click', event => {
        if (!event.target.closest('.remove-line')) return;
        if (lineEditor.querySelectorAll('[data-line]').length === 1) {
            toast('Au moins une ligne est obligatoire.', 'error');
            return;
        }
        event.target.closest('[data-line]').remove();
        refreshLineNames();
        calculateTotals();
    });

    lineEditor.addEventListener('input', calculateTotals);
    form.addEventListener('input', event => {
        if (['discount_percentage', 'shipping_fees', 'administration_fees'].includes(event.target.name)) calculateTotals();
    });

    lineEditor.addEventListener('change', event => {
        if (event.target.dataset.field !== 'product_id') return;
        const product = productById(event.target.value);
        if (!product) return;
        const row = event.target.closest('[data-line]');
        row.querySelector('[data-field="description"]').value = product.name;
        row.querySelector('[data-field="unit_price"]').value = product.price;
        row.querySelector('[data-field="tax_rate"]').value = product.tax_rate;
        row.querySelector('[data-field="type"]').value = ['produit', 'service'].includes(product.type) ? product.type : 'prestation';
        calculateTotals();
    });

    document.getElementById('logoInput').addEventListener('change', event => {
        const file = event.target.files[0];
        if (!file) return;
        document.getElementById('removeLogoInput').value = '0';
        const preview = document.getElementById('logoPreview');
        preview.innerHTML = '';
        preview.style.backgroundImage = `url("${URL.createObjectURL(file)}")`;
    });

    document.getElementById('removeLogoBtn').addEventListener('click', () => {
        document.getElementById('logoInput').value = '';
        document.getElementById('removeLogoInput').value = '1';
        const preview = document.getElementById('logoPreview');
        preview.style.backgroundImage = '';
        preview.innerHTML = '<i class="fas fa-image"></i>';
    });

    form.addEventListener('submit', async event => {
        event.preventDefault();
        clearErrors();

        if (!form.checkValidity()) {
            form.reportValidity();
            toast('Merci de compléter les champs obligatoires.', 'error');
            return;
        }

        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Enregistrement';

        try {
            const data = new FormData(form);
            if (documentMethod !== 'POST') data.set('_method', documentMethod);
            const response = await fetch(submitUrl, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: data,
            });
            const payload = await response.json();

            if (!response.ok || !payload.success) {
                showErrors(payload.errors || {});
                throw new Error(payload.message || 'Enregistrement impossible.');
            }

            toast(payload.message || 'Document enregistré.');
            window.location.href = payload.redirect_url;
        } catch (error) {
            toast(error.message || 'Une erreur est survenue.', 'error');
        } finally {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-save me-2"></i>Enregistrer';
        }
    });

    initialLines.forEach(line => addLine(line));
});
</script>
@endsection
