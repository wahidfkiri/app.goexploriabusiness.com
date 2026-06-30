<div class="tab-pane fade show active" id="v-pills-dashboard" role="tabpanel">
    <div class="tab-content-header">
        <h3 class="tab-title">
            <i class="fas fa-tachometer-alt me-2" style="color: var(--primary-color);"></i>
            Tableau de bord
        </h3>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="info-card">
                <div class="info-card-header">
                    <i class="fas fa-check-circle text-success"></i>
                    <h5>Statut du site</h5>
                </div>
                <div class="info-card-body">
                    <p>Thème actif: <strong>{{ $stats['active_theme']->template->name ?? $stats['active_theme']->name ?? 'Aucun thème actif' }}</strong></p>
                    <p>Dernière mise à jour: <strong>{{ now()->format('d/m/Y H:i') }}</strong></p>
                    <p>Pages publiées: <strong>{{ $stats['published_pages'] ?? 0 }}</strong></p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="info-card">
                <div class="info-card-header">
                    <i class="fas fa-globe text-primary"></i>
                    <h5>URL du site</h5>
                </div>
                <div class="info-card-body">
                    <p><a href="{{ $stats['site_url'] }}" target="_blank">
                        {{ $stats['site_url'] }}
                    </a></p>
                    <p class="text-muted small">Voir le site en direct</p>
                    <hr>
                    <p>Page d'accueil: <strong>{{ $stats['homepage'] ?? 'Non définie' }}</strong></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-4">
        <div class="col-lg-6">
            <div class="info-card h-100">
                <div class="info-card-header">
                    <i class="fas fa-layer-group text-primary"></i>
                    <h5>Templates récents</h5>
                </div>
                <div class="table-container-modern">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Template</th>
                                <th>Statut</th>
                                <th>Dernière modification</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stats['recent_themes'] ?? [] as $installedTemplate)
                                @php
                                    $template = $installedTemplate->template;
                                    $templatePage = $installedTemplate->page;
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $template->name ?? 'Template sans nom' }}</strong><br>
                                        <small class="text-muted">{{ $template->category ?? 'Sans catégorie' }} - v{{ $template->version ?? '1.0.0' }}</small>
                                    </td>
                                    <td>
                                        @if($installedTemplate->is_active)
                                            <span class="badge bg-success">Actif</span>
                                        @else
                                            <span class="badge bg-secondary">Installé</span>
                                        @endif
                                    </td>
                                    <td>{{ optional($installedTemplate->updated_at)->diffForHumans() ?? '-' }}</td>
                                    <td>
                                        @if($templatePage)
                                            <a href="{{ route('cms.admin.pages.edit-content', ['etablissementId' => $stats['etablissement']->id, 'id' => $templatePage->id]) }}"
                                               target="_blank"
                                               class="btn btn-sm btn-outline-primary"
                                               title="Modifier le contenu">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                        <a href="{{ route('cms.admin.dashboard', ['etablissementId' => $stats['etablissement']->id, 'slug' => $stats['etablissement']->slug ?: $stats['site_slug']]) }}?section=themes"
                                           class="btn btn-sm btn-outline-secondary"
                                           title="Voir les templates">
                                            <i class="fas fa-list"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Aucun template installé pour le moment</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="info-card h-100">
                <div class="info-card-header">
                    <i class="fas fa-box-open text-success"></i>
                    <h5>Produits récents</h5>
                </div>
                <div class="table-container-modern">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th>Prix</th>
                                <th>Disponibilité</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stats['recent_products'] ?? [] as $product)
                                <tr>
                                    <td>
                                        <strong>{{ $product->name }}</strong><br>
                                        <small class="text-muted">{{ $product->reference ?: $product->main_type }}</small>
                                    </td>
                                    <td>{{ number_format((float) $product->price_ttc, 2, ',', ' ') }} EUR</td>
                                    <td>
                                        @if($product->is_available_for_sale)
                                            <span class="badge bg-success">En vente</span>
                                        @else
                                            <span class="badge bg-secondary">Masqué</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('products.edit', $product) }}" target="_blank" class="btn btn-sm btn-outline-primary" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('products.show', $product) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Voir">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Aucun produit créé pour le moment</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="recent-pages-section mt-4">
        <h5>Articles blog récents</h5>
        <div class="table-container-modern">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Statut</th>
                        <th>Dernière modification</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stats['recent_blog_posts'] ?? [] as $post)
                    <tr>
                        <td>
                            <i class="fas fa-blog me-2" style="color: var(--primary-color);"></i>
                            {{ $post->title }}
                        </td>
                        <td>
                            @if($post->status === 'published')
                                <span class="badge bg-success">Publié</span>
                            @elseif($post->status === 'archived')
                                <span class="badge bg-secondary">Archivé</span>
                            @else
                                <span class="badge bg-warning text-dark">Brouillon</span>
                            @endif
                        </td>
                        <td>{{ $post->updated_at->diffForHumans() }}</td>
                        <td>
                            <a href="{{ route('cms.admin.blogs.edit', ['etablissementId' => $stats['etablissement']->id, 'id' => $post->id]) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="{{ route('cms.company.blog.show', ['etablissementId' => $stats['etablissement']->id, 'slug' => $post->slug]) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center">Aucun article de blog pour le moment</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
