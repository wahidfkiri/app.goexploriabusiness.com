@extends('layouts.app')

@section('content')
    <!-- MAIN CONTENT -->
    <main class="dashboard-content">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">
                <span class="page-title-icon"><i class="fas fa-cms"></i></span>
                Espace entreprise - {{ $stats['etablissement']->name ?? 'Espace entreprise' }}
            </h1>
            
            <div class="page-actions">
                <a href="{{ route('cms.admin.pages.create', ['etablissementId' => $stats['etablissement']->id]) }}" class="btn btn-primary">
                    <i class="fas fa-plus-circle me-2"></i>Nouvelle page
                </a>

            </div>
        </div>

        <script>
            var currentEtablissementId = window.currentEtablissementId = {{ (int) $stats['etablissement']->id }};
        </script>
        
        <!-- Stats Cards -->
        @include('cms::admin.partials.stats-cards')
        
        <!-- Main Content -->
        <div class="main-card-modern mt-4">
            <div class="row g-0">
                <!-- Left Vertical Tabs -->
                <div class="col-md-3">
                    <div class="vertical-tabs-wrapper-modern">
                        <div class="profile-header-modern">
                            <div class="profile-avatar-modern">
                                <div class="avatar-gradient">
                                    <i class="fas fa-building"></i>
                                </div>
                                <div class="avatar-status {{ $stats['etablissement']->is_active ? 'active' : 'inactive' }}"></div>
                            </div>
                            <div class="profile-info-modern">
                                <h4 class="profile-name-modern">{{ $stats['etablissement']->name }}</h4>
                                <p class="profile-type-modern">{{ $stats['etablissement']->lname ?? 'Établissement' }}</p>
                                @if($stats['etablissement']->is_active)
                                    <span class="badge-active"><i class="fas fa-check-circle"></i> Actif</span>
                                @else
                                    <span class="badge-inactive"><i class="fas fa-times-circle"></i> Inactif</span>
                                @endif
                            </div>
                        </div>

                        <div class="tabs-search-modern">
                            <div class="search-input-wrapper">
                                <i class="fas fa-search search-icon"></i>
                                <input type="text" class="search-input" id="tabSearch" placeholder="Rechercher un onglet..." oninput="filterTabs(this.value)">
                            </div>
                        </div>

                        <div class="vertical-tabs-modern" id="verticalTabList">
                            <button class="nav-link-modern active" data-bs-toggle="pill" data-bs-target="#v-pills-dashboard" type="button" role="tab">
                                <i class="fas fa-tachometer-alt"></i>
                                <span>Tableau de bord</span>
                            </button>
                            <button class="nav-link-modern" data-bs-toggle="pill" data-bs-target="#v-pills-pages" type="button" role="tab">
                                <i class="fas fa-file-alt"></i>
                                <span>Pages</span>
                            </button>
                            <button class="nav-link-modern" data-bs-toggle="pill" data-bs-target="#v-pills-blogs" type="button" role="tab">
                                <i class="fas fa-blog"></i>
                                <span>Blog</span>
                            </button>
                            <button class="nav-link-modern" data-bs-toggle="pill" data-bs-target="#v-pills-header-footer" type="button" role="tab">
                                <i class="fas fa-window-maximize"></i>
                                <span>Header &amp; Footer</span>
                            </button>
                            <button class="nav-link-modern" data-bs-toggle="pill" data-bs-target="#v-pills-config" type="button" role="tab">
                                <i class="fas fa-cog"></i>
                                <span>Configuration</span>
                            </button>
                            <button class="nav-link-modern" data-bs-toggle="pill" data-bs-target="#v-pills-social" type="button" role="tab">
                                <i class="fas fa-share-alt"></i>
                                <span>Réseaux sociaux</span>
                            </button>
                            <button class="nav-link-modern" data-bs-toggle="pill" data-bs-target="#v-pills-seo" type="button" role="tab">
                                <i class="fas fa-chart-line"></i>
                                <span>SEO</span>
                            </button>
                            <button class="nav-link-modern" data-bs-toggle="pill" data-bs-target="#v-pills-slider" type="button" role="tab">
                                <i class="fas fa-images"></i>
                                <span>Sliders</span>
                            </button>
                            <button class="nav-link-modern" data-bs-toggle="pill" data-bs-target="#v-pills-slideshow" type="button" role="tab">
                                <i class="fas fa-play-circle"></i>
                                <span>Slideshow</span>
                            </button>
                            <button class="nav-link-modern" data-bs-toggle="pill" data-bs-target="#v-pills-map-videos" type="button" role="tab">
                                <i class="fas fa-video"></i>
                                <span>Vidéos maps</span>
                            </button>
                            <button class="nav-link-modern" data-bs-toggle="pill" data-bs-target="#v-pills-ecommerce" type="button" role="tab">
                                <i class="fas fa-shopping-cart"></i>
                                <span>E-commerce</span>
                            </button>
                            <button class="nav-link-modern" data-bs-toggle="pill" data-bs-target="#v-pills-media" type="button" role="tab">
                                <i class="fas fa-image"></i>
                                <span>Médiathèque</span>
                            </button>
                            <button class="nav-link-modern" data-bs-toggle="pill" data-bs-target="#v-pills-newsletter" type="button" role="tab">
                                <i class="fas fa-envelope-open-text"></i>
                                <span>Newsletter</span>
                            </button>
                            <button class="nav-link-modern" data-bs-toggle="pill" data-bs-target="#v-pills-subscribers" type="button" role="tab">
                                <i class="fas fa-users"></i>
                                <span>Abonnés</span>
                            </button>
                            <button class="nav-link-modern" data-bs-toggle="pill" data-bs-target="#v-pills-contact-messages" type="button" role="tab">
                                <i class="fas fa-inbox"></i>
                                <span>Messages contact</span>
                            </button>
                            <button class="nav-link-modern" data-bs-toggle="pill" data-bs-target="#v-pills-comments" type="button" role="tab">
                                <i class="fas fa-comments"></i>
                                <span>Commentaires</span>
                            </button>
                            <button class="nav-link-modern" data-bs-toggle="pill" data-bs-target="#v-pills-danger" type="button" role="tab">
                                <i class="fas fa-exclamation-triangle"></i>
                                <span>Zone de danger</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Right Tab Content -->
                <div class="col-md-9">
                    <div class="tab-content-wrapper">
                        <div class="tab-content">
                            <!-- Tableau de bord -->
                            @include('cms::admin.partials.dashboard-tab')
                            
                            <!-- Pages -->
                            @include('cms::admin.partials.pages-tab')
                            
                            <!-- Blogs -->
                            @include('cms::admin.partials.blogs-tab')
                            
                            <!-- Header & Footer -->
                            @include('cms::admin.partials.header-footer-tab')
                            
                            <!-- Configuration -->
                            @include('cms::admin.partials.config-tab')
                            
                            <!-- Réseaux sociaux -->
                            @include('cms::admin.partials.social-tab')
                            
                            <!-- SEO -->
                            @include('cms::admin.partials.seo-tab')
                            
                            <!-- Sliders -->
                            @include('cms::admin.partials.slider-tab')

                            <!-- Slideshow -->
                            @include('cms::admin.partials.slideshow-tab')

                            <!-- Vidéos maps -->
                            @include('cms::admin.partials.map-videos-tab')

                            <!-- E-commerce -->
                            @include('cms::admin.partials.ecommerce-tab')
                            
                            <!-- Médiathèque -->
                            @include('cms::admin.partials.media-tab')
                            
                            <!-- Newsletter -->
                            @include('cms::admin.partials.newsletter-tab')
                            
                            <!-- Abonnés -->
                            @include('cms::admin.partials.subscribers-tab')

                            <!-- Messages contact -->
                            @include('cms::admin.partials.contact-messages-tab')
                            
                            <!-- Commentaires -->
                            @include('cms::admin.partials.comments-tab')
                            
                            <!-- Zone de danger -->
                            @include('cms::admin.partials.danger-tab')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Modals -->
    @include('cms::admin.partials.apps')
    
    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    @include('cms::admin.partials.scripts')
    <script>
        var currentEtablissementId = window.currentEtablissementId = {{ (int) $stats['etablissement']->id }};
        
        function deletePage(pageId) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cette page ?')) {
                fetch(`/admin/cms/${window.currentEtablissementId}/pages/${pageId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                }).then(() => {
                    window.location.reload();
                });
            }
        }
        
        function clearCache(etablissementId) {
            if (confirm('Vider le cache du site ?')) {
                fetch(`/admin/cms/${etablissementId}/cache/clear`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                }).then(() => {
                    alert('Cache vidé avec succès');
                });
            }
        }
        
        function resetConfig(etablissementId) {
            if (confirm('ATTENTION: ATTENTION: Cette action réinitialisera toute votre configuration. Continuer ?')) {
                fetch(`/admin/cms/${etablissementId}/settings/reset`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                }).then(() => {
                    window.location.reload();
                });
            }
        }
        
        function deleteAllPages(etablissementId) {
            if (confirm('ATTENTION: ATTENTION: Cette action supprimera TOUTES les pages définitivement. Êtes-vous absolument sûr ?')) {
                fetch(`/admin/cms/${etablissementId}/pages/bulk/delete`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ ids: 'all' })
                }).then(() => {
                    window.location.reload();
                });
            }
        }
        
        function activateTheme(themeId, button) {
            if (!confirm('Voulez-vous activer ce thème ?')) return;
            
            fetch(`/admin/cms/${window.currentEtablissementId}/themes/${themeId}/activate`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            }).then(() => {
                window.location.reload();
            });
        }
        
        function navigateTo(section, urlSection = null, activeMenuSection = null) {
            // Cacher tous les contenus
            document.querySelectorAll('.tab-pane').forEach(pane => {
                pane.classList.remove('show', 'active');
            });
            
            // Afficher le contenu sélectionné
            const targetPane = document.getElementById(section);
            if (targetPane) {
                targetPane.classList.add('show', 'active');
            }
            
            // Mettre à jour l'URL sans recharger
            const url = new URL(window.location.href);
            url.searchParams.set('section', urlSection || section.replace('v-pills-', ''));
            window.history.pushState({}, '', url);
            
            // Mettre à jour le menu actif
            document.querySelectorAll('.nav-link-modern').forEach(link => {
                link.classList.remove('active');
            });
            const activeLink = document.querySelector(`.nav-link-modern[data-section="${activeMenuSection || section}"]`);
            if (activeLink) {
                activeLink.classList.add('active');
            }
        }
        
        // Gestion de la section depuis l'URL
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const section = urlParams.get('section');
            
            const searchInput = document.getElementById('tabSearchInput');
            const navLinks = document.querySelectorAll('.nav-link-modern');

            
    if (searchInput) {
        // Fonction de filtrage
        function filterTabs(searchTerm) {
            const term = searchTerm.toLowerCase().trim();
            
            navLinks.forEach(link => {
                // Récupérer le titre et la description de l'onglet
                const titleElement = link.querySelector('.tab-title-modern');
                const descriptionElement = link.querySelector('.tab-description');
                
                const title = titleElement ? titleElement.textContent.toLowerCase() : '';
                const description = descriptionElement ? descriptionElement.textContent.toLowerCase() : '';
                
                // Vérifier si le terme de recherche correspond
                const matches = term === '' || 
                               title.includes(term) || 
                               description.includes(term);
                
                // Afficher ou cacher l'onglet
                if (matches) {
                    link.style.display = 'flex';
                    // Ajouter une animation d'apparition
                    link.style.animation = 'fadeInUp 0.3s ease';
                } else {
                    link.style.display = 'none';
                }
            });
            
            // Afficher un message si aucun résultat
            showNoResultsMessage(term);
        }
        
        // Fonction pour afficher un message "aucun résultat"
        function showNoResultsMessage(searchTerm) {
            const container = document.querySelector('.vertical-tabs-modern');
            const existingMessage = document.querySelector('.no-results-message');
            
            // Compter les éléments visibles
            const visibleLinks = Array.from(navLinks).filter(link => link.style.display !== 'none');
            
            if (visibleLinks.length === 0 && searchTerm !== '') {
                // Créer le message si inexistant
                if (!existingMessage) {
                    const messageDiv = document.createElement('div');
                    messageDiv.className = 'no-results-message';
                    messageDiv.innerHTML = `
                        <div class="no-results-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <div class="no-results-text">Aucun menu trouvé pour "${escapeHtml(searchTerm)}"</div>
                        <button class="clear-search-btn" onclick="document.getElementById('tabSearchInput').value = ''; filterTabs('');">
                            <i class="fas fa-times"></i> Effacer la recherche
                        </button>
                    `;
                    container.appendChild(messageDiv);
                } else {
                    existingMessage.style.display = 'block';
                    const textElement = existingMessage.querySelector('.no-results-text');
                    if (textElement) {
                        textElement.innerHTML = `Aucun menu trouvé pour "${escapeHtml(searchTerm)}"`;
                    }
                }
            } else {
                if (existingMessage) {
                    existingMessage.style.display = 'none';
                }
            }
        }
        
        // Écouteur d'événement avec debounce pour meilleures performances
        let debounceTimer;
        searchInput.addEventListener('input', function(e) {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                filterTabs(e.target.value);
            }, 300);
        });
        
        // Ajouter un bouton pour effacer la recherche
        const searchWrapper = document.querySelector('.search-input-wrapper');
        if (searchWrapper && !searchWrapper.querySelector('.clear-search')) {
            const clearButton = document.createElement('button');
            clearButton.type = 'button';
            clearButton.className = 'clear-search';
            clearButton.innerHTML = '<i class="fas fa-times"></i>';
            clearButton.style.display = 'none';
            clearButton.addEventListener('click', function() {
                searchInput.value = '';
                filterTabs('');
                searchInput.focus();
                clearButton.style.display = 'none';
            });
            
            searchWrapper.appendChild(clearButton);
            
            // Afficher/masquer le bouton clear
            searchInput.addEventListener('input', function() {
                clearButton.style.display = this.value.length > 0 ? 'flex' : 'none';
            });
        }
    }
    
    // Fonction utilitaire pour échapper le HTML
    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/[&<>]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
    }
    
    // Animation de mise en évidence pour les résultats
    function highlightMatchingText(link, term) {
        if (!term || term === '') return;
        
        const titleElement = link.querySelector('.tab-title-modern');
        const descriptionElement = link.querySelector('.tab-description');
        
        if (titleElement) {
            const originalTitle = titleElement.getAttribute('data-original-title');
            if (originalTitle && term) {
                const regex = new RegExp(`(${term})`, 'gi');
                titleElement.innerHTML = originalTitle.replace(regex, '<mark class="search-highlight">$1</mark>');
            }
        }
        
        if (descriptionElement) {
            const originalDesc = descriptionElement.getAttribute('data-original-desc');
            if (originalDesc && term) {
                const regex = new RegExp(`(${term})`, 'gi');
                descriptionElement.innerHTML = originalDesc.replace(regex, '<mark class="search-highlight">$1</mark>');
            }
        }
    }
    
    // Sauvegarder les textes originaux pour le highlighting
    navLinks.forEach(link => {
        const titleElement = link.querySelector('.tab-title-modern');
        const descriptionElement = link.querySelector('.tab-description');
        
        if (titleElement && !titleElement.getAttribute('data-original-title')) {
            titleElement.setAttribute('data-original-title', titleElement.innerHTML);
        }
        if (descriptionElement && !descriptionElement.getAttribute('data-original-desc')) {
            descriptionElement.setAttribute('data-original-desc', descriptionElement.innerHTML);
        }
    });
            
            if (section === 'blocks') {
                navigateTo('v-pills-themes', 'blocks', 'v-pills-blocks');
                if (typeof showTemplatesPanel === 'function') {
                    showTemplatesPanel('blocks');
                }
            } else if (section === 'themes') {
                navigateTo('v-pills-themes', 'themes', 'v-pills-themes');
                if (typeof showTemplatesPanel === 'function') {
                    showTemplatesPanel('templates');
                }
            } else if (section) {
                const sectionMap = {
                    'dashboard': 'v-pills-dashboard',
                    'pages': 'v-pills-pages',
                    'blogs': 'v-pills-blogs',
                    'header-footer': 'v-pills-header-footer',
                    'config': 'v-pills-config',
                    'social': 'v-pills-social',
                    'seo': 'v-pills-seo',
                    'slider': 'v-pills-slider',
                    'slideshow': 'v-pills-slideshow',
                    'map-videos': 'v-pills-map-videos',
                    'ecommerce': 'v-pills-ecommerce',
                    'media': 'v-pills-media',
                    'newsletter': 'v-pills-newsletter',
                    'subscribers': 'v-pills-subscribers',
                    'contact-messages': 'v-pills-contact-messages',
                    'comments': 'v-pills-comments',
                    'danger': 'v-pills-danger'
                };
                
                const targetSection = sectionMap[section];
                if (targetSection) {
                    navigateTo(targetSection);
                }
            } else {
                navigateTo('v-pills-dashboard');
            }
        });
    </script>
    
    <style>
         /* Styles pour la recherche */
    .clear-search {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #94a3b8;
        cursor: pointer;
        display: none;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        transition: all 0.2s ease;
    }
    
    .clear-search:hover {
        background: #f1f5f9;
        color: #ef4444;
    }
    
    .no-results-message {
        text-align: center;
        padding: 40px 20px;
        background: #f8fafc;
        border-radius: 16px;
        margin-top: 20px;
        animation: fadeInUp 0.3s ease;
    }
    
    .no-results-icon {
        font-size: 48px;
        color: #94a3b8;
        margin-bottom: 16px;
    }
    
    .no-results-text {
        color: #64748b;
        font-size: 14px;
        margin-bottom: 16px;
    }
    
    .clear-search-btn {
        background: #e2e8f0;
        border: none;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 13px;
        color: #475569;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .clear-search-btn:hover {
        background: #cbd5e1;
        color: #1e293b;
    }
    
    .search-highlight {
        background: #fef08a;
        color: #854d0e;
        padding: 2px 4px;
        border-radius: 4px;
        font-weight: 500;
    }
    
    /* Animation */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Transition pour les éléments filtrés */
    .nav-link-modern {
        transition: display 0.3s ease, opacity 0.3s ease;
    }
    
    /* Compteur de résultats */
    .search-results-count {
        font-size: 12px;
        color: #64748b;
        margin-top: 12px;
        padding: 0 16px;
        text-align: center;
    }
        /* Animated badge */
        .badge-count {
            position: relative;
        }
        
        .badge-count.has-notification::after {
            content: '';
            position: absolute;
            top: -2px;
            right: -2px;
            width: 8px;
            height: 8px;
            background: #ef4444;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.2); opacity: 0.7; }
        }
        
        .vertical-tabs-wrapper-modern {
            background: linear-gradient(135deg, #ffffff 0%, #fafbfd 100%);
            border-radius: 20px;
            padding: 24px 16px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            max-width: 100%;
            min-width: 0;
            overflow-x: hidden;
            position: sticky;
            top: 16px;
            transition: all 0.3s ease;
        }
        
        .profile-header-modern {
            flex: 0 0 auto;
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 0 12px 24px 12px;
            margin-bottom: 16px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
        }
        
        .profile-avatar-modern {
            position: relative;
        }
        
        .avatar-gradient {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #4361ee, #3a56e4);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.8rem;
            box-shadow: 0 6px 14px rgba(67, 97, 238, 0.25);
            transition: transform 0.3s ease;
        }
        
        .avatar-gradient:hover {
            transform: scale(1.05);
        }
        
        .avatar-status {
            position: absolute;
            bottom: 2px;
            right: 2px;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            border: 2px solid white;
        }
        
        .avatar-status.active {
            background: #10b981;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
        }
        
        .avatar-status.inactive {
            background: #ef4444;
        }
        
        .profile-info-modern {
            flex: 1;
            min-width: 0;
        }

        .profile-site-link {
            align-items: center;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 12px;
            color: #2563eb;
            display: inline-flex;
            flex: 0 0 38px;
            height: 38px;
            justify-content: center;
            text-decoration: none;
            transition: all 0.2s ease;
            width: 38px;
        }

        .profile-site-link:hover {
            background: #2563eb;
            border-color: #2563eb;
            color: #fff;
            transform: translateY(-1px);
        }
        
        .profile-name-modern {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 4px 0;
            line-height: 1.3;
        }
        
        .profile-type-modern {
            font-size: 0.75rem;
            color: #64748b;
            margin: 0 0 8px 0;
        }
        
        .badge-active, .badge-inactive {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.7rem;
            padding: 4px 8px;
            border-radius: 20px;
            font-weight: 500;
        }
        
        .badge-active {
            background: rgba(16, 185, 129, 0.12);
            color: #10b981;
        }
        
        .badge-inactive {
            background: rgba(239, 68, 68, 0.12);
            color: #ef4444;
        }
        
        .tabs-search-modern {
            flex: 0 0 auto;
            padding: 0 12px;
            margin-bottom: 20px;
        }
        
        .search-input-wrapper {
            position: relative;
        }
        
        .search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 0.9rem;
            pointer-events: none;
        }
        
        .search-input {
            width: 100%;
            padding: 10px 12px 10px 38px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            font-size: 0.85rem;
            background: white;
            transition: all 0.3s ease;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #4361ee;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }
        
        .vertical-tabs-modern {
            display: block;
            flex: 1 1 auto;
            max-width: 100%;
            min-height: 0;
            min-width: 0;
            overflow-x: hidden;
            overflow-y: scroll;
            padding-right: 8px;
            scrollbar-gutter: stable;
        }
        
        .vertical-tabs-modern::-webkit-scrollbar {
            width: 4px;
        }
        
        .vertical-tabs-modern::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .vertical-tabs-modern::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .vertical-tabs-modern > * {
            margin-bottom: 6px;
            max-width: 100%;
            min-width: 0;
        }

        .vertical-tabs-modern > *:last-child {
            margin-bottom: 0;
        }
        
        .nav-link-modern {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: 14px;
            background: transparent;
            border: none;
            cursor: pointer;
            transition: all 0.25s ease;
            text-align: left;
            width: 100%;
            position: relative;
            text-decoration: none;
            color: #1e293b;
            min-width: 0;
            max-width: 100%;
            overflow: hidden;
        }
        
        .nav-link-modern:hover {
            background: rgba(67, 97, 238, 0.06);
            transform: translateX(4px);
            text-decoration: none;
            color: #1e293b;
        }
        
        .nav-link-modern.active {
            background: linear-gradient(135deg, #4361ee, #3a56e4);
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.25);
            color: white;
        }
        
        .nav-link-modern.active .tab-icon-wrapper i {
            color: white;
        }
        
        .nav-link-modern.active .tab-title-modern {
            color: white;
        }
        
        .nav-link-modern.active .tab-description {
            color: rgba(255, 255, 255, 0.8);
        }
        
        .nav-link-modern.active .badge-count {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }
        
        .nav-link-modern.danger:hover {
            background: rgba(239, 68, 68, 0.08);
        }
        
        .nav-link-modern.danger.active {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.25);
        }
        
        .tab-icon-wrapper {
            width: 36px;
            height: 36px;
            flex: 0 0 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: rgba(67, 97, 238, 0.1);
            transition: all 0.25s ease;
        }
        
        .nav-link-modern.active .tab-icon-wrapper {
            background: rgba(255, 255, 255, 0.15);
        }
        
        .tab-icon-wrapper i {
            font-size: 1.1rem;
            color: #4361ee;
        }
        
        .tab-content-wrapper-modern {
            flex: 1;
            min-width: 0;
            overflow: hidden;
        }
        
        .tab-title-modern {
            display: block;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 2px;
            overflow: hidden;
            text-overflow: ellipsis;
            transition: color 0.25s ease;
            white-space: nowrap;
        }
        
        .tab-description {
            display: block;
            font-size: 0.7rem;
            color: #64748b;
            overflow: hidden;
            text-overflow: ellipsis;
            transition: color 0.25s ease;
            white-space: nowrap;
        }
        
        .nav-link-modern.active .tab-description {
            color: rgba(255, 255, 255, 0.8);
        }
        
        .tab-badge {
            flex: 0 0 auto;
            min-width: 28px;
        }
        
        .badge-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #f1f5f9;
            color: #475569;
            font-size: 0.7rem;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 20px;
            min-width: 28px;
            transition: all 0.25s ease;
        }
        
        .divider-modern {
            height: 1px;
            background: linear-gradient(90deg, transparent, #e2e8f0, transparent);
            margin: 12px 0;
        }
        
        .nav-link-modern::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 3px;
            border-radius: 0 4px 4px 0;
            transform: scaleY(0);
            transition: transform 0.25s ease;
        }
        
        .nav-link-modern.active::before {
            transform: scaleY(1);
        }
        
        .tab-highlight {
            animation: highlightPulse 0.5s ease;
        }
        
        @keyframes highlightPulse {
            0%, 100% { background: transparent; }
            50% { background: rgba(67, 97, 238, 0.1); }
        }
        
        .tab-content-wrapper {
            padding: 30px;
            background: white;
            border-radius: 0 12px 12px 0;
            min-height: 500px;
        }
        
        .tab-content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .tab-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #333;
            margin: 0;
        }
        
        .info-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .info-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
        }
        
        .info-card-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .info-card-header i {
            font-size: 1.5rem;
        }
        
        .info-card-header h5 {
            margin: 0;
            font-weight: 600;
        }
        
        .table-container-modern {
            overflow-x: auto;
        }
        
        .modern-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .modern-table th,
        .modern-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        
        .modern-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }
        
        .modern-table tr:hover {
            background: #f8f9fa;
        }
        
        .empty-state-small {
            text-align: center;
            padding: 60px 20px;
            background: #f8f9fa;
            border-radius: 12px;
        }
        
        .danger-zone {
            border: 1px solid #ffcdd2;
            border-radius: 10px;
            padding: 20px;
            background: #fff5f5;
        }
        
        .danger-action {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #ffcdd2;
        }
        
        .danger-action:last-child {
            border-bottom: none;
        }
        
        .danger-action h5 {
            color: #d32f2f;
            margin-bottom: 5px;
        }
        
        .themes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .theme-card {
            background: #f8f9fa;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .theme-card.active {
            border-color: #4361ee;
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.2);
        }
        
        .theme-preview {
            background: #e9ecef;
            height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .theme-preview-placeholder {
            text-align: center;
            color: #adb5bd;
        }
        
        .theme-info {
            padding: 15px;
            text-align: center;
        }
        
        .config-sections {
            max-height: 500px;
            overflow-y: auto;
        }
        
        .config-group {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .config-group h4 {
            margin-bottom: 20px;
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
        }
        
        .config-item {
            margin-bottom: 15px;
        }
        
        .config-label {
            font-weight: 500;
            margin-bottom: 8px;
            display: block;
            color: #495057;
        }
        
        .social-icon-item {
            display: flex;
            align-items: center;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .stats-grid-mini {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .stat-mini-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
        }
        
        .stat-mini-value {
            font-size: 2rem;
            font-weight: 700;
            color: #4361ee;
        }
        
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
        }
        
        .gallery-item {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
        }
        
        .gallery-placeholder {
            color: #adb5bd;
        }
        
        .form-actions {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            text-align: right;
        }
        
        @media (max-width: 768px) {
            .vertical-tabs-wrapper-modern {
                border-radius: 20px;
                margin-bottom: 20px;
                max-height: none;
                position: static;
            }
            
            .tab-content-wrapper {
                border-radius: 12px;
            }
            
            .profile-header-modern {
                padding: 0 8px 20px 8px;
            }
            
            .avatar-gradient {
                width: 52px;
                height: 52px;
                font-size: 1.4rem;
            }
            
            .tab-description {
                display: none;
            }
            
            .tab-icon-wrapper {
                width: 32px;
                height: 32px;
            }
            
            .tab-title-modern {
                font-size: 0.85rem;
            }
            
            .nav-link-modern {
                padding: 10px 12px;
            }
            
            .danger-action {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .stats-grid-mini {
                grid-template-columns: 1fr;
            }
            
            .themes-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection


