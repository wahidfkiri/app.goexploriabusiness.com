<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Plateforme Moderne</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #3B82F6;
            --primary-hover: #2563EB;
            --secondary: #10B981;
            --accent: #8B5CF6;
        }
        
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: url('/images/fond.png') center/cover no-repeat;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }
        

        
        .card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            position: relative;
            overflow: hidden;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .card::-webkit-scrollbar {
            width: 4px;
        }
        
        .card::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        .card::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }
        
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(to right, var(--primary), var(--accent));
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-hover) 100%);
            color: white;
            font-weight: 600;
            padding: 14px 28px;
            border-radius: 10px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.3);
        }
        
        .btn-primary:active {
            transform: translateY(0);
        }
        
        .social-btn {
            border: 1.5px solid #E5E7EB;
            border-radius: 10px;
            padding: 10px 16px;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.3s ease;
            background: white;
        }
        
        .social-btn:hover {
            border-color: var(--primary);
            background: #F8FAFC;
            transform: translateY(-1px);
        }
        
        .input-field {
            border: 1.5px solid #E5E7EB;
            border-radius: 10px;
            padding: 14px 20px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: white;
        }
        
        .input-field:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }
        
        .password-toggle {
            cursor: pointer;
            color: #6B7280;
            transition: color 0.3s ease;
        }
        
        .password-toggle:hover {
            color: var(--primary);
        }
        
        .checkbox-custom {
            width: 18px;
            height: 18px;
            border: 2px solid #D1D5DB;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .checkbox-custom.checked {
            background: var(--primary);
            border-color: var(--primary);
        }
        
        .checkbox-custom.checked i {
            color: white;
            font-size: 11px;
        }
        
        .floating-label {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #6B7280;
            transition: all 0.3s ease;
            pointer-events: none;
            background: white;
            padding: 0 5px;
        }
        
        .input-field:focus + .floating-label,
        .input-field:not(:placeholder-shown) + .floating-label {
            top: 0;
            font-size: 11px;
            color: var(--primary);
            transform: translateY(-50%);
        }
        
        .spinner {
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .shake {
            animation: shake 0.5s ease-in-out;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        
        /* Alert styles */
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
        
        .alert-error {
            background-color: #FEE2E2;
            border: 1px solid #EF4444;
            color: #991B1B;
        }
        
        .alert i {
            margin-right: 10px;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>

    
    <div class="w-full max-w-md px-4">
        <div class="card p-6">
            <!-- Logo et en-tête -->
            <div class="text-center mb-6">
                <p class="font-weight-bold text-gray-800" style="font-weight: 700; font-size: 0.8rem; letter-spacing: 0.5px; margin-bottom: 8px;">
                    BIENVENIUE, WILLKOMMEN<br>WELCOME, BIENVENIDO
                </p>
                <div class="w-30 h-30 flex items-center justify-center mx-auto mb-4">
                    <img src="{{asset('logo.png')}}" style="width:200px;">
                </div>
                <h2 class="text-xl font-semibold text-gray-800">Connectez-vous à votre compte</h2>
                <p class="text-gray-600 text-sm mt-1">Accédez à votre espace personnel</p>
            </div>

            <!-- Alert div pour succès/erreurs -->
            <div id="alertContainer" class="hidden"></div>
            @if(session('error'))
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ session('error') }}
                </div>
            @endif

            <!-- Formulaire de Connexion -->
            <form id="loginForm" class="space-y-5">
                @csrf
                
                <!-- Email -->
                <div class="relative">
                    <input type="email" 
                           id="loginEmail"
                           name="email"
                           class="input-field w-full"
                           placeholder=" "
                           required>
                    <label class="floating-label">Adresse email</label>
                    <div class="absolute right-3 top-1/2 transform -translate-y-1/2">
                        <i class="fas fa-envelope text-gray-400 text-sm"></i>
                    </div>
                    <div class="text-red-500 text-xs mt-1 hidden" id="loginEmailError"></div>
                </div>

                <!-- Mot de passe -->
                <div class="relative">
                    <input type="password" 
                           id="loginPassword"
                           name="password"
                           class="input-field w-full pr-12"
                           placeholder=" "
                           required>
                    <label class="floating-label">Mot de passe</label>
                    <button type="button" 
                            onclick="togglePassword('loginPassword', 'loginEyeIcon')" 
                            class="password-toggle absolute right-3 top-1/2 transform -translate-y-1/2">
                        <i class="fas fa-eye text-sm" id="loginEyeIcon"></i>
                    </button>
                    <div class="text-red-500 text-xs mt-1 hidden" id="loginPasswordError"></div>
                </div>

                <!-- Remember me & Forgot password -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2 cursor-pointer" onclick="toggleRemember()">
                        <div class="checkbox-custom" id="rememberCheckbox">
                            <i class="fas fa-check"></i>
                        </div>
                        <span class="text-gray-700 text-xs">Se souvenir de moi</span>
                    </div>
                    <a href="#" class="text-xs text-blue-600 hover:text-blue-800 hover:underline transition-colors">
                        Mot de passe oublié ?
                    </a>
                </div>

                <!-- Submit button -->
                <button type="submit" id="loginBtn" class="btn-primary w-full flex items-center justify-center" style="font-weight: 700; font-size: 1.05rem;">
                    <span id="loginBtnText">Se connecter</span>
                    <div id="loginSpinner" class="hidden ml-2">
                        <div class="spinner"></div>
                    </div>
                </button>
            </form>

            <!-- Lien vers inscription -->
            <div class="text-center mt-5">
                <p class="text-gray-800" style="font-weight: 700; font-size: 1rem;">
                    Pas encore de compte ? 
                    <a href="{{ route('register') }}" class="text-blue-600 hover:text-blue-800" style="font-weight: 700; text-decoration: underline;">
                        S'inscrire
                    </a>
                </p>
            </div>

            <!-- Séparateur -->
            <!-- <div class="my-6">
                <div class="flex items-center">
                    <div class="flex-grow border-t border-gray-300"></div>
                    <span class="mx-4 text-gray-500 text-xs">Ou continuer avec</span>
                    <div class="flex-grow border-t border-gray-300"></div>
                </div>
            </div> -->

            <!-- Boutons sociaux -->
            <div class="grid grid-cols-2 gap-3 mb-5">
                <a href="{{ route('auth.google') }}" 
                   class="social-btn flex items-center justify-center space-x-2">
                    <i class="fab fa-google text-red-500 text-sm"></i>
                    <span class="text-sm">Google</span>
                </a>
            </div>

            <!-- Footer -->
            <div class="text-center pt-4 border-t border-gray-200">
                <p class="text-gray-600 text-xs">
                    En vous connectant, vous acceptez notre
                    <a href="#" class="text-blue-600 hover:underline font-medium">Politique de confidentialité</a>
                </p>
                <p class="text-gray-500 text-xs mt-3">
                    © 2026 Go Exploria Business. Tous droits réservés.
                </p>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Toggle remember me
        function toggleRemember() {
            const checkbox = document.getElementById('rememberCheckbox');
            checkbox.classList.toggle('checked');
        }

        // Show alert function
        function showAlert(message, type = 'success') {
            const alertContainer = document.getElementById('alertContainer');
            
            let icon = 'fa-check-circle';
            let alertClass = 'alert-success';
            
            if (type === 'error') {
                icon = 'fa-exclamation-circle';
                alertClass = 'alert-error';
            }
            
            alertContainer.innerHTML = `
                <div class="alert ${alertClass}">
                    <i class="fas ${icon}"></i>
                    ${message}
                </div>
            `;
            
            alertContainer.classList.remove('hidden');
            
            // Auto-hide success alerts after 5 seconds
            if (type === 'success') {
                setTimeout(() => {
                    alertContainer.classList.add('hidden');
                }, 5000);
            }
        }

        // Login form submission
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Reset errors and hide alert
            document.getElementById('loginEmailError').classList.add('hidden');
            document.getElementById('loginPasswordError').classList.add('hidden');
            document.getElementById('alertContainer').classList.add('hidden');
            
            // Get elements
            const btn = document.getElementById('loginBtn');
            const btnText = document.getElementById('loginBtnText');
            const spinner = document.getElementById('loginSpinner');
            
            // Show loading
            btn.disabled = true;
            btnText.textContent = 'Connexion en cours...';
            spinner.classList.remove('hidden');
            
            // Form data
            const formData = new FormData(this);
            
            try {
                const response = await fetch('{{ route("ajax.login") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Success - show alert
                    showAlert('Connexion réussie ! Redirection en cours...', 'success');
                    
                    // Animation de succès
                    btn.classList.add('bg-green-500');
                    btn.classList.remove('bg-gradient-to-r', 'from-blue-500', 'to-blue-600');
                    
                    // Redirection
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1500);
                    
                } else {
                    // Error handling
                    if (data.errors) {
                        // Validation errors
                        if (data.errors.email) {
                            document.getElementById('loginEmailError').textContent = data.errors.email[0];
                            document.getElementById('loginEmailError').classList.remove('hidden');
                            document.getElementById('loginEmail').classList.add('shake');
                            setTimeout(() => {
                                document.getElementById('loginEmail').classList.remove('shake');
                            }, 500);
                        }
                        if (data.errors.password) {
                            document.getElementById('loginPasswordError').textContent = data.errors.password[0];
                            document.getElementById('loginPasswordError').classList.remove('hidden');
                            document.getElementById('loginPassword').classList.add('shake');
                            setTimeout(() => {
                                document.getElementById('loginPassword').classList.remove('shake');
                            }, 500);
                        }
                    } else if (data.needs_verification) {
                        showAlert(data.message, 'error');
                    } else {
                        showAlert(data.message || 'Erreur de connexion', 'error');
                    }
                }
                
            } catch (error) {
                showAlert('Erreur réseau. Veuillez réessayer.', 'error');
            } finally {
                // Reset button
                btn.disabled = false;
                btnText.textContent = 'Se connecter';
                spinner.classList.add('hidden');
            }
        });
    </script>
</body>
</html>