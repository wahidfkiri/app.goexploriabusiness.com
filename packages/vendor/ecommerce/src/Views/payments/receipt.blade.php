<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Reçu {{ $payment->payment_reference }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #222; }
        .header { margin-bottom: 20px; }
        .title { font-size: 20px; font-weight: bold; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 8px; border: 1px solid #ddd; }
        .label { width: 35%; background: #f7f7f7; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Reçu de paiement</div>
        <div>Référence: {{ $payment->payment_reference }}</div>
    </div>

    <table>
        <tr>
            <td class="label">Montant</td>
            <td>{{ number_format($payment->amount, 2, ',', ' ') }} €</td>
        </tr>
        <tr>
            <td class="label">Date de paiement</td>
            <td>{{ optional($payment->payment_date)->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td class="label">Méthode</td>
            <td>{{ $payment->method ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Transaction</td>
            <td>{{ $payment->transaction_id ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Client</td>
            <td>{{ optional($payment->client)->nom_complet ?? optional($payment->client)->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Facture liée</td>
            <td>{{ optional($payment->invoice)->invoice_number ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Statut</td>
            <td>{{ $payment->status ?? '-' }}</td>
        </tr>
    </table>
</body>
</html>
