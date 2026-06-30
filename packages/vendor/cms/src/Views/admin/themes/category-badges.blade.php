<div class="template-category-badges" id="templateCategoryBadges">
    <a
        href="{{ route('cms.admin.themes.index', array_filter(['search' => $filters['search'] ?? null])) }}"
        class="template-category-badge {{ ($filters['category'] ?? '') === '' ? 'active' : '' }}">
        Tous
    </a>
    @foreach($templateCategories as $category)
        <a
            href="{{ route('cms.admin.themes.index', array_filter(['search' => $filters['search'] ?? null, 'category' => $category])) }}"
            class="template-category-badge {{ ($filters['category'] ?? '') === $category ? 'active' : '' }}">
            {{ $category }}
        </a>
    @endforeach
</div>
