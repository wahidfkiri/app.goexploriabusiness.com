@extends('layouts.app')

@section('content')
<main class="dashboard-content">
    <div class="page-header">
        <h1 class="page-title">
            <span class="page-title-icon"><i class="fas fa-tags"></i></span>
            Catégories de points carte
        </h1>
        <div class="page-actions">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fas fa-plus-circle me-2"></i>Nouvelle catégorie
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Nom</th>
                            <th style="width: 100px;">Icône</th>
                            <th style="width: 100px;">Couleur</th>
                            <th style="width: 120px;">Image</th>
                            <th style="width: 80px;">Ordre</th>
                            <th style="width: 80px;">Lieux</th>
                            <th style="width: 100px;">Statut</th>
                            <th style="width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $cat)
                        <tr data-id="{{ $cat->id }}">
                            <td>{{ $cat->id }}</td>
                            <td>
                                <strong>{{ $cat->name }}</strong>
                                <div class="text-muted small">{{ $cat->slug }}</div>
                            </td>
                            <td>
                                @if($cat->icon_class)
                                    <i class="{{ $cat->icon_class }}" style="font-size: 1.3rem; color: {{ $cat->color ?? '#666' }};"></i>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($cat->color)
                                    <span class="badge" style="background: {{ $cat->color }}; color: #fff;">{{ $cat->color }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($cat->image_url)
                                    <img src="{{ $cat->image_url }}" alt="" style="width: 36px; height: 36px; object-fit: contain; border-radius: 4px;">
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>{{ $cat->sort_order }}</td>
                            <td>
                                <span class="badge bg-info">{{ $cat->places_count }}</span>
                            </td>
                            <td>
                                <button class="btn btn-sm toggle-status {{ $cat->is_active ? 'btn-success' : 'btn-secondary' }}" data-id="{{ $cat->id }}">
                                    {{ $cat->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary edit-btn" data-id="{{ $cat->id }}" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-danger delete-btn" data-id="{{ $cat->id }}" data-name="{{ $cat->name }}" title="Supprimer" {{ $cat->places_count > 0 ? 'disabled' : '' }}>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="9" class="text-center py-4 text-muted">Aucune catégorie</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

{{-- Create Modal --}}
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nouvelle catégorie</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nom * <i class="fas fa-info-circle text-info ms-1" data-bs-toggle="tooltip" title="Nom de la catégorie affiché sur la carte"></i></label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Icône <i class="fas fa-info-circle text-info ms-1" data-bs-toggle="tooltip" title="Classe Font Awesome ex: fas fa-utensils, fas fa-hotel"></i></label>
                        <input type="text" class="form-control" name="icon_class" placeholder="ex: fas fa-utensils">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Couleur <i class="fas fa-info-circle text-info ms-1" data-bs-toggle="tooltip" title="Couleur du marqueur sur la carte (code hexadécimal)"></i></label>
                        <div class="input-group">
                            <input type="color" class="form-control form-control-color" name="color" style="max-width: 60px;" value="#e53e3e">
                            <input type="text" class="form-control" name="color_text" placeholder="#e53e3e" maxlength="20">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Image <i class="fas fa-info-circle text-info ms-1" data-bs-toggle="tooltip" title="Image personnalisée (remplace l'icône Font Awesome)"></i></label>
                        <input type="file" class="form-control" name="image" accept="image/*">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ordre <i class="fas fa-info-circle text-info ms-1" data-bs-toggle="tooltip" title="Ordre d'affichage dans la liste (croissant)"></i></label>
                        <input type="number" class="form-control" name="sort_order" value="0" min="0">
                    </div>
                    <div class="form-check form-switch">
                        <input type="hidden" name="is_active" value="0">
                        <input class="form-check-input" type="checkbox" id="createIsActive" name="is_active" value="1" checked>
                        <label class="form-check-label" for="createIsActive">Active <i class="fas fa-info-circle text-info ms-1" data-bs-toggle="tooltip" title="Décocher pour masquer cette catégorie sur la carte"></i></label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary" id="createBtn">Créer</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modifier la catégorie</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" id="editId" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nom * <i class="fas fa-info-circle text-info ms-1" data-bs-toggle="tooltip" title="Nom de la catégorie affiché sur la carte"></i></label>
                        <input type="text" class="form-control" id="editName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Icône <i class="fas fa-info-circle text-info ms-1" data-bs-toggle="tooltip" title="Classe Font Awesome ex: fas fa-utensils, fas fa-hotel"></i></label>
                        <input type="text" class="form-control" id="editIconClass" name="icon_class" placeholder="ex: fas fa-utensils">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Couleur <i class="fas fa-info-circle text-info ms-1" data-bs-toggle="tooltip" title="Couleur du marqueur sur la carte (code hexadécimal)"></i></label>
                        <div class="input-group">
                            <input type="color" class="form-control form-control-color" id="editColor" name="color" style="max-width: 60px;">
                            <input type="text" class="form-control" id="editColorText" name="color_text" placeholder="#e53e3e" maxlength="20">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Image <i class="fas fa-info-circle text-info ms-1" data-bs-toggle="tooltip" title="Image personnalisée (remplace l'icône Font Awesome)"></i></label>
                        <div id="editImagePreview" class="mb-2"></div>
                        <input type="file" class="form-control" name="image" accept="image/*">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="editRemoveImage" name="remove_image" value="1">
                            <label class="form-check-label" for="editRemoveImage">Supprimer l'image <i class="fas fa-info-circle text-info ms-1" data-bs-toggle="tooltip" title="Cocher pour supprimer l'image existante"></i></label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ordre <i class="fas fa-info-circle text-info ms-1" data-bs-toggle="tooltip" title="Ordre d'affichage dans la liste (croissant)"></i></label>
                        <input type="number" class="form-control" id="editSortOrder" name="sort_order" min="0">
                    </div>
                    <div class="form-check form-switch">
                        <input type="hidden" name="is_active" value="0">
                        <input class="form-check-input" type="checkbox" id="editIsActive" name="is_active" value="1">
                        <label class="form-check-label" for="editIsActive">Active <i class="fas fa-info-circle text-info ms-1" data-bs-toggle="tooltip" title="Décocher pour masquer cette catégorie sur la carte"></i></label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary" id="updateBtn">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser les tooltips Bootstrap
    if (typeof bootstrap !== 'undefined') {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
    }

    const token = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

    // Color picker sync
    document.querySelectorAll('input[type="color"]').forEach(picker => {
        const textInput = picker.closest('.input-group')?.querySelector('input[type="text"]');
        if (textInput) {
            picker.addEventListener('input', () => textInput.value = picker.value);
            textInput.addEventListener('input', () => { try { picker.value = textInput.value; } catch(e) {} });
        }
    });

    // Create
    document.getElementById('createForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('createBtn');
        btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        const formData = new FormData(this);
        if (!formData.has('is_active')) formData.append('is_active', '0');

        formData.append('_token', token);

        fetch('{{ route("geomap.map-categories.store") }}', {
            method: 'POST', body: formData
        })
        .then(async res => {
            const text = await res.text();
            try { return JSON.parse(text); } catch(e) { throw new Error(text.substring(0, 200)); }
        })
        .then(data => {
            if (data.success) { location.reload(); }
            else { alert(data.message); }
        })
        .catch(err => alert('Erreur: ' + err.message))
        .finally(() => { btn.disabled = false; btn.innerHTML = 'Créer'; });
    });

    // Edit - load data
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const row = this.closest('tr');
            const name = row.querySelector('td:nth-child(2) strong').textContent.trim();
            const iconEl = row.querySelector('td:nth-child(3) i');
            const colorEl = row.querySelector('td:nth-child(4) .badge');
            const imageEl = row.querySelector('td:nth-child(5) img');
            const orderEl = row.querySelector('td:nth-child(6)');
            const statusEl = row.querySelector('.toggle-status');

            document.getElementById('editId').value = id;
            document.getElementById('editName').value = name;
            document.getElementById('editIconClass').value = iconEl ? (iconEl.className || '') : '';
            const color = colorEl ? colorEl.textContent.trim() : '';
            document.getElementById('editColor').value = color || '#e53e3e';
            document.getElementById('editColorText').value = color;
            document.getElementById('editSortOrder').value = orderEl ? orderEl.textContent.trim() : 0;
            document.getElementById('editIsActive').checked = statusEl ? statusEl.textContent.trim() === 'Active' : true;
            document.getElementById('editRemoveImage').checked = false;

            const preview = document.getElementById('editImagePreview');
            if (imageEl && imageEl.src) {
                preview.innerHTML = `<img src="${imageEl.src}" style="max-height: 48px; border-radius: 4px;">`;
            } else {
                preview.innerHTML = '<span class="text-muted">Aucune image</span>';
            }

            new bootstrap.Modal(document.getElementById('editModal')).show();
        });
    });

    // Update
    document.getElementById('editForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const id = document.getElementById('editId').value;
        const btn = document.getElementById('updateBtn');
        btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        const formData = new FormData(this);
        if (!formData.has('is_active')) formData.append('is_active', '0');

        fetch(`/map-categories/${id}`, {
            method: 'POST',
            body: formData,
            headers: { 'Accept': 'application/json' }
        })
        .then(async res => {
            const text = await res.text();
            try { return JSON.parse(text); } catch(e) { throw new Error(text.substring(0, 200)); }
        })
        .then(data => {
            if (data.success) { location.reload(); }
            else { alert(data.message); }
        })
        .catch(err => alert('Erreur: ' + err.message))
        .finally(() => { btn.disabled = false; btn.innerHTML = 'Enregistrer'; });
    });

    // Delete
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (this.disabled) return;
            const id = this.dataset.id;
            const name = this.dataset.name;
            if (!confirm(`Supprimer "${name}" ?`)) return;

            fetch(`/map-categories/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }
            })
            .then(async res => {
                const text = await res.text();
                try { return JSON.parse(text); } catch(e) { throw new Error(text.substring(0, 200)); }
            })
            .then(data => {
                if (data.success) location.reload();
                else alert(data.message);
            })
            .catch(err => alert('Erreur: ' + err.message));
        });
    });

    // Toggle status
    document.querySelectorAll('.toggle-status').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            fetch(`/map-categories/${id}/toggle-status`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }
            })
            .then(async res => {
                const text = await res.text();
                try { return JSON.parse(text); } catch(e) { throw new Error(text.substring(0, 200)); }
            })
            .then(data => {
                if (data.success) location.reload();
                else alert(data.message);
            })
            .catch(err => alert('Erreur: ' + err.message));
        });
    });
});
</script>
@endsection
