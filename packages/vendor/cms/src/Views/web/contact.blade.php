<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Contact - {{ $etablissement->name ?? config('app.name') }}</title>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #f6f8fb; color: #172033; }
        .contact-shell { width: min(920px, calc(100% - 32px)); margin: 40px auto; background: #fff; border: 1px solid #dfe5ee; border-radius: 8px; padding: 28px; }
        h1 { margin: 0 0 8px; font-size: 30px; }
        p { color: #64748b; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        label { display: grid; gap: 6px; color: #526070; font-weight: 700; font-size: 13px; }
        input, textarea { border: 1px solid #cbd5e1; border-radius: 7px; padding: 11px; font: inherit; }
        textarea, .full { grid-column: 1 / -1; }
        button { border: 0; border-radius: 7px; background: #2563eb; color: #fff; padding: 12px 18px; font-weight: 800; cursor: pointer; }
        .success { padding: 12px; border-radius: 7px; background: #dcfce7; color: #166534; margin-bottom: 18px; }
        @media (max-width: 700px) { .grid { grid-template-columns: 1fr; } .contact-shell { margin: 16px auto; padding: 18px; } }
    </style>
</head>
<body>
    <main class="contact-shell">
        <h1>Contact</h1>
        <p>Envoyez votre message à {{ $etablissement->name ?? 'notre équipe' }}.</p>

        @if(session('success'))
            <div class="success">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('cms.company.contact.send', ['etablissementId' => $etablissement->id]) }}" class="grid">
            @csrf
            <input type="text" name="website" autocomplete="off" tabindex="-1" style="display:none">
            <label>
                Nom
                <input type="text" name="name" value="{{ old('name') }}">
            </label>
            <label>
                Email
                <input type="email" name="email" value="{{ old('email') }}">
            </label>
            <label>
                Téléphone
                <input type="text" name="phone" value="{{ old('phone') }}">
            </label>
            <label>
                Société
                <input type="text" name="company" value="{{ old('company') }}">
            </label>
            <label class="full">
                Sujet
                <input type="text" name="subject" value="{{ old('subject') }}">
            </label>
            <label class="full">
                Message
                <textarea name="message" rows="7" required>{{ old('message') }}</textarea>
            </label>
            <label class="full">
                <span><input type="checkbox" name="consent" value="1"> J’accepte d’être recontacté au sujet de ma demande.</span>
            </label>
            <div class="full">
                <button type="submit">Envoyer</button>
            </div>
        </form>
    </main>
</body>
</html>
