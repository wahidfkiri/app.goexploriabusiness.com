<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Plateforme Moderne</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #ef7724;
            --primary-hover: #d6651e;
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
            box-shadow: 0 8px 20px rgba(239, 119, 36, 0.3);
        }
        
        .btn-primary:active {
            transform: translateY(0);
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

        .spinner-sm {
            width: 14px;
            height: 14px;
            border: 2px solid #E5E7EB;
            border-radius: 50%;
            border-top-color: #ef7724;
            animation: spin 1s linear infinite;
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
        
        /* Checkbox styles */
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
        
        /* Step indicator */
        .step-indicator {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 24px;
        }
        
        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
        }
        
        .step-number {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: #E5E7EB;
            color: #6B7280;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 18px;
            transition: all 0.3s ease;
            z-index: 2;
        }
        
        .step.active .step-number {
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 12px rgba(239, 119, 36, 0.3);
        }
        
        .step-label {
            margin-top: 10px;
            font-size: 13px;
            color: #6B7280;
            text-align: center;
            font-weight: 600;
        }
        
        .step.active .step-label {
            color: var(--primary);
            font-weight: 700;
        }
        
        .step-line {
            width: 100px;
            height: 3px;
            background: #E5E7EB;
            margin: 0 8px;
            position: relative;
            top: 20px;
        }
        
        .step-line.active {
            background: var(--primary);
        }
        
        /* Hidden form steps */
        .form-step {
            display: none;
            animation: fadeIn 0.5s ease-out;
        }
        
        .form-step.active {
            display: block;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .max-w-md {
         max-width: 50rem !important;
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
                <h2 class="text-xl font-semibold text-gray-800">Activer votre compte</h2>
                <p class="text-gray-600 text-sm mt-1">Rejoignez notre plateforme en quelques étapes</p>
            </div>

            <!-- Indicateur d'étapes -->
            <div class="step-indicator">
                <div class="step active" id="step1">
                    <div class="step-number">1</div>
                    <div class="step-label">Compte</div>
                </div>
                <div class="step-line" id="line1"></div>
                <div class="step" id="step2">
                    <div class="step-number">2</div>
                    <div class="step-label">Type du profil</div>
                </div>
                <div class="step-line" id="line2"></div>
                <div class="step" id="step3">
                    <div class="step-number">3</div>
                    <div class="step-label">Finalisation</div>
                </div>
            </div>

            <!-- Alert div pour succès/erreurs -->
            <div id="alertContainer" class="hidden"></div>

            <!-- Étape 1 : Informations du compte -->
            <form id="registerForm" class="space-y-5">
                @csrf
                
                <!-- Étape 1 -->
                <div id="step1Form" class="form-step active space-y-4">
                    <h3 class="text-lg font-semibold text-gray-800">Informations du compte</h3>
                    
                    <!-- Nom -->
                    <div class="relative">
                        <input type="text" 
                               id="name"
                               name="name"
                               class="input-field w-full"
                               placeholder=" "
                               required>
                        <label class="floating-label">Nom complet</label>
                        <div class="absolute right-3 top-1/2 transform -translate-y-1/2">
                            <i class="fas fa-user text-gray-400 text-sm"></i>
                        </div>
                        <div class="text-red-500 text-xs mt-1 hidden" id="nameError"></div>
                    </div>

                    <!-- Email -->
                    <div class="relative">
                        <input type="email" 
                               id="email"
                               name="email"
                               class="input-field w-full"
                               placeholder=" "
                               autocomplete="email"
                               required>
                        <label class="floating-label">Adresse email</label>
                        <div class="absolute right-3 top-1/2 transform -translate-y-1/2">
                            <i class="fas fa-envelope text-gray-400 text-sm"></i>
                        </div>
                        <div class="text-red-500 text-xs mt-1 hidden" id="emailError"></div>
                        <div id="emailCheckIndicator" class="hidden absolute right-10 top-1/2 transform -translate-y-1/2">
                            <div class="spinner-sm"></div>
                        </div>
                    </div>

                    <!-- Mot de passe -->
                    <div class="relative">
                        <input type="password" 
                               id="password"
                               name="password"
                               class="input-field w-full pr-12"
                               placeholder=" "
                               required>
                        <label class="floating-label">Mot de passe</label>
                        <button type="button" 
                                onclick="togglePassword('password', 'passwordEyeIcon')" 
                                class="password-toggle absolute right-3 top-1/2 transform -translate-y-1/2">
                            <i class="fas fa-eye text-sm" id="passwordEyeIcon"></i>
                        </button>
                        <div class="text-red-500 text-xs mt-1 hidden" id="passwordError"></div>
                        <p class="text-gray-500 text-xs mt-2">Minimum 8 caractères avec majuscule, minuscule et chiffre</p>
                    </div>

                    <!-- Confirmation mot de passe -->
                    <div class="relative">
                        <input type="password" 
                               id="password_confirmation"
                               name="password_confirmation"
                               class="input-field w-full pr-12"
                               placeholder=" "
                               required>
                        <label class="floating-label">Confirmer le mot de passe</label>
                        <button type="button" 
                                onclick="togglePassword('password_confirmation', 'passwordConfirmEyeIcon')" 
                                class="password-toggle absolute right-3 top-1/2 transform -translate-y-1/2">
                            <i class="fas fa-eye text-sm" id="passwordConfirmEyeIcon"></i>
                        </button>
                        <div class="text-red-500 text-xs mt-1 hidden" id="passwordConfirmationError"></div>
                    </div>

                    <!-- Bouton Suivant -->
                    <button type="button" onclick="nextStep()" class="btn-primary w-full mt-2" style="font-weight: 700; font-size: 1.05rem;">
                        Suivant
                    </button>
                </div>

                <!-- Étape 2 : Informations de l'établissement -->
                <div id="step2Form" class="form-step space-y-4">
                    <h3 class="text-lg font-semibold text-gray-800">Informations professionnelles</h3>
                    
                    <!-- Type de compte -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">Type de compte</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="border-2 rounded-lg p-3 cursor-pointer text-center transition-all hover:border-[#f9a05a] has-[:checked]:border-[#ef7724] has-[:checked]:bg-[#fff5eb]" id="roleEntreprise">
                                <input type="radio" name="role" value="entreprise" class="hidden" checked onchange="updateRoleSelection(this)">
                                <i class="fas fa-building text-lg text-[#ef7724] mb-1"></i>
                                <div class="text-xs font-semibold text-gray-700">ENTREPRISES</div>
                                <div class="text-xs text-gray-500">Sociétés, agences, professionnels</div>
                            </label>
                            <label class="border-2 rounded-lg p-3 cursor-pointer text-center transition-all hover:border-[#f9a05a] has-[:checked]:border-[#ef7724] has-[:checked]:bg-[#fff5eb]" id="roleSpecialVoyage">
                                <input type="radio" name="role" value="special-voyage" class="hidden" onchange="updateRoleSelection(this)">
                                <i class="fas fa-suitcase-rolling text-lg text-emerald-600 mb-1"></i>
                                <div class="text-xs font-semibold text-gray-700">SPÉCIAUX VOYAGES</div>
                                <div class="text-xs text-gray-500">Tours opérateurs, guides, transport</div>
                            </label>
                            <label class="border-2 rounded-lg p-3 cursor-pointer text-center transition-all hover:border-[#f9a05a] has-[:checked]:border-[#ef7724] has-[:checked]:bg-[#fff5eb]" id="rolePartenaire">
                                <input type="radio" name="role" value="partenaire-affilie" class="hidden" onchange="updateRoleSelection(this)">
                                <i class="fas fa-handshake text-lg text-purple-600 mb-1"></i>
                                <div class="text-xs font-semibold text-gray-700">PARTENAIRES AFFILIÉS</div>
                                <div class="text-xs text-gray-500">Influenceurs, blogueurs, revendeurs</div>
                            </label>
                            <label class="border-2 rounded-lg p-3 cursor-pointer text-center transition-all hover:border-[#f9a05a] has-[:checked]:border-[#ef7724] has-[:checked]:bg-[#fff5eb]" id="roleWebVoyageur">
                                <input type="radio" name="role" value="web-voyageur" class="hidden" onchange="updateRoleSelection(this)">
                                <i class="fas fa-globe text-lg text-amber-600 mb-1"></i>
                                <div class="text-xs font-semibold text-gray-700">WEB VOYAGEURS</div>
                                <div class="text-xs text-gray-500">Voyageurs, passionnés, explorateurs</div>
                            </label>
                        </div>
                    </div>

                    <!-- Nom de l'établissement -->
                    <div class="relative">
                        <input type="text" 
                               id="etablissement_name"
                               name="etablissement_name"
                               class="input-field w-full"
                               placeholder=" ">
                        <label class="floating-label">Nom de l'établissement</label>
                        <div class="absolute right-3 top-1/2 transform -translate-y-1/2">
                            <i class="fas fa-building text-gray-400 text-sm"></i>
                        </div>
                    </div>

                    <!-- Prénom du responsable -->
                    <div class="relative">
                        <input type="text" 
                               id="lname"
                               name="lname"
                               class="input-field w-full"
                               placeholder=" ">
                        <label class="floating-label">Prénom du responsable</label>
                        <div class="absolute right-3 top-1/2 transform -translate-y-1/2">
                            <i class="fas fa-user-tie text-gray-400 text-sm"></i>
                        </div>
                    </div>

                    <!-- Téléphone -->
                    <div class="relative">
                        <input type="tel" 
                               id="phone"
                               name="phone"
                               class="input-field w-full"
                               placeholder=" ">
                        <label class="floating-label">Téléphone</label>
                        <div class="absolute right-3 top-1/2 transform -translate-y-1/2">
                            <i class="fas fa-phone text-gray-400 text-sm"></i>
                        </div>
                    </div>

                    <!-- Adresse -->
                    <div class="relative">
                        <input type="text" 
                               id="adresse"
                               name="adresse"
                               class="input-field w-full"
                               placeholder=" ">
                        <label class="floating-label">Adresse</label>
                        <div class="absolute right-3 top-1/2 transform -translate-y-1/2">
                            <i class="fas fa-map-marker-alt text-gray-400 text-sm"></i>
                        </div>
                    </div>

                    <!-- Ville -->
                    <div class="relative">
                        <input type="text" 
                               id="ville"
                               name="ville"
                               class="input-field w-full"
                               placeholder=" ">
                        <label class="floating-label">Ville</label>
                        <div class="absolute right-3 top-1/2 transform -translate-y-1/2">
                            <i class="fas fa-city text-gray-400 text-sm"></i>
                        </div>
                    </div>

                    <!-- Pays avec drapeau -->
                    <div class="relative">
                        <div class="flex">
                            <div class="relative flex-1">
                                <select id="pays" name="pays" class="input-field w-full appearance-none" style="padding-left: 50px;">
                                    <option value="">Sélectionnez un pays</option>
                                </select>
                                <label class="floating-label" for="pays" style="left: 50px;">Pays</label>
                                <div class="absolute left-3 top-1/2 transform -translate-y-1/2 flex items-center" id="flagContainer">
                                    <span id="flagPlaceholder" class="text-gray-400 text-sm"><i class="fas fa-globe"></i></span>
                                    <img id="flagImage" src="" alt="" class="hidden" style="width: 24px; height: 16px; border-radius: 2px; object-fit: cover;">
                                </div>
                                <div class="absolute right-3 top-1/2 transform -translate-y-1/2 pointer-events-none">
                                    <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Code postal -->
                    <div class="relative">
                        <input type="text" 
                               id="zip_code"
                               name="zip_code"
                               class="input-field w-full"
                               placeholder=" ">
                        <label class="floating-label">Code postal</label>
                        <div class="absolute right-3 top-1/2 transform -translate-y-1/2">
                            <i class="fas fa-mail-bulk text-gray-400 text-sm"></i>
                        </div>
                    </div>

                    <!-- Boutons navigation -->
                    <div class="flex space-x-3">
                        <button type="button" onclick="prevStep()" class="btn-primary w-1/2 bg-gray-500 hover:bg-gray-600">
                            <i class="fas fa-arrow-left mr-2"></i> Précédent
                        </button>
                        <button type="button" onclick="nextStep()" class="btn-primary w-1/2" style="font-weight: 700; font-size: 1.05rem;">
                            Suivant
                        </button>
                    </div>
                </div>

                <!-- Étape 3 : Conditions et finalisation -->
                <div id="step3Form" class="form-step space-y-4">
                    <h3 class="text-lg font-semibold text-gray-800">Finalisation</h3>
                    
                    <!-- Conditions d'utilisation -->
                    <div class="border border-gray-200 rounded-lg p-4 mb-4">
                        <h4 class="font-semibold text-gray-700 mb-2">Conditions d'utilisation</h4>
                        <div class="text-gray-600 text-sm max-h-40 overflow-y-auto pr-2">
                            <p class="mb-2">En créant un compte, vous acceptez nos conditions d'utilisation :</p>
                            <ul class="list-disc pl-5 space-y-1">
                                <li>Respect des données personnelles et confidentialité</li>
                                <li>Utilisation professionnelle de la plateforme</li>
                                <li>Exactitude des informations fournies</li>
                                <li>Respect des droits d'auteur et propriété intellectuelle</li>
                                <li>Non-utilisation à des fins illégales ou frauduleuses</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Acceptation des conditions -->
                    <div class="flex items-center space-x-2 cursor-pointer mb-4" onclick="toggleTerms()">
                        <div class="checkbox-custom" id="termsCheckbox">
                            <i class="fas fa-check"></i>
                        </div>
                        <span class="text-gray-700 text-sm">
                            J'accepte les <a href="#" class="text-[#ef7724] hover:underline">conditions d'utilisation</a> et la <a href="#" class="text-[#ef7724] hover:underline">politique de confidentialité</a>
                        </span>
                    </div>
                    <div class="text-red-500 text-xs mt-1 hidden" id="termsError"></div>

                    <!-- Newsletter -->
                    <div class="flex items-center space-x-2 cursor-pointer mb-6" onclick="toggleNewsletter()">
                        <div class="checkbox-custom" id="newsletterCheckbox">
                            <i class="fas fa-check"></i>
                        </div>
                        <span class="text-gray-700 text-sm">
                            Je souhaite recevoir des offres spéciales et des mises à jour (optionnel)
                        </span>
                    </div>

                    <!-- Boutons navigation et soumission -->
                    <div class="flex space-x-3">
                        <button type="button" onclick="prevStep()" class="btn-primary w-1/2 bg-gray-500 hover:bg-gray-600">
                            <i class="fas fa-arrow-left mr-2"></i> Précédent
                        </button>
                        <button type="submit" id="registerBtn" class="btn-primary w-1/2 flex items-center justify-center" style="font-weight: 700; font-size: 1.05rem;">
                            <span id="registerBtnText">S'inscrire</span>
                            <div id="registerSpinner" class="hidden ml-2">
                                <div class="spinner"></div>
                            </div>
                        </button>
                    </div>
                </div>
            </form>

            <!-- Lien vers connexion -->
            <div class="text-center mt-5">
                <p class="text-gray-800" style="font-weight: 700; font-size: 1rem;">
                    Déjà un compte ? 
                    <a href="{{ route('login') }}" class="text-[#ef7724] hover:text-[#d6651e]" style="font-weight: 700; text-decoration: underline;">
                        Se connecter
                    </a>
                </p>
            </div>

            <!-- Footer -->
            <div class="text-center pt-4 border-t border-gray-200">
                <p class="text-gray-600 text-xs">
                    En vous inscrivant, vous acceptez notre
                    <a href="#" class="text-[#ef7724] hover:underline font-medium">Politique de confidentialité</a>
                </p>
                <p class="text-gray-500 text-xs mt-3">
                    © 2026 Go Exploria Business. Tous droits réservés.
                </p>
            </div>
        </div>
    </div>

    <script>
        // Variables globales
        let currentStep = 1;
        const totalSteps = 3;

        // Role selection
        function updateRoleSelection(input) {
            document.querySelectorAll('[id^="role"]').forEach(el => {
                if (!el.id.startsWith('role')) return;
                el.classList.remove('border-[#ef7724]', 'bg-[#fff5eb]');
                el.classList.add('border-gray-200');
            });
            const parent = input.closest('label');
            parent.classList.remove('border-gray-200');
            parent.classList.add('border-[#ef7724]', 'bg-[#fff5eb]');
        }

        // Email availability check with debounce
        let emailCheckTimeout;
        let emailExists = false;

        document.getElementById('email').addEventListener('input', function() {
            clearTimeout(emailCheckTimeout);
            const email = this.value.trim();
            const errorEl = document.getElementById('emailError');
            const indicator = document.getElementById('emailCheckIndicator');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            errorEl.classList.add('hidden');
            indicator.classList.add('hidden');
            emailExists = false;

            if (!email || !emailRegex.test(email)) return;

            emailCheckTimeout = setTimeout(async () => {
                indicator.classList.remove('hidden');

                try {
                    const response = await fetch('{{ route("ajax.check-email") }}?email=' + encodeURIComponent(email), {
                        headers: { 'Accept': 'application/json' }
                    });
                    const data = await response.json();

                    if (data.exists) {
                        errorEl.textContent = data.message;
                        errorEl.classList.remove('hidden');
                        emailExists = true;
                    }
                } catch (e) {
                    console.error('Email check error:', e);
                } finally {
                    indicator.classList.add('hidden');
                }
            }, 500);
        });

        // Liste des pays avec codes ISO alpha-2 (pour les drapeaux via flagcdn.com)
        const countries = [
            {code: 'AF', name: 'Afghanistan'},
            {code: 'ZA', name: 'Afrique du Sud'},
            {code: 'AL', name: 'Albanie'},
            {code: 'DZ', name: 'Algérie'},
            {code: 'DE', name: 'Allemagne'},
            {code: 'AD', name: 'Andorre'},
            {code: 'AO', name: 'Angola'},
            {code: 'AI', name: 'Anguilla'},
            {code: 'AG', name: 'Antigua-et-Barbuda'},
            {code: 'SA', name: 'Arabie Saoudite'},
            {code: 'AR', name: 'Argentine'},
            {code: 'AM', name: 'Arménie'},
            {code: 'AW', name: 'Aruba'},
            {code: 'AU', name: 'Australie'},
            {code: 'AT', name: 'Autriche'},
            {code: 'AZ', name: 'Azerbaïdjan'},
            {code: 'BS', name: 'Bahamas'},
            {code: 'BH', name: 'Bahreïn'},
            {code: 'BD', name: 'Bangladesh'},
            {code: 'BB', name: 'Barbade'},
            {code: 'BE', name: 'Belgique'},
            {code: 'BZ', name: 'Belize'},
            {code: 'BJ', name: 'Bénin'},
            {code: 'BM', name: 'Bermudes'},
            {code: 'BT', name: 'Bhoutan'},
            {code: 'BY', name: 'Biélorussie'},
            {code: 'BO', name: 'Bolivie'},
            {code: 'BA', name: 'Bosnie-Herzégovine'},
            {code: 'BW', name: 'Botswana'},
            {code: 'BR', name: 'Brésil'},
            {code: 'BN', name: 'Brunéi'},
            {code: 'BG', name: 'Bulgarie'},
            {code: 'BF', name: 'Burkina Faso'},
            {code: 'BI', name: 'Burundi'},
            {code: 'KH', name: 'Cambodge'},
            {code: 'CM', name: 'Cameroun'},
            {code: 'CA', name: 'Canada'},
            {code: 'CV', name: 'Cap-Vert'},
            {code: 'CF', name: 'République Centrafricaine'},
            {code: 'CL', name: 'Chili'},
            {code: 'CN', name: 'Chine'},
            {code: 'CY', name: 'Chypre'},
            {code: 'CO', name: 'Colombie'},
            {code: 'KM', name: 'Comores'},
            {code: 'CG', name: 'Congo'},
            {code: 'CD', name: 'République Démocratique du Congo'},
            {code: 'KP', name: 'Corée du Nord'},
            {code: 'KR', name: 'Corée du Sud'},
            {code: 'CR', name: 'Costa Rica'},
            {code: 'CI', name: "Côte d'Ivoire"},
            {code: 'HR', name: 'Croatie'},
            {code: 'CU', name: 'Cuba'},
            {code: 'CW', name: 'Curaçao'},
            {code: 'DK', name: 'Danemark'},
            {code: 'DJ', name: 'Djibouti'},
            {code: 'DM', name: 'Dominique'},
            {code: 'EG', name: 'Égypte'},
            {code: 'AE', name: 'Émirats Arabes Unis'},
            {code: 'EC', name: 'Équateur'},
            {code: 'ER', name: 'Érythrée'},
            {code: 'ES', name: 'Espagne'},
            {code: 'EE', name: 'Estonie'},
            {code: 'US', name: 'États-Unis'},
            {code: 'ET', name: 'Éthiopie'},
            {code: 'FJ', name: 'Fidji'},
            {code: 'FI', name: 'Finlande'},
            {code: 'FR', name: 'France'},
            {code: 'GA', name: 'Gabon'},
            {code: 'GM', name: 'Gambie'},
            {code: 'GE', name: 'Géorgie'},
            {code: 'GH', name: 'Ghana'},
            {code: 'GI', name: 'Gibraltar'},
            {code: 'GR', name: 'Grèce'},
            {code: 'GD', name: 'Grenade'},
            {code: 'GL', name: 'Groenland'},
            {code: 'GP', name: 'Guadeloupe'},
            {code: 'GU', name: 'Guam'},
            {code: 'GT', name: 'Guatemala'},
            {code: 'GG', name: 'Guernesey'},
            {code: 'GN', name: 'Guinée'},
            {code: 'GQ', name: 'Guinée Équatoriale'},
            {code: 'GW', name: 'Guinée-Bissau'},
            {code: 'GY', name: 'Guyana'},
            {code: 'GF', name: 'Guyane Française'},
            {code: 'HT', name: 'Haïti'},
            {code: 'HN', name: 'Honduras'},
            {code: 'HU', name: 'Hongrie'},
            {code: 'HK', name: 'Hong Kong'},
            {code: 'IN', name: 'Inde'},
            {code: 'ID', name: 'Indonésie'},
            {code: 'IR', name: 'Iran'},
            {code: 'IQ', name: 'Irak'},
            {code: 'IE', name: 'Irlande'},
            {code: 'IS', name: 'Islande'},
            {code: 'IL', name: 'Israël'},
            {code: 'IT', name: 'Italie'},
            {code: 'JM', name: 'Jamaïque'},
            {code: 'JP', name: 'Japon'},
            {code: 'JE', name: 'Jersey'},
            {code: 'JO', name: 'Jordanie'},
            {code: 'KZ', name: 'Kazakhstan'},
            {code: 'KE', name: 'Kenya'},
            {code: 'KG', name: 'Kirghizistan'},
            {code: 'KI', name: 'Kiribati'},
            {code: 'KW', name: 'Koweït'},
            {code: 'LA', name: 'Laos'},
            {code: 'LS', name: 'Lesotho'},
            {code: 'LV', name: 'Lettonie'},
            {code: 'LB', name: 'Liban'},
            {code: 'LR', name: 'Libéria'},
            {code: 'LY', name: 'Libye'},
            {code: 'LI', name: 'Liechtenstein'},
            {code: 'LT', name: 'Lituanie'},
            {code: 'LU', name: 'Luxembourg'},
            {code: 'MO', name: 'Macao'},
            {code: 'MK', name: 'Macédoine du Nord'},
            {code: 'MG', name: 'Madagascar'},
            {code: 'MY', name: 'Malaisie'},
            {code: 'MW', name: 'Malawi'},
            {code: 'MV', name: 'Maldives'},
            {code: 'ML', name: 'Mali'},
            {code: 'MT', name: 'Malte'},
            {code: 'MA', name: 'Maroc'},
            {code: 'MQ', name: 'Martinique'},
            {code: 'MU', name: 'Maurice'},
            {code: 'MR', name: 'Mauritanie'},
            {code: 'YT', name: 'Mayotte'},
            {code: 'MX', name: 'Mexique'},
            {code: 'MD', name: 'Moldavie'},
            {code: 'MC', name: 'Monaco'},
            {code: 'MN', name: 'Mongolie'},
            {code: 'ME', name: 'Monténégro'},
            {code: 'MS', name: 'Montserrat'},
            {code: 'MZ', name: 'Mozambique'},
            {code: 'MM', name: 'Myanmar'},
            {code: 'NA', name: 'Namibie'},
            {code: 'NR', name: 'Nauru'},
            {code: 'NP', name: 'Népal'},
            {code: 'NI', name: 'Nicaragua'},
            {code: 'NE', name: 'Niger'},
            {code: 'NG', name: 'Nigéria'},
            {code: 'NO', name: 'Norvège'},
            {code: 'NC', name: 'Nouvelle-Calédonie'},
            {code: 'NZ', name: 'Nouvelle-Zélande'},
            {code: 'OM', name: 'Oman'},
            {code: 'UG', name: 'Ouganda'},
            {code: 'UZ', name: 'Ouzbékistan'},
            {code: 'PK', name: 'Pakistan'},
            {code: 'PW', name: 'Palaos'},
            {code: 'PS', name: 'Palestine'},
            {code: 'PA', name: 'Panama'},
            {code: 'PG', name: 'Papouasie-Nouvelle-Guinée'},
            {code: 'PY', name: 'Paraguay'},
            {code: 'NL', name: 'Pays-Bas'},
            {code: 'PE', name: 'Pérou'},
            {code: 'PH', name: 'Philippines'},
            {code: 'PN', name: 'Pitcairn'},
            {code: 'PL', name: 'Pologne'},
            {code: 'PF', name: 'Polynésie Française'},
            {code: 'PR', name: 'Porto Rico'},
            {code: 'PT', name: 'Portugal'},
            {code: 'QA', name: 'Qatar'},
            {code: 'RE', name: 'La Réunion'},
            {code: 'RO', name: 'Roumanie'},
            {code: 'GB', name: 'Royaume-Uni'},
            {code: 'RU', name: 'Russie'},
            {code: 'RW', name: 'Rwanda'},
            {code: 'EH', name: 'Sahara Occidental'},
            {code: 'BL', name: 'Saint-Barthélemy'},
            {code: 'SH', name: 'Sainte-Hélène'},
            {code: 'LC', name: 'Sainte-Lucie'},
            {code: 'KN', name: 'Saint-Christophe-et-Niévès'},
            {code: 'SM', name: 'Saint-Marin'},
            {code: 'MF', name: 'Saint-Martin'},
            {code: 'PM', name: 'Saint-Pierre-et-Miquelon'},
            {code: 'VC', name: 'Saint-Vincent-et-les-Grenadines'},
            {code: 'SV', name: 'Salvador'},
            {code: 'WS', name: 'Samoa'},
            {code: 'AS', name: 'Samoa Américaines'},
            {code: 'ST', name: 'Sao Tomé-et-Principe'},
            {code: 'SN', name: 'Sénégal'},
            {code: 'RS', name: 'Serbie'},
            {code: 'SC', name: 'Seychelles'},
            {code: 'SL', name: 'Sierra Leone'},
            {code: 'SG', name: 'Singapour'},
            {code: 'SX', name: 'Saint-Martin'},
            {code: 'SK', name: 'Slovaquie'},
            {code: 'SI', name: 'Slovénie'},
            {code: 'SO', name: 'Somalie'},
            {code: 'SD', name: 'Soudan'},
            {code: 'SS', name: 'Soudan du Sud'},
            {code: 'LK', name: 'Sri Lanka'},
            {code: 'SE', name: 'Suède'},
            {code: 'CH', name: 'Suisse'},
            {code: 'SR', name: 'Suriname'},
            {code: 'SJ', name: 'Svalbard et Jan Mayen'},
            {code: 'SZ', name: 'Eswatini'},
            {code: 'SY', name: 'Syrie'},
            {code: 'TJ', name: 'Tadjikistan'},
            {code: 'TW', name: 'Taïwan'},
            {code: 'TZ', name: 'Tanzanie'},
            {code: 'TD', name: 'Tchad'},
            {code: 'CZ', name: 'Tchéquie'},
            {code: 'TH', name: 'Thaïlande'},
            {code: 'TL', name: 'Timor Oriental'},
            {code: 'TG', name: 'Togo'},
            {code: 'TK', name: 'Tokelau'},
            {code: 'TO', name: 'Tonga'},
            {code: 'TT', name: 'Trinité-et-Tobago'},
            {code: 'TN', name: 'Tunisie'},
            {code: 'TM', name: 'Turkménistan'},
            {code: 'TC', name: 'Îles Turques-et-Caïques'},
            {code: 'TR', name: 'Turquie'},
            {code: 'TV', name: 'Tuvalu'},
            {code: 'UA', name: 'Ukraine'},
            {code: 'UY', name: 'Uruguay'},
            {code: 'VU', name: 'Vanuatu'},
            {code: 'VA', name: 'Vatican'},
            {code: 'VE', name: 'Venezuela'},
            {code: 'VN', name: 'Vietnam'},
            {code: 'VG', name: 'Îles Vierges Britanniques'},
            {code: 'VI', name: 'Îles Vierges Américaines'},
            {code: 'WF', name: 'Wallis-et-Futuna'},
            {code: 'YE', name: 'Yémen'},
            {code: 'ZM', name: 'Zambie'},
            {code: 'ZW', name: 'Zimbabwe'},
        ];

        // Initialiser le select des pays
        (function initCountrySelect() {
            const select = document.getElementById('pays');
            countries.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.name;
                opt.dataset.code = c.code;
                opt.textContent = c.name;
                select.appendChild(opt);
            });
            select.addEventListener('change', updateFlag);
            select.value = 'Canada';
            updateFlag();
        })();

        function updateFlag() {
            const select = document.getElementById('pays');
            const placeholder = document.getElementById('flagPlaceholder');
            const image = document.getElementById('flagImage');
            const selectedOption = select.options[select.selectedIndex];

            if (selectedOption && selectedOption.dataset.code) {
                const code = selectedOption.dataset.code.toLowerCase();
                placeholder.classList.add('hidden');
                image.classList.remove('hidden');
                image.src = 'https://flagcdn.com/w40/' + code + '.png';
                image.alt = selectedOption.textContent;
                image.onerror = function() {
                    this.classList.add('hidden');
                    placeholder.classList.remove('hidden');
                };
            } else {
                placeholder.classList.remove('hidden');
                image.classList.add('hidden');
                image.src = '';
            }
        }

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

        // Toggle terms checkbox
        function toggleTerms() {
            const checkbox = document.getElementById('termsCheckbox');
            checkbox.classList.toggle('checked');
        }

        // Toggle newsletter checkbox
        function toggleNewsletter() {
            const checkbox = document.getElementById('newsletterCheckbox');
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

        // Validate step 1
        function validateStep1() {
            let isValid = true;
            
            // Reset errors
            document.getElementById('nameError').classList.add('hidden');
            document.getElementById('emailError').classList.add('hidden');
            document.getElementById('passwordError').classList.add('hidden');
            document.getElementById('passwordConfirmationError').classList.add('hidden');
            
            // Validate name
            const name = document.getElementById('name').value.trim();
            if (!name) {
                document.getElementById('nameError').textContent = 'Le nom est requis';
                document.getElementById('nameError').classList.remove('hidden');
                document.getElementById('name').classList.add('shake');
                isValid = false;
            }
            
            // Validate email
            const email = document.getElementById('email').value.trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!email) {
                document.getElementById('emailError').textContent = 'L\'email est requis';
                document.getElementById('emailError').classList.remove('hidden');
                document.getElementById('email').classList.add('shake');
                isValid = false;
            } else if (!emailRegex.test(email)) {
                document.getElementById('emailError').textContent = 'Format d\'email invalide';
                document.getElementById('emailError').classList.remove('hidden');
                document.getElementById('email').classList.add('shake');
                isValid = false;
            } else if (emailExists) {
                document.getElementById('emailError').textContent = 'Cet email est déjà utilisé';
                document.getElementById('emailError').classList.remove('hidden');
                document.getElementById('email').classList.add('shake');
                isValid = false;
            }
            
            // Validate password
            const password = document.getElementById('password').value;
            const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;
            if (!password) {
                document.getElementById('passwordError').textContent = 'Le mot de passe est requis';
                document.getElementById('passwordError').classList.remove('hidden');
                document.getElementById('password').classList.add('shake');
                isValid = false;
            } else if (!passwordRegex.test(password)) {
                document.getElementById('passwordError').textContent = 'Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule et un chiffre';
                document.getElementById('passwordError').classList.remove('hidden');
                document.getElementById('password').classList.add('shake');
                isValid = false;
            }
            
            // Validate password confirmation
            const passwordConfirmation = document.getElementById('password_confirmation').value;
            if (password !== passwordConfirmation) {
                document.getElementById('passwordConfirmationError').textContent = 'Les mots de passe ne correspondent pas';
                document.getElementById('passwordConfirmationError').classList.remove('hidden');
                document.getElementById('password_confirmation').classList.add('shake');
                isValid = false;
            }
            
            // Remove shake animation
            setTimeout(() => {
                document.getElementById('name').classList.remove('shake');
                document.getElementById('email').classList.remove('shake');
                document.getElementById('password').classList.remove('shake');
                document.getElementById('password_confirmation').classList.remove('shake');
            }, 500);
            
            return isValid;
        }

        // Validate step 3 (terms)
        function validateStep3() {
            const termsChecked = document.getElementById('termsCheckbox').classList.contains('checked');
            const termsError = document.getElementById('termsError');
            
            if (!termsChecked) {
                termsError.textContent = 'Vous devez accepter les conditions d\'utilisation';
                termsError.classList.remove('hidden');
                return false;
            }
            
            termsError.classList.add('hidden');
            return true;
        }

        // Navigate to next step
        function nextStep() {
            if (currentStep === 1) {
                if (!validateStep1()) {
                    return;
                }
            } else if (currentStep === 2) {
                // Step 2 doesn't require validation
            } else if (currentStep === 3) {
                return; // Step 3 is for submission
            }
            
            // Hide current step
            document.getElementById(`step${currentStep}Form`).classList.remove('active');
            document.getElementById(`step${currentStep}`).classList.remove('active');
            
            // Activate line between steps
            if (currentStep < totalSteps) {
                document.getElementById(`line${currentStep}`).classList.add('active');
            }
            
            // Move to next step
            currentStep++;
            
            // Show next step
            document.getElementById(`step${currentStep}Form`).classList.add('active');
            document.getElementById(`step${currentStep}`).classList.add('active');
        }

        // Navigate to previous step
        function prevStep() {
            // Hide current step
            document.getElementById(`step${currentStep}Form`).classList.remove('active');
            document.getElementById(`step${currentStep}`).classList.remove('active');
            
            // Deactivate line between steps
            if (currentStep > 1) {
                document.getElementById(`line${currentStep - 1}`).classList.remove('active');
            }
            
            // Move to previous step
            currentStep--;
            
            // Show previous step
            document.getElementById(`step${currentStep}Form`).classList.add('active');
            document.getElementById(`step${currentStep}`).classList.add('active');
        }

        // Form submission
        document.getElementById('registerForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Validate terms
            if (!validateStep3()) {
                return;
            }
            
            // Reset errors and hide alert
            document.getElementById('termsError').classList.add('hidden');
            document.getElementById('alertContainer').classList.add('hidden');
            
            // Get elements
            const btn = document.getElementById('registerBtn');
            const btnText = document.getElementById('registerBtnText');
            const spinner = document.getElementById('registerSpinner');
            
            // Show loading
            btn.disabled = true;
            btnText.textContent = 'Inscription en cours...';
            spinner.classList.remove('hidden');
            
            // Prepare form data
            const formData = new FormData();
            
            // User data
            formData.append('name', document.getElementById('name').value.trim());
            formData.append('email', document.getElementById('email').value.trim());
            formData.append('password', document.getElementById('password').value);
            formData.append('password_confirmation', document.getElementById('password_confirmation').value);
            
            // Etablissement data
            formData.append('etablissement_name', document.getElementById('etablissement_name').value.trim());
            formData.append('lname', document.getElementById('lname').value.trim());
            formData.append('phone', document.getElementById('phone').value.trim());
            formData.append('adresse', document.getElementById('adresse').value.trim());
            formData.append('pays', document.getElementById('pays').value);
            formData.append('ville', document.getElementById('ville').value.trim());
            formData.append('zip_code', document.getElementById('zip_code').value.trim());
            
            // Role
            const selectedRole = document.querySelector('input[name="role"]:checked');
            if (selectedRole) {
                formData.append('role', selectedRole.value);
            }

            // Terms and newsletter
            formData.append('terms', document.getElementById('termsCheckbox').classList.contains('checked') ? '1' : '0');
            formData.append('newsletter', document.getElementById('newsletterCheckbox').classList.contains('checked') ? '1' : '0');
            
            try {
                const response = await fetch('{{ route("ajax.register") }}', {
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
                    showAlert(data.message || 'Inscription réussie ! Redirection en cours...', 'success');
                    
                    // Animation de succès
                    btn.classList.add('bg-green-500');
                    btn.classList.remove('bg-gradient-to-r', 'from-[#ef7724]', 'to-[#d6651e]');
                    
                    // Redirection
                    setTimeout(() => {
                        window.location.href = data.redirect || '{{ route("verification.notice") }}';
                    }, 1500);
                    
                } else {
                    // Error handling
                    if (data.errors) {
                        // Validation errors
                        for (const field in data.errors) {
                            const errorElement = document.getElementById(field + 'Error');
                            const inputElement = document.getElementById(field);
                            
                            if (errorElement && inputElement) {
                                errorElement.textContent = data.errors[field][0];
                                errorElement.classList.remove('hidden');
                                inputElement.classList.add('shake');
                                
                                setTimeout(() => {
                                    inputElement.classList.remove('shake');
                                }, 500);
                            }
                        }
                        
                        // Scroll to first error
                        const firstError = document.querySelector('[id$="Error"]:not(.hidden)');
                        if (firstError) {
                            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    } else {
                        // General error
                        showAlert(data.message || 'Erreur lors de l\'inscription', 'error');
                    }
                }
                
            } catch (error) {
                console.error('Error:', error);
                showAlert('Erreur réseau. Veuillez réessayer.', 'error');
            } finally {
                // Reset button
                btn.disabled = false;
                btnText.textContent = 'S\'inscrire';
                spinner.classList.add('hidden');
            }
        });
    </script>
</body>
</html>