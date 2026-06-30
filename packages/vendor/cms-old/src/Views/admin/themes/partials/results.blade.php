<div id="globalThemesResults">
    @if($templates->isEmpty())
        <div class="empty-global-state">
            <div class="eg-icon"><i class="fas fa-file-code"></i></div>
            <h3>Aucun template trouve</h3>
            <p>Ajustez la recherche ou la categorie pour afficher plus de templates.</p>
            @if(($filters['search'] ?? '') !== '' || ($filters['category'] ?? '') !== '')
                <a href="{{ route('cms.admin.themes.index') }}" class="btn-header-secondary mx-auto">
                    <i class="fas fa-times me-2"></i>Reinitialiser les filtres
                </a>
            @else
                <button class="btn-header-primary mx-auto" onclick="openTemplateCreate()">
                    <i class="fas fa-plus me-2"></i>Creer un template
                </button>
            @endif
        </div>
    @else
        <div class="global-templates-grid" id="globalThemesGrid">
            @foreach($templates as $template)
                @include('cms::admin.themes.partials.theme-card', ['template' => $template, 'theme' => $template])
            @endforeach
        </div>

        @if($templates->hasPages())
            <div class="pagination-modern mt-4">
                {{ $templates->links('pagination::bootstrap-5') }}
            </div>
        @endif
    @endif
</div>
