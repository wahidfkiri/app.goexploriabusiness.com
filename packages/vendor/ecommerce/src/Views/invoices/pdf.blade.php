@php
    $currency = $billingSettings->currency ?: 'CAD';
    $currencySymbol = $currency === 'CAD' ? '$' : $currency;
    $money = fn ($amount) => number_format((float) $amount, 2, ',', ' ') . ' ' . $currencySymbol;
    $logoUrl = data_get($invoice->metadata, 'logo_url') ?: ($billingSettings->billing_logo_url ?? null);
    $companyName = data_get($invoice->metadata, 'etablissement_name') ?: config('app.name');
    $companyAddress = collect([
        $billingSettings->company_address ?? null,
        $billingSettings->company_city ?? null,
        $billingSettings->company_country ?? null,
    ])->filter()->map(fn ($line) => e($line))->implode(' - ');
    $clientAddress = collect([
        $invoice->client_address,
        trim(($invoice->client_zipcode ?? '') . ' ' . ($invoice->client_city ?? '')),
        $invoice->client_country,
        $invoice->client_email,
        $invoice->client_phone,
    ])->filter()->map(fn ($line) => e($line))->implode('<br>');
    $taxBreakdown = collect($invoice->taxes_breakdown ?? [])->filter(fn ($tax) => (float) data_get($tax, 'amount', 0) > 0);
    $reference = data_get($invoice->metadata, 'billing_request_number') ?: ($invoice->internal_notes ?? null);
