@php
    $linkedTemplates = $stats['linked_templates'] ?? collect();
    $allTemplates = $stats['all_global_templates'] ?? collect();
    $activeTemplate = $stats['active_template'] ?? null;
    $installedTemplateIds = $stats['installed_template_ids'] ?? [];
    $availableTemplates = $allTemplates->reject(fn ($template) => in_array($template->id, $installedTemplateIds));
    $availableTemplateCategories = $availableTemplates
        ->map(fn ($template) => trim((string) ($template->category ?: 'Autres')))
        ->filter()
        ->unique()
        ->sort()
        ->values();
@endphp

<div class="tab-pane fade" id="v-pills-themes" role="tabpanel">
    <div class="tab-content-header">
        <h3 class="tab-title">
            <i class="fas fa-file-code me-2" style="color: #2563eb;"></i>
            Templates
        </h3>
        <a href="{{ route('cms.admin.themes.index') }}" class="btn btn-outline-secondary btn-sm me-2">
            <i class="fas fa-globe me-1"></i>Gerer les templates CMS
        </a>
    </div>

    <div id="templatesPanel" class="templates-panel active">

    @if($activeTemplate && $activeTemplate->template)
        <div class="active-template-banner mb-4">
            <div class="active-template-inner">
                <div class="active-template-preview">
                    @if($activeTemplate->template->getPreviewImageUrl())
                        <img src="{{ $activeTemplate->template->getPreviewImageUrl() }}" alt="{{ $activeTemplate->template->name }}">
                    @else
                        <div class="preview-placeholder-sm"><i class="fas fa-file-code"></i></div>
                    @endif
                </div>
                <div class="active-template-info">
                    <div class="active-label"><i class="fas fa-check-circle me-1"></i>Template actif</div>
                    <h4>{{ $activeTemplate->template->name }}</h4>
                    <span class="version-tag">v{{ $activeTemplate->template->version }}</span>
                    @if($activeTemplate->page)
                        <span class="version-tag ms-1">Page #{{ $activeTemplate->page->id }}</span>
                    @endif
                </div>
                <div class="active-template-actions ms-auto">
                    <a href="{{ route('cms.admin.etab.themes.preview', ['etablissementId' => $stats['etablissement']->id, 'id' => $activeTemplate->template_cms_id]) }}"
                       class="btn btn-sm btn-outline-light" target="_blank">
                        <i class="fas fa-eye me-1"></i>Preview
                    </a>
                    @if($activeTemplate->page)
                        <a href="{{ route('cms.admin.pages.edit-content', ['etablissementId' => $stats['etablissement']->id, 'id' => $activeTemplate->page->id]) }}"
                           class="btn btn-sm btn-light" target="_blank" rel="noopener">
                            <i class="fas fa-edit me-1"></i>Editer page
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <div class="section-label mb-3">
        <i class="fas fa-link me-1"></i>
        Templates lies a cet etablissement <span class="badge bg-secondary ms-1">{{ $linkedTemplates->count() }}</span>
    </div>

    @if($linkedTemplates->isEmpty())
        <div class="empty-themes-state">
            <i class="fas fa-file-code"></i>
            <h4>Aucun template installe</h4>
            <p>Installez un template depuis la liste disponible ci-dessous.</p>
        </div>
    @else
        <div class="templates-grid-dashboard mb-4">
            @foreach($linkedTemplates as $installed)
                @php
                    $template = $installed->template;
                @endphp
                @if($template)
                    <div class="template-card-dash {{ $installed->is_active ? 'is-active' : '' }}" id="template-card-installed-{{ $template->id }}">
                        <div class="tcd-preview">
                            @if($template->getPreviewImageUrl())
                                <img src="{{ $template->getPreviewImageUrl() }}" alt="{{ $template->name }}">
                            @else
                                <div class="tcd-preview-placeholder"><i class="fas fa-file-code"></i></div>
                            @endif
                            @if($installed->is_active)
                                <div class="tcd-active-ribbon"><i class="fas fa-check"></i> Actif</div>
                            @endif
                        </div>
                        <div class="tcd-body">
                            <div class="tcd-header">
                                <h5 class="tcd-name">{{ $template->name }}</h5>
                                <span class="tcd-version">v{{ $template->version }}</span>
                            </div>
                            <p class="tcd-desc">{{ Str::limit($template->description ?: 'Template installe pour cet etablissement.', 82) }}</p>
                            <div class="tcd-actions">
                                @if($installed->is_active)
                                    <button class="tcd-btn tcd-btn-active" disabled><i class="fas fa-check-circle"></i> Actif</button>
                                @else
                                    <button class="tcd-btn tcd-btn-activate" onclick="activateTemplateDash({{ $template->id }}, this)">
                                        <i class="fas fa-random"></i> Switch
                                    </button>
                                @endif
                                @if($installed->page)
                                    <a href="{{ route('cms.admin.pages.edit-content', ['etablissementId' => $stats['etablissement']->id, 'id' => $installed->page->id]) }}"
                                       class="tcd-btn tcd-btn-edit" target="_blank" rel="noopener" title="Editer">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif
                                <a href="{{ route('cms.admin.etab.themes.preview', ['etablissementId' => $stats['etablissement']->id, 'id' => $template->id]) }}"
                                   class="tcd-btn tcd-btn-preview" target="_blank" title="Preview">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    @endif

    <div class="section-label mb-3">
        <i class="fas fa-download me-1"></i>
        Templates possibles a installer <span class="badge bg-secondary ms-1">{{ $availableTemplates->count() }}</span>
    </div>

    @if($availableTemplates->isEmpty())
        <div class="empty-themes-state compact">
            <i class="fas fa-check-circle"></i>
            <h4>Tous les templates disponibles sont deja installes</h4>
        </div>
    @else
        <div class="available-template-filters mb-3" id="availableTemplateCategoryFilters">
            <button type="button" class="template-category-filter active" data-category="all" onclick="filterAvailableTemplatesByCategory('all', this)">
                Tous <span>{{ $availableTemplates->count() }}</span>
            </button>
            @foreach($availableTemplateCategories as $category)
                @php
                    $categoryCount = $availableTemplates->filter(fn ($template) => trim((string) ($template->category ?: 'Autres')) === $category)->count();
                @endphp
                <button type="button" class="template-category-filter" data-category="{{ e($category) }}" onclick="filterAvailableTemplatesByCategory(@json($category), this)">
                    {{ $category }} <span>{{ $categoryCount }}</span>
                </button>
            @endforeach
        </div>

        <div class="templates-grid-dashboard" id="availableTemplatesGrid">
            @foreach($availableTemplates as $template)
                @php
                    $templateCategory = trim((string) ($template->category ?: 'Autres'));
                @endphp
                <div class="template-card-dash" id="template-card-available-{{ $template->id }}" data-template-category="{{ e($templateCategory) }}">
                    <div class="tcd-preview">
                        @if($template->getPreviewImageUrl())
                            <img src="{{ $template->getPreviewImageUrl() }}" alt="{{ $template->name }}">
                        @else
                            <div class="tcd-preview-placeholder"><i class="fas fa-file-code"></i></div>
                        @endif
                    </div>
                    <div class="tcd-body">
                        <div class="tcd-header">
                            <h5 class="tcd-name">{{ $template->name }}</h5>
                            <span class="tcd-version">v{{ $template->version }}</span>
                        </div>
                        <div class="tcd-category">{{ $templateCategory }}</div>
                        <p class="tcd-desc">{{ Str::limit($template->description ?: 'Template CMS pret a installer.', 82) }}</p>
                        <div class="tcd-actions">
                            <button class="tcd-btn tcd-btn-install" onclick="installTemplateDash({{ $template->id }}, this)">
                                <i class="fas fa-download"></i> Installer
                            </button>
                            <a href="{{ route('cms.admin.etab.themes.preview', ['etablissementId' => $stats['etablissement']->id, 'id' => $template->id]) }}"
                               class="tcd-btn tcd-btn-preview" target="_blank" title="Preview">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="empty-themes-state compact d-none" id="availableTemplatesEmptyFilter">
            <i class="fas fa-filter"></i>
            <h4>Aucun template dans cette categorie</h4>
        </div>
    @endif
    </div>

    <div id="blocksPanel" class="templates-panel">
        <div class="blocks-admin-header mb-3">
            <div>
                <div class="section-label mb-1"><i class="fas fa-cubes me-1"></i>Blocs CMS</div>
                <p class="blocks-admin-subtitle">Gerer les blocs reutilisables dans l'editeur de pages.</p>
            </div>
            <button type="button" class="btn btn-primary btn-sm" onclick="openBlockForm()">
                <i class="fas fa-plus me-1"></i>Nouveau bloc
            </button>
        </div>

        <div class="blocks-admin-toolbar mb-3">
            <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="search" class="form-control" id="blocksAdminSearch" placeholder="Rechercher un bloc..." oninput="filterBlocksAdmin()">
            </div>
        </div>

        <div class="blocks-admin-grid" id="blocksAdminGrid">
            <div class="blocks-loading">Chargement des blocs...</div>
        </div>
    </div>

    <div class="modal fade" id="blockFormModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <form id="blockForm" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="blockFormTitle">Nouveau bloc</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="blockId" name="id">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nom</label>
                                <input type="text" class="form-control" name="name" id="blockName" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Icone FontAwesome</label>
                                <input type="text" class="form-control" name="icon" id="blockIcon" placeholder="fa-cube">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Ordre</label>
                                <input type="number" class="form-control" name="order" id="blockOrder" value="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Section</label>
                                <select class="form-select" name="section_id" id="blockSection">
                                    <option value="">Aucune section</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Categorie</label>
                                <input type="text" class="form-control" name="category" id="blockCategory" value="Basic">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Type de site</label>
                                <input type="text" class="form-control" name="website_type" id="blockWebsiteType" value="General">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Largeur</label>
                                <input type="number" class="form-control" name="width" id="blockWidth">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Hauteur</label>
                                <input type="number" class="form-control" name="height" id="blockHeight">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Image preview</label>
                                <input type="file" class="form-control" name="thumbnail" id="blockThumbnail" accept="image/*">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description" id="blockDescription" rows="2"></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Tags</label>
                                <input type="text" class="form-control" name="tags" id="blockTags" placeholder="hero, responsive, landing">
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_responsive" id="blockResponsive" value="1" checked>
                                    <label class="form-check-label" for="blockResponsive">Responsive</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_free" id="blockFree" value="1" checked>
                                    <label class="form-check-label" for="blockFree">Gratuit</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="blockActive" value="1" checked>
                                    <label class="form-check-label" for="blockActive">Actif</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">HTML</label>
                                <textarea class="form-control block-code-area" name="html_content" id="blockHtml" required></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">CSS</label>
                                <textarea class="form-control block-code-area small" name="css_content" id="blockCss"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">JS</label>
                                <textarea class="form-control block-code-area small" name="js_content" id="blockJs"></textarea>
                            </div>
                        </div>
                        <div class="alert alert-danger mt-3 d-none" id="blockFormErrors"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary" id="blockFormSubmit">
                            <i class="fas fa-save me-1"></i>Sauvegarder
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.templates-panel { display: none; }
.templates-panel.active { display: block; }
.active-template-banner { background: linear-gradient(135deg, #1e293b, #334155); border-radius: 14px; padding: 20px; color: white; }
.active-template-inner { display: flex; align-items: center; gap: 16px; }
.active-template-preview { width: 96px; height: 64px; border-radius: 10px; overflow: hidden; background: rgba(255,255,255,.1); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.active-template-preview img { width: 100%; height: 100%; object-fit: cover; }
.preview-placeholder-sm { color: rgba(255,255,255,.45); font-size: 1.6rem; }
.active-label { font-size: .72rem; color: #22c55e; font-weight: 700; letter-spacing: .04em; margin-bottom: 4px; text-transform: uppercase; }
.active-template-info h4 { margin: 0 0 5px; font-size: 1.1rem; font-weight: 700; }
.version-tag { font-size: .72rem; background: rgba(255,255,255,.15); padding: 2px 8px; border-radius: 999px; color: rgba(255,255,255,.78); }
.section-label { font-size: .82rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .05em; }
.templates-grid-dashboard { display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 16px; }
.available-template-filters { display: flex; flex-wrap: wrap; gap: 8px; }
.template-category-filter { align-items: center; background: #fff; border: 1px solid #dbe3ef; border-radius: 999px; color: #475569; cursor: pointer; display: inline-flex; font-size: .78rem; font-weight: 800; gap: 7px; min-height: 34px; padding: 0 12px; transition: all .18s ease; }
.template-category-filter span { background: #f1f5f9; border-radius: 999px; color: #64748b; font-size: .68rem; line-height: 1; padding: 4px 7px; }
.template-category-filter:hover { border-color: #93c5fd; color: #1d4ed8; transform: translateY(-1px); }
.template-category-filter.active { background: #2563eb; border-color: #2563eb; color: #fff; box-shadow: 0 8px 18px rgba(37,99,235,.22); }
.template-category-filter.active span { background: rgba(255,255,255,.18); color: #fff; }
.template-card-dash { background: #fff; border-radius: 12px; overflow: hidden; border: 2px solid transparent; box-shadow: 0 2px 8px rgba(15,23,42,.07); transition: all .22s ease; }
.template-card-dash.is-filter-hidden { display: none; }
.template-card-dash:hover { transform: translateY(-3px); box-shadow: 0 10px 24px rgba(15,23,42,.12); }
.template-card-dash.is-active { border-color: #22c55e; box-shadow: 0 4px 16px rgba(34,197,94,.2); }
.tcd-preview { position: relative; height: 150px; background: linear-gradient(135deg,#f1f5f9,#e2e8f0); display: flex; align-items: center; justify-content: center; overflow: hidden; }
.tcd-preview img { width: 100%; height: 100%; object-fit: cover; }
.tcd-preview-placeholder { font-size: 2.5rem; color: #94a3b8; }
.tcd-active-ribbon { position: absolute; top: 10px; left: 10px; background: #22c55e; color: #fff; font-size: .7rem; font-weight: 700; padding: 3px 10px; border-radius: 999px; }
.tcd-body { padding: 14px; }
.tcd-header { display: flex; justify-content: space-between; align-items: baseline; gap: 8px; margin-bottom: 6px; }
.tcd-name { font-size: .96rem; font-weight: 700; color: #1e293b; margin: 0; }
.tcd-version { font-size: .7rem; color: #64748b; background: #f1f5f9; padding: 2px 8px; border-radius: 999px; }
.tcd-category { display: inline-flex; align-items: center; background: #eff6ff; border-radius: 999px; color: #1d4ed8; font-size: .68rem; font-weight: 800; margin-bottom: 9px; max-width: 100%; padding: 3px 9px; }
.tcd-desc { font-size: .78rem; color: #64748b; margin-bottom: 12px; line-height: 1.45; min-height: 34px; }
.tcd-actions { display: flex; gap: 6px; }
.tcd-btn { flex: 1; padding: 7px 10px; border-radius: 8px; font-size: .78rem; font-weight: 700; border: none; cursor: pointer; transition: all .2s ease; display: flex; align-items: center; justify-content: center; gap: 5px; text-decoration: none; }
.tcd-btn-install, .tcd-btn-activate { background: #2563eb; color: #fff; }
.tcd-btn-active { background: #dcfce7; color: #15803d; cursor: default; }
.tcd-btn-edit { background: #e0f2fe; color: #0369a1; flex: 0 0 38px; padding: 7px; }
.tcd-btn-preview { background: #f1f5f9; color: #475569; flex: 0 0 38px; padding: 7px; }
.empty-themes-state { text-align: center; padding: 42px 20px; color: #94a3b8; border: 1px dashed #cbd5e1; border-radius: 12px; background: #fff; margin-bottom: 24px; }
.empty-themes-state.compact { padding: 28px 20px; }
.empty-themes-state i { font-size: 2.4rem; margin-bottom: 12px; display: block; }
.empty-themes-state h4 { color: #1e293b; margin-bottom: 8px; }
.blocks-admin-header { display: flex; align-items: center; justify-content: space-between; gap: 16px; }
.blocks-admin-subtitle { margin: 0; color: #64748b; font-size: .86rem; }
.blocks-admin-toolbar { max-width: 420px; }
.blocks-admin-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px; }
.blocks-loading { grid-column: 1 / -1; padding: 28px; text-align: center; color: #64748b; background: #fff; border: 1px dashed #cbd5e1; border-radius: 12px; }
.block-admin-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(15,23,42,.06); }
.block-admin-preview { height: 132px; background: #f8fafc; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden; }
.block-admin-preview img { width: 100%; height: 100%; object-fit: cover; }
.block-preview-design { position: absolute; top: 0; left: 50%; width: 1000px; height: 520px; border: 0; background: #fff; transform: translateX(-50%) scale(.28); transform-origin: top center; pointer-events: none; }
.block-preview-design-overlay { position: absolute; inset: 0; background: linear-gradient(180deg, rgba(15,23,42,0) 62%, rgba(15,23,42,.12)); pointer-events: none; }
.block-admin-placeholder { color: #94a3b8; font-size: 2.3rem; }
.block-admin-status { position: absolute; top: 9px; right: 9px; padding: 3px 8px; border-radius: 999px; font-size: .66rem; font-weight: 800; background: #dcfce7; color: #15803d; z-index: 2; }
.block-admin-status.inactive { background: #fee2e2; color: #991b1b; }
.block-admin-body { padding: 14px; }
.block-admin-title { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 7px; }
.block-admin-title h5 { margin: 0; font-size: .98rem; font-weight: 800; color: #1e293b; }
.block-admin-desc { min-height: 38px; color: #64748b; font-size: .8rem; line-height: 1.45; margin-bottom: 10px; }
.block-admin-meta { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 12px; }
.block-admin-pill { background: #f1f5f9; color: #475569; font-size: .68rem; font-weight: 700; padding: 3px 8px; border-radius: 999px; }
.block-admin-actions { display: flex; gap: 7px; }
.block-admin-btn { border: 0; border-radius: 8px; height: 34px; min-width: 36px; padding: 0 10px; display: inline-flex; align-items: center; justify-content: center; gap: 6px; font-size: .78rem; font-weight: 700; text-decoration: none; }
.block-admin-btn.edit { background: #e0f2fe; color: #0369a1; }
.block-admin-btn.preview { background: #f1f5f9; color: #475569; }
.block-admin-btn.delete { background: #fee2e2; color: #991b1b; margin-left: auto; }
.block-code-area { min-height: 220px; font-family: Consolas, Monaco, "Courier New", monospace; font-size: 13px; line-height: 1.45; background: #0f172a; color: #e2e8f0; border-color: #334155; }
.block-code-area.small { min-height: 150px; }
@media (max-width: 768px) { .templates-grid-dashboard { grid-template-columns: 1fr 1fr; } .active-template-inner { flex-wrap: wrap; } }
@media (max-width: 480px) { .templates-grid-dashboard { grid-template-columns: 1fr; } }
</style>

<script>
let blocksAdminItems = [];
let blocksAdminSections = [];
let blocksAdminLoaded = false;

function showTemplatesPanel(panel) {
    document.querySelectorAll('.templates-subnav-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.templatePanel === panel);
    });
    document.getElementById('templatesPanel')?.classList.toggle('active', panel === 'templates');
    document.getElementById('blocksPanel')?.classList.toggle('active', panel === 'blocks');

    if (panel === 'blocks' && !blocksAdminLoaded) {
        loadBlocksAdmin();
    }
}

function filterAvailableTemplatesByCategory(category, button) {
    const normalizedCategory = String(category || 'all');
    const cards = document.querySelectorAll('#availableTemplatesGrid .template-card-dash');
    let visibleCount = 0;

    document.querySelectorAll('#availableTemplateCategoryFilters .template-category-filter').forEach(item => {
        item.classList.toggle('active', item === button);
    });

    cards.forEach(card => {
        const cardCategory = card.dataset.templateCategory || 'Autres';
        const isVisible = normalizedCategory === 'all' || cardCategory === normalizedCategory;
        card.classList.toggle('is-filter-hidden', !isVisible);
        if (isVisible) visibleCount++;
    });

    document.getElementById('availableTemplatesEmptyFilter')?.classList.toggle('d-none', visibleCount > 0);
}

async function loadBlocksAdmin() {
    const grid = document.getElementById('blocksAdminGrid');
    if (grid) grid.innerHTML = '<div class="blocks-loading">Chargement des blocs...</div>';

    try {
        const response = await fetch(`/admin/cms/${currentEtablissementId}/blocks/manage`, {
            headers: { 'Accept': 'application/json' },
        });
        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message || 'Erreur');
        }

        blocksAdminItems = data.blocks || [];
        blocksAdminSections = data.sections || [];
        blocksAdminLoaded = true;
        populateBlockSections();
        renderBlocksAdmin(blocksAdminItems);
    } catch (error) {
        if (grid) grid.innerHTML = '<div class="blocks-loading">Impossible de charger les blocs.</div>';
    }
}

function populateBlockSections() {
    const select = document.getElementById('blockSection');
    if (!select) return;

    select.innerHTML = '<option value="">Aucune section</option>';
    blocksAdminSections.forEach(section => {
        const option = document.createElement('option');
        option.value = section.id;
        option.textContent = section.name;
        select.appendChild(option);
    });
}

function renderBlocksAdmin(blocks) {
    const grid = document.getElementById('blocksAdminGrid');
    if (!grid) return;

    if (!blocks.length) {
        grid.innerHTML = '<div class="blocks-loading">Aucun bloc trouve.</div>';
        return;
    }

    grid.innerHTML = blocks.map(block => `
        <div class="block-admin-card" data-block-id="${block.id}">
            <div class="block-admin-preview">
                ${renderBlockPreviewSlot(block)}
                <span class="block-admin-status ${block.is_active ? '' : 'inactive'}">${block.is_active ? 'ACTIF' : 'INACTIF'}</span>
            </div>
            <div class="block-admin-body">
                <div class="block-admin-title">
                    <h5>${escapeBlockAdmin(block.name)}</h5>
                    <span class="block-admin-pill">${escapeBlockAdmin(block.category || 'Basic')}</span>
                </div>
                <p class="block-admin-desc">${escapeBlockAdmin(block.description || 'Aucune description.')}</p>
                <div class="block-admin-meta">
                    <span class="block-admin-pill">${escapeBlockAdmin(block.section_name || 'Sans section')}</span>
                    <span class="block-admin-pill">${escapeBlockAdmin(block.website_type || 'General')}</span>
                    ${block.is_responsive ? '<span class="block-admin-pill">Responsive</span>' : ''}
                </div>
                <div class="block-admin-actions">
                    <button type="button" class="block-admin-btn edit" onclick="openBlockForm(${block.id})"><i class="fas fa-edit"></i></button>
                    <a class="block-admin-btn preview" href="${escapeBlockAdmin(block.preview_url)}" target="_blank" title="Preview"><i class="fas fa-eye"></i></a>
                    <button type="button" class="block-admin-btn delete" onclick="deleteBlockAdmin(${block.id}, this)"><i class="fas fa-trash"></i></button>
                </div>
            </div>
        </div>
    `).join('');

    hydrateBlockPreviewFrames(blocks);
}

function renderBlockPreviewSlot(block) {
    if (String(block.html_content || '').trim() !== '') {
        return `
            <iframe class="block-preview-design"
                    data-block-preview-id="${Number(block.id)}"
                    title="${escapeBlockAdmin(block.name)}"
                    loading="lazy"
                    sandbox=""></iframe>
            <span class="block-preview-design-overlay"></span>
        `;
    }

    if (block.thumbnail_url) {
        return `<img src="${escapeBlockAdmin(block.thumbnail_url)}" alt="${escapeBlockAdmin(block.name)}">`;
    }

    return `<div class="block-admin-placeholder"><i class="fas ${escapeBlockAdmin(block.icon || 'fa-cube')}"></i></div>`;
}

function hydrateBlockPreviewFrames(blocks) {
    const blockMap = new Map(blocks.map(block => [String(block.id), block]));

    document.querySelectorAll('.block-preview-design[data-block-preview-id]').forEach(frame => {
        const block = blockMap.get(frame.dataset.blockPreviewId);
        if (!block) return;

        frame.srcdoc = buildBlockPreviewSrcdoc(block);
    });
}

function buildBlockPreviewSrcdoc(block) {
    return `<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        * { box-sizing: border-box; }
        html, body { margin: 0; min-height: 100%; background: #fff; overflow: hidden; }
        body { font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        img, video { max-width: 100%; height: auto; }
        .block-preview-stage { width: 1000px; min-height: 520px; background: #fff; overflow: hidden; }
        ${block.css_content || ''}
    </style>
</head>
<body>
    <div class="block-preview-stage">${block.html_content || ''}</div>
</body>
</html>`;
}

function filterBlocksAdmin() {
    const term = (document.getElementById('blocksAdminSearch')?.value || '').toLowerCase();
    const filtered = blocksAdminItems.filter(block => [
        block.name,
        block.description,
        block.category,
        block.website_type,
        block.section_name,
    ].some(value => String(value || '').toLowerCase().includes(term)));

    renderBlocksAdmin(filtered);
}

function openBlockForm(blockId = null) {
    const form = document.getElementById('blockForm');
    const title = document.getElementById('blockFormTitle');
    const errors = document.getElementById('blockFormErrors');
    form.reset();
    errors.classList.add('d-none');
    errors.innerHTML = '';
    document.getElementById('blockId').value = '';
    document.getElementById('blockResponsive').checked = true;
    document.getElementById('blockFree').checked = true;
    document.getElementById('blockActive').checked = true;

    if (blockId) {
        const block = blocksAdminItems.find(item => Number(item.id) === Number(blockId));
        if (!block) return;
        title.textContent = 'Modifier le bloc';
        document.getElementById('blockId').value = block.id;
        document.getElementById('blockName').value = block.name || '';
        document.getElementById('blockIcon').value = block.icon || 'fa-cube';
        document.getElementById('blockOrder').value = block.order || 0;
        document.getElementById('blockSection').value = block.section_id || '';
        document.getElementById('blockCategory').value = block.category || 'Basic';
        document.getElementById('blockWebsiteType').value = block.website_type || 'General';
        document.getElementById('blockWidth').value = block.width || '';
        document.getElementById('blockHeight').value = block.height || '';
        document.getElementById('blockDescription').value = block.description || '';
        document.getElementById('blockTags').value = Array.isArray(block.tags) ? block.tags.join(', ') : '';
        document.getElementById('blockResponsive').checked = !!block.is_responsive;
        document.getElementById('blockFree').checked = !!block.is_free;
        document.getElementById('blockActive').checked = !!block.is_active;
        document.getElementById('blockHtml').value = block.html_content || '';
        document.getElementById('blockCss').value = block.css_content || '';
        document.getElementById('blockJs').value = block.js_content || '';
    } else {
        title.textContent = 'Nouveau bloc';
        document.getElementById('blockIcon').value = 'fa-cube';
        document.getElementById('blockCategory').value = 'Basic';
        document.getElementById('blockWebsiteType').value = 'General';
        document.getElementById('blockOrder').value = '0';
    }

    new bootstrap.Modal(document.getElementById('blockFormModal')).show();
}

document.getElementById('blockForm')?.addEventListener('submit', async function (event) {
    event.preventDefault();

    const blockId = document.getElementById('blockId').value;
    const errors = document.getElementById('blockFormErrors');
    const button = document.getElementById('blockFormSubmit');
    const formData = new FormData(this);

    if (!document.getElementById('blockResponsive').checked) formData.set('is_responsive', '0');
    if (!document.getElementById('blockFree').checked) formData.set('is_free', '0');
    if (!document.getElementById('blockActive').checked) formData.set('is_active', '0');
    if (blockId) formData.append('_method', 'PUT');

    errors.classList.add('d-none');
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Sauvegarde...';

    try {
        const response = await fetch(blockId ? `/admin/cms/${currentEtablissementId}/blocks/${blockId}` : `/admin/cms/${currentEtablissementId}/blocks`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: formData,
        });
        const data = await response.json();

        if (!response.ok || !data.success) {
            errors.innerHTML = data.errors ? Object.values(data.errors).flat().join('<br>') : (data.message || 'Erreur');
            errors.classList.remove('d-none');
            return;
        }

        bootstrap.Modal.getInstance(document.getElementById('blockFormModal'))?.hide();
        showToast(data.message || 'Bloc sauvegarde', 'success');
        blocksAdminLoaded = false;
        loadBlocksAdmin();
    } catch (error) {
        errors.textContent = 'Erreur reseau pendant la sauvegarde.';
        errors.classList.remove('d-none');
    } finally {
        button.disabled = false;
        button.innerHTML = '<i class="fas fa-save me-1"></i>Sauvegarder';
    }
});

function deleteBlockAdmin(blockId, btn) {
    if (!confirm('Supprimer ce bloc ?')) return;

    btn.disabled = true;
    fetch(`/admin/cms/${currentEtablissementId}/blocks/${blockId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Bloc supprime', 'success');
            blocksAdminItems = blocksAdminItems.filter(block => Number(block.id) !== Number(blockId));
            filterBlocksAdmin();
        } else {
            showToast(data.message || 'Erreur', 'error');
            btn.disabled = false;
        }
    })
    .catch(() => {
        showToast('Erreur reseau', 'error');
        btn.disabled = false;
    });
}

function escapeBlockAdmin(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function installTemplateDash(templateId, btn) {
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    fetch(`/admin/cms/${currentEtablissementId}/themes/${templateId}/install`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Template installe avec succes', 'success');
            setTimeout(() => location.reload(), 700);
        } else {
            showToast(data.message || 'Erreur', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-download"></i> Installer';
        }
    })
    .catch(() => {
        showToast('Erreur reseau', 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-download"></i> Installer';
    });
}

function activateTemplateDash(templateId, btn) {
    if (!confirm('Switcher vers ce template pour cet etablissement ?')) return;

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    fetch(`/admin/cms/${currentEtablissementId}/themes/${templateId}/activate`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('Template active avec succes', 'success');
            setTimeout(() => location.reload(), 700);
        } else {
            showToast(data.message || 'Erreur', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-random"></i> Switch';
        }
    })
    .catch(() => {
        showToast('Erreur reseau', 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-random"></i> Switch';
    });
}
</script>
