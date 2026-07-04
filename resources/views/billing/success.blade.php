@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="fas fa-check-circle"></i> Paiement réussi !</h4>
                </div>
                <div class="card-body text-center">
                    <div class="py-4">
                        <i class="fas fa-check-circle" style="font-size: 80px; color: #22c55e;"></i>
                    </div>
                    <h2>Merci pour votre paiement !</h2>
                    <p class="lead">Votre plan a été activé avec succès.</p>
                    <p>Un email de confirmation vous a été envoyé.</p>
                    <a href="{{ route('dashboard') }}" class="btn btn-primary mt-3">
                        <i class="fas fa-arrow-right"></i> Accéder au tableau de bord
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection