@extends('layouts.app')

@section('content')
<style>
.page-header-modern{display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:20px;padding:20px;border-radius:14px;background:#fff;border:1px solid #e2e8f0}
.page-header-left{display:flex;align-items:center;gap:14px}
.page-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#3b82f6,#1d4ed8);color:#fff}
.page-title-modern{margin:0;font-size:1.35rem;font-weight:700;color:#0f172a}
.page-subtitle{margin:4px 0 0;color:#64748b}
.btn-primary-modern{display:inline-flex;align-items:center;gap:8px;background:#2563eb;color:#fff;text-decoration:none;padding:10px 14px;border-radius:10px;border:1px solid #1d4ed8}
.btn-primary-modern:hover{background:#1d4ed8;color:#fff}
.filter-bar-modern{background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:14px;margin-bottom:18px}
.filter-label{display:block;margin-bottom:6px;font-size:.86rem;color:#475569;font-weight:600}
.filter-select{width:100%;height:40px;border:1px solid #cbd5e1;border-radius:10px;padding:0 10px;background:#fff}
.btn-filter-apply{height:40px;padding:0 14px;border:1px solid #1d4ed8;background:#2563eb;color:#fff;border-radius:10px}
.btn-filter-apply:hover{background:#1d4ed8}
.table-container-modern{background:#fff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden}
.table-modern thead th{background:#f8fafc;color:#334155;font-weight:700;border-bottom:1px solid #e2e8f0}
.table-modern td,.table-modern th{vertical-align:middle}
.empty-state-modern{text-align:center;padding:40px 20px}
.empty-icon{width:64px;height:64px;border-radius:999px;background:#eff6ff;color:#1d4ed8;display:inline-flex;align-items:center;justify-content:center;margin-bottom:10px}
.empty-state-modern h3{margin:8px 0;color:#0f172a}
.empty-state-modern p{margin:0 0 14px;color:#64748b}
.actions-wrap{display:flex;gap:8px;align-items:center}
.action-btn{width:34px;height:34px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;border:1px solid transparent;background:#f8fafc;color:#334155;cursor:pointer;text-decoration:none}
.action-btn:hover{transform:translateY(-1px)}
.action-btn-edit{border-color:#bfdbfe;background:#eff6ff;color:#1d4ed8}
.action-btn-edit:hover{background:#dbeafe;color:#1e40af}
.action-btn-delete{border-color:#fecaca;background:#fef2f2;color:#dc2626}
.action-btn-delete:hover{background:#fee2e2;color:#b91c1c}
</style>
<main class="dashboard-content">
    <div class="page-header-modern">
        <div class="page-header-left">
            <div class="page-icon">
                <i class="fas fa-concierge-bell"></i>
            </div>
            <div>
                <h1 class="page-title-modern">Services des plans</h1>
                <p class="page-subtitle">Gerez les services associes aux plans</p>
            </div>
        </div>
        <div class="page-header-right">
            <a href="{{ route('plan-services.create', ['plan_id' => $selectedPlanId]) }}" class="btn-primary-modern">
                <i class="fas fa-plus-circle me-2"></i>Nouveau service
            </a>
        </div>
    </div>

    <div class="filter-bar-modern">
        <form method="GET" action="{{ route('plan-services.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="filter-label">Plan</label>
                <select name="plan_id" class="filter-select">
                    <option value="">Tous les plans</option>
                    @foreach($plans as $plan)
                        <option value="{{ $plan->id }}" {{ (int)$selectedPlanId === (int)$plan->id ? 'selected' : '' }}>{{ $plan->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn-filter-apply" type="submit">
                    <i class="fas fa-search"></i> Filtrer
                </button>
            </div>
        </form>
    </div>

    <div class="table-container-modern">
        @if($services->count() > 0)
            <div class="table-responsive">
                <table class="table table-modern table-hover mb-0">
                    <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Plan</th>
                        <th>Type</th>
                        <th>Prix</th>
                        <th>Statut</th>
                        <th style="width:180px;">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($services as $service)
                        <tr id="service-row-{{ $service->id }}">
                            <td>{{ $service->title }}</td>
                            <td>{{ $service->plan->name ?? '-' }}</td>
                            <td>{{ strtoupper($service->service_type) }}</td>
                            <td>{{ number_format((float)$service->price, 2, ',', ' ') }} {{ $service->currency }}</td>
                            <td>{!! $service->is_active ? '<span class="badge bg-success">Actif</span>' : '<span class="badge bg-secondary">Inactif</span>' !!}</td>
                            <td>
                                <div class="actions-wrap">
                                    <a class="action-btn action-btn-edit" href="{{ route('plan-services.edit', $service->id) }}" title="Modifier">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <button type="button" class="action-btn action-btn-delete" onclick="deleteService({{ $service->id }})" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state-modern">
                <div class="empty-icon">
                    <i class="fas fa-concierge-bell"></i>
                </div>
                <h3>Aucun service trouve</h3>
                <p>Commencez par creer votre premier service</p>
                <a href="{{ route('plan-services.create', ['plan_id' => $selectedPlanId]) }}" class="btn-primary-modern">
                    <i class="fas fa-plus-circle me-2"></i>Creer un service
                </a>
            </div>
        @endif
    </div>

    @if($services->count() > 0)
        <div class="mt-3">{{ $services->withQueryString()->links() }}</div>
    @endif
</main>

<div class="toast-container" id="toastContainer"></div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function showToast(msg){
    const div = document.createElement('div');
    div.style.cssText = 'position:fixed;top:20px;right:20px;background:#111;color:#fff;padding:10px 14px;border-radius:8px;z-index:9999';
    div.textContent = msg;
    document.body.appendChild(div);
    setTimeout(() => div.remove(), 2500);
}

function deleteService(id){
    if(!confirm('Supprimer ce service ?')) return;
    $.ajax({
        url: '/admin/plan-services/' + id,
        type: 'DELETE',
        data: { _token: '{{ csrf_token() }}' },
        success: function(res){
            if(res.success){
                $('#service-row-' + id).remove();
                showToast(res.message || 'Service supprime');

                const bodyRows = document.querySelectorAll('table tbody tr').length;
                if (bodyRows === 0) {
                    window.location.reload();
                }
            }
        },
        error: function(xhr){
            showToast(xhr.responseJSON?.message || 'Erreur suppression');
        }
    });
}
</script>
@endsection
