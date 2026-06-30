@extends('layouts.app')

@section('content')
    <main class="dashboard-content">
        <div class="page-header">
            <h1 class="page-title">
                <span class="page-title-icon"><i class="fas fa-building"></i></span>
                Mon Établissement
            </h1>
            <div class="page-actions">
                <a href="{{ route('etablissements.edit') }}" class="btn btn-primary">
                    <i class="fas fa-edit me-2"></i>Modifier mon établissement
                </a>
            </div>
        </div>

        @if(isset($noEstablishment) && $noEstablishment)
            <div class="empty-state-modern">
                <div class="empty-icon-modern">
                    <i class="fas fa-building"></i>
                </div>
                <h3 class="empty-title-modern">Aucun établissement associé</h3>
                <p class="empty-text-modern">Votre compte n'est lié à aucun établissement. Contactez l'administration.</p>
            </div>
        @else
            @php
                $activitiesCount = $etablissement->activities->count();
            @endphp

            <section class="etab-hero-strip">
                <div class="etab-hero-main">
                    <span class="etab-hero-kicker">{{ $etablissement->ville ?: 'Localisation à définir' }}</span>
                    <h2 class="etab-hero-title">{{ $etablissement->name }}</h2>
                    <p class="etab-hero-text">
                        @if($etablissement->country)
                            {{ $etablissement->country->name }}
                        @endif
                        @if($etablissement->province)
                             · {{ $etablissement->province->name }}
                        @endif
                    </p>
                </div>
                <div class="etab-hero-grid">
                    <div class="etab-hero-card">
                        <span class="etab-hero-card-label">Statut</span>
                        <strong>{!! $etablissement->is_active ? '<span class="text-success">Actif</span>' : '<span class="text-danger">Inactif</span>' !!}</strong>
                        <small>Compte {{ $etablissement->is_active ? 'activé' : 'désactivé' }}</small>
                    </div>
                    <div class="etab-hero-card">
                        <span class="etab-hero-card-label">Activités</span>
                        <strong>{{ $activitiesCount }}</strong>
                        <small>Activité(s) associée(s)</small>
                    </div>
                    <div class="etab-hero-card">
                        <span class="etab-hero-card-label">Contact</span>
                        <strong>{{ $etablissement->phone ?: 'Non renseigné' }}</strong>
                        <small>{{ $etablissement->email_contact ?: 'Email non renseigné' }}</small>
                    </div>
                </div>
            </section>

            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="main-card-modern">
                        <div class="card-header-modern">
                            <h3 class="card-title-modern">
                                <i class="fas fa-info-circle me-2"></i>Informations générales
                            </h3>
                        </div>
                        <div class="card-body-modern">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="text-muted">Nom</td>
                                    <td class="fw-semibold">{{ $etablissement->name }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Adresse</td>
                                    <td>{{ $etablissement->adresse ?: 'Non renseignée' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Ville</td>
                                    <td>{{ $etablissement->ville ?: 'Non renseignée' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Code postal</td>
                                    <td>{{ $etablissement->zip_code ?: 'Non renseigné' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Pays</td>
                                    <td>{{ $etablissement->country?->name ?: 'Non renseigné' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Province</td>
                                    <td>{{ $etablissement->province?->name ?: 'Non renseignée' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="main-card-modern">
                        <div class="card-header-modern">
                            <h3 class="card-title-modern">
                                <i class="fas fa-phone me-2"></i>Coordonnées
                            </h3>
                        </div>
                        <div class="card-body-modern">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="text-muted">Téléphone</td>
                                    <td>{{ $etablissement->phone ?: 'Non renseigné' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Fax</td>
                                    <td>{{ $etablissement->fax ?: 'Non renseigné' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Email contact</td>
                                    <td>{{ $etablissement->email_contact ?: 'Non renseigné' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Site web</td>
                                    <td>
                                        @if($etablissement->website)
                                            <a href="{{ $etablissement->website }}" target="_blank">{{ $etablissement->website }}</a>
                                        @else
                                            Non renseigné
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Utilisateur lié</td>
                                    <td>{{ $etablissement->user?->name ?: 'Aucun' }} ({{ $etablissement->user?->email ?: '-' }})</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="main-card-modern mt-3">
                        <div class="card-header-modern">
                            <h3 class="card-title-modern">
                                <i class="fas fa-tasks me-2"></i>Activités
                            </h3>
                        </div>
                        <div class="card-body-modern">
                            @if($activitiesCount > 0)
                                <ul class="list-group list-group-flush">
                                    @foreach($etablissement->activities as $activity)
                                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            {{ $activity->name }}
                                            @if($etablissement->primary_activity_id === $activity->id)
                                                <span class="badge bg-primary">Principale</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted mb-0">Aucune activité associée.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </main>
@endsection

@push('styles')
<style>
    .dashboard-content { padding-bottom: 2.5rem; }

    .etab-hero-strip {
        display: grid;
        grid-template-columns: minmax(0, 1.4fr) minmax(320px, 1fr);
        gap: 1.5rem;
        background: linear-gradient(135deg, var(--primary-color), #1a237e);
        border-radius: 16px;
        padding: 2rem 2rem;
        color: #fff;
        margin-bottom: 1.5rem;
    }

    .etab-hero-kicker {
        display: inline-block;
        background: rgba(255,255,255,0.15);
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        letter-spacing: 0.3px;
        margin-bottom: 0.75rem;
        text-transform: uppercase;
        font-weight: 600;
    }

    .etab-hero-title {
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        line-height: 1.2;
    }

    .etab-hero-text {
        font-size: 0.95rem;
        opacity: 0.85;
        margin-bottom: 0;
    }

    .etab-hero-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
    }

    .etab-hero-card {
        background: rgba(255,255,255,0.1);
        backdrop-filter: blur(8px);
        border-radius: 12px;
        padding: 1rem 1.25rem;
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
    }

    .etab-hero-card-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        opacity: 0.7;
    }

    .etab-hero-card strong {
        font-size: 1.25rem;
    }

    .etab-hero-card small {
        font-size: 0.8rem;
        opacity: 0.75;
    }

    .main-card-modern {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        overflow: hidden;
    }

    .card-header-modern {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #f0f0f0;
    }

    .card-title-modern {
        font-size: 1rem;
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
    }

    .card-body-modern {
        padding: 1.25rem 1.5rem;
    }

    .empty-state-modern {
        text-align: center;
        padding: 4rem 2rem;
    }

    .empty-icon-modern {
        font-size: 4rem;
        color: #ccc;
        margin-bottom: 1rem;
    }

    .empty-title-modern {
        font-size: 1.5rem;
        font-weight: 600;
        color: #666;
    }

    .empty-text-modern {
        color: #999;
        margin-bottom: 1.5rem;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .page-title {
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .page-title-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        background: linear-gradient(135deg, var(--primary-color), #3a56e4);
        color: #fff;
        border-radius: 12px;
        font-size: 1.1rem;
    }

    .table-borderless td {
        padding: 0.6rem 0;
        border: none;
    }

    .table-borderless td:first-child {
        width: 140px;
    }
</style>
@endpush
