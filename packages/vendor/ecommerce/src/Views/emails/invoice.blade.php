<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture {{ $invoice->invoice_number }}</title>
</head>
<body style="font-family: Arial, sans-serif; color:#222; line-height:1.5;">
    <h2>Facture {{ $invoice->invoice_number }}</h2>
    <p>Bonjour,</p>
    <p>Veuillez trouver en pièce jointe votre facture d'un montant de <strong>{{ number_format($invoice->total, 2, ',', ' ') }} €</strong>.</p>
    <p>Date d'échéance: <strong>{{ optional($invoice->due_date)->format('d/m/Y') }}</strong></p>
    <p>Merci pour votre confiance.</p>
</body>
</html>
