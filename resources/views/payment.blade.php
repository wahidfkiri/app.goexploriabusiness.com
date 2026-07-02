@extends('layouts.app')

@section('content')
<main class="dashboard-content">
    <div class="page-header">
        <h1 class="page-title">
            <span class="page-title-icon"><i class="fas fa-credit-card"></i></span>
            Activer votre plan
        </h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Plans -->
    <div class="row mt-3">
        @foreach($plans as $plan)
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="stats-card" style="cursor: pointer; border: 2px solid {{ $plan->is_popular ? '#ef7724' : '#e5e7eb' }};" onclick="selectPlan({{ $plan->id }}, '{{ $plan->name }}', {{ $plan->price }})">
                @if($plan->is_popular)
                <span style="position: absolute; top: 8px; right: 8px; background: #ef7724; color: #fff; padding: 2px 10px; border-radius: 12px; font-size: 11px; font-weight: 700;">POPULAIRE</span>
                @endif
                <div class="stats-header">
                    <div class="stats-icon-container" style="background: rgba(239,119,36,0.1); color: #ef7724;">
                        <i class="fas fa-crown"></i>
                    </div>
                </div>
                <div class="stats-value" style="font-size: 1.2rem;">{{ $plan->name }}</div>
                <div class="stats-label" style="font-size: 1.5rem; font-weight: 800; color: #1e293b;">
                    {{ number_format($plan->price, 2) }} $ <small style="font-size: 0.6rem; font-weight: 400; color: #64748b;">/{{ $plan->billing_cycle ?? 'mois' }}</small>
                </div>
                <p style="font-size: 0.8rem; color: #64748b; margin-top: 8px;">{{ Str::limit($plan->features ? (is_array($plan->features) ? implode(', ', $plan->features) : $plan->features) : '', 80) }}</p>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Services -->
    <div class="main-card-modern mt-4">
        <div class="card-header" style="padding: 16px 20px; border-bottom: 1px solid #e5e7eb;">
            <h5 style="margin: 0; font-weight: 700;"><i class="fas fa-cogs me-2"></i>Services disponibles</h5>
        </div>
        <div style="padding: 20px;">
            <div class="row">
                @forelse($services as $service)
                <div class="col-xl-4 col-md-6 mb-3">
                    <div style="background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 16px; height: 100%;">
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                            <div style="width: 38px; height: 38px; border-radius: 8px; background: #f8f9fa; display: flex; align-items: center; justify-content: center; color: #ef7724;">
                                <i class="fas fa-tag"></i>
                            </div>
                            <h6 style="margin: 0; font-weight: 700; font-size: 0.9rem;">{{ $service->title }}</h6>
                        </div>
                        <p style="font-size: 0.8rem; color: #64748b; margin: 0 0 8px;">{{ $service->description }}</p>
                        <div style="font-weight: 700; color: #1e293b;">{{ number_format($service->unit_price, 2) }} $</div>
                        @if($service->tax)
                            <span style="font-size: 11px; color: #64748b;">Taxe: {{ $service->tax->rate }}%</span>
                        @endif
                    </div>
                </div>
                @empty
                <div class="col-12"><p class="text-muted">Aucun service disponible.</p></div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Discounts -->
    @if($discounts->count())
    <div class="main-card-modern mt-3">
        <div class="card-header" style="padding: 16px 20px; border-bottom: 1px solid #e5e7eb;">
            <h5 style="margin: 0; font-weight: 700;"><i class="fas fa-percent me-2"></i>Réductions disponibles</h5>
        </div>
        <div style="padding: 16px 20px;">
            @foreach($discounts as $discount)
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #f1f5f9;">
                <div>
                    <strong style="font-size: 0.9rem;">{{ $discount->name }}</strong>
                    <span style="font-size: 0.8rem; color: #64748b; margin-left: 8px;">{{ $discount->description }}</span>
                </div>
                <span style="background: #dcfce7; color: #166534; padding: 2px 10px; border-radius: 12px; font-size: 0.8rem; font-weight: 700;">
                    {{ $discount->display_value }}
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Billing History -->
    @if($requests->count())
    <div class="main-card-modern mt-3">
        <div class="card-header" style="padding: 16px 20px; border-bottom: 1px solid #e5e7eb;">
            <h5 style="margin: 0; font-weight: 700;"><i class="fas fa-history me-2"></i>Historique des demandes</h5>
        </div>
        <div style="padding: 16px 20px;">
            <table style="width: 100%; font-size: 0.85rem;">
                <thead>
                    <tr style="border-bottom: 2px solid #e5e7eb;">
                        <th style="padding: 8px; text-align: left;">N°</th>
                        <th style="padding: 8px; text-align: left;">Date</th>
                        <th style="padding: 8px; text-align: left;">Statut</th>
                        <th style="padding: 8px; text-align: right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $req)
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 8px;">{{ $req->request_number }}</td>
                        <td style="padding: 8px;">{{ $req->created_at->format('d/m/Y') }}</td>
                        <td style="padding: 8px;"><span class="badge bg-{{ $req->status == 'new' ? 'warning' : ($req->status == 'quoted' ? 'info' : 'success') }}">{{ $req->status_label }}</span></td>
                        <td style="padding: 8px; text-align: right; font-weight: 700;">{{ number_format($req->total, 2) }} $</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Settings -->
    @if($settings)
    <div class="main-card-modern mt-3">
        <div class="card-header" style="padding: 16px 20px; border-bottom: 1px solid #e5e7eb;">
            <h5 style="margin: 0; font-weight: 700;"><i class="fas fa-info-circle me-2"></i>Configuration de facturation</h5>
        </div>
        <div style="padding: 16px 20px; display: flex; gap: 24px; flex-wrap: wrap; font-size: 0.85rem;">
            <div><strong>Devise :</strong> {{ $settings->currency ?? 'CAD' }}</div>
            <div><strong>Locale :</strong> {{ $settings->locale ?? 'fr_CA' }}</div>
            <div><strong>Taxes :</strong> {{ $settings->taxes_enabled ? 'Activées' : 'Désactivées' }}</div>
            <div><strong>Paiement en ligne :</strong> {{ $settings->enable_online_payment ? 'Activé' : 'Désactivé' }}</div>
            @if($settings->default_discount_id)
            <div><strong>Réduction par défaut :</strong> {{ $discounts->firstWhere('id', $settings->default_discount_id)?->name ?? 'N/A' }}</div>
            @endif
        </div>
    </div>
    @endif
</main>

<script>
function selectPlan(id, name, price) {
    alert('Plan sélectionné : ' + name + ' (' + price + ' $)');
}
</script>
@endsection