@extends('layouts.app')

@section('content')
    
    <!-- MAIN CONTENT -->
    <main class="dashboard-content">
        <!-- Welcome Section -->
        <div class="welcome-card" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h2 class="welcome-title" style="margin-bottom: 0;">
                    Bonjour, {{ auth()->user()->name }}
                    @if(isset($etablissement) && $etablissement)
                        <small class="text-muted" style="font-size: 0.6em; display: block;">{{ $etablissement->nom }}</small>
                    @endif
                </h2>
                <p class="welcome-text" style="margin-bottom: 0; margin-top: 8px;">Bienvenue sur votre tableau de bord.</p>
            </div>
            <div style="text-align: right;">
                <div style="font-size: 2rem; font-weight: 700; line-height: 1.2; color: #fff;"><i class="fas fa-clock me-2"></i><span id="clock-time"></span></div>
                <div style="font-size: 1em; opacity: 0.85; color: #fff;" id="clock-date"></div>
            </div>
        </div>

        <script>
            function updateClock() {
                const now = new Date();
                const dateOpts = { timeZone: 'America/Toronto', weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                document.getElementById('clock-date').textContent = now.toLocaleDateString('fr-CA', dateOpts);
                document.getElementById('clock-time').textContent = now.toLocaleTimeString('en-CA', { timeZone: 'America/Toronto', hour12: false });
            }
            updateClock();
            setInterval(updateClock, 1000);
        </script>

        @if(auth()->user()->hasRole('entreprise') || auth()->user()->hasRole('partenaire-affilie'))
        <div class="alert d-flex align-items-center justify-content-between mt-3" role="alert" style="background: linear-gradient(135deg, #fff8e1, #fff3cd); border: 1px solid #ffc107; border-radius: 12px; padding: 16px 20px;">
            <div class="d-flex align-items-center">
                <div class="me-3" style="font-size: 1.5rem; color: #ff8f00;"><i class="fas fa-lightbulb"></i></div>
                <div>
                    <strong style="font-size: 1rem; color: #e65100;">Fonctionnalités à découvrir !</strong>
                    <p class="mb-0" style="color: #5d4037; font-size: 0.9rem;">Activez les modules ci-dessous pour booster votre présence en ligne et développer votre activité.</p>
                </div>
            </div>
            <a href="{{ route('billing.payment') }}" class="btn" style="background: #ef7724; color: white; font-weight: 700; padding: 10px 24px; border-radius: 8px; text-decoration: none; white-space: nowrap; font-size: 0.9rem;">Activer Votre Plan</a>
        </div>

        <div class="row mt-3">
            @php
                $modules = [
                    ['icon' => 'fa-globe', 'title' => 'Site Web', 'desc' => 'Créez votre site professionnel en quelques clics', 'color' => '#4361ee', 'route' => route('etablissements.index')],
                    ['icon' => 'fa-shopping-cart', 'title' => 'E-Commerce', 'desc' => 'Vendez vos produits et services en ligne 24/7', 'color' => '#ef7724', 'route' => '#'],
                    ['icon' => 'fa-search', 'title' => 'SEO & Visibilité', 'desc' => 'Optimisez votre référencement sur Google', 'color' => '#06d6a0', 'route' => '#'],
                    ['icon' => 'fa-bullhorn', 'title' => 'Publicité Ads', 'desc' => 'Campagnes Google & Meta Ads ciblées', 'color' => '#e63946', 'route' => '#'],
                    ['icon' => 'fa-envelope-open-text', 'title' => 'Email Marketing', 'desc' => 'Newsletters et campagnes automatisées', 'color' => '#8b5cf6', 'route' => '#'],
                    ['icon' => 'fa-robot', 'title' => 'Assistant IA', 'desc' => 'Générez du contenu avec intelligence artificielle', 'color' => '#06b6d4', 'route' => '#'],
                    ['icon' => 'fa-comments', 'title' => 'Chat & Messagerie', 'desc' => 'Communiquez avec vos clients en temps réel', 'color' => '#10b981', 'route' => '#'],
                    ['icon' => 'fa-map-marked-alt', 'title' => 'Géo-Marketing', 'desc' => 'Ciblez vos clients par zone géographique', 'color' => '#f59e0b', 'route' => '#'],
                ];
            @endphp
            @foreach($modules as $module)
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="module-card" style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #e5e7eb; transition: all 0.3s ease; height: 100%; cursor: pointer;" onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 8px 24px rgba(0,0,0,0.1)'" onmouseout="this.style.transform='';this.style.boxShadow='0 2px 8px rgba(0,0,0,0.06)'" onclick="window.location.href='{{ $module['route'] }}'">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                        <div style="width: 44px; height: 44px; border-radius: 10px; background: {{ $module['color'] }}15; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; color: {{ $module['color'] }};">
                            <i class="fas {{ $module['icon'] }}"></i>
                        </div>
                        <h6 style="margin: 0; font-weight: 700; font-size: 0.95rem; color: #1e293b;">{{ $module['title'] }}</h6>
                    </div>
                    <p style="margin: 0; font-size: 0.82rem; color: #64748b; line-height: 1.4;">{{ $module['desc'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </main>
@endsection
