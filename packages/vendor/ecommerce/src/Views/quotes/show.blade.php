@extends('layouts.app')

@php($logoUrl = data_get($quote->metadata, 'logo_url') ?: ($billingSettings->billing_logo_url ?? null))

@section('content')
<main class="dashboard-content document-show">
    <div class="doc-head">
        <div>
            <p class="doc-kicker">Devis</p>
            <h1>{{ $quote->quote_number }}</h1>
            <span class="status-pill status-{{ $quote->status }}">{{ $quote->status_label }}</span>
        </div>
        <div class="doc-actions">
            <a href="{{ route('quotes.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Retour</a>
            @if(!in_array($quote->status, ['converti_en_facture', 'annule'], true))
                <a href="{{ route('quotes.edit', $quote) }}" class="btn btn-primary"><i class="fas fa-edit me-2"></i>Modifier</a>
            @endif
            <a href="{{ route('quotes.pdf', $quote) }}" class="btn btn-outline-primary"><i class="fas fa-file-pdf me-2"></i>PDF</a>
            @if($quote->invoice)
                <a href="{{ route('invoices.show', $quote->invoice) }}" class="btn btn-success"><i class="fas fa-file-invoice-dollar me-2"></i>Voir la facture</a>
            @else
                <button class="btn btn-success" id="convertQuoteBtn"><i class="fas fa-file-invoice-dollar me-2"></i>Transformer en facture</button>
            @endif
        </div>
    </div>

    <section class="doc-card">
        <div class="doc-top">
            <div>
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="Logo" class="doc-logo">
                @endif
                <h2>{{ $quote->etablissement->nom ?? config('app.name') }}</h2>
                <p>{{ $quote->etablissement->adresse ?? '' }}</p>
            </div>
            <div class="doc-meta">
                <div><span>Date</span><strong>{{ optional($quote->quote_date)->format('d/m/Y') }}</strong></div>
                <div><span>{{ $billingSettings->quote_validity_label ?? 'Validité' }}</span><strong>{{ optional($quote->valid_until)->format('d/m/Y') }}</strong></div>
                <div><span>Total</span><strong>{{ number_format((float) $quote->total, 2, ',', ' ') }} $</strong></div>
            </div>
        </div>

        <div class="client-box">
            <span>Client</span>
            <strong>{{ $quote->client?->name ?: $quote->client?->lname }}</strong>
            <p>{{ $quote->client?->adresse }} {{ $quote->client?->zip_code }} {{ $quote->client?->villeRelation?->name ?? $quote->client?->ville }} {{ $quote->client?->country?->name }}</p>
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
                    @foreach($quote->lines as $line)
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
                @if($quote->notes)
                    <h3>Note</h3>
                    <p>{{ $quote->notes }}</p>
                @endif
                @if($quote->conditions)
                    <h3>Conditions</h3>
                    <p>{{ $quote->conditions }}</p>
                @endif
            </div>
            <div class="totals">
                <div><span>Sous-total</span><strong>{{ number_format((float) $quote->subtotal, 2, ',', ' ') }} $</strong></div>
                <div><span>Remise</span><strong>-{{ number_format((float) $quote->discount_amount, 2, ',', ' ') }} $</strong></div>
                <div><span>Taxes</span><strong>{{ number_format((float) $quote->tax_total, 2, ',', ' ') }} $</strong></div>
                <div><span>Frais</span><strong>{{ number_format((float) $quote->shipping_fees + (float) $quote->administration_fees, 2, ',', ' ') }} $</strong></div>
                <div class="grand"><span>Total</span><strong>{{ number_format((float) $quote->total, 2, ',', ' ') }} $</strong></div>
            </div>
        </div>
    </section>
    <div id="docToastHost" class="doc-toast-host"></div>
</main>

<style>
    .document-show { padding: 28px; background: #f3f6fa; min-height: calc(100vh - 72px); color: #172033; }
    .doc-head { display: flex; justify-content: space-between; gap: 18px; align-items: center; margin-bottom: 22px; padding: 20px; border: 1px solid #dbe3ee; border-radius: 8px; background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%); box-shadow: 0 14px 32px rgba(15, 23, 42, .06); }
    .doc-head h1 { margin: 0 0 8px; font-size: 30px; font-weight: 850; color: #0f172a; }
    .doc-kicker { margin: 0 0 5px; color: #2563eb; font-size: 12px; font-weight: 850; text-transform: uppercase; }
    .doc-actions { display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-start; justify-content: flex-end; }
    .doc-actions .btn { min-height: 40px; border-radius: 7px; font-weight: 750; }
    .doc-card { background: #fff; border: 1px solid #dbe3ee; border-radius: 8px; padding: 26px; box-shadow: 0 12px 28px rgba(15, 23, 42, .05); }
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
    .status-accepte, .status-converti_en_facture { background: #dcfce7; color: #166534; }
    .status-envoye, .status-en_attente { background: #dbeafe; color: #1d4ed8; }
    .status-refuse { background: #fee2e2; color: #991b1b; }
    .doc-toast-host { position: fixed; right: 20px; bottom: 20px; z-index: 1080; display: grid; gap: 10px; }
    .doc-toast { padding: 12px 14px; border-radius: 8px; color: #fff; background: #16a34a; box-shadow: 0 15px 30px rgba(15, 23, 42, .18); min-width: 240px; font-weight: 700; }
    .doc-toast.error { background: #dc2626; }
    @media (max-width: 900px) { .document-show { padding: 14px; } .doc-head, .doc-top, .doc-bottom { display: grid; grid-template-columns: 1fr; } .doc-meta { min-width: 0; } }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const button = document.getElementById('convertQuoteBtn');
    const csrf = @json(csrf_token());
    const toast = (message, type = 'success') => {
        const host = document.getElementById('docToastHost');
        const item = document.createElement('div');
        item.className = `doc-toast ${type}`;
        item.textContent = message;
        host.appendChild(item);
        setTimeout(() => item.remove(), 4200);
    };
    button?.addEventListener('click', async () => {
        button.disabled = true;
        try {
            const response = await fetch(@json(route('quotes.convert-to-invoice', $quote)), { method: 'POST', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf } });
            const payload = await response.json();
            if (!response.ok || !payload.success) throw new Error(payload.message || 'Conversion impossible.');
            toast(payload.message || 'Devis transformé.');
            window.location.href = payload.redirect_url;
        } catch (error) {
            toast(error.message, 'error');
            button.disabled = false;
        }
    });
});
</script>
@endsection
