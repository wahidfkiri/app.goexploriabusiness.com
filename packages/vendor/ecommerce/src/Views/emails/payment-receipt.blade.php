<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Reçu {{ $payment->payment_reference }}</title>
</head>
<body style="font-family: Arial, sans-serif; color:#222; line-height:1.5;">
    <h2>Reçu de paiement {{ $payment->payment_reference }}</h2>
    <p>Bonjour,</p>
    <p>Votre paiement de <strong>{{ number_format($payment->amount, 2, ',', ' ') }} €</strong> a bien été enregistré.</p>
    <p>Date: <strong>{{ optional($payment->payment_date)->format('d/m/Y H:i') }}</strong></p>
    <p>Le reçu PDF est joint à cet email.</p>
</body>
</html>
