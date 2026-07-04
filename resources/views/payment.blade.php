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
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @php
        $paypalMode = config('paypal.mode', 'sandbox');
        $paypalClientId = $paypalMode === 'live'
            ? config('paypal.live.client_id')
            : config('paypal.sandbox.client_id');
    @endphp

    <div class="row mt-3">
        @forelse($plans as $plan)
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="stats-card plan-card" data-id="{{ $plan->id }}" data-name="{{ $plan->title }}" data-price="{{ $plan->unit_price }}" style="cursor: pointer; border: 2px solid {{ $plan->is_featured ? '#ef7724' : '#e5e7eb' }}; position: relative;">
                @if($plan->is_featured)
                <span style="position: absolute; top: 8px; right: 8px; background: #ef7724; color: #fff; padding: 2px 10px; border-radius: 12px; font-size: 11px; font-weight: 700;">POPULAIRE</span>
                @endif
                <div class="stats-header">
                    <div class="stats-icon-container" style="background: rgba(239,119,36,0.1); color: #ef7724;">
                        <i class="fas fa-crown"></i>
                    </div>
                </div>
                <div class="stats-value" style="font-size: 1.2rem;">{{ $plan->title }}</div>
                <div class="stats-label" style="font-size: 1.5rem; font-weight: 800; color: #1e293b;">
                    {{ number_format($plan->unit_price, 2) }} $ <small style="font-size: 0.6rem; font-weight: 400; color: #64748b;">/{{ $plan->billing_unit ?? 'mois' }}</small>
                </div>
                <p style="font-size: 0.8rem; color: #64748b; margin-top: 8px;">
                    {{ Str::limit($plan->description ?? '', 80) }}
                </p>
                @if($plan->tax)
                <span style="font-size: 11px; color: #64748b;">Taxe: {{ $plan->tax->rate }}%</span>
                @endif
                <div class="plan-check" style="position: absolute; top: 8px; left: 8px; width: 20px; height: 20px; border-radius: 50%; border: 2px solid #d1d5db; display: none; align-items: center; justify-content: center; background: #fff;">
                    <i class="fas fa-check" style="font-size: 10px; color: #fff;"></i>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12"><p class="text-muted">Aucun plan disponible.</p></div>
        @endforelse
    </div>

    <!-- Payment Section -->
    <div class="main-card-modern mt-4" id="paymentSection" style="display: none;">
        <div class="card-header" style="padding: 16px 20px; border-bottom: 1px solid #e5e7eb;">
            <h5 style="margin: 0; font-weight: 700;"><i class="fas fa-lock me-2"></i>Paiement sécurisé</h5>
        </div>
        <div style="padding: 24px 20px;">
            <div class="row">
                <div class="col-md-6">
                    <h6 style="font-weight: 700; color: #1e293b;">Résumé de votre commande</h6>
                    <div style="background: #f8fafc; border-radius: 8px; padding: 16px; margin-top: 8px;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.9rem;">
                            <span id="selectedPlanName" style="font-weight: 600;">-</span>
                            <span id="selectedPlanPrice" style="font-weight: 700;">-</span>
                        </div>
                        <hr style="margin: 12px 0; border-color: #e5e7eb;">
                        <div style="display: flex; justify-content: space-between; font-size: 1.1rem;">
                            <strong>Total</strong>
                            <strong id="selectedPlanTotal" style="color: #ef7724;">-</strong>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <h6 style="font-weight: 700; color: #1e293b;">Payer avec</h6>
                    <div style="margin-top: 8px;">
                        <div id="paypal-button-container"></div>
                        <div id="payment-loader" style="display: none; text-align: center; padding: 20px;">
                            <div class="spinner-border text-warning" role="status"></div>
                            <p class="mt-2 text-muted">Traitement du paiement en cours...</p>
                        </div>
                        <div id="payment-error" style="display: none; color: #dc2626; font-size: 0.85rem; margin-top: 8px; padding: 8px; background: #fee2e2; border-radius: 6px;"></div>
                        <div id="payment-success" style="display: none; color: #16a34a; font-size: 0.85rem; margin-top: 8px; padding: 8px; background: #dcfce7; border-radius: 6px;"></div>
                    </div>
                </div>
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

    <!-- Payment History -->
    @if($invoices->count())
    <div class="main-card-modern mt-3">
        <div class="card-header" style="padding: 16px 20px; border-bottom: 1px solid #e5e7eb;">
            <h5 style="margin: 0; font-weight: 700;"><i class="fas fa-receipt me-2"></i>Historique des paiements</h5>
        </div>
        <div style="padding: 16px 20px;">
            <table style="width: 100%; font-size: 0.85rem;">
                <thead>
                    <tr style="border-bottom: 2px solid #e5e7eb;">
                        <th style="padding: 8px; text-align: left;">Facture</th>
                        <th style="padding: 8px; text-align: left;">Date</th>
                        <th style="padding: 8px; text-align: left;">Plan</th>
                        <th style="padding: 8px; text-align: right;">Montant</th>
                        <th style="padding: 8px; text-align: center;">Statut</th>
                        <th style="padding: 8px; text-align: center;">Facture</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoices as $inv)
                    @php $firstLine = $inv->lines->first(); @endphp
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 8px; font-weight: 600;">{{ $inv->invoice_number }}</td>
                        <td style="padding: 8px;">{{ $inv->invoice_date->format('d/m/Y') }}</td>
                        <td style="padding: 8px;">{{ $firstLine ? Str::limit($firstLine->description, 40) : '-' }}</td>
                        <td style="padding: 8px; text-align: right; font-weight: 700;">{{ number_format($inv->total, 2) }} $</td>
                        <td style="padding: 8px; text-align: center;">
                            <span class="badge bg-{{ $inv->status_badge }}">{{ $inv->status_label }}</span>
                        </td>
                        <td style="padding: 8px; text-align: center;">
                            <a href="{{ route('invoices.pdf', $inv) }}" class="btn btn-sm btn-outline-secondary" target="_blank" title="Télécharger la facture PDF">
                                <i class="fas fa-file-pdf"></i> PDF
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Billing Requests History -->
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

