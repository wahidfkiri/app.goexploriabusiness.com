@extends('layouts.app')

@php
    $logoUrl = data_get($invoice->metadata, 'logo_url') ?: ($billingSettings->billing_logo_url ?? null);
@endphp

@section('content')
<main class="dashboard-content document-show">
    <div class="doc-head">
        <div>
            <p class="doc-kicker">Facture</p>
            <h1>{{ $invoice->invoice_number }}</h1>
            <span class="status-pill status-{{ $invoice->status }}">{{ $invoice->status_label }}</span>
        </div>
        <div class="doc-actions">
            <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Retour</a>
            @if(!in_array($invoice->status, ['payee', 'annulee'], true))
                <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-primary"><i class="fas fa-edit me-2"></i>Modifier</a>
            @endif
            <a href="{{ route('invoices.pdf', $invoice) }}" class="btn btn-outline-primary"><i class="fas fa-file-pdf me-2"></i>PDF</a>
            @if($invoice->status !== 'payee')
                <button class="btn btn-success" id="markPaidBtn"><i class="fas fa-check me-2"></i>Marquer payée</button>
            @endif
            <button class="btn btn-outline-primary" id="sendInvoiceBtn"><i class="fas fa-paper-plane me-2"></i>Envoyer</button>
        </div>
    </div>

    <section class="doc-card">
        <div class="doc-top">
            <div>
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="Logo" class="doc-logo">
                @endif
                <h2>{{ $invoice->etablissement->nom ?? config('app.name') }}</h2>
                <p>{{ $invoice->etablissement->adresse ?? '' }}</p>
            </div>
            <div class="doc-meta">
                <div><span>Date</span><strong>{{ optional($invoice->invoice_date)->format('d/m/Y') }}</strong></div>
                <div><span>{{ $billingSettings->invoice_due_label ?? 'Échéance' }}</span><strong>{{ optional($invoice->due_date)->format('d/m/Y') }}</strong></div>
                <div><span>Reste à payer</span><strong>{{ number_format((float) $invoice->remaining_amount, 2, ',', ' ') }} $</strong></div>
            </div>
        </div>

        <div class="client-box">
            <span>Facturé à</span>
            <strong>{{ $invoice->client_name }}</strong>
            <p>{{ $invoice->client_address }} {{ $invoice->client_zipcode }} {{ $invoice->client_city }} {{ $invoice->client_country }}</p>
        </div>

        <div class="table-wrap">
            <table class="doc-lines">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th class="text-end">Qté</th>
                        <th class="text-end">Prix</th>
                        <th class="text-end">Taxe</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->lines as $line)
                        <tr>
                            <td>
                                <strong>{{ $line->description }}</strong>
                                @if($line->detailed_description)
                                    <div class="muted">{{ $line->detailed_description }}</div>
                                @endif
                            </td>
                            <td class="text-end">{{ $line->quantity }}</td>
                            <td class="text-end">{{ number_format((float) $line->unit_price, 2, ',', ' ') }} $</td>
                            <td class="text-end">{{ number_format((float) $line->tax_amount, 2, ',', ' ') }} $</td>
                            <td class="text-end"><strong>{{ number_format((float) $line->total, 2, ',', ' ') }} $</strong></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="doc-bottom">
            <div class="notes">
                @if($invoice->notes)
                    <h3>Note</h3>
                    <p>{{ $invoice->notes }}</p>
                @endif
                @if($invoice->footer)
                    <h3>Conditions</h3>
                    <p>{{ $invoice->footer }}</p>
                @endif
            </div>
            <div class="totals">
                <div><span>Sous-total</span><strong>{{ number_format((float) $invoice->subtotal, 2, ',', ' ') }} $</strong></div>
                <div><span>Remise</span><strong>-{{ number_format((float) $invoice->discount_amount, 2, ',', ' ') }} $</strong></div>
                <div><span>Taxes</span><strong>{{ number_format((float) $invoice->tax_total, 2, ',', ' ') }} $</strong></div>
                <div><span>Frais</span><strong>{{ number_format((float) $invoice->shipping_fees + (float) $invoice->administration_fees, 2, ',', ' ') }} $</strong></div>
                <div class="grand"><span>Total</span><strong>{{ number_format((float) $invoice->total, 2, ',', ' ') }} $</strong></div>
            </div>
        </div>
    </section>

    @if($relatedInvoices->count())
        <section class="doc-card compact">
            <h2>Autres factures du client</h2>
            <div class="related-list">
                @foreach($relatedInvoices as $related)
                    <a href="{{ route('invoices.show', $related) }}">
                        <strong>{{ $related->invoice_number }}</strong>
                        <span>{{ number_format((float) $related->total, 2, ',', ' ') }} $</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <div id="docToastHost" class="doc-toast-host"></div>
</main>