@endphp
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Facture {{ $invoice->invoice_number }}</title>
    <style>
        @page { margin: 24px 26px; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: DejaVu Sans, Arial, sans-serif; color: #000000; font-size: 11.5px; line-height: 1.45; background: #ffffff; }
        .page { position: relative; }
        .top-band { height: 8px; background: #0f766e; border-radius: 8px 8px 0 0; }
        .header { width: 100%; border-collapse: collapse; background: #f8fafc; border: 1px solid #dbe3ee; border-top: 0; }
        .header td { padding: 20px 22px; vertical-align: top; }
        .brand-logo { max-width: 155px; max-height: 80px; margin-bottom: 10px; }
        .brand-name { font-size: 18px; font-weight: 800; color: #000000; margin-bottom: 4px; }
        .brand-info { color: #000000; font-size: 10.5px; }
        .doc-title { text-align: right; }
        .doc-type { display: inline-block; padding: 7px 12px; background: #0f766e; color: #ffffff; border-radius: 999px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .08em; }
        .doc-number { margin-top: 12px; font-size: 27px; font-weight: 800; color: #000000; }
        .doc-status { margin-top: 5px; color: #000000; font-size: 10.5px; text-transform: uppercase; }
        .section-table { width: 100%; border-collapse: separate; border-spacing: 0 12px; margin-top: 14px; }
        .card { border: 1px solid #dbe3ee; border-radius: 8px; padding: 14px; background: #ffffff; vertical-align: top; }
        .card + .card { margin-left: 12px; }
        .card-title { color: #000000; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: .06em; margin-bottom: 8px; }
        .client-name { font-size: 16px; font-weight: 800; color: #000000; margin-bottom: 5px; }
        .muted { color: #000000; }
        .meta-table { width: 100%; border-collapse: collapse; }
        .meta-table td { padding: 6px 0; border-bottom: 1px solid #edf2f7; }
        .meta-table tr:last-child td { border-bottom: 0; }
        .meta-label { color: #000000; font-size: 10px; text-transform: uppercase; font-weight: 800; }
        .meta-value { text-align: right; font-weight: 800; color: #000000; }
        .lines { width: 100%; border-collapse: collapse; margin-top: 4px; border: 1px solid #dbe3ee; }
        .lines thead th { background: #111827; color: #ffffff; padding: 11px 10px; font-size: 10px; text-transform: uppercase; letter-spacing: .04em; text-align: left; }
        .lines thead th.right { text-align: right; }
        .lines tbody td { padding: 12px 10px; border-bottom: 1px solid #edf2f7; vertical-align: top; }
        .lines tbody tr:nth-child(even) td { background: #fbfdff; }
        .line-title { font-weight: 800; color: #000000; }
        .line-desc { color: #000000; font-size: 10.5px; margin-top: 4px; white-space: pre-line; }
        .right { text-align: right; white-space: nowrap; }
        .bottom { width: 100%; border-collapse: collapse; margin-top: 16px; }
        .bottom td { vertical-align: top; }
        .notes { width: 56%; padding-right: 18px; }
        .note-box { border: 1px solid #dbe3ee; border-radius: 8px; padding: 13px; background: #f8fafc; min-height: 88px; white-space: pre-line; }
        .totals { width: 44%; border: 1px solid #dbe3ee; border-radius: 8px; background: #ffffff; }
        .totals table { width: 100%; border-collapse: collapse; }
        .totals td { padding: 9px 13px; border-bottom: 1px solid #edf2f7; }
        .totals tr:last-child td { border-bottom: 0; }
        .totals .label { color: #000000; font-weight: 700; }
        .totals .value { text-align: right; font-weight: 800; color: #000000; }
        .totals .grand td { background: #0f766e; color: #ffffff; font-size: 16px; font-weight: 800; padding: 13px; }
        .tax-box { margin-top: 10px; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 13px; background: #fbfdff; }
        .tax-row { width: 100%; border-collapse: collapse; }
        .tax-row td { padding: 3px 0; color: #000000; font-size: 10.5px; }
        .tax-row .tax-value { text-align: right; color: #000000; font-weight: 700; }
        .footer { position: fixed; left: 26px; right: 26px; bottom: 14px; border-top: 1px solid #dbe3ee; padding-top: 8px; color: #000000; font-size: 9.5px; text-align: center; }
        .footer strong { color: #000000; }
    </style>
</head>
<body>
    <div class="page">
        <div class="top-band"></div>
        <table class="header">
            <tr>
                <td style="width:52%;">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" class="brand-logo" alt="Logo">
                    @endif
                    <div class="brand-name">{{ $companyName }}</div>
                    @if($companyAddress)
                        <div class="brand-info">{!! $companyAddress !!}</div>
                    @endif
                    @if($billingSettings->tax_number_tps || $billingSettings->tax_number_tvq || $billingSettings->neq)
                        <div class="brand-info" style="margin-top:6px;">
                            @if($billingSettings->tax_number_tps) TPS: {{ $billingSettings->tax_number_tps }} @endif
                            @if($billingSettings->tax_number_tvq) &nbsp; TVQ/TVA: {{ $billingSettings->tax_number_tvq }} @endif
                            @if($billingSettings->neq) &nbsp; NEQ: {{ $billingSettings->neq }} @endif
                        </div>
                    @endif
                </td>
                <td style="width:48%;" class="doc-title">
                    <span class="doc-type">Facture</span>
                    <div class="doc-number">{{ $invoice->invoice_number }}</div>
                    <div class="doc-status">{{ $invoice->status_label ?? ucfirst((string) $invoice->status) }}</div>
                </td>
            </tr>
        </table>

        <table class="section-table">
            <tr>
                <td class="card" style="width:52%;">
                    <div class="card-title">Factur&eacute; &agrave;</div>
                    <div class="client-name">{{ $invoice->client_name ?: 'Client' }}</div>
                    <div class="muted">{!! $clientAddress ?: '-' !!}</div>
                </td>
                <td style="width:2%;"></td>
                <td class="card" style="width:46%;">
                    <div class="card-title">Informations</div>
                    <table class="meta-table">
                        <tr><td class="meta-label">Date facture</td><td class="meta-value">{{ optional($invoice->invoice_date)->format('d/m/Y') ?: '-' }}</td></tr>
                        <tr><td class="meta-label">{{ $billingSettings->invoice_due_label ?: 'Echeance' }}</td><td class="meta-value">{{ optional($invoice->due_date)->format('d/m/Y') ?: '-' }}</td></tr>
                        <tr><td class="meta-label">Reste &agrave; payer</td><td class="meta-value">{{ $money($invoice->remaining_amount ?? $invoice->total) }}</td></tr>
                        @if($reference)
                            <tr><td class="meta-label">R&eacute;f&eacute;rence</td><td class="meta-value">{{ $reference }}</td></tr>
                        @endif
                    </table>
                </td>
            </tr>
        </table>

        <table class="lines">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="right" style="width:64px;">Qt&eacute;</th>
                    <th class="right" style="width:92px;">Prix</th>
                    <th class="right" style="width:92px;">Taxe</th>
                    <th class="right" style="width:98px;">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoice->lines as $line)
                    <tr>
                        <td>
                            <div class="line-title">{{ $line->description }}</div>
                            @if($line->detailed_description)
                                <div class="line-desc">{{ $line->detailed_description }}</div>
                            @endif
                        </td>
                        <td class="right">{{ number_format((float) $line->quantity, 2, ',', ' ') }}</td>
                        <td class="right">{{ $money($line->unit_price) }}</td>
                        <td class="right">{{ $money($line->tax_amount) }}</td>
                        <td class="right"><strong>{{ $money($line->total) }}</strong></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="muted" style="text-align:center;">Aucune ligne de facture.</td></tr>
                @endforelse
            </tbody>
        </table>

        <table class="bottom">
            <tr>
                <td class="notes">
                    <div class="card-title">Notes et conditions</div>
                    <div class="note-box">
@if($invoice->notes)
{{ $invoice->notes }}
@endif
@if($invoice->footer)

{{ $invoice->footer }}
@endif
                    </div>
                    @if($taxBreakdown->count())
                        <div class="tax-box">
                            <table class="tax-row">
                                @foreach($taxBreakdown as $tax)
                                    <tr>
                                        <td>{{ data_get($tax, 'name', 'Taxe') }} {{ number_format((float) data_get($tax, 'rate', 0), 2, ',', ' ') }}% sur {{ $money(data_get($tax, 'base', 0)) }}</td>
                                        <td class="tax-value">{{ $money(data_get($tax, 'amount', 0)) }}</td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    @endif
                </td>
                <td class="totals">
                    <table>
                        <tr><td class="label">Sous-total</td><td class="value">{{ $money($invoice->subtotal) }}</td></tr>
                        @if((float) $invoice->discount_amount > 0)
                            <tr><td class="label">Remise</td><td class="value">-{{ $money($invoice->discount_amount) }}</td></tr>
                        @endif
                        <tr><td class="label">Taxes</td><td class="value">{{ $money($invoice->tax_total) }}</td></tr>
                        @if(((float) $invoice->shipping_fees + (float) $invoice->administration_fees) > 0)
                            <tr><td class="label">Frais</td><td class="value">{{ $money((float) $invoice->shipping_fees + (float) $invoice->administration_fees) }}</td></tr>
                        @endif
                        @if(isset($invoice->paid_amount) && (float) $invoice->paid_amount > 0)
                            <tr><td class="label">D&eacute;j&agrave; pay&eacute;</td><td class="value">-{{ $money($invoice->paid_amount) }}</td></tr>
                        @endif
                        <tr class="grand"><td>Total</td><td style="text-align:right;">{{ $money($invoice->total) }}</td></tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <strong>{{ $companyName }}</strong>
        @if($billingSettings->invoice_footer_note)
            - {{ $billingSettings->invoice_footer_note }}
        @endif
    </div>
</body>
</html>
