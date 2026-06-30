<div class="col-md-3">
    <div class="vertical-tabs-wrapper-modern">
        @php
            $cmsDashboardSlug = $stats['etablissement']->slug ?: \Illuminate\Support\Str::slug((string) ($stats['etablissement']->name ?? ''));
            $cmsPublicSiteUrl = url('/company/' . $stats['etablissement']->id . ($cmsDashboardSlug ? '/' . $cmsDashboardSlug : ''));
            $notificationsCount = ($stats['comments_count'] ?? 0) + ($stats['pending_pages'] ?? 0);

            $cmsTabs = [
                [
                    'section' => 'dashboard',
                    'pill' => 'v-pills-dashboard',
                    'icon' => 'fa-chart-line',
                    'title' => 'Tableau de bord',
                    'description' => "Vue d'ensemble du site",
                    'badge' => null,
                ],
                [
                    'section' => 'config',
                    'pill' => 'v-pills-config',
                    'icon' => 'fa-sliders-h',
                    'title' => 'Configuration',
                    'description' => 'Paramètres généraux',
                    'badge' => null,
                ],
                [
                    'section' => 'media',
                    'pill' => 'v-pills-media',
                    'icon' => 'fa-images',
                    'title' => 'Médiathèque',
                    'description' => 'Images et fichiers',
                    'badge' => $stats['media_count'] ?? 0,
                ],
                [
                    'section' => 'themes',
                    'pill' => 'v-pills-themes',
                    'icon' => 'fa-palette',
                    'title' => 'Templates',
                    'description' => 'Templates du site',
                    'badge' => $stats['total_themes'] ?? 0,
                    'onclick' => "navigateTo('v-pills-themes', 'themes', 'v-pills-themes'); if (typeof showTemplatesPanel === 'function') showTemplatesPanel('templates'); return false;",
                ],
                [
                    'section' => 'header-footer',
                    'pill' => 'v-pills-header-footer',
                    'icon' => 'fa-layer-group',
                    'title' => 'Header & Footer',
                    'description' => 'Zones globales',
                    'badge' => null,
                ],
                [
                    'section' => 'slider',
                    'pill' => 'v-pills-slider',
                    'icon' => 'fa-video',
                    'title' => 'Slider Vidéos',
                    'description' => 'Gestion des slides',
                    'badge' => null,
                ],
                [
                    'section' => 'map-videos',
                    'pill' => 'v-pills-map-videos',
                    'icon' => 'fa-map-marked-alt',
                    'title' => 'Vidéos maps',
                    'description' => 'Points vidéo géolocalisés',
                    'badge' => null,
                ],
                [
                    'section' => 'slideshow',
                    'pill' => 'v-pills-slideshow',
                    'icon' => 'fa-photo-video',
                    'title' => 'Slideshow',
                    'description' => 'Vidéos de présentation',
                    'badge' => null,
                ],
                [
                    'section' => 'contact-messages',
                    'pill' => 'v-pills-contact-messages',
                    'icon' => 'fa-inbox',
                    'title' => 'Messages contact',
                    'description' => 'Demandes visiteurs',
                    'badge' => $stats['new_contact_messages_count'] ?? 0,
                    'notify' => ($stats['new_contact_messages_count'] ?? 0) > 0,
                ],
                [
                    'section' => 'blogs',
                    'pill' => 'v-pills-blogs',
                    'icon' => 'fa-blog',
                    'title' => 'Blogs',
                    'description' => 'Articles et SEO',
                    'badge' => $stats['total_blog_posts'] ?? 0,
                ],
                [
                    'section' => 'ecommerce',
                    'pill' => 'v-pills-ecommerce',
                    'icon' => 'fa-shopping-cart',
                    'title' => 'E-commerce',
                    'description' => 'Produits et commandes',
                    'badge' => $stats['active_orders_count'] ?? 0,
                ],
                [
                    'section' => 'seo',
                    'pill' => 'v-pills-seo',
                    'icon' => 'fa-search',
                    'title' => 'SEO',
                    'description' => 'Optimisation moteurs',
                    'badge' => null,
                ],
                [
                    'section' => 'social',
                    'pill' => 'v-pills-social',
                    'icon' => 'fa-share-alt',
                    'title' => 'Réseaux sociaux',
                    'description' => 'Liens et partages',
                    'badge' => null,
                ],
                [
                    'section' => 'newsletter',
                    'pill' => 'v-pills-newsletter',
                    'icon' => 'fa-envelope-open-text',
                    'title' => 'Newsletter',
                    'description' => 'Campagnes emails',
                    'badge' => null,
                ],
                [
                    'section' => 'subscribers',
                    'pill' => 'v-pills-subscribers',
                    'icon' => 'fa-users',
                    'title' => 'Abonnés',
                    'description' => 'Liste des contacts',
                    'badge' => $stats['subscribers_count'] ?? 0,
                ],
                [
                    'section' => 'comments',
                    'pill' => 'v-pills-comments',
                    'icon' => 'fa-comments',
                    'title' => 'Commentaires',
                    'description' => 'Modération',
                    'badge' => $notificationsCount > 0 ? $notificationsCount : ($stats['comments_count'] ?? 0),
                    'notify' => $notificationsCount > 0,
                ],
            ];
        @endphp

        <div class="profile-header-modern">
            <div class="profile-avatar-modern">
                @if(has_logo())
                    <img src="{{ get_logo_url() }}" alt="Logo de l'établissement" class="avatar-image">
                    <style>
                        .avatar-image {
                            width: 60px;
                            height: 60px;
                            object-fit: cover;
                            border-radius: 50%;
                        }
                    </style>
                @else
                    <div class="avatar-gradient">
                        <i class="fas fa-building"></i>
                    </div>
                @endif
                <div class="avatar-status {{ ($stats['etablissement']->is_active ?? false) ? 'active' : 'inactive' }}"></div>
            </div>
            <div class="profile-info-modern">
                <h3 class="profile-name-modern">{{ $stats['etablissement']->name ?? 'Établissement' }}</h3>
                <p class="profile-type-modern">{{ $stats['etablissement']->lname ?? 'CMS Management' }}</p>
                <div class="profile-badge-modern">
                    @if(($stats['etablissement']->is_active ?? false))
                        <span class="badge-active"><i class="fas fa-check-circle"></i> Actif</span>
                    @else
                        <span class="badge-inactive"><i class="fas fa-circle"></i> Inactif</span>
                    @endif
                </div>
            </div>
            <a href="{{ $cmsPublicSiteUrl }}" class="profile-site-link" target="_blank" rel="noopener" title="Ouvrir le site web">
                <i class="fas fa-globe"></i>
            </a>
        </div>

        <div class="tabs-search-modern">
            <div class="search-input-wrapper">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="tabSearchInput" placeholder="Rechercher un menu..." class="search-input">
            </div>
        </div>

        <div class="nav flex-column vertical-tabs-modern">
            @foreach($cmsTabs as $tab)
                @php
                    $activeSection = request()->get('section', 'dashboard');
                    $isActive = $activeSection === $tab['section'];
                    $onclick = $tab['onclick'] ?? "navigateTo('{$tab['pill']}'); return false;";
                @endphp
                <a href="{{ route('cms.admin.dashboard', ['etablissementId' => $stats['etablissement']->id, 'slug' => $cmsDashboardSlug, 'section' => $tab['section']]) }}"
                   class="nav-link-modern {{ $isActive ? 'active' : '' }}"
                   data-section="{{ $tab['pill'] }}"
                   onclick="{{ $onclick }}">
                    <div class="tab-icon-wrapper">
                        <i class="fas {{ $tab['icon'] }}"></i>
                    </div>
                    <div class="tab-content-wrapper-modern">
                        <span class="tab-title-modern">{{ $tab['title'] }}</span>
                        <span class="tab-description">{{ $tab['description'] }}</span>
                    </div>
                    <div class="tab-badge">
                        @if($tab['badge'] !== null)
                            <span class="badge-count {{ ($tab['notify'] ?? false) ? 'has-notification' : '' }}">{{ $tab['badge'] }}</span>
                        @endif
                    </div>
                </a>
            @endforeach

            <div class="divider-modern"></div>

            <a href="{{ route('cms.admin.dashboard', ['etablissementId' => $stats['etablissement']->id, 'slug' => $cmsDashboardSlug, 'section' => 'danger']) }}"
               class="nav-link-modern danger {{ request()->get('section') == 'danger' ? 'active' : '' }}"
               data-section="v-pills-danger"
               onclick="navigateTo('v-pills-danger'); return false;">
                <div class="tab-icon-wrapper">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="tab-content-wrapper-modern">
                    <span class="tab-title-modern">Zone de danger</span>
                    <span class="tab-description">Actions sensibles</span>
                </div>
                <div class="tab-badge"></div>
            </a>
        </div>
    </div>
</div>
