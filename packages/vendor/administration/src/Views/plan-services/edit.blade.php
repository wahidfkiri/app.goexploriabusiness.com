@extends('layouts.app')

@section('content')
<style>
.media-preview-box{border:1px solid #e2e8f0;border-radius:12px;background:#f8fafc;padding:12px}
.media-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:10px}
.media-card{position:relative;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;background:#fff;min-height:120px;display:flex;align-items:center;justify-content:center}
.media-card img,.media-card video{width:100%;height:140px;object-fit:cover}
.media-badge{position:absolute;top:8px;left:8px;background:rgba(15,23,42,.8);color:#fff;border-radius:999px;padding:2px 8px;font-size:.72rem}
.media-remove{position:absolute;top:8px;right:8px;border:0;background:#dc2626;color:#fff;border-radius:999px;width:26px;height:26px;line-height:26px;text-align:center;cursor:pointer}
.media-empty{color:#94a3b8;font-size:.9rem}
</style>

<main class="dashboard-content" style="padding:24px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-0">Modifier service</h2>
            <small class="text-muted">Service #{{ $service->id }}</small>
        </div>
        <a href="{{ route('plan-services.index', ['plan_id' => $service->plan_id]) }}" class="btn btn-outline-secondary">Retour</a>
    </div>

    <div class="card p-4">
        <form id="serviceEditForm" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Plan *</label>
                    <select name="plan_id" class="form-select" required>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}" {{ (int)$service->plan_id === (int)$plan->id ? 'selected' : '' }}>{{ $plan->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Titre *</label>
                    <input type="text" name="title" class="form-control" value="{{ $service->title }}" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3">{{ $service->description }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Contenu (WYSIWYG)</label>
                    <textarea name="content" id="contentEditor" class="form-control" rows="8">{!! $service->content !!}</textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Type</label>
                    <select name="service_type" id="serviceType" class="form-select">
                        <option value="free" {{ $service->service_type==='free'?'selected':'' }}>Gratuit</option>
                        <option value="paid" {{ $service->service_type==='paid'?'selected':'' }}>Payant</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Prix</label>
                    <input type="number" step="0.01" min="0" name="price" id="priceInput" class="form-control" value="{{ $service->price }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Devise</label>
                    <input type="text" name="currency" class="form-control" value="{{ $service->currency }}" maxlength="3">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Media principal</label>
                    <select name="main_media_type" id="mainMediaType" class="form-select">
                        <option value="image" {{ $service->main_media_type==='image'?'selected':'' }}>Image</option>
                        <option value="video_upload" {{ $service->main_media_type==='video_upload'?'selected':'' }}>Video upload</option>
                        <option value="video_url" {{ $service->main_media_type==='video_url'?'selected':'' }}>URL video</option>
                    </select>
                </div>
                <div class="col-md-8 d-flex align-items-end">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $service->is_active ? 'checked' : '' }}>
                        <label class="form-check-label">Actif</label>
                    </div>
                </div>

                <div class="col-md-6 media-input media-image">
                    <label class="form-label">Nouvelle image principale</label>
                    <input type="file" name="main_image" class="form-control" accept="image/*">
                </div>
                <div class="col-md-6 media-input media-video-upload d-none">
                    <label class="form-label">Nouvelle video principale</label>
                    <input type="file" name="main_video" class="form-control" accept="video/*">
                </div>
                <div class="col-md-6 media-input media-video-url d-none">
                    <label class="form-label">URL video principale</label>
                    <input type="url" name="main_video_url" class="form-control" value="{{ $service->main_video_url }}" placeholder="https://...">
                </div>

                <div class="col-md-6"><label class="form-label">Ajouter images galerie</label><input type="file" name="gallery_images[]" class="form-control" accept="image/*" multiple></div>
                <div class="col-md-6"><label class="form-label">Ajouter videos galerie</label><input type="file" name="gallery_videos[]" class="form-control" accept="video/*" multiple></div>
            </div>

            <div class="mt-4">
                <h6 class="mb-2">Media principal actuel</h6>
                <div class="media-preview-box">
                    @if($service->main_media_type === 'image' && !empty($service->main_image_path))
                        <div class="media-grid">
                            <div class="media-card">
                                <span class="media-badge">Image</span>
                                <img src="{{ $service->main_image_path }}" alt="Main image">
                            </div>
                        </div>
                    @elseif($service->main_media_type === 'video_upload' && !empty($service->main_video_path))
                        <div class="media-grid">
                            <div class="media-card">
                                <span class="media-badge">Video</span>
                                <video controls src="{{ $service->main_video_path }}"></video>
                            </div>
                        </div>
                    @elseif($service->main_media_type === 'video_url' && !empty($service->main_video_url))
                        <a href="{{ $service->main_video_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">Ouvrir l'URL video</a>
                    @else
                        <div class="media-empty">Aucun media principal</div>
                    @endif
                </div>
            </div>

            <div class="mt-4">
                <h6 class="mb-2">Galerie actuelle</h6>
                <div class="media-preview-box">
                    @php($gallery = is_array($service->gallery) ? $service->gallery : [])
                    @if(count($gallery))
                        <div class="media-grid" id="existingGalleryGrid">
                            @foreach($gallery as $gIndex => $gItem)
                                @php($gType = $gItem['type'] ?? 'image')
                                @php($gPath = $gItem['path'] ?? '')
                                <div class="media-card existing-gallery-item" data-gallery-index="{{ $gIndex }}">
                                    <button type="button" class="media-remove delete-gallery-item" title="Supprimer" data-gallery-index="{{ $gIndex }}">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    <span class="media-badge">{{ $gType === 'video' ? 'Video' : 'Image' }}</span>
                                    @if($gType === 'video')
                                        <video controls src="{{ $gPath }}"></video>
                                    @else
                                        <img src="{{ $gPath }}" alt="Gallery item">
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        <div id="deletedGalleryIndicesContainer"></div>
                    @else
                        <div class="media-empty">Aucun element dans la galerie</div>
                    @endif
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary" id="saveBtn">Mettre a jour</button>
            </div>
        </form>
    </div>
</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
let serviceEditor = null;
ClassicEditor.create(document.querySelector('#contentEditor')).then(e=>serviceEditor=e).catch(()=>{});

function syncMainMediaInputs(){
  const t=$('#mainMediaType').val();
  $('.media-input').addClass('d-none');
  if(t==='image')$('.media-image').removeClass('d-none');
  if(t==='video_upload')$('.media-video-upload').removeClass('d-none');
  if(t==='video_url')$('.media-video-url').removeClass('d-none');
}
function syncPricing(){const paid=$('#serviceType').val()==='paid';$('#priceInput').prop('disabled',!paid);if(!paid)$('#priceInput').val('0');}
$('#mainMediaType').on('change',syncMainMediaInputs);$('#serviceType').on('change',syncPricing);syncMainMediaInputs();syncPricing();

function showToast(msg){
  const d=document.createElement('div');
  d.style.cssText='position:fixed;top:20px;right:20px;background:#111;color:#fff;padding:10px 14px;border-radius:8px;z-index:9999';
  d.textContent=msg;
  document.body.appendChild(d);
  setTimeout(()=>d.remove(),2500);
}

$(document).on('click', '.delete-gallery-item', function(){
 const idx = $(this).data('gallery-index');
 $(this).closest('.existing-gallery-item').remove();
 $('#deletedGalleryIndicesContainer').append('<input type="hidden" name="delete_gallery_indices[]" value="'+idx+'">');
});

$('#serviceEditForm').on('submit',function(e){
 e.preventDefault();
 if(serviceEditor){$('textarea[name="content"]').val(serviceEditor.getData());}
 const btn=$('#saveBtn'); const old=btn.text(); btn.prop('disabled',true).text('Enregistrement...');
 const fd=new FormData(this);
 $.ajax({
  url:'{{ route("plan-services.update", $service->id) }}',
  method:'POST',
  data:fd,
  processData:false,
  contentType:false,
  success:function(res){
    if(res.success){
      showToast(res.message||'Service modifie');
      setTimeout(()=>window.location.href=(res.redirect||'{{ route("plan-services.index") }}'),700);
    }
  },
  error:function(xhr){showToast(xhr.responseJSON?.message||'Erreur mise a jour');},
  complete:function(){btn.prop('disabled',false).text(old);}
 });
});
</script>
@endsection
