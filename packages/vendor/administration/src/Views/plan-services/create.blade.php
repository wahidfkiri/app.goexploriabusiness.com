@extends('layouts.app')

@section('content')
<style>
    .service-create-wrap {
        width: 100%;
        margin: 0;
        padding: 24px;
        box-sizing: border-box;
    }
    .service-create-card {
        border: 1px solid #e9ecef;
        border-radius: 14px;
        box-shadow: 0 8px 24px rgba(0,0,0,.06);
        background: #fff;
    }
    .service-create-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
    }
    .service-create-title {
        margin: 0;
        font-size: 1.4rem;
        font-weight: 700;
    }
    .service-create-subtitle {
        margin: 2px 0 0;
        color: #6c757d;
    }
    .service-section-title {
        font-size: .98rem;
        font-weight: 700;
        margin-bottom: 10px;
        color: #334155;
    }
    .service-separator {
        border-top: 1px dashed #e2e8f0;
        margin: 18px 0;
    }
    .file-box input[type="file"] {
        border: 1.5px dashed #cbd5e1;
        background: #f8fafc;
        border-radius: 12px;
        padding: 14px;
    }
    .file-help {
        font-size: .82rem;
        color: #64748b;
        margin-top: 6px;
    }
    .preview-wrap {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px;
    }
    .preview-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 10px;
    }
    .preview-item {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        overflow: hidden;
        background: #fff;
        min-height: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }
    .preview-item img,
    .preview-item video {
        width: 100%;
        height: 140px;
        object-fit: cover;
    }
    .preview-empty {
        color: #94a3b8;
        font-size: .9rem;
    }
    .preview-badge {
        position: absolute;
        top: 8px;
        left: 8px;
        background: rgba(15,23,42,.8);
        color: #fff;
        border-radius: 999px;
        padding: 2px 8px;
        font-size: .72rem;
        z-index: 2;
    }
</style>

