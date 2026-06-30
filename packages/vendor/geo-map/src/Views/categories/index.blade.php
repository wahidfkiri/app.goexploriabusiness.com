@extends('layouts.app')

@section('content')
<main class="dashboard-content">
    <div class="page-header">
        <h1 class="page-title">
            <span class="page-title-icon"><i class="fas fa-map-marked-alt"></i></span>
            Catégories carte
        </h1>
        <div class="page-actions">
            <a href="#" class="btn btn-outline-secondary">
                <i class="fas fa-external-link-alt me-2"></i>Gestion complète
            </a>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex gap-2">
                    <div class="input-group" style="max-width: 320px;">
                        <input type="text" id="searchInput" class="form-control" placeholder="Rechercher..." value="{{ request('search') }}">
                        <button class="btn btn-outline-secondary" id="searchBtn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <select id="typeFilter" class="form-select" style="max-width: 200px;">
                        <option value="">Tous les types</option>
                        @foreach($categorieTypes as $type)
                            <option value="{{ $type->id }}" {{ request('type') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle" id="categoriesTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px;">#</th>
                            <th>Nom</th>
                            <th>Type</th>
                            <th style="width: 120px;">Icône actuelle</th>
                            <th style="width: 220px;">Nouvelle icône</th>
                            <th style="width: 100px;">Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                        <tr data-id="{{ $category->id }}">
                            <td class="text-muted">{{ $category->id }}</td>
                            <td>
                                <strong>{{ $category->name }}</strong>
                                <div class="text-muted small">{{ $category->slug }}</div>
                            </td>
                            <td>{{ $category->type?->name ?? '-' }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($category->icon_url)
                                        <img src="{{ $category->icon_url }}" alt="icône" style="width: 36px; height: 36px; object-fit: contain; border-radius: 6px; border: 1px solid #eee;">
                                        <button class="btn btn-sm btn-outline-danger remove-icon-btn" title="Supprimer l'icône" data-id="{{ $category->id }}">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    @else
                                        <span class="badge bg-light text-muted px-3 py-2">Aucune</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <form class="icon-upload-form d-flex align-items-center gap-2">
                                    @csrf
                                    <input type="file" name="icon" accept="image/png,image/svg+xml,image/jpeg,image/gif,image/webp" class="form-control form-control-sm" style="max-width: 140px;">
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        <i class="fas fa-upload"></i>
                                    </button>
                                </form>
                            </td>
                            <td>
                                @if($category->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Aucune catégorie trouvée</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <small class="text-muted">{{ $categories->total() }} catégorie(s)</small>
                {{ $categories->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</main>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const token = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

    document.querySelectorAll('.icon-upload-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const row = this.closest('tr');
            const id = row.dataset.id;
            const fileInput = this.querySelector('input[type="file"]');
            const file = fileInput.files[0];
            if (!file) return;

            const btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

            const formData = new FormData();
            formData.append('icon', file);
            formData.append('_token', token);

            fetch(`/map/categories/${id}/icon`, {
                method: 'POST',
                body: formData,
                headers: { 'X-CSRF-TOKEN': token }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const iconCell = row.querySelector('td:nth-child(4)');
                    iconCell.innerHTML = `
                        <div class="d-flex align-items-center gap-2">
                            <img src="${data.icon_url}" alt="icône" style="width: 36px; height: 36px; object-fit: contain; border-radius: 6px; border: 1px solid #eee;">
                            <button class="btn btn-sm btn-outline-danger remove-icon-btn" title="Supprimer l'icône" data-id="${id}">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `;
                    bindRemoveButtons();
                    fileInput.value = '';
                    if (typeof toastr !== 'undefined') toastr.success(data.message);
                }
            })
            .catch(() => { if (typeof toastr !== 'undefined') toastr.error('Erreur lors de l\'upload'); })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-upload"></i>';
            });
        });
    });

    function bindRemoveButtons() {
        document.querySelectorAll('.remove-icon-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const row = document.querySelector(`tr[data-id="${id}"]`);
                if (!confirm('Supprimer cette icône ?')) return;

                fetch(`/map/categories/${id}/icon`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': token }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const iconCell = row.querySelector('td:nth-child(4)');
                        iconCell.innerHTML = '<span class="badge bg-light text-muted px-3 py-2">Aucune</span>';
                        if (typeof toastr !== 'undefined') toastr.success(data.message);
                    }
                })
                .catch(() => { if (typeof toastr !== 'undefined') toastr.error('Erreur'); });
            });
        });
    }
    bindRemoveButtons();

    document.getElementById('searchBtn')?.addEventListener('click', function() {
        applyFilters();
    });
    document.getElementById('searchInput')?.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') applyFilters();
    });
    document.getElementById('typeFilter')?.addEventListener('change', applyFilters);

    function applyFilters() {
        const search = document.getElementById('searchInput').value;
        const type = document.getElementById('typeFilter').value;
        const params = new URLSearchParams(window.location.search);
        if (search) params.set('search', search); else params.delete('search');
        if (type) params.set('type', type); else params.delete('type');
        window.location.search = params.toString();
    }
});
</script>
@endsection