<style>
    .document-show { padding: 28px; background: #f3f6fa; min-height: calc(100vh - 72px); color: #172033; }
    .doc-head { display: flex; justify-content: space-between; gap: 18px; align-items: center; margin-bottom: 22px; padding: 20px; border: 1px solid #dbe3ee; border-radius: 8px; background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%); box-shadow: 0 14px 32px rgba(15, 23, 42, .06); }
    .doc-head h1 { margin: 0 0 8px; font-size: 30px; font-weight: 850; color: #0f172a; }
    .doc-kicker { margin: 0 0 5px; color: #2563eb; font-size: 12px; font-weight: 850; text-transform: uppercase; }
    .doc-actions { display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-start; justify-content: flex-end; }
    .doc-actions .btn { min-height: 40px; border-radius: 7px; font-weight: 750; }
    .doc-card { background: #fff; border: 1px solid #dbe3ee; border-radius: 8px; padding: 26px; box-shadow: 0 12px 28px rgba(15, 23, 42, .05); margin-bottom: 16px; }
    .doc-card.compact { padding: 18px; }
    .doc-card.compact h2 { font-size: 18px; margin: 0 0 12px; font-weight: 850; }
    .doc-top { display: flex; justify-content: space-between; gap: 22px; padding-bottom: 22px; border-bottom: 1px solid #e2e8f0; }
    .doc-top h2 { margin-bottom: 6px; font-weight: 850; color: #0f172a; }
    .doc-logo { max-width: 160px; max-height: 92px; object-fit: contain; margin-bottom: 14px; }
    .doc-meta { display: grid; gap: 10px; min-width: 280px; padding: 14px; border-radius: 8px; border: 1px solid #e2e8f0; background: #fbfdff; }
    .doc-meta div, .totals div { display: flex; justify-content: space-between; gap: 18px; }
    .doc-meta span, .totals span, .client-box span, .muted { color: #64748b; }
    .doc-meta strong { color: #0f172a; }
    .client-box { margin: 22px 0; padding: 16px; border-radius: 8px; border: 1px solid #e2e8f0; background: #f8fafc; }
    .client-box strong { display: block; margin-top: 4px; font-size: 19px; color: #0f172a; }
    .table-wrap { overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 8px; }
    .doc-lines { width: 100%; border-collapse: separate; border-spacing: 0; }
    .doc-lines th { color: #64748b; font-size: 11px; text-transform: uppercase; padding: 13px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; letter-spacing: .02em; }
    .doc-lines td { padding: 14px; border-bottom: 1px solid #eef2f7; vertical-align: top; color: #334155; }
    .doc-lines tbody tr:last-child td { border-bottom: 0; }
    .doc-bottom { display: grid; grid-template-columns: minmax(0, 1fr) 340px; gap: 24px; margin-top: 22px; }
    .notes h3 { font-size: 14px; margin: 0 0 7px; font-weight: 850; color: #0f172a; text-transform: uppercase; }
    .notes p { white-space: pre-line; color: #475569; }
    .totals { display: grid; gap: 10px; padding: 16px; border-radius: 8px; background: #fbfdff; border: 1px solid #e2e8f0; align-self: start; }
    .totals .grand { border-top: 1px solid #dbe4f0; padding-top: 13px; font-size: 20px; font-weight: 850; color: #0f766e; }
    .status-pill { display: inline-flex; padding: 6px 10px; border-radius: 999px; font-weight: 850; font-size: 12px; background: #e2e8f0; color: #334155; }
    .status-payee { background: #dcfce7; color: #166534; }
    .status-envoyee, .status-en_attente { background: #dbeafe; color: #1d4ed8; }
    .status-annulee { background: #e5e7eb; color: #374151; }
    .related-list { display: grid; gap: 8px; }
    .related-list a { display: flex; justify-content: space-between; color: #172033; text-decoration: none; padding: 11px; border-radius: 7px; background: #f8fafc; border: 1px solid #e2e8f0; }
    .doc-toast-host { position: fixed; right: 20px; bottom: 20px; z-index: 1080; display: grid; gap: 10px; }
    .doc-toast { padding: 12px 14px; border-radius: 8px; color: #fff; background: #16a34a; box-shadow: 0 15px 30px rgba(15, 23, 42, .18); min-width: 240px; font-weight: 700; }
    .doc-toast.error { background: #dc2626; }
    @media (max-width: 900px) { .document-show { padding: 14px; } .doc-head, .doc-top, .doc-bottom { display: grid; grid-template-columns: 1fr; } .doc-meta { min-width: 0; } }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const csrf = @json(csrf_token());
    const toast = (message, type = 'success') => {
        const host = document.getElementById('docToastHost');
        const item = document.createElement('div');
        item.className = `doc-toast ${type}`;
        item.textContent = message;
        host.appendChild(item);
        setTimeout(() => item.remove(), 4200);
    };
    const post = async (url) => {
        const response = await fetch(url, { method: 'POST', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf } });
        const payload = await response.json();
        if (!response.ok || !payload.success) throw new Error(payload.message || 'Action impossible.');
        toast(payload.message || 'Action effectuée.');
        setTimeout(() => window.location.reload(), 600);
    };
    document.getElementById('markPaidBtn')?.addEventListener('click', () => post(@json(route('invoices.mark-paid', $invoice))).catch(error => toast(error.message, 'error')));
    document.getElementById('sendInvoiceBtn')?.addEventListener('click', () => post(@json(route('invoices.send', $invoice))).catch(error => toast(error.message, 'error')));
});
</script>
@endsection