<main class="dashboard-content">
    <div class="service-create-wrap">
        <div class="service-create-header">
            <div>
                <h1 class="service-create-title">Nouveau service</h1>
                <p class="service-create-subtitle">Créer un service lié à un plan</p>
            </div>
            <div>
                <a href="{{ route('plans.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour plans
                </a>
            </div>
        </div>

        <div class="service-create-card p-4">
            <form id="serviceCreateForm" enctype="multipart/form-data">
                @csrf

                <div class="service-section-title">Informations de base</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Plan *</label>
                        <select name="plan_id" class="form-select" required>
                            <option value="">Sélectionner un plan</option>
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}" {{ (int)$selectedPlanId === (int)$plan->id ? 'selected' : '' }}>{{ $plan->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Titre *</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Description courte</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Contenu détaillé (WYSIWYG)</label>
                        <textarea name="content" id="contentEditor" class="form-control" rows="10"></textarea>
                    </div>
                </div>

                <div class="service-separator"></div>
                <div class="service-section-title">Tarification</div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Type de service *</label>
                        <select name="service_type" id="serviceType" class="form-select" required>
                            <option value="free">Gratuit</option>
                            <option value="paid">Payant</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Prix</label>
                        <input type="number" step="0.01" min="0" name="price" id="priceInput" class="form-control" value="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Devise</label>
                        <input type="text" name="currency" class="form-control" value="CAD" maxlength="3">
                    </div>
                </div>

                <div class="service-separator"></div>
                <div class="service-section-title">Média principal</div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Type *</label>
                        <select name="main_media_type" id="mainMediaType" class="form-select" required>
                            <option value="image">Image</option>
                            <option value="video_upload">Vidéo upload</option>
                            <option value="video_url">URL vidéo</option>
                        </select>
                    </div>
                    <div class="col-md-8 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" checked>
                            <label class="form-check-label" for="is_active">Actif</label>
                        </div>
                    </div>

                    <div class="col-md-6 media-input media-image file-box">
                        <label class="form-label">Image principale</label>
                        <input type="file" id="mainImageInput" name="main_image" class="form-control" accept="image/*">
                        <div class="file-help">JPG, PNG, WEBP - max 5MB</div>
                    </div>
                    <div class="col-md-6 media-input media-video-upload d-none file-box">
                        <label class="form-label">Vidéo principale</label>
                        <input type="file" id="mainVideoInput" name="main_video" class="form-control" accept="video/*">
                        <div class="file-help">MP4, MOV, AVI, WEBM - max 50MB</div>
                    </div>
                    <div class="col-md-6 media-input media-video-url d-none">
                        <label class="form-label">URL vidéo principale</label>
                        <input type="url" id="mainVideoUrlInput" name="main_video_url" class="form-control" placeholder="https://...">
                    </div>
                </div>

                <div class="service-separator"></div>
                <div class="service-section-title">Galerie</div>
                <div class="row g-3">
                    <div class="col-md-6 file-box">
                        <label class="form-label">Galerie images</label>
                        <input type="file" id="galleryImagesInput" name="gallery_images[]" class="form-control" accept="image/*" multiple>
                        <div class="file-help">Plusieurs images autorisées</div>
                    </div>
                    <div class="col-md-6 file-box">
                        <label class="form-label">Galerie vidéos</label>
                        <input type="file" id="galleryVideosInput" name="gallery_videos[]" class="form-control" accept="video/*" multiple>
                        <div class="file-help">Plusieurs vidéos autorisées</div>
                    </div>
                </div>

                <div class="service-separator"></div>
                <div class="service-section-title">Prévisualisation</div>
                <div class="preview-wrap mb-2">
                    <div class="mb-2 fw-semibold">Média principal</div>
                    <div id="mainPreview" class="preview-grid">
                        <div class="preview-item"><span class="preview-empty">Aucun média sélectionné</span></div>
                    </div>
                </div>
                <div class="preview-wrap">
                    <div class="mb-2 fw-semibold">Galerie</div>
                    <div id="galleryPreview" class="preview-grid">
                        <div class="preview-item"><span class="preview-empty">Aucun fichier galerie</span></div>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary" id="saveServiceBtn">
                        <i class="fas fa-save me-2"></i>Créer le service
                    </button>
                    <a href="{{ route('plans.index') }}" class="btn btn-light">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
