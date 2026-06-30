<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification email - GoExploria Business</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: white;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }
        .bg-circles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }
        .circle {
            position: absolute;
            border-radius: 50%;
            filter: blur(20px);
            animation: float 20s infinite linear;
        }
        .circle:nth-child(1) { width: 150px; height: 150px; background: linear-gradient(45deg, #3B82F6, #8B5CF6); top: 10%; left: 5%; }
        .circle:nth-child(2) { width: 200px; height: 200px; background: linear-gradient(45deg, #10B981, #3B82F6); top: 60%; right: 10%; animation-delay: -5s; }
        .circle:nth-child(3) { width: 120px; height: 120px; background: linear-gradient(45deg, #8B5CF6, #EC4899); bottom: 10%; left: 20%; animation-delay: -10s; }
        .circle:nth-child(4) { width: 180px; height: 180px; background: linear-gradient(45deg, #F59E0B, #10B981); top: 20%; right: 20%; animation-delay: -7s; }
        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            25% { transform: translateY(-20px) rotate(90deg); }
            50% { transform: translateY(20px) rotate(180deg); }
            75% { transform: translateY(-10px) rotate(270deg); }
        }
        .card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            position: relative;
            overflow: hidden;
        }
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(to right, #3B82F6, #8B5CF6);
        }
        .btn-primary {
            background: linear-gradient(135deg, #3B82F6, #2563EB);
            color: white;
            font-weight: 600;
            padding: 14px 28px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.3);
        }
        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 16px;
            font-size: 14px;
            display: flex;
            align-items: center;
            animation: slideIn 0.3s ease-out;
        }
        .alert-success {
            background-color: #D1FAE5;
            border: 1px solid #10B981;
            color: #065F46;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="bg-circles">
        <div class="circle"></div>
        <div class="circle"></div>
        <div class="circle"></div>
        <div class="circle"></div>
    </div>

    <div class="w-full max-w-lg px-4">
        <div class="card p-8 text-center">
            <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-envelope-open-text text-3xl text-blue-600"></i>
            </div>

            <h2 class="text-2xl font-bold text-gray-800 mb-3">Vérifiez votre adresse email</h2>
            <p class="text-gray-600 mb-6">
                Un email de vérification a été envoyé à <strong>{{ auth()->user()->email ?? 'votre adresse' }}</strong>.
                Cliquez sur le lien dans l'email pour activer votre compte.
            </p>

            @if (session('resent'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle mr-2"></i>
                    Un nouveau lien de vérification a été envoyé à votre adresse email.
                </div>
            @endif

            <div class="bg-gray-50 rounded-lg p-4 mb-6 text-sm text-gray-600">
                <p>Vous n'avez pas reçu l'email ?</p>
                <p class="mt-1">Vérifiez vos spams ou cliquez ci-dessous pour renvoyer le lien.</p>
            </div>

            <form method="POST" action="{{ route('verification.resend') }}">
                @csrf
                <button type="submit" class="btn-primary w-full">
                    <i class="fas fa-redo mr-2"></i> Renvoyer l'email de vérification
                </button>
            </form>

            <div class="mt-6">
                <a href="{{ route('logout') }}" class="text-gray-500 hover:text-gray-700 text-sm"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fas fa-sign-out-alt mr-1"></i> Se déconnecter
                </a>
            </div>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                @csrf
            </form>
        </div>
    </div>
</body>
</html>
