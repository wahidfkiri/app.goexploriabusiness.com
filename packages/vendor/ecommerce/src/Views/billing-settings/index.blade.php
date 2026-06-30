@extends('layouts.app')

@php
    $routes = [
        'update' => route('billing.settings.update'),
        'taxStore' => route('billing.settings.taxes.store'),
        'taxUpdate' => route('billing.settings.taxes.update', ['id' => '__ID__']),
        'taxDestroy' => route('billing.settings.taxes.destroy', ['id' => '__ID__']),
        'discountStore' => route('billing.settings.discounts.store'),
        'discountUpdate' => route('billing.settings.discounts.update', ['id' => '__ID__']),
        'discountDestroy' => route('billing.settings.discounts.destroy', ['id' => '__ID__']),
        'invoices' => route('invoices.index'),
        'quotes' => route('quotes.index'),
        'requestServices' => route('billing.request-services.index'),
        'requests' => route('billing.requests.index'),
    ];
    $defaultTaxIds = collect($settings->default_tax_ids ?? [])->map(fn ($id) => (int) $id)->all();
@endphp

@section('content')
<main class="dashboard-content billing-settings-page">
    <div class="settings-head">
        <div>
            <p class="settings-kicker">Facturation</p>
            <h1>Paramètres de facturation</h1>
        </div>
        <div class="settings-actions">
            <a href="{{ $routes['requestServices'] }}" class="btn btn-outline-secondary"><i class="fas fa-sliders-h me-2"></i>Options demandes</a>
            <a href="{{ $routes['requests'] }}" class="btn btn-outline-secondary"><i class="fas fa-inbox me-2"></i>Demandes re&ccedil;ues</a>
            <a href="{{ $routes['invoices'] }}" class="btn btn-outline-secondary"><i class="fas fa-file-invoice-dollar me-2"></i>Factures</a>
            <a href="{{ $routes['quotes'] }}" class="btn btn-outline-secondary"><i class="fas fa-file-signature me-2"></i>Devis</a>
            <button type="submit" form="billingSettingsForm" class="btn btn-primary" id="saveBillingSettingsBtn">
                <i class="fas fa-save me-2"></i>Enregistrer
            </button>
        </div>
    </div>

    <form id="billingSettingsForm" class="settings-layout" enctype="multipart/form-data" novalidate>
        @csrf

        <section class="settings-main">
            <div class="settings-card">
                <div class="settings-card-title">
                    <i class="fas fa-hashtag"></i>
                    <span>Numérotation</span>
                </div>
                <div class="settings-grid three">
                    <label>
                        <span>Préfixe facture</span>
                        <input type="text" name="invoice_prefix" value="{{ old('invoice_prefix', $settings->invoice_prefix ?: 'F-') }}" required>
                    </label>
                    <label>
                        <span>Dernier numéro facture</span>
                        <input type="text" name="last_invoice_number" value="{{ old('last_invoice_number', $settings->last_invoice_number ?: 'F-26000') }}" required>
                    </label>
                    <label>
                        <span>Longueur facture</span>
                        <input type="number" name="invoice_number_length" min="3" max="12" value="{{ old('invoice_number_length', $settings->invoice_number_length ?: 5) }}" required>
                    </label>
                    <label>
                        <span>Préfixe devis</span>
                        <input type="text" name="quote_prefix" value="{{ old('quote_prefix', $settings->quote_prefix ?: 'D-') }}" required>
                    </label>
                    <label>
                        <span>Dernier numéro devis</span>
                        <input type="text" name="last_quote_number" value="{{ old('last_quote_number', $settings->last_quote_number ?: 'D-26000') }}" required>
                    </label>
                    <label>
                        <span>Longueur devis</span>
                        <input type="number" name="quote_number_length" min="3" max="12" value="{{ old('quote_number_length', $settings->quote_number_length ?: 5) }}" required>
                    </label>
                </div>
            </div>

            <div class="settings-card">
                <div class="settings-card-title">
                    <i class="fas fa-file-alt"></i>
                    <span>Templates et rendu</span>
                </div>
                <div class="settings-grid two">
                    <label>
                        <span>Template facture</span>
                        <select name="invoice_template" required>
                            @foreach($templateOptions as $value => $label)
                                <option value="{{ $value }}" @selected(($settings->invoice_template ?: 'classic') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        <span>Template devis</span>
                        <select name="quote_template" required>
                            @foreach($templateOptions as $value => $label)
                                <option value="{{ $value }}" @selected(($settings->quote_template ?: 'classic') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        <span>Devise</span>
                        <select name="currency" required>
                            @foreach(['CAD' => 'CAD - Dollar canadien', 'USD' => 'USD - Dollar américain', 'EUR' => 'EUR - Euro', 'MAD' => 'MAD - Dirham marocain'] as $value => $label)
                                <option value="{{ $value }}" @selected(($settings->currency ?: 'CAD') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        <span>Locale</span>
                        <select name="locale" required>
                            @foreach(['fr_CA' => 'Français Canada', 'fr_FR' => 'Français France', 'en_CA' => 'English Canada', 'en_US' => 'English US'] as $value => $label)
                                <option value="{{ $value }}" @selected(($settings->locale ?: 'fr_CA') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        <span>Libellé échéance facture</span>
                        <input type="text" name="invoice_due_label" value="{{ old('invoice_due_label', $settings->invoice_due_label ?: 'Échéance') }}">
                    </label>
                    <label>
                        <span>Libellé validité devis</span>
                        <input type="text" name="quote_validity_label" value="{{ old('quote_validity_label', $settings->quote_validity_label ?: 'Valide jusqu’au') }}">
                    </label>
                </div>
            </div>

            <div class="settings-card">
                <div class="settings-card-title with-action">
                    <div><i class="fas fa-percent"></i><span>Taxes et TVA</span></div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addTaxBtn">
                        <i class="fas fa-plus me-1"></i>Ajouter taxe
                    </button>
                </div>
                <label class="settings-switch mb-3">
                    <input type="checkbox" name="taxes_enabled" value="1" @checked((bool) ($settings->taxes_enabled ?? true))>
                    Activer les taxes dans les factures, devis et demandes
                </label>
                <div class="tax-table-wrap">
                    <table class="tax-table">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Code</th>
                                <th>Type</th>
                                <th>Taux</th>
                                <th>Défaut</th>
                                <th>Active</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="taxTableBody">
                            @forelse($taxes as $tax)
                                <tr data-tax-id="{{ $tax->id }}">
                                    <td>{{ $tax->name }}</td>
                                    <td><strong>{{ $tax->code }}</strong></td>
                                    <td>{{ strtoupper($tax->type) }}</td>
                                    <td>{{ number_format((float) $tax->rate, 2, ',', ' ') }} %</td>
                                    <td>{!! $tax->is_default ? '<span class="settings-pill success">Oui</span>' : '<span class="settings-pill">Non</span>' !!}</td>
                                    <td>{!! $tax->is_active ? '<span class="settings-pill success">Active</span>' : '<span class="settings-pill muted">Inactive</span>' !!}</td>
                                    <td class="text-end">
                                        <button type="button" class="icon-action" data-edit-tax='@json($tax)'><i class="fas fa-edit"></i></button>
                                        <button type="button" class="icon-action danger" data-delete-tax="{{ $tax->id }}"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="empty-cell">Aucune taxe configurée.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <label class="settings-check mt-3">
                    <span>TVA globale pour demandes/services</span>
                    <select name="default_tax_ids[]" multiple>
                        @foreach($taxes as $tax)
                            <option value="{{ $tax->id }}" @selected(in_array($tax->id, $defaultTaxIds, true))>{{ $tax->name }} - {{ number_format((float) $tax->rate, 2, ',', ' ') }}%</option>
                        @endforeach
                    </select>
                </label>
            </div>

            <div class="settings-card">
                <div class="settings-card-title with-action">
                    <div><i class="fas fa-tags"></i><span>Remises globales</span></div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addDiscountBtn">
                        <i class="fas fa-plus me-1"></i>Ajouter remise
                    </button>
                </div>
                <div class="tax-table-wrap">
                    <table class="tax-table">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Code</th>
                                <th>Type</th>
                                <th>Valeur</th>
                                <th>DÃ©faut</th>
                                <th>Active</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="discountTableBody">
                            @forelse($discounts as $discount)
                                <tr data-discount-id="{{ $discount->id }}">
                                    <td>{{ $discount->name }}</td>
                                    <td><strong>{{ $discount->code ?: '-' }}</strong></td>
                                    <td>{{ $discount->type === 'fixed' ? 'Montant fixe' : 'Pourcentage' }}</td>
                                    <td>{{ $discount->type === 'fixed' ? number_format((float) $discount->value, 2, ',', ' ') . ' $' : number_format((float) $discount->value, 2, ',', ' ') . ' %' }}</td>
                                    <td>{!! $discount->is_default ? '<span class="settings-pill success">Oui</span>' : '<span class="settings-pill">Non</span>' !!}</td>
                                    <td>{!! $discount->is_active ? '<span class="settings-pill success">Active</span>' : '<span class="settings-pill muted">Inactive</span>' !!}</td>
                                    <td class="text-end">
                                        <button type="button" class="icon-action" data-edit-discount='@json($discount)'><i class="fas fa-edit"></i></button>
                                        <button type="button" class="icon-action danger" data-delete-discount="{{ $discount->id }}"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="empty-cell">Aucune remise configurÃ©e.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <label class="settings-check mt-3">
                    <span>Remise globale pour les demandes/services</span>
                    <select name="default_discount_id">
                        <option value="">Utiliser la remise manuelle ci-contre</option>
                        @foreach($discounts->where('is_active', true) as $discount)
                            <option value="{{ $discount->id }}" @selected((int) $settings->default_discount_id === (int) $discount->id)>
                                {{ $discount->name }} - {{ $discount->type === 'fixed' ? number_format((float) $discount->value, 2, ',', ' ') . ' $' : number_format((float) $discount->value, 2, ',', ' ') . '%' }}
                            </option>
                        @endforeach
                    </select>
                </label>
            </div>

            <div class="settings-card">
                <div class="settings-card-title">
                    <i class="fas fa-align-left"></i>
                    <span>Notes, conditions et mentions</span>
                </div>
                <div class="settings-grid two">
                    <label>
                        <span>Note par défaut</span>
                        <textarea name="default_note" rows="4">{{ old('default_note', $settings->default_note) }}</textarea>
                    </label>
                    <label>
                        <span>Instructions internes</span>
                        <textarea name="instructions" rows="4">{{ old('instructions', $settings->instructions) }}</textarea>
                    </label>
                    <label>
                        <span>Pied de page facture</span>
                        <textarea name="invoice_footer_note" rows="4">{{ old('invoice_footer_note', $settings->invoice_footer_note) }}</textarea>
                    </label>
                    <label>
                        <span>Pied de page devis</span>
                        <textarea name="quote_footer_note" rows="4">{{ old('quote_footer_note', $settings->quote_footer_note) }}</textarea>
                    </label>
                    <label class="span-2">
                        <span>Conditions générales</span>
                        <textarea name="terms_and_conditions" rows="5">{{ old('terms_and_conditions', $settings->terms_and_conditions) }}</textarea>
                    </label>
                    <label class="span-2">
                        <span>Mentions légales</span>
                        <textarea name="legal_mentions" rows="4">{{ old('legal_mentions', $settings->legal_mentions) }}</textarea>
                    </label>
                </div>
            </div>
        </section>

        <aside class="settings-side">
            <div class="settings-card sticky-settings">
                <div class="settings-card-title">
                    <i class="fas fa-building"></i>
                    <span>Identité</span>
                </div>
                <div class="logo-uploader">
                    <input type="hidden" name="remove_logo" id="removeBillingLogo" value="0">
                    <label for="billingLogoInput" class="logo-drop">
                        <input type="file" id="billingLogoInput" name="logo" accept="image/png,image/jpeg,image/webp,image/svg+xml">
                        <span class="logo-preview" id="billingLogoPreview" @if($settings->billing_logo_url) style="background-image:url('{{ $settings->billing_logo_url }}')" @endif>
                            @unless($settings->billing_logo_url)
                                <i class="fas fa-image"></i>
                            @endunless
                        </span>
                        <strong>Logo facture/devis</strong>
                        <small>Utilisé dans les PDF et documents</small>
                    </label>
                    <button type="button" class="btn btn-sm btn-outline-danger w-100" id="removeBillingLogoBtn">Retirer</button>
                </div>

                <div class="settings-grid one">
                    <label>
                        <span>N° TPS</span>
                        <input type="text" name="tax_number_tps" value="{{ old('tax_number_tps', $settings->tax_number_tps) }}">
                    </label>
                    <label>
                        <span>N° TVQ / TVA</span>
                        <input type="text" name="tax_number_tvq" value="{{ old('tax_number_tvq', $settings->tax_number_tvq) }}">
                    </label>
                    <label>
                        <span>NEQ</span>
                        <input type="text" name="neq" value="{{ old('neq', $settings->neq) }}">
                    </label>
                    <label>
                        <span>RCS</span>
                        <input type="text" name="rcs_number" value="{{ old('rcs_number', $settings->rcs_number) }}">
                    </label>
                    <label>
                        <span>SIRET</span>
                        <input type="text" name="siret" value="{{ old('siret', $settings->siret) }}">
                    </label>
                </div>
            </div>

            <div class="settings-card">
                <div class="settings-card-title">
                    <i class="fas fa-sliders-h"></i>
                    <span>Défauts</span>
                </div>
                <div class="settings-grid one">
                    <label>
                        <span>Délai paiement facture</span>
                        <input type="number" name="payment_deadline_days" min="0" max="365" value="{{ old('payment_deadline_days', $settings->payment_deadline_days ?: 30) }}">
                    </label>
                    <label>
                        <span>Validité devis</span>
                        <input type="number" name="quote_validity_days" min="0" max="365" value="{{ old('quote_validity_days', $settings->quote_validity_days ?: 30) }}">
                    </label>
                    <label>
                        <span>Frais livraison</span>
                        <input type="number" step="0.01" name="default_shipping_fees" value="{{ old('default_shipping_fees', $settings->default_shipping_fees ?? 0) }}">
                    </label>
                    <label>
                        <span>Frais administratifs</span>
                        <input type="number" step="0.01" name="default_administration_fees" value="{{ old('default_administration_fees', $settings->default_administration_fees ?? 0) }}">
                    </label>
                    <label>
                        <span>Remise défaut (%)</span>
                        <input type="number" step="0.01" name="default_discount_percentage" value="{{ old('default_discount_percentage', $settings->default_discount_percentage ?? 0) }}">
                    </label>
                </div>
            </div>

            <div class="settings-card">
                <div class="settings-card-title">
                    <i class="fas fa-credit-card"></i>
                    <span>Paiement</span>
                </div>
                <div class="switch-list">
                    <label><input type="checkbox" name="enable_online_payment" value="1" @checked($settings->enable_online_payment)> Paiement en ligne</label>
                    <label><input type="checkbox" name="enable_partial_payments" value="1" @checked($settings->enable_partial_payments)> Paiements partiels</label>
                    <label><input type="checkbox" name="auto_send_invoice_pdf" value="1" @checked($settings->auto_send_invoice_pdf ?? true)> Joindre PDF automatiquement</label>
                    <label><input type="checkbox" name="auto_convert_accepted_quote" value="1" @checked($settings->auto_convert_accepted_quote)> Convertir devis accepté</label>
                    <label><input type="checkbox" name="hide_invoice_button" value="1" @checked($settings->hide_invoice_button)> Masquer bouton facture</label>
                </div>
                <div class="settings-grid one mt-3">
                    <label>
                        <span>Ordre du chèque</span>
                        <input type="text" name="cheque_order" value="{{ old('cheque_order', $settings->cheque_order) }}">
                    </label>
                    <label>
                        <span>Code bouton paiement</span>
                        <input type="text" name="payment_button_code" value="{{ old('payment_button_code', $settings->payment_button_code) }}">
                    </label>
                    <label>
                        <span>Banque</span>
                        <input type="text" name="bank_details[bank_name]" value="{{ old('bank_details.bank_name', data_get($settings->bank_details, 'bank_name')) }}">
                    </label>
                    <label>
                        <span>IBAN</span>
                        <input type="text" name="bank_details[iban]" value="{{ old('bank_details.iban', data_get($settings->bank_details, 'iban')) }}">
                    </label>
                    <label>
                        <span>BIC / SWIFT</span>
                        <input type="text" name="bank_details[bic]" value="{{ old('bank_details.bic', data_get($settings->bank_details, 'bic')) }}">
                    </label>
                    <label>
                        <span>Procédure paiement</span>
                        <textarea name="procedure" rows="4">{{ old('procedure', $settings->procedure) }}</textarea>
                    </label>
                </div>
            </div>
        </aside>
    </form>

    <div id="billingSettingsToastHost" class="settings-toast-host"></div>
</main>

<div class="modal fade" id="taxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" id="taxForm">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="taxModalTitle">Ajouter une taxe</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body settings-grid one">
                <input type="hidden" id="taxId">
                <label><span>Nom</span><input type="text" name="name" required></label>
                <label><span>Code</span><input type="text" name="code" required></label>
                <label><span>Type</span>
                    <select name="type" required>
                        <option value="tps">TPS</option>
                        <option value="tvq">TVQ</option>
                        <option value="tva">TVA</option>
                        <option value="autres">Autres</option>
                    </select>
                </label>
                <label><span>Taux (%)</span><input type="number" step="0.01" min="0" max="100" name="rate" required></label>
                <label><span>Description</span><textarea name="description" rows="3"></textarea></label>
                <label class="settings-switch"><input type="checkbox" name="is_default" value="1"> Taxe par défaut</label>
                <label class="settings-switch"><input type="checkbox" name="is_active" value="1" checked> Active</label>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="discountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" id="discountForm">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="discountModalTitle">Ajouter une remise</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body settings-grid one">
                <input type="hidden" id="discountId">
                <label><span>Nom</span><input type="text" name="name" required></label>
                <label><span>Code</span><input type="text" name="code"></label>
                <label><span>Type</span>
                    <select name="type" required>
                        <option value="percentage">Pourcentage</option>
                        <option value="fixed">Montant fixe</option>
                    </select>
                </label>
                <label><span>Valeur</span><input type="number" step="0.01" min="0" name="value" required></label>
                <label><span>Description</span><textarea name="description" rows="3"></textarea></label>
                <label class="settings-switch"><input type="checkbox" name="is_default" value="1"> Remise par dÃ©faut</label>
                <label class="settings-switch"><input type="checkbox" name="is_active" value="1" checked> Active</label>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<style>
    .billing-settings-page { padding: 28px; background: #f3f6fa; min-height: calc(100vh - 72px); color: #172033; }
    .settings-head { display: flex; justify-content: space-between; gap: 18px; align-items: center; margin-bottom: 22px; padding: 20px; border: 1px solid #dbe3ee; border-radius: 8px; background: linear-gradient(135deg, #fff 0%, #f8fbff 100%); box-shadow: 0 14px 32px rgba(15, 23, 42, .06); }
    .settings-head h1 { margin: 0; font-size: 30px; font-weight: 850; color: #0f172a; }
    .settings-kicker { margin: 0 0 5px; color: #2563eb; text-transform: uppercase; font-size: 12px; font-weight: 850; }
    .settings-actions { display: flex; flex-wrap: wrap; gap: 10px; }
    .settings-actions .btn { min-height: 40px; border-radius: 7px; font-weight: 750; }
    .settings-layout { display: grid; grid-template-columns: minmax(0, 1fr) 390px; gap: 20px; align-items: start; }
    .settings-main, .settings-side { display: grid; gap: 18px; }
    .settings-card { background: #fff; border: 1px solid #dbe3ee; border-radius: 8px; padding: 18px; box-shadow: 0 12px 28px rgba(15, 23, 42, .05); }
    .sticky-settings { position: sticky; top: 18px; }
    .settings-card-title { display: flex; align-items: center; gap: 10px; margin: -18px -18px 18px; padding: 14px 18px; border-bottom: 1px solid #e2e8f0; background: #fbfdff; border-radius: 8px 8px 0 0; color: #0f172a; font-weight: 850; }
    .settings-card-title i { color: #2563eb; }
    .settings-card-title.with-action { justify-content: space-between; }
    .settings-card-title.with-action > div { display: flex; align-items: center; gap: 10px; }
    .settings-grid { display: grid; gap: 14px; }
    .settings-grid.one { grid-template-columns: 1fr; }
    .settings-grid.two { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .settings-grid.three { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .settings-grid .span-2 { grid-column: span 2; }
    .settings-grid label, .settings-check { display: grid; gap: 7px; color: #475569; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: .02em; }
    .settings-grid input, .settings-grid select, .settings-grid textarea, .settings-check select { width: 100%; border: 1px solid #cbd5e1; border-radius: 7px; padding: 11px 12px; color: #172033; background: #fff; font: inherit; font-size: 14px; font-weight: 600; text-transform: none; letter-spacing: 0; }
    .settings-grid textarea { resize: vertical; line-height: 1.5; }
    .settings-grid input:focus, .settings-grid select:focus, .settings-grid textarea:focus { outline: 0; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, .12); }
    .tax-table-wrap { border: 1px solid #e2e8f0; border-radius: 8px; overflow-x: auto; }
    .tax-table { width: 100%; border-collapse: separate; border-spacing: 0; }
    .tax-table th { padding: 12px; color: #64748b; background: #f8fafc; border-bottom: 1px solid #e2e8f0; font-size: 11px; text-transform: uppercase; }
    .tax-table td { padding: 12px; border-bottom: 1px solid #eef2f7; color: #334155; }
    .tax-table tbody tr:last-child td { border-bottom: 0; }
    .settings-pill { display: inline-flex; padding: 5px 9px; border-radius: 999px; background: #e2e8f0; color: #334155; font-size: 12px; font-weight: 800; }
    .settings-pill.success { background: #dcfce7; color: #166534; }
    .settings-pill.muted { background: #f1f5f9; color: #64748b; }
    .icon-action { width: 32px; height: 32px; border: 1px solid #d6dfeb; border-radius: 7px; background: #fff; color: #475569; display: inline-flex; align-items: center; justify-content: center; }
    .icon-action.danger { color: #dc2626; border-color: #fecaca; }
    .empty-cell { text-align: center; padding: 24px !important; color: #64748b; }
    .logo-uploader { display: grid; gap: 10px; margin-bottom: 16px; }
    .logo-drop { border: 1px dashed #9eb0c8; border-radius: 8px; padding: 16px; text-align: center; cursor: pointer; background: #fbfdff; }
    .logo-drop input { display: none; }
    .logo-preview { width: 108px; height: 76px; display: inline-flex; align-items: center; justify-content: center; margin: 0 auto 10px; border-radius: 7px; border: 1px solid #e2e8f0; background: #eef2f7 center/contain no-repeat; color: #64748b; font-size: 24px; }
    .switch-list { display: grid; gap: 10px; }
    .switch-list label, .settings-switch { display: flex; gap: 10px; align-items: center; color: #334155; font-weight: 750; }
    .switch-list input, .settings-switch input { width: 18px; height: 18px; }
    .settings-toast-host { position: fixed; right: 20px; bottom: 20px; z-index: 1080; display: grid; gap: 10px; }
    .settings-toast { padding: 12px 14px; border-radius: 8px; color: #fff; background: #16a34a; box-shadow: 0 15px 30px rgba(15, 23, 42, .18); min-width: 240px; font-weight: 750; }
    .settings-toast.error { background: #dc2626; }
    @media (max-width: 1180px) { .settings-layout { grid-template-columns: 1fr; } .sticky-settings { position: static; } }
    @media (max-width: 760px) { .billing-settings-page { padding: 14px; } .settings-head { display: grid; padding: 16px; } .settings-grid.two, .settings-grid.three { grid-template-columns: 1fr; } .settings-grid .span-2 { grid-column: span 1; } }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const routes = @json($routes);
    const csrf = @json(csrf_token());
    const form = document.getElementById('billingSettingsForm');
    const taxForm = document.getElementById('taxForm');
    const taxModalElement = document.getElementById('taxModal');
    const taxModal = taxModalElement && window.bootstrap ? new bootstrap.Modal(taxModalElement) : null;
    const taxIdInput = document.getElementById('taxId');
    const discountForm = document.getElementById('discountForm');
    const discountModalElement = document.getElementById('discountModal');
    const discountModal = discountModalElement && window.bootstrap ? new bootstrap.Modal(discountModalElement) : null;
    const discountIdInput = document.getElementById('discountId');
    const route = (name, id) => routes[name].replace('__ID__', id);
    const toast = (message, type = 'success') => {
        if (typeof window.showToast === 'function') {
            window.showToast(message, type);
            return;
        }
        const host = document.getElementById('billingSettingsToastHost');
        const item = document.createElement('div');
        item.className = `settings-toast ${type}`;
        item.textContent = message;
        host.appendChild(item);
        setTimeout(() => item.remove(), 4200);
    };

    form.addEventListener('submit', async event => {
        event.preventDefault();
        const button = document.getElementById('saveBillingSettingsBtn');
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Enregistrement';
        try {
            const response = await fetch(routes.update, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf },
                body: new FormData(form),
            });
            const payload = await response.json();
            if (!response.ok || !payload.success) throw new Error(payload.message || 'Enregistrement impossible.');
            toast(payload.message || 'Paramètres enregistrés.');
        } catch (error) {
            toast(error.message || 'Erreur lors de la sauvegarde.', 'error');
        } finally {
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-save me-2"></i>Enregistrer';
        }
    });

    document.getElementById('billingLogoInput').addEventListener('change', event => {
        const file = event.target.files[0];
        if (!file) return;
        document.getElementById('removeBillingLogo').value = '0';
        const preview = document.getElementById('billingLogoPreview');
        preview.innerHTML = '';
        preview.style.backgroundImage = `url("${URL.createObjectURL(file)}")`;
    });

    document.getElementById('removeBillingLogoBtn').addEventListener('click', () => {
        document.getElementById('billingLogoInput').value = '';
        document.getElementById('removeBillingLogo').value = '1';
        const preview = document.getElementById('billingLogoPreview');
        preview.style.backgroundImage = '';
        preview.innerHTML = '<i class="fas fa-image"></i>';
    });

    document.getElementById('addTaxBtn').addEventListener('click', () => {
        taxForm.reset();
        taxIdInput.value = '';
        taxForm.elements.is_active.checked = true;
        document.getElementById('taxModalTitle').textContent = 'Ajouter une taxe';
        taxModal?.show();
    });

    document.getElementById('taxTableBody').addEventListener('click', async event => {
        const edit = event.target.closest('[data-edit-tax]');
        const del = event.target.closest('[data-delete-tax]');
        if (edit) {
            const tax = JSON.parse(edit.dataset.editTax);
            taxIdInput.value = tax.id;
            taxForm.elements.name.value = tax.name || '';
            taxForm.elements.code.value = tax.code || '';
            taxForm.elements.type.value = tax.type || 'tva';
            taxForm.elements.rate.value = tax.rate || 0;
            taxForm.elements.description.value = tax.description || '';
            taxForm.elements.is_default.checked = Boolean(tax.is_default);
            taxForm.elements.is_active.checked = Boolean(tax.is_active);
            document.getElementById('taxModalTitle').textContent = 'Modifier une taxe';
            taxModal?.show();
        }
        if (del) {
            if (!confirm('Supprimer cette taxe ?')) return;
            try {
                const response = await fetch(route('taxDestroy', del.dataset.deleteTax), {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf },
                });
                const payload = await response.json();
                if (!response.ok || !payload.success) throw new Error(payload.message || 'Suppression impossible.');
                toast(payload.message);
                setTimeout(() => window.location.reload(), 600);
            } catch (error) {
                toast(error.message, 'error');
            }
        }
    });

    taxForm.addEventListener('submit', async event => {
        event.preventDefault();
        const id = taxIdInput.value;
        const data = new FormData(taxForm);
        if (id) data.set('_method', 'PUT');
        // Ensure checkboxes are always included
        data.set('is_active', taxForm.elements.is_active.checked ? '1' : '0');
        data.set('is_default', taxForm.elements.is_default.checked ? '1' : '0');
        try {
            const response = await fetch(id ? route('taxUpdate', id) : routes.taxStore, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf },
                body: data,
            });
            const payload = await response.json();
            if (!response.ok || !payload.success) throw new Error(payload.message || 'Enregistrement impossible.');
            toast(payload.message);
            taxModal?.hide();
            setTimeout(() => window.location.reload(), 600);
        } catch (error) {
            toast(error.message, 'error');
        }
    });

    document.getElementById('addDiscountBtn').addEventListener('click', () => {
        discountForm.reset();
        discountIdInput.value = '';
        discountForm.elements.is_active.checked = true;
        document.getElementById('discountModalTitle').textContent = 'Ajouter une remise';
        discountModal?.show();
    });

    document.getElementById('discountTableBody').addEventListener('click', async event => {
        const edit = event.target.closest('[data-edit-discount]');
        const del = event.target.closest('[data-delete-discount]');
        if (edit) {
            const discount = JSON.parse(edit.dataset.editDiscount);
            discountIdInput.value = discount.id;
            discountForm.elements.name.value = discount.name || '';
            discountForm.elements.code.value = discount.code || '';
            discountForm.elements.type.value = discount.type || 'percentage';
            discountForm.elements.value.value = discount.value || 0;
            discountForm.elements.description.value = discount.description || '';
            discountForm.elements.is_default.checked = Boolean(discount.is_default);
            discountForm.elements.is_active.checked = Boolean(discount.is_active);
            document.getElementById('discountModalTitle').textContent = 'Modifier une remise';
            discountModal?.show();
        }
        if (del) {
            if (!confirm('Supprimer cette remise ?')) return;
            try {
                const response = await fetch(route('discountDestroy', del.dataset.deleteDiscount), {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf },
                });
                const payload = await response.json();
                if (!response.ok || !payload.success) throw new Error(payload.message || 'Suppression impossible.');
                toast(payload.message);
                setTimeout(() => window.location.reload(), 600);
            } catch (error) {
                toast(error.message, 'error');
            }
        }
    });

    discountForm.addEventListener('submit', async event => {
        event.preventDefault();
        const id = discountIdInput.value;
        const data = new FormData(discountForm);
        if (id) data.set('_method', 'PUT');
        // Ensure checkboxes are always included
        data.set('is_active', discountForm.elements.is_active.checked ? '1' : '0');
        data.set('is_default', discountForm.elements.is_default.checked ? '1' : '0');
        try {
            const response = await fetch(id ? route('discountUpdate', id) : routes.discountStore, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf },
                body: data,
            });
            const payload = await response.json();
            if (!response.ok || !payload.success) throw new Error(payload.message || 'Enregistrement impossible.');
            toast(payload.message);
            discountModal?.hide();
            setTimeout(() => window.location.reload(), 600);
        } catch (error) {
            toast(error.message, 'error');
        }
    });
});
</script>
@endsection