function showToast(type, message) {
    const cls = type === 'success' ? 'success' : 'danger';
    const title = type === 'success' ? 'Succès' : 'Erreur';
    const id = 'toast-' + Date.now();
    const html = `
        <div id="${id}" class="toast align-items-center text-white bg-${cls} border-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body"><strong>${title}:</strong> ${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    let container = document.getElementById('serviceToastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'serviceToastContainer';
        container.style.position = 'fixed';
        container.style.top = '20px';
        container.style.right = '20px';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
    }
    container.insertAdjacentHTML('beforeend', html);
    const toastEl = document.getElementById(id);
    if (window.bootstrap && bootstrap.Toast) {
        const t = new bootstrap.Toast(toastEl, { delay: 3500 });
        t.show();
        toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
    } else {
        setTimeout(() => toastEl.remove(), 3500);
    }
}

let serviceEditor = null;
ClassicEditor.create(document.querySelector('#contentEditor'))
    .then(editor => { serviceEditor = editor; })
    .catch(() => {});

function syncMainMediaInputs() {
    const type = $('#mainMediaType').val();
    $('.media-input').addClass('d-none');
    if (type === 'image') $('.media-image').removeClass('d-none');
    if (type === 'video_upload') $('.media-video-upload').removeClass('d-none');
    if (type === 'video_url') $('.media-video-url').removeClass('d-none');
    renderMainPreview();
}

function syncPricing() {
    const paid = $('#serviceType').val() === 'paid';
    $('#priceInput').prop('disabled', !paid);
    if (!paid) $('#priceInput').val('0');
}

function renderMainPreview() {
    const wrap = $('#mainPreview');
    const mediaType = $('#mainMediaType').val();
    wrap.empty();

    if (mediaType === 'image') {
        const file = $('#mainImageInput')[0].files[0];
        if (!file) {
            wrap.html('<div class="preview-item"><span class="preview-empty">Aucune image</span></div>');
            return;
        }
        const url = URL.createObjectURL(file);
        wrap.html('<div class="preview-item"><span class="preview-badge">Image</span><img src="'+url+'" alt="preview"></div>');
        return;
    }

    if (mediaType === 'video_upload') {
        const file = $('#mainVideoInput')[0].files[0];
        if (!file) {
            wrap.html('<div class="preview-item"><span class="preview-empty">Aucune vidéo</span></div>');
            return;
        }
        const url = URL.createObjectURL(file);
        wrap.html('<div class="preview-item"><span class="preview-badge">Vidéo</span><video controls src="'+url+'"></video></div>');
        return;
    }

    const vurl = ($('#mainVideoUrlInput').val() || '').trim();
    if (!vurl) {
        wrap.html('<div class="preview-item"><span class="preview-empty">Aucune URL vidéo</span></div>');
        return;
    }
    wrap.html('<div class="preview-item"><span class="preview-badge">URL</span><a href="'+vurl+'" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">Ouvrir la vidéo</a></div>');
}

function renderGalleryPreview() {
    const wrap = $('#galleryPreview');
    const imageFiles = $('#galleryImagesInput')[0].files || [];
    const videoFiles = $('#galleryVideosInput')[0].files || [];
    wrap.empty();

    if (imageFiles.length === 0 && videoFiles.length === 0) {
        wrap.html('<div class="preview-item"><span class="preview-empty">Aucun fichier galerie</span></div>');
        return;
    }

    Array.from(imageFiles).forEach(function(f) {
        const url = URL.createObjectURL(f);
        wrap.append('<div class="preview-item"><span class="preview-badge">Image</span><img src="'+url+'" alt="img"></div>');
    });
    Array.from(videoFiles).forEach(function(f) {
        const url = URL.createObjectURL(f);
        wrap.append('<div class="preview-item"><span class="preview-badge">Vidéo</span><video controls src="'+url+'"></video></div>');
    });
}

$('#mainMediaType').on('change', syncMainMediaInputs);
$('#serviceType').on('change', syncPricing);
$('#mainImageInput').on('change', renderMainPreview);
$('#mainVideoInput').on('change', renderMainPreview);
$('#mainVideoUrlInput').on('input', renderMainPreview);
$('#galleryImagesInput, #galleryVideosInput').on('change', renderGalleryPreview);
syncMainMediaInputs();
syncPricing();
renderMainPreview();
renderGalleryPreview();

$('#serviceCreateForm').on('submit', function(e) {
    e.preventDefault();
    if (serviceEditor) {
        $('textarea[name="content"]').val(serviceEditor.getData());
    }

    const btn = $('#saveServiceBtn');
    const oldHtml = btn.html();
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Enregistrement...');

    const formData = new FormData(this);
    $.ajax({
        url: '{{ route("plan-services.store") }}',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        success: function(res) {
            if (res.success) {
                showToast('success', res.message || 'Service créé');
                window.location.href = res.redirect || '{{ route("plans.index") }}';
                return;
            }
            showToast('error', res.message || 'Erreur');
        },
        error: function(xhr) {
            if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                const first = Object.values(xhr.responseJSON.errors)[0];
                showToast('error', Array.isArray(first) ? first[0] : 'Validation invalide');
            } else {
                const message = xhr.responseJSON?.message || 'Erreur serveur lors de la création';
                showToast('error', message);
            }
        },
        complete: function() {
            btn.prop('disabled', false).html(oldHtml);
        }
    });
});
</script>
@endsection
