<aside class="dashboard-sidebar" id="dashboardSidebar">
    <div class="sidebar-logo">
        <div class="logo-main">
            <img src="{{ asset('logo.png') }}" style="width: 180px;" alt="Logo">
        </div>
    </div>
    
    <ul class="sidebar-menu">
        @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('super-admin'))
        <li>
            <a href="{{ route('dashboard') }}" class="menu-item active">
                <span class="menu-icon"><i class="fas fa-tachometer-alt"></i></span>
                <span class="menu-text">Tableau de bord</span>
            </a>
        </li>
        
        
        <li>
            <a href="{{ route('etablissements.index') }}" class="menu-item">
                <span class="menu-icon"><i class="fas fa-building"></i></span>
                <span class="menu-text">Mon &Eacute;tablissement</span>
            </a>
        </li>

        <li>
            <a href="{{ route('etablissements.rendezvous.index') }}" class="menu-item">
                <span class="menu-icon"><i class="fas fa-calendar-check"></i></span>
                <span class="menu-text">Rendez-vous</span>
            </a>
        </li>

        
        <li>
            <a href="{{ route('modules.index') }}" class="menu-item">
                <span class="menu-icon"><i class="fas fa-plug"></i></span>
                <span class="menu-text">Applications</span>
            </a>
        </li>

        <li>
            <a href="{{ route('mode-emploi.index') }}" class="menu-item">
                <span class="menu-icon"><i class="fas fa-graduation-cap"></i></span>
                <span class="menu-text">Mode d'Emploi</span>
            </a>
        </li>
        
        <li class="has-submenu">
            <a href="#" class="menu-link">
                <span class="menu-icon"><i class="fas fa-cube"></i></span>
                <span class="menu-text">Projets</span>
                <span class="menu-arrow"><i class="fas fa-chevron-down"></i></span>
            </a>
            <ul class="submenu">
                <li><a href="{{ route('projects.index') }}" class="submenu-item"><i class="fas fa-cubes submenu-icon"></i>Gestion des projets</a></li>
                <li><a href="{{ route('tasks.index') }}" class="submenu-item"><i class="fas fa-check-circle submenu-icon"></i>Liste des tâches</a></li>
                <li><a href="{{ route('projects.calendar') }}" class="submenu-item"><i class="fas fa-calendar-alt submenu-icon"></i>Calendrier des projets</a></li>
            </ul>
        </li>
        
        <li class="has-submenu">
            <a href="#" class="menu-link">
                <span class="menu-icon"><i class="fas fa-shopping-cart"></i></span>
                <span class="menu-text">Ecommerce</span>
                <span class="menu-arrow"><i class="fas fa-chevron-down"></i></span>
            </a>
            <ul class="submenu">
                <li>
                    <a href="{{ route('products.index') }}" class="submenu-item">
                        <i class="fas fa-box submenu-icon"></i>
                        Produits 
                        @if(\App\Models\Product::count() > 0)
                        <span class="submenu-badge">
                            {{ \App\Models\Product::count() }}
                        </span>
                        @endif
                    </a>
                </li>
                <li>
                    <a href="{{ route('payments.index') }}" class="submenu-item">
                        <i class="fas fa-credit-card submenu-icon"></i>
                        Paiements  
                        @if(\App\Models\Payment::where('status', 'en_attente')->count() > 0)
                        <span class="submenu-badge bg-warning">
                            {{ \App\Models\Payment::where('status', 'en_attente')->count() }}
                        </span>
                        @endif
                    </a>
                </li>
                <li>
                    <a href="{{ url('transactions.index') }}" class="submenu-item">
                        <i class="fas fa-history submenu-icon"></i>
                        Transactions
                    </a>
                </li>
                <li>
                    <a href="{{ url('orders.index') }}" class="submenu-item">
                        <i class="fas fa-shopping-cart submenu-icon"></i>
                        Commandes
                    </a>
                </li>
                <li>
                    <a href="{{ url('customers.index') }}" class="submenu-item">
                        <i class="fas fa-users submenu-icon"></i>
                        Clients
                        <span class="submenu-badge">
                            {{ \App\Models\Customer::count() }}
                        </span>
                    </a>
                </li>
                <li class="submenu-divider"></li>
                <li>
                    <a href="{{ route('admin.payment.gateways') }}" class="submenu-item">
                        <i class="fas fa-cog submenu-icon"></i>
                        Configuration paiements
                    </a>
                </li>
                <li>
                    <a href="{{ url('ecommerce/stats') }}" class="submenu-item">
                        <i class="fas fa-chart-line submenu-icon"></i>
                        Statistiques
                    </a>
                </li>
            </ul>
        </li>
        
        <li class="has-submenu {{ request()->routeIs('invoices.*') || request()->routeIs('quotes.*') || request()->routeIs('billing.*') ? 'active' : '' }}">
            <a href="#" class="menu-link">
                <span class="menu-icon"><i class="fas fa-file-invoice"></i></span>
                <span class="menu-text">Facturation</span>
                <span class="menu-arrow"><i class="fas fa-chevron-down"></i></span>
            </a>
            <ul class="submenu">
               <!--  <li><a href="{{ route('invoices.index') }}" class="submenu-item"><i class="fas fa-file-invoice-dollar submenu-icon"></i>Factures @php($unpaidInvoices = \App\Models\Invoice::whereIn('status', ['en_attente', 'partiellement_payee'])->count())@if($unpaidInvoices > 0)<span class="submenu-badge bg-danger">{{ $unpaidInvoices }}</span>@endif</a></li>
                <li><a href="{{ route('quotes.index') }}" class="submenu-item {{ request()->routeIs('quotes.*') ? 'active' : '' }}"><i class="fas fa-file-signature submenu-icon"></i>Devis</a></li> -->
                <li><a href="{{ route('billing.requests.index') }}" class="submenu-item {{ request()->routeIs('billing.requests.*') ? 'active' : '' }}"><i class="fas fa-inbox submenu-icon"></i>Demandes re&ccedil;ues</a></li> 
                <li><a href="{{ route('billing.request-services.index') }}" class="submenu-item {{ request()->routeIs('billing.request-services.*') ? 'active' : '' }}"><i class="fas fa-list-check submenu-icon"></i>Gestion des plans</a></li>
                <li><a href="{{ route('billing.settings.index') }}" class="submenu-item"><i class="fas fa-sliders-h submenu-icon"></i>Paramètres facturation</a></li>
                <li><a href="{{ url('ecommerce/settings') }}" class="submenu-item"><i class="fas fa-sliders-h submenu-icon"></i>Paramètres</a></li>
            </ul>
        </li>
        
        
        <li>
            <a href="#" class="menu-item">
                <span class="menu-icon"><i class="fas fa-chart-line"></i></span>
                <span class="menu-text">Analytics</span>
            </a>
        </li>
        
        <li class="has-submenu">
            <a href="#" class="menu-link">
                <span class="menu-icon"><i class="fas fa-cog"></i></span>
                <span class="menu-text">Paramètres</span>
                <span class="menu-arrow"><i class="fas fa-chevron-down"></i></span>
            </a>
            <ul class="submenu">
                <li><a href="{{ route('menus.index') }}" class="submenu-item"><i class="fas fa-bars submenu-icon"></i>Gestion de menus</a></li>
                <li><a href="{{ route('sliders.index') }}" class="submenu-item"><i class="fas fa-images submenu-icon"></i>Sliders</a></li>
                <li><a href="{{ route('plans.index') }}" class="submenu-item"><i class="fas fa-crown submenu-icon"></i>Plans</a></li>
                <li><a href="{{ route('plan-services.index') }}" class="submenu-item"><i class="fas fa-sliders-h submenu-icon"></i>Services</a></li>
                <li><a href="{{ route('abonnements.index') }}" class="submenu-item"><i class="fas fa-calendar-check submenu-icon"></i>Abonnements</a></li>
            </ul>
        </li>
        @endif

        @if(auth()->user()->hasRole('entreprise') || auth()->user()->hasRole('partenaire-affilie'))
        <li class="sidebar-section-header">
            <span class="menu-text" style="font-size: 11px; text-transform: uppercase; color: #6B7280; padding: 8px 16px; display: block; font-weight: 600;">Espace établissement</span>
        </li>
        <li>
            <a href="{{ route('etablissements.index') }}" class="menu-item {{ request()->routeIs('etablissements.*') ? 'active' : '' }}">
                <span class="menu-icon"><i class="fas fa-building"></i></span>
                <span class="menu-text">Mon Établissement</span>
            </a>
        </li>
        @endif
        
        <li>
            <a class="menu-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <span class="menu-icon"><i class="fas fa-sign-out-alt"></i></span>
                <span class="menu-text">Se déconnecter</span>
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">@csrf</form>
        </li>
    </ul>
</aside>