</main>

@if($paypalClientId)
<script src="https://www.paypal.com/sdk/js?client-id={{ $paypalClientId }}&currency=CAD&locale=fr_FR&intent=capture&enable-funding=card" data-sdk-integration-source="button-factory"></script>
@endif

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let selectedPlan = null;
        let lastCreatedOrderId = null;
        const errorElement = document.getElementById('payment-error');
        const successElement = document.getElementById('payment-success');
        const loaderElement = document.getElementById('payment-loader');

        function parseJsonResponse(response) {
            return response.text().then(function(text) {
                let payload = {};

                if (text) {
                    try {
                        payload = JSON.parse(text);
                    } catch (e) {
                        payload = { message: 'Erreur de communication avec le serveur' };
                    }
                }

                if (!response.ok) {
                    throw new Error(payload.message || 'Erreur de communication avec le serveur');
                }

                return payload;
            });
        }

        const planCards = document.querySelectorAll('.plan-card');
        
        // CORRECTION: Vérifier que les cartes existent
        if (planCards.length > 0) {
            planCards.forEach(card => {
                card.addEventListener('click', function() {
                    // Désélectionner toutes les autres cartes
                    planCards.forEach(c => {
                        c.style.borderColor = '#e5e7eb';
                        const check = c.querySelector('.plan-check');
                        if (check) {
                            check.style.display = 'none';
                        }
                    });
                    
                    // Sélectionner cette carte
                    this.style.borderColor = '#ef7724';
                    const check = this.querySelector('.plan-check');
                    if (check) {
                        check.style.display = 'flex';
                    }

                    selectedPlan = {
                        id: this.dataset.id,
                        name: this.dataset.name,
                        price: parseFloat(this.dataset.price)
                    };

                    document.getElementById('selectedPlanName').textContent = selectedPlan.name;
                    document.getElementById('selectedPlanPrice').textContent = selectedPlan.price.toFixed(2) + ' $';
                    document.getElementById('selectedPlanTotal').textContent = selectedPlan.price.toFixed(2) + ' $';
                    document.getElementById('paymentSection').style.display = 'block';
                    if (errorElement) errorElement.style.display = 'none';
                    if (successElement) successElement.style.display = 'none';
                });
            });
        }

        @if($paypalClientId)
        // CORRECTION: Vérifier que PayPal est chargé
        if (typeof paypal !== 'undefined') {
            paypal.Buttons({
                createOrder: function() {
                    if (!selectedPlan) {
                        if (errorElement) {
                            errorElement.textContent = 'Veuillez d\'abord sélectionner un plan.';
                            errorElement.style.display = 'block';
                        }
                        return Promise.reject('Aucun plan sélectionné');
                    }
                    
                    if (loaderElement) loaderElement.style.display = 'block';
                    if (errorElement) errorElement.style.display = 'none';
                    if (successElement) successElement.style.display = 'none';

                    return fetch('{{ route("billing.payment.paypal.create") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            plan_id: selectedPlan.id,
                            amount: selectedPlan.price,
                            plan_name: selectedPlan.name
                        })
                    }).then(function(response) {
                        // CORRECTION: Gérer correctement la réponse
                        return parseJsonResponse(response);
                    }).then(function(data) {
                        if (loaderElement) loaderElement.style.display = 'none';
                        
                        if (data.success) {
                            lastCreatedOrderId = data.order_id || data.orderID || data.id;

                            if (!lastCreatedOrderId) {
                                throw new Error('ID de commande PayPal manquant.');
                            }

                            return lastCreatedOrderId;
                        } else {
                            if (errorElement) {
                                errorElement.textContent = data.message || 'Erreur lors de la création du paiement.';
                                errorElement.style.display = 'block';
                            }
                            throw new Error(data.message || 'Erreur');
                        }
                    }).catch(function(err) {
                        if (loaderElement) loaderElement.style.display = 'none';
                        if (errorElement) {
                            errorElement.textContent = err.message || 'Erreur de connexion au serveur.';
                            errorElement.style.display = 'block';
                        }
                        console.error('createOrder error:', err);
                        return Promise.reject(err);
                    });
                },
                onApprove: function(data) {
                    if (loaderElement) loaderElement.style.display = 'block';
                    if (errorElement) errorElement.style.display = 'none';
                    if (successElement) successElement.style.display = 'none';

                    const approvedOrderId = data.orderID || data.orderId || data.order_id || data.id || lastCreatedOrderId;

                    if (!approvedOrderId) {
                        if (loaderElement) loaderElement.style.display = 'none';
                        if (errorElement) {
                            errorElement.textContent = 'ID de commande PayPal manquant. Veuillez relancer le paiement.';
                            errorElement.style.display = 'block';
                        }
                        return Promise.reject(new Error('ID de commande PayPal manquant.'));
                    }
                    
                    return fetch('{{ route("billing.payment.paypal.capture") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ order_id: approvedOrderId })
                    }).then(function(response) {
                        // CORRECTION: Gérer correctement la réponse
                        return parseJsonResponse(response);
                    }).then(function(result) {
                        if (loaderElement) loaderElement.style.display = 'none';
                        
                        if (result.success) {
                            if (successElement) {
                                successElement.textContent = '✅ ' + (result.message || 'Paiement réussi !');
                                successElement.style.display = 'block';
                            }
                            // Redirection après un court délai
                            setTimeout(function() {
                                window.location.href = result.redirect_url || '{{ route("billing.success") }}';
                            }, 1500);
                        } else {
                            if (errorElement) {
                                errorElement.textContent = result.message || 'Erreur lors de la capture du paiement.';
                                errorElement.style.display = 'block';
                            }
                        }
                    }).catch(function(err) {
                        if (loaderElement) loaderElement.style.display = 'none';
                        if (errorElement) {
                            errorElement.textContent = err.message || 'Erreur lors de la capture du paiement.';
                            errorElement.style.display = 'block';
                        }
                        console.error('Capture error:', err);
                    });
                },
                onCancel: function() {
                    if (errorElement) {
                        errorElement.textContent = 'Paiement annulé. Vous pouvez réessayer.';
                        errorElement.style.display = 'block';
                    }
                    if (loaderElement) loaderElement.style.display = 'none';
                },
                onError: function(err) {
                    if (loaderElement) loaderElement.style.display = 'none';
                    if (errorElement) {
                        errorElement.textContent = 'Erreur de paiement. Veuillez réessayer.';
                        errorElement.style.display = 'block';
                    }
                    console.error('PayPal Error:', err);
                }
            }).render('#paypal-button-container');
        } else {
            console.error('PayPal SDK not loaded');
            if (errorElement) {
                errorElement.textContent = 'Le service de paiement n\'est pas disponible actuellement.';
                errorElement.style.display = 'block';
            }
        }
        @endif
    });
</script>
@endsection
