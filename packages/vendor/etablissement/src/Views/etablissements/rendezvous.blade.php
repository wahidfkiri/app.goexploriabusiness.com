@extends('layouts.app')

@section('content')
<main class="dashboard-content rendezvous-page">
    <div class="rdv-hero">
        <div class="rdv-hero-copy">
            <div class="rdv-eyebrow">Planning clients</div>
            <h1 class="rdv-title">
                <span class="rdv-title-icon"><i class="fas fa-calendar-check"></i></span>
                Rendez Vous
            </h1>
            <p class="rdv-subtitle">
                Pilotez les rendez-vous des &eacute;tablissements dans un calendrier moderne, rapide et clair.
            </p>
        </div>

        <div class="rdv-hero-actions">
            <button type="button" class="btn btn-light rdv-btn rdv-btn-light" id="rdvTodayBtn">
                <i class="fas fa-calendar-day"></i>
                <span>Aujourd&apos;hui</span>
            </button>
            <button type="button" class="btn btn-primary rdv-btn rdv-btn-primary" id="rdvCreateBtn">
                <i class="fas fa-plus-circle"></i>
                <span>Nouveau rendez-vous</span>
            </button>
        </div>
    </div>

    <section class="rdv-stats-grid" id="rdvStatsGrid">
        <article class="rdv-stat-card rdv-stat-indigo">
            <div class="rdv-stat-meta">
                <span class="rdv-stat-label">Total p&eacute;riode</span>
                <strong class="rdv-stat-value" id="statTotal">0</strong>
                <span class="rdv-stat-foot">Tous les rendez-vous visibles</span>
            </div>
            <div class="rdv-stat-icon"><i class="fas fa-layer-group"></i></div>
        </article>

        <article class="rdv-stat-card rdv-stat-cyan">
            <div class="rdv-stat-meta">
                <span class="rdv-stat-label">Aujourd&apos;hui</span>
                <strong class="rdv-stat-value" id="statToday">0</strong>
                <span class="rdv-stat-foot">Rendez-vous du jour</span>
            </div>
            <div class="rdv-stat-icon"><i class="fas fa-sun"></i></div>
        </article>

        <article class="rdv-stat-card rdv-stat-emerald">
            <div class="rdv-stat-meta">
                <span class="rdv-stat-label">Confirm&eacute;s</span>
                <strong class="rdv-stat-value" id="statConfirmed">0</strong>
                <span class="rdv-stat-foot">Confirmes et verrouilles</span>
            </div>
            <div class="rdv-stat-icon"><i class="fas fa-check-circle"></i></div>
        </article>

        <article class="rdv-stat-card rdv-stat-amber">
            <div class="rdv-stat-meta">
                <span class="rdv-stat-label">A venir</span>
                <strong class="rdv-stat-value" id="statUpcoming">0</strong>
                <span class="rdv-stat-foot">Plages futures actives</span>
            </div>
            <div class="rdv-stat-icon"><i class="fas fa-hourglass-half"></i></div>
        </article>

        <article class="rdv-stat-card rdv-stat-rose">
            <div class="rdv-stat-meta">
                <span class="rdv-stat-label">Annul&eacute;s</span>
                <strong class="rdv-stat-value" id="statCancelled">0</strong>
                <span class="rdv-stat-foot">A surveiller</span>
            </div>
            <div class="rdv-stat-icon"><i class="fas fa-ban"></i></div>
        </article>
    </section>

    <section class="rdv-filter-card">
        <div class="rdv-filter-topbar">
            <div>
                <h2>Vue calendrier</h2>
                <p>Filtrez par &eacute;tablissement, statut, type et basculez entre mois, semaine, ann&eacute;e, jour ou liste.</p>
            </div>
            <div class="rdv-view-switch" role="group" aria-label="Changer la vue du calendrier">
                <button type="button" class="rdv-view-btn is-active" data-view="dayGridMonth">Mois</button>
                <button type="button" class="rdv-view-btn" data-view="timeGridWeek">Week</button>
                <button type="button" class="rdv-view-btn" data-view="multiMonthYear">Year</button>
                <button type="button" class="rdv-view-btn" data-view="timeGridDay">Day</button>
                <button type="button" class="rdv-view-btn" data-view="listMonth">Liste</button>
            </div>
        </div>

        <div class="row g-3 align-items-end">
            <div class="col-lg-4">
                <label class="form-label rdv-label" for="filterEtablissement">&Eacute;tablissement</label>
                <input type="hidden" id="filterEtablissement" value="">
                <div class="rdv-autocomplete" id="filterEtablissementAutocomplete">
                    <div class="rdv-search-wrap">
                        <i class="fas fa-building"></i>
                        <input type="text" class="form-control rdv-input rdv-search-input rdv-autocomplete-input" id="filterEtablissementSearch" placeholder="Rechercher un &eacute;tablissement" autocomplete="off">
                        <button type="button" class="rdv-autocomplete-clear d-none" id="filterEtablissementClear" aria-label="Effacer">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="rdv-autocomplete-menu d-none" id="filterEtablissementResults"></div>
                </div>
            </div>
            <div class="col-lg-3">
                <label class="form-label rdv-label" for="filterStatus">Statut</label>
                <select class="form-select rdv-input" id="filterStatus">
                    <option value="">Tous les statuts</option>
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3">
                <label class="form-label rdv-label" for="filterMeetingType">Type</label>
                <select class="form-select rdv-input" id="filterMeetingType">
                    <option value="">Tous les types</option>
                    @foreach($meetingTypeOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2">
                <label class="form-label rdv-label" for="filterSearch">Recherche</label>
                <div class="rdv-search-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" class="form-control rdv-input rdv-search-input" id="filterSearch" placeholder="Nom, contact, lieu">
                </div>
            </div>
        </div>
    </section>

    <section class="rdv-layout-grid">
        <div class="rdv-calendar-card">
            <div class="rdv-calendar-head">
                <div class="rdv-calendar-nav">
                    <button type="button" class="btn btn-light rdv-nav-btn" id="rdvPrevBtn" aria-label="Periode precedente">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <div>
                        <div class="rdv-calendar-label">P&eacute;riode</div>
                        <div class="rdv-calendar-title" id="rdvCalendarTitle">Chargement...</div>
                    </div>
                    <button type="button" class="btn btn-light rdv-nav-btn" id="rdvNextBtn" aria-label="Periode suivante">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>

                <div class="rdv-legend">
                    <span class="rdv-legend-item"><i class="rdv-dot" style="--dot:#4f46e5"></i>Planifie</span>
                    <span class="rdv-legend-item"><i class="rdv-dot" style="--dot:#059669"></i>Confirme</span>
                    <span class="rdv-legend-item"><i class="rdv-dot" style="--dot:#d97706"></i>Reporte</span>
                    <span class="rdv-legend-item"><i class="rdv-dot" style="--dot:#0f172a"></i>Termine</span>
                    <span class="rdv-legend-item"><i class="rdv-dot" style="--dot:#dc2626"></i>Annule</span>
                    <span class="rdv-legend-item"><i class="rdv-dot" style="--dot:#be123c"></i>Evenement pays</span>
                </div>
            </div>

            <div class="rdv-calendar-body">
                <div class="rdv-loading" id="rdvLoadingState">
                    <div class="spinner-border text-primary" role="status"></div>
                    <span>Chargement du planning...</span>
                </div>
                <div id="rendezVousCalendar"></div>
            </div>
        </div>

        <aside class="rdv-side-column">
            <article class="rdv-side-card rdv-side-highlight" id="selectedRdvCard">
                <div class="rdv-side-card-head">
                    <div>
                        <span class="rdv-panel-kicker">Focus</span>
                        <h3>D&eacute;tail du rendez-vous</h3>
                    </div>
                    <span class="rdv-side-badge" id="selectedRdvStatus">Aucun</span>
                </div>
                <div class="rdv-side-empty" id="selectedRdvEmpty">
                    <i class="fas fa-calendar-plus"></i>
                    <p>S&eacute;lectionnez un rendez-vous dans le calendrier pour afficher ses d&eacute;tails ici.</p>
                </div>
                <div class="rdv-side-content d-none" id="selectedRdvContent">
                    <h4 id="selectedRdvTitle">-</h4>
                    <div class="rdv-side-lines">
                        <div><i class="fas fa-building"></i><span id="selectedRdvEtablissement">-</span></div>
                        <div><i class="fas fa-clock"></i><span id="selectedRdvSchedule">-</span></div>
                        <div><i class="fas fa-user"></i><span id="selectedRdvContact">-</span></div>
                        <div><i class="fas fa-location-dot"></i><span id="selectedRdvLocation">-</span></div>
                        <div><i class="fas fa-video"></i><span id="selectedRdvType">-</span></div>
                    </div>
                    <div class="rdv-side-note" id="selectedRdvNotes">Aucune note</div>
                    <div class="rdv-side-actions">
                        <button type="button" class="btn btn-outline-primary rdv-side-btn" id="selectedRdvEditBtn">
                            <i class="fas fa-pen"></i>Modifier
                        </button>
                        <button type="button" class="btn btn-outline-danger rdv-side-btn" id="selectedRdvDeleteBtn">
                            <i class="fas fa-trash"></i>Supprimer
                        </button>
                    </div>
                </div>
            </article>

            <article class="rdv-side-card">
                <div class="rdv-side-card-head compact">
                    <div>
                        <span class="rdv-panel-kicker">Apercu</span>
                        <h3>Prochains rendez-vous</h3>
                    </div>
                </div>
                <div class="rdv-upcoming-list" id="upcomingRdvList">
                    <div class="rdv-upcoming-empty">Aucun rendez-vous futur pour l&apos;instant.</div>
                </div>
            </article>
        </aside>
    </section>
</main>

<div class="modal fade" id="rdvFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content rdv-modal-content">
            <form id="rdvForm" novalidate>
                <div class="modal-header rdv-modal-header">
                    <div>
                        <div class="rdv-modal-kicker">Edition rapide</div>
                        <h5 class="modal-title" id="rdvModalTitle">Nouveau rendez-vous</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body rdv-modal-body">
                    <input type="hidden" id="rdvId" name="id">

                    <div class="row g-3">
                        <div class="col-lg-5">
                            <label class="form-label rdv-label" for="rdvEtablissementId">
                                <span>&Eacute;tablissement *</span>
                                <span class="rdv-field-help" data-rdv-tooltip data-bs-toggle="tooltip" data-bs-placement="top" title="Choisissez l&apos;&eacute;tablissement concern&eacute; pour lier le rendez-vous &agrave; sa fiche et &agrave; son calendrier.">
                                    <i class="fas fa-circle-info"></i>
                                </span>
                            </label>
                            <input type="hidden" id="rdvEtablissementId" name="etablissement_id" required>
                            <div class="rdv-autocomplete" id="rdvEtablissementAutocomplete">
                                <div class="rdv-search-wrap">
                                    <i class="fas fa-building"></i>
                                    <input type="text" class="form-control rdv-input rdv-search-input rdv-autocomplete-input" id="rdvEtablissementSearch" placeholder="Rechercher et selectionner un etablissement" autocomplete="off">
                                    <button type="button" class="rdv-autocomplete-clear d-none" id="rdvEtablissementClear" aria-label="Effacer">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <div class="rdv-autocomplete-menu d-none" id="rdvEtablissementResults"></div>
                            </div>
                            <div class="invalid-feedback">Choisissez un &eacute;tablissement.</div>
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label rdv-label" for="rdvTitle">
                                <span>Titre *</span>
                                <span class="rdv-field-help" data-rdv-tooltip data-bs-toggle="tooltip" data-bs-placement="top" title="Saisissez un titre court et clair pour identifier rapidement ce rendez-vous dans le calendrier.">
                                    <i class="fas fa-circle-info"></i>
                                </span>
                            </label>
                            <input type="text" class="form-control rdv-input" id="rdvTitle" name="title" maxlength="255" required>
                            <div class="invalid-feedback">Le titre est requis.</div>
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label rdv-label" for="rdvStatus">
                                <span>Statut *</span>
                                <span class="rdv-field-help" data-rdv-tooltip data-bs-toggle="tooltip" data-bs-placement="top" title="Le statut permet de suivre l&apos;avancement: planifi&eacute;, confirm&eacute;, termin&eacute;, annul&eacute; ou report&eacute;.">
                                    <i class="fas fa-circle-info"></i>
                                </span>
                            </label>
                            <select class="form-select rdv-input" id="rdvStatus" name="status" required>
                                @foreach($statusOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-3">
                            <label class="form-label rdv-label" for="rdvMeetingType">
                                <span>Type</span>
                                <span class="rdv-field-help" data-rdv-tooltip data-bs-toggle="tooltip" data-bs-placement="top" title="Pr&eacute;cisez le format du rendez-vous: sur place, visio, appel, d&eacute;mo ou suivi.">
                                    <i class="fas fa-circle-info"></i>
                                </span>
                            </label>
                            <select class="form-select rdv-input" id="rdvMeetingType" name="meeting_type">
                                <option value="">S&eacute;lectionner</option>
                                @foreach($meetingTypeOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label rdv-label" for="rdvStartsAt">
                                <span>D&eacute;but *</span>
                                <span class="rdv-field-help" data-rdv-tooltip data-bs-toggle="tooltip" data-bs-placement="top" title="Choisissez la date et l&apos;heure de d&eacute;but du rendez-vous.">
                                    <i class="fas fa-circle-info"></i>
                                </span>
                            </label>
                            <input type="datetime-local" class="form-control rdv-input" id="rdvStartsAt" name="starts_at" required>
                            <div class="invalid-feedback">La date de d&eacute;but est requise.</div>
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label rdv-label" for="rdvEndsAt">
                                <span>Fin</span>
                                <span class="rdv-field-help" data-rdv-tooltip data-bs-toggle="tooltip" data-bs-placement="top" title="La fin est optionnelle. Si elle est vide, une dur&eacute;e par d&eacute;faut sera appliqu&eacute;e.">
                                    <i class="fas fa-circle-info"></i>
                                </span>
                            </label>
                            <input type="datetime-local" class="form-control rdv-input" id="rdvEndsAt" name="ends_at">
                            <div class="invalid-feedback">La date de fin doit &ecirc;tre post&eacute;rieure.</div>
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label rdv-label" for="rdvColor">
                                <span>Couleur</span>
                                <span class="rdv-field-help" data-rdv-tooltip data-bs-toggle="tooltip" data-bs-placement="top" title="Choisissez une couleur pour distinguer visuellement ce rendez-vous dans le calendrier.">
                                    <i class="fas fa-circle-info"></i>
                                </span>
                            </label>
                            <div class="rdv-color-wrap">
                                <input type="color" class="form-control form-control-color" id="rdvColor" name="color" value="#4f46e5">
                                <label class="rdv-checkbox" for="rdvAllDay">
                                    <input type="checkbox" id="rdvAllDay" name="all_day" value="1">
                                    <span>Toute la journ&eacute;e</span>
                                    <span class="rdv-field-help" data-rdv-tooltip data-bs-toggle="tooltip" data-bs-placement="top" title="Activez cette option si le rendez-vous n&apos;a pas d&apos;horaire pr&eacute;cis et doit couvrir toute la journ&eacute;e.">
                                        <i class="fas fa-circle-info"></i>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <label class="form-label rdv-label" for="rdvContactName">
                                <span>Contact</span>
                                <span class="rdv-field-help" data-rdv-tooltip data-bs-toggle="tooltip" data-bs-placement="top" title="Nom du contact principal pour ce rendez-vous.">
                                    <i class="fas fa-circle-info"></i>
                                </span>
                            </label>
                            <input type="text" class="form-control rdv-input" id="rdvContactName" name="contact_name" maxlength="255">
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label rdv-label" for="rdvContactEmail">
                                <span>Email</span>
                                <span class="rdv-field-help" data-rdv-tooltip data-bs-toggle="tooltip" data-bs-placement="top" title="Adresse email du contact pour confirmer ou relancer le rendez-vous.">
                                    <i class="fas fa-circle-info"></i>
                                </span>
                            </label>
                            <input type="email" class="form-control rdv-input" id="rdvContactEmail" name="contact_email" maxlength="255">
                            <div class="invalid-feedback">L&apos;email n&apos;est pas valide.</div>
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label rdv-label" for="rdvContactPhone">
                                <span>T&eacute;l&eacute;phone</span>
                                <span class="rdv-field-help" data-rdv-tooltip data-bs-toggle="tooltip" data-bs-placement="top" title="Num&eacute;ro direct du contact ou du standard &agrave; joindre.">
                                    <i class="fas fa-circle-info"></i>
                                </span>
                            </label>
                            <input type="text" class="form-control rdv-input" id="rdvContactPhone" name="contact_phone" maxlength="50">
                        </div>

                        <div class="col-12">
                            <label class="form-label rdv-label" for="rdvLocation">
                                <span>Lieu / lien</span>
                                <span class="rdv-field-help" data-rdv-tooltip data-bs-toggle="tooltip" data-bs-placement="top" title="Indiquez l&apos;adresse, la salle, le lien visio ou toute information d&apos;acc&egrave;s utile.">
                                    <i class="fas fa-circle-info"></i>
                                </span>
                            </label>
                            <input type="text" class="form-control rdv-input" id="rdvLocation" name="location" maxlength="255" placeholder="Adresse, salle, visio, lien meet...">
                        </div>

                        <div class="col-12">
                            <label class="form-label rdv-label" for="rdvNotes">
                                <span>Notes</span>
                                <span class="rdv-field-help" data-rdv-tooltip data-bs-toggle="tooltip" data-bs-placement="top" title="Ajoutez ici l&apos;objectif, les points &agrave; pr&eacute;parer ou les informations &agrave; retenir.">
                                    <i class="fas fa-circle-info"></i>
                                </span>
                            </label>
                            <textarea class="form-control rdv-input rdv-textarea" id="rdvNotes" name="notes" rows="5" placeholder="Objectif, points a preparer, informations utiles..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer rdv-modal-footer">
                    <button type="button" class="btn btn-light rdv-btn rdv-btn-light" data-bs-dismiss="modal">Fermer</button>
                    <button type="submit" class="btn btn-primary rdv-btn rdv-btn-primary" id="rdvSubmitBtn">
                        <i class="fas fa-save"></i>
                        <span>Enregistrer</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="toast-container position-fixed bottom-0 end-0 p-3" id="rdvToastContainer" style="z-index: 2000;"></div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/locales-all.global.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const endpoints = {
        events: @json(route('api.etablissements.rendezvous.events')),
        statistics: @json(route('etablissements.rendezvous.statistics')),
        searchEtablissements: @json(route('api.etablissements.rendezvous.etablissements.search')),
        store: @json(route('api.etablissements.rendezvous.store')),
        update: @json(route('api.etablissements.rendezvous.update', ['id' => '__ID__'])),
        show: @json(route('api.etablissements.rendezvous.show', ['id' => '__ID__'])),
        destroy: @json(route('api.etablissements.rendezvous.destroy', ['id' => '__ID__'])),
        move: @json(route('api.etablissements.rendezvous.move', ['id' => '__ID__'])),
    };

    const state = {
        calendar: null,
        currentView: 'dayGridMonth',
        selectedRecord: null,
        upcomingRecords: {},
        filters: {
            etablissement_id: '',
            status: '',
            meeting_type: '',
            search: '',
        },
    };

    const elements = {
        calendar: document.getElementById('rendezVousCalendar'),
        loading: document.getElementById('rdvLoadingState'),
        title: document.getElementById('rdvCalendarTitle'),
        todayBtn: document.getElementById('rdvTodayBtn'),
        prevBtn: document.getElementById('rdvPrevBtn'),
        nextBtn: document.getElementById('rdvNextBtn'),
        createBtn: document.getElementById('rdvCreateBtn'),
        filterEtablissement: document.getElementById('filterEtablissement'),
        filterEtablissementSearch: document.getElementById('filterEtablissementSearch'),
        filterEtablissementResults: document.getElementById('filterEtablissementResults'),
        filterEtablissementClear: document.getElementById('filterEtablissementClear'),
        filterEtablissementAutocomplete: document.getElementById('filterEtablissementAutocomplete'),
        filterStatus: document.getElementById('filterStatus'),
        filterMeetingType: document.getElementById('filterMeetingType'),
        filterSearch: document.getElementById('filterSearch'),
        stats: {
            total: document.getElementById('statTotal'),
            today: document.getElementById('statToday'),
            confirmed: document.getElementById('statConfirmed'),
            upcoming: document.getElementById('statUpcoming'),
            cancelled: document.getElementById('statCancelled'),
        },
        selectedStatus: document.getElementById('selectedRdvStatus'),
        selectedEmpty: document.getElementById('selectedRdvEmpty'),
        selectedContent: document.getElementById('selectedRdvContent'),
        selectedTitle: document.getElementById('selectedRdvTitle'),
        selectedEtablissement: document.getElementById('selectedRdvEtablissement'),
        selectedSchedule: document.getElementById('selectedRdvSchedule'),
        selectedContact: document.getElementById('selectedRdvContact'),
        selectedLocation: document.getElementById('selectedRdvLocation'),
        selectedType: document.getElementById('selectedRdvType'),
        selectedNotes: document.getElementById('selectedRdvNotes'),
        editBtn: document.getElementById('selectedRdvEditBtn'),
        deleteBtn: document.getElementById('selectedRdvDeleteBtn'),
        upcomingList: document.getElementById('upcomingRdvList'),
        form: document.getElementById('rdvForm'),
        modalTitle: document.getElementById('rdvModalTitle'),
        modalEl: document.getElementById('rdvFormModal'),
        submitBtn: document.getElementById('rdvSubmitBtn'),
        fields: {
            id: document.getElementById('rdvId'),
            etablissement_id: document.getElementById('rdvEtablissementId'),
            etablissement_search: document.getElementById('rdvEtablissementSearch'),
            etablissement_results: document.getElementById('rdvEtablissementResults'),
            etablissement_clear: document.getElementById('rdvEtablissementClear'),
            etablissement_autocomplete: document.getElementById('rdvEtablissementAutocomplete'),
            title: document.getElementById('rdvTitle'),
            status: document.getElementById('rdvStatus'),
            meeting_type: document.getElementById('rdvMeetingType'),
            starts_at: document.getElementById('rdvStartsAt'),
            ends_at: document.getElementById('rdvEndsAt'),
            all_day: document.getElementById('rdvAllDay'),
            color: document.getElementById('rdvColor'),
            contact_name: document.getElementById('rdvContactName'),
            contact_email: document.getElementById('rdvContactEmail'),
            contact_phone: document.getElementById('rdvContactPhone'),
            location: document.getElementById('rdvLocation'),
            notes: document.getElementById('rdvNotes'),
        },
        toastContainer: document.getElementById('rdvToastContainer'),
    };

    const modal = new bootstrap.Modal(elements.modalEl);
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const autocompleteState = {};

    document.querySelectorAll('[data-rdv-tooltip]').forEach((element) => {
        new bootstrap.Tooltip(element, {
            container: 'body',
            trigger: 'hover focus',
        });
    });

    const firstValidationError = (errors) => {
        if (!errors || typeof errors !== 'object') {
            return '';
        }

        const firstKey = Object.keys(errors)[0];
        if (!firstKey || !Array.isArray(errors[firstKey])) {
            return '';
        }

        return errors[firstKey][0] || '';
    };

    const jsonFetch = async (url, options = {}) => {
        const config = {
            method: 'GET',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...options.headers,
            },
            ...options,
        };

        if (!(config.body instanceof FormData) && config.method !== 'GET') {
            config.headers['Content-Type'] = 'application/json';
        }

        if (config.method !== 'GET' && csrfToken) {
            config.headers['X-CSRF-TOKEN'] = csrfToken;
        }

        const response = await fetch(url, config);
        const contentType = response.headers.get('content-type') || '';
        const payload = contentType.includes('application/json') ? await response.json() : await response.text();

        if (!response.ok) {
            const message = typeof payload === 'object'
                ? payload.message || firstValidationError(payload.errors) || 'Une erreur serveur est survenue.'
                : 'Une erreur serveur est survenue.';
            throw { response, payload, message };
        }

        return payload;
    };

    const escapeHtml = (value) => {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    };

    const showToast = (type, message, title = 'Rendez-vous') => {
        const toast = document.createElement('div');
        toast.className = `toast rdv-toast rdv-toast-${type}`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        toast.innerHTML = `
            <div class="toast-header">
                <strong class="me-auto">${escapeHtml(title)}</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">${escapeHtml(message)}</div>
        `;
        elements.toastContainer.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast, { delay: 3800 });
        bsToast.show();
        toast.addEventListener('hidden.bs.toast', () => toast.remove());
    };

    const debounce = (callback, delay = 350) => {
        let timer;
        return (...args) => {
            window.clearTimeout(timer);
            timer = window.setTimeout(() => callback(...args), delay);
        };
    };

    const mapEtablissementResult = (item) => ({
        id: item.id,
        name: item.name || '',
        ville: item.ville || '',
        email_contact: item.email_contact || '',
        phone: item.phone || '',
        adresse: item.adresse || '',
        label: item.label || [item.name, item.ville].filter(Boolean).join(' - '),
    });

    const createEtablissementAutocomplete = ({
        key,
        hiddenInput,
        textInput,
        resultsContainer,
        clearButton,
        wrapper,
        placeholder,
        onSelect,
        onClear,
    }) => {
        if (!hiddenInput || !textInput || !resultsContainer || !wrapper) {
            return {
                setSelected: () => {},
                clearSelection: () => {},
                getSelected: () => null,
            };
        }

        autocompleteState[key] = {
            selected: null,
            items: [],
            activeIndex: -1,
            requestId: 0,
        };

        const stateRef = autocompleteState[key];

        const hideMenu = () => {
            resultsContainer.classList.add('d-none');
            resultsContainer.innerHTML = '';
            stateRef.activeIndex = -1;
        };

        const updateClearButton = () => {
            clearButton?.classList.toggle('d-none', !hiddenInput.value && !textInput.value.trim());
        };

        const setSelected = (item, triggerCallback = true) => {
            stateRef.selected = item ? mapEtablissementResult(item) : null;
            hiddenInput.value = stateRef.selected?.id || '';
            textInput.value = stateRef.selected?.label || '';
            updateClearButton();
            hideMenu();

            if (triggerCallback && typeof onSelect === 'function') {
                onSelect(stateRef.selected);
            }
        };

        const clearSelection = (triggerCallback = true) => {
            stateRef.selected = null;
            hiddenInput.value = '';
            textInput.value = '';
            updateClearButton();
            hideMenu();

            if (triggerCallback && typeof onClear === 'function') {
                onClear();
            }
        };

        const renderItems = (items) => {
            stateRef.items = items.map(mapEtablissementResult);

            if (stateRef.items.length === 0) {
                resultsContainer.innerHTML = '<div class="rdv-autocomplete-empty">Aucun etablissement trouve.</div>';
                resultsContainer.classList.remove('d-none');
                return;
            }

            resultsContainer.innerHTML = stateRef.items.map((item, index) => `
                <button type="button" class="rdv-autocomplete-item${index === stateRef.activeIndex ? ' is-active' : ''}" data-index="${index}">
                    <strong>${escapeHtml(item.name || 'Etablissement')}</strong>
                    <small>${escapeHtml(item.ville || item.adresse || item.email_contact || '')}</small>
                </button>
            `).join('');

            resultsContainer.classList.remove('d-none');

            resultsContainer.querySelectorAll('.rdv-autocomplete-item').forEach((button) => {
                button.addEventListener('mousedown', (event) => {
                    event.preventDefault();
                    const selected = stateRef.items[Number(button.dataset.index)];
                    setSelected(selected);
                });
            });
        };

        const fetchItems = debounce(async (query = '') => {
            stateRef.requestId += 1;
            const currentRequestId = stateRef.requestId;

            try {
                const params = new URLSearchParams({
                    q: query,
                    limit: query ? '12' : '8',
                }).toString();
                const response = await jsonFetch(`${endpoints.searchEtablissements}?${params}`);

                if (currentRequestId !== stateRef.requestId) {
                    return;
                }

                renderItems(response.data || []);
            } catch (error) {
                hideMenu();
                showToast('danger', error.message || 'Impossible de rechercher les etablissements.');
            }
        }, 260);

        textInput.setAttribute('placeholder', placeholder);

        textInput.addEventListener('focus', () => {
            fetchItems(textInput.value.trim());
        });

        textInput.addEventListener('input', () => {
            if (stateRef.selected && textInput.value.trim() !== stateRef.selected.label) {
                hiddenInput.value = '';
                stateRef.selected = null;
            }
            updateClearButton();
            fetchItems(textInput.value.trim());
        });

        textInput.addEventListener('keydown', (event) => {
            if (resultsContainer.classList.contains('d-none')) {
                return;
            }

            if (event.key === 'ArrowDown') {
                event.preventDefault();
                stateRef.activeIndex = Math.min(stateRef.activeIndex + 1, stateRef.items.length - 1);
                renderItems(stateRef.items);
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                stateRef.activeIndex = Math.max(stateRef.activeIndex - 1, 0);
                renderItems(stateRef.items);
            } else if (event.key === 'Enter' && stateRef.items[stateRef.activeIndex]) {
                event.preventDefault();
                setSelected(stateRef.items[stateRef.activeIndex]);
            } else if (event.key === 'Escape') {
                hideMenu();
            }
        });

        clearButton?.addEventListener('click', () => {
            clearSelection();
            textInput.focus();
        });

        document.addEventListener('click', (event) => {
            if (!wrapper.contains(event.target)) {
                hideMenu();
            }
        });

        updateClearButton();

        return {
            setSelected,
            clearSelection,
            getSelected: () => stateRef.selected,
        };
    };

    const filterEtablissementAutocomplete = createEtablissementAutocomplete({
        key: 'filter',
        hiddenInput: elements.filterEtablissement,
        textInput: elements.filterEtablissementSearch,
        resultsContainer: elements.filterEtablissementResults,
        clearButton: elements.filterEtablissementClear,
        wrapper: elements.filterEtablissementAutocomplete,
        placeholder: 'Rechercher un etablissement',
        onSelect: (item) => {
            state.filters.etablissement_id = item?.id ? String(item.id) : '';
            refreshCalendarData();
        },
        onClear: () => {
            state.filters.etablissement_id = '';
            refreshCalendarData();
        },
    });

    const modalEtablissementAutocomplete = createEtablissementAutocomplete({
        key: 'modal',
        hiddenInput: elements.fields.etablissement_id,
        textInput: elements.fields.etablissement_search,
        resultsContainer: elements.fields.etablissement_results,
        clearButton: elements.fields.etablissement_clear,
        wrapper: elements.fields.etablissement_autocomplete,
        placeholder: 'Rechercher et selectionner un etablissement',
        onSelect: (item) => {
            autofillContactFromEtablissement(item);
            clearFormValidation();
        },
        onClear: () => {
            clearFormValidation();
        },
    });

    const updateCalendarTitle = () => {
        if (state.calendar) {
            elements.title.textContent = state.calendar.view.title;
        }
    };

    const collectFilters = () => ({
        ...state.filters,
        start: state.calendar?.view?.activeStart?.toISOString() || '',
        end: state.calendar?.view?.activeEnd?.toISOString() || '',
    });

    const loadStatistics = async () => {
        try {
            const query = new URLSearchParams(collectFilters()).toString();
            const response = await jsonFetch(`${endpoints.statistics}?${query}`);
            const stats = response.data || {};
            elements.stats.total.textContent = stats.total || 0;
            elements.stats.today.textContent = stats.today || 0;
            elements.stats.confirmed.textContent = stats.confirmed || 0;
            elements.stats.upcoming.textContent = stats.upcoming || 0;
            elements.stats.cancelled.textContent = stats.cancelled || 0;
        } catch (error) {
            showToast('danger', error.message || 'Impossible de charger les statistiques.');
        }
    };

    const showLoading = (active) => {
        elements.loading.classList.toggle('is-visible', active);
    };

    const formatDateTime = (value, allDay = false) => {
        if (!value) {
            return '-';
        }
        const date = new Date(value);
        if (Number.isNaN(date.getTime())) {
            return value;
        }
        return allDay
            ? date.toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' })
            : date.toLocaleString('fr-FR', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
            });
    };

    const formatRange = (record) => {
        if (!record.starts_at) {
            return '-';
        }
        if (!record.ends_at) {
            return formatDateTime(record.starts_at, record.all_day);
        }
        return `${formatDateTime(record.starts_at, record.all_day)} -> ${formatDateTime(record.ends_at, record.all_day)}`;
    };

    const isCountryCalendarRecord = (record) => (record?.source_type || '') === 'country_calendar_event';

    const setSelectedRecord = (record) => {
        state.selectedRecord = record;

        if (!record) {
            elements.selectedEmpty.classList.remove('d-none');
            elements.selectedContent.classList.add('d-none');
            elements.selectedStatus.textContent = 'Aucun';
            elements.selectedStatus.style.background = '#e2e8f0';
            elements.selectedStatus.style.color = '#0f172a';
            elements.editBtn.classList.remove('d-none');
            elements.deleteBtn.classList.remove('d-none');
            return;
        }

        const isCountryEvent = isCountryCalendarRecord(record);
        elements.selectedEmpty.classList.add('d-none');
        elements.selectedContent.classList.remove('d-none');
        elements.selectedTitle.textContent = record.title || '-';
        elements.selectedEtablissement.textContent = [record.etablissement_name, record.etablissement_city].filter(Boolean).join(' - ') || '-';
        elements.selectedSchedule.textContent = formatRange(record);
        elements.selectedContact.textContent = isCountryEvent
            ? 'Calendrier pays'
            : ([record.contact_name, record.contact_phone, record.contact_email].filter(Boolean).join(' | ') || 'Aucun contact');
        elements.selectedLocation.textContent = record.location || (isCountryEvent ? 'Pays associe' : 'Aucun lieu precise');
        elements.selectedType.textContent = record.meeting_type_label || (isCountryEvent ? 'Evenement pays' : 'Type libre');
        elements.selectedNotes.textContent = record.notes || (isCountryEvent ? 'Evenement pays synchronise automatiquement.' : 'Aucune note renseignee.');
        elements.selectedStatus.textContent = record.status_label || record.status || '-';
        elements.selectedStatus.style.background = record.color || '#4f46e5';
        elements.selectedStatus.style.color = '#ffffff';
        elements.editBtn.classList.toggle('d-none', isCountryEvent);
        elements.deleteBtn.classList.toggle('d-none', isCountryEvent);
    };

    const loadRecord = async (id) => {
        const response = await jsonFetch(endpoints.show.replace('__ID__', id));
        setSelectedRecord(response.data || null);
    };

    const clearFormValidation = () => {
        elements.form.querySelectorAll('.is-invalid').forEach((element) => element.classList.remove('is-invalid'));
        elements.fields.etablissement_search.classList.remove('is-invalid');
    };

    const applyValidationErrors = (errors = {}) => {
        clearFormValidation();
        Object.entries(errors).forEach(([field, messages]) => {
            const input = elements.form.querySelector(`[name="${field}"]`);
            if (!input) {
                return;
            }
            if (field === 'etablissement_id') {
                elements.fields.etablissement_search.classList.add('is-invalid');
            } else {
                input.classList.add('is-invalid');
            }
            const wrapper = input.closest('.col-lg-3, .col-lg-4, .col-lg-5, .col-12');
            const feedback = wrapper?.querySelector('.invalid-feedback');
            if (feedback && Array.isArray(messages) && messages[0]) {
                feedback.textContent = messages[0];
            }
        });
    };

    const autofillContactFromEtablissement = (selected = null) => {
        const item = selected || modalEtablissementAutocomplete.getSelected();
        if (!item || !item.id) {
            return;
        }

        if (!elements.fields.contact_email.value && item.email_contact) {
            elements.fields.contact_email.value = item.email_contact;
        }
        if (!elements.fields.contact_phone.value && item.phone) {
            elements.fields.contact_phone.value = item.phone;
        }
    };

    const populateForm = (record = null) => {
        elements.form.reset();
        clearFormValidation();
        elements.fields.id.value = record?.id || '';
        if (record?.etablissement_id) {
            modalEtablissementAutocomplete.setSelected({
                id: record.etablissement_id,
                name: record.etablissement_name || '',
                ville: record.etablissement_city || '',
                email_contact: record.etablissement_email || '',
                phone: record.etablissement_phone || '',
                label: [record.etablissement_name, record.etablissement_city].filter(Boolean).join(' - '),
            }, false);
        } else {
            modalEtablissementAutocomplete.clearSelection(false);
        }
        elements.fields.title.value = record?.title || '';
        elements.fields.status.value = record?.status || 'planned';
        elements.fields.meeting_type.value = record?.meeting_type || '';
        elements.fields.starts_at.value = record?.starts_at || '';
        elements.fields.ends_at.value = record?.ends_at || '';
        elements.fields.all_day.checked = Boolean(record?.all_day);
        elements.fields.color.value = record?.color || '#4f46e5';
        elements.fields.contact_name.value = record?.contact_name || '';
        elements.fields.contact_email.value = record?.contact_email || '';
        elements.fields.contact_phone.value = record?.contact_phone || '';
        elements.fields.location.value = record?.location || '';
        elements.fields.notes.value = record?.notes || '';
        elements.modalTitle.innerHTML = record?.id ? 'Modifier le rendez-vous' : 'Nouveau rendez-vous';
        autofillContactFromEtablissement();
    };

    const openCreateModal = (prefill = {}) => {
        populateForm({
            status: 'planned',
            color: '#4f46e5',
            ...prefill,
        });
        modal.show();
    };

    const openEditModal = async (id) => {
        if (String(id).startsWith('country-event-')) {
            showToast('warning', 'Cet evenement pays est synchronise automatiquement.');
            return;
        }

        try {
            const response = await jsonFetch(endpoints.show.replace('__ID__', id));
            populateForm(response.data || null);
            modal.show();
        } catch (error) {
            showToast('danger', error.message || 'Impossible de charger le rendez-vous.');
        }
    };

    const buildPayload = () => ({
        etablissement_id: elements.fields.etablissement_id.value,
        title: elements.fields.title.value.trim(),
        status: elements.fields.status.value,
        meeting_type: elements.fields.meeting_type.value || null,
        starts_at: elements.fields.starts_at.value,
        ends_at: elements.fields.ends_at.value || null,
        all_day: elements.fields.all_day.checked,
        color: elements.fields.color.value,
        contact_name: elements.fields.contact_name.value.trim() || null,
        contact_email: elements.fields.contact_email.value.trim() || null,
        contact_phone: elements.fields.contact_phone.value.trim() || null,
        location: elements.fields.location.value.trim() || null,
        notes: elements.fields.notes.value.trim() || null,
    });

    const validateFront = (payload) => {
        clearFormValidation();
        const errors = {};

        if (!payload.etablissement_id) {
            errors.etablissement_id = ['Choisissez un etablissement.'];
        }
        if (!payload.title) {
            errors.title = ['Le titre est requis.'];
        }
        if (!payload.starts_at) {
            errors.starts_at = ['La date de debut est requise.'];
        }
        if (payload.contact_email && !/^\S+@\S+\.\S+$/.test(payload.contact_email)) {
            errors.contact_email = ['L email nest pas valide.'];
        }
        if (payload.ends_at && payload.starts_at && payload.ends_at < payload.starts_at) {
            errors.ends_at = ['La date de fin doit etre superieure ou egale au debut.'];
        }

        if (Object.keys(errors).length > 0) {
            applyValidationErrors(errors);
            return false;
        }

        return true;
    };

    const refreshCalendarData = async () => {
        state.calendar?.refetchEvents();
        await loadStatistics();
    };

    const persistRecord = async (event) => {
        event.preventDefault();
        const payload = buildPayload();

        if (!validateFront(payload)) {
            showToast('warning', 'Merci de corriger les champs obligatoires.');
            return;
        }

        const id = elements.fields.id.value;
        const isUpdate = Boolean(id);
        elements.submitBtn.disabled = true;
        elements.submitBtn.classList.add('is-loading');

        try {
            const response = await jsonFetch(
                isUpdate ? endpoints.update.replace('__ID__', id) : endpoints.store,
                {
                    method: isUpdate ? 'PUT' : 'POST',
                    body: JSON.stringify(payload),
                }
            );

            modal.hide();
            showToast('success', response.message || 'Rendez-vous enregistre.');
            await refreshCalendarData();
            if (response.data?.id) {
                setSelectedRecord(response.data);
            }
        } catch (error) {
            if (error.payload?.errors) {
                applyValidationErrors(error.payload.errors);
            }
            showToast('danger', error.message || 'Impossible denregistrer le rendez-vous.');
        } finally {
            elements.submitBtn.disabled = false;
            elements.submitBtn.classList.remove('is-loading');
        }
    };

    const removeRecord = async (id) => {
        if (String(id).startsWith('country-event-')) {
            showToast('warning', 'Cet evenement pays est synchronise automatiquement.');
            return;
        }

        if (!window.confirm('Supprimer ce rendez-vous ?')) {
            return;
        }

        try {
            const response = await jsonFetch(endpoints.destroy.replace('__ID__', id), {
                method: 'DELETE',
            });
            showToast('success', response.message || 'Rendez-vous supprime.');
            setSelectedRecord(null);
            await refreshCalendarData();
        } catch (error) {
            showToast('danger', error.message || 'Impossible de supprimer le rendez-vous.');
        }
    };

    const syncMovedEvent = async (changeInfo) => {
        if (isCountryCalendarRecord(changeInfo.event.extendedProps || {})) {
            changeInfo.revert();
            showToast('warning', 'Les evenements pays ne peuvent pas etre deplaces.');
            return;
        }

        let eventEnd = changeInfo.event.end ? new Date(changeInfo.event.end) : null;
        if (changeInfo.event.allDay && eventEnd) {
            eventEnd = new Date(eventEnd.getTime() - (60 * 1000));
        }

        const payload = {
            starts_at: changeInfo.event.start ? changeInfo.event.start.toISOString() : null,
            ends_at: eventEnd ? eventEnd.toISOString() : null,
            all_day: changeInfo.event.allDay,
        };

        try {
            const response = await jsonFetch(endpoints.move.replace('__ID__', changeInfo.event.id), {
                method: 'PATCH',
                body: JSON.stringify(payload),
            });
            showToast('success', response.message || 'Planning mis a jour.');
            if (response.data) {
                setSelectedRecord(response.data);
            }
            await loadStatistics();
        } catch (error) {
            changeInfo.revert();
            showToast('danger', error.message || 'Impossible de deplacer le rendez-vous.');
        }
    };

    const updateUpcomingList = (events = []) => {
        const items = events
            .map((event) => event.extendedProps || {})
            .filter((record) => record.starts_at)
            .map((record) => ({
                ...record,
                sortDate: new Date(record.starts_at),
            }))
            .filter((record) => !Number.isNaN(record.sortDate.getTime()) && record.sortDate >= new Date())
            .sort((a, b) => a.sortDate - b.sortDate)
            .slice(0, 5);

        state.upcomingRecords = {};
        items.forEach((record) => {
            state.upcomingRecords[String(record.id)] = record;
        });

        if (items.length === 0) {
            elements.upcomingList.innerHTML = '<div class="rdv-upcoming-empty">Aucun rendez-vous futur pour l&apos;instant.</div>';
            return;
        }

        elements.upcomingList.innerHTML = items.map((record) => `
            <button type="button" class="rdv-upcoming-item" data-rdv-id="${record.id}" data-source-type="${record.source_type || 'rendezvous'}">
                <span class="rdv-upcoming-color" style="background:${record.color || '#4f46e5'}"></span>
                <span class="rdv-upcoming-copy">
                    <strong>${escapeHtml(record.title || '')}</strong>
                    <small>${escapeHtml(record.etablissement_name || 'Etablissement')} - ${escapeHtml(formatDateTime(record.starts_at, record.all_day))}</small>
                </span>
            </button>
        `).join('');

        elements.upcomingList.querySelectorAll('[data-rdv-id]').forEach((button) => {
            button.addEventListener('click', async () => {
                if (button.dataset.sourceType === 'country_calendar_event') {
                    setSelectedRecord(state.upcomingRecords[button.dataset.rdvId] || null);
                    return;
                }

                await loadRecord(button.dataset.rdvId);
            });
        });
    };

    const bootCalendar = () => {
        state.calendar = new FullCalendar.Calendar(elements.calendar, {
            initialView: state.currentView,
            locale: 'fr',
            firstDay: 1,
            selectable: true,
            editable: true,
            dayMaxEvents: true,
            eventAllow: (dropInfo, draggedEvent) => !isCountryCalendarRecord(draggedEvent.extendedProps || {}),
            headerToolbar: false,
            nowIndicator: true,
            height: 'auto',
            businessHours: {
                daysOfWeek: [1, 2, 3, 4, 5],
                startTime: '08:00',
                endTime: '18:00',
            },
            views: {
                multiMonthYear: {
                    multiMonthMaxColumns: 3,
                },
            },
            events: async (fetchInfo, successCallback, failureCallback) => {
                showLoading(true);
                try {
                    const query = new URLSearchParams({
                        ...state.filters,
                        start: fetchInfo.startStr,
                        end: fetchInfo.endStr,
                    }).toString();
                    const data = await jsonFetch(`${endpoints.events}?${query}`);
                    successCallback(data);
                } catch (error) {
                    failureCallback(error);
                    showToast('danger', error.message || 'Impossible de charger le planning.');
                } finally {
                    showLoading(false);
                }
            },
            select: (selectionInfo) => {
                const selectedEtablissement = filterEtablissementAutocomplete.getSelected();
                const start = selectionInfo.allDay
                    ? `${selectionInfo.startStr.slice(0, 10)}T09:00`
                    : selectionInfo.startStr.slice(0, 16);
                const end = selectionInfo.allDay
                    ? `${selectionInfo.startStr.slice(0, 10)}T10:00`
                    : (selectionInfo.endStr ? selectionInfo.endStr.slice(0, 16) : '');
                openCreateModal({
                    starts_at: start,
                    ends_at: end,
                    all_day: selectionInfo.allDay,
                    etablissement_id: state.filters.etablissement_id || '',
                    etablissement_name: selectedEtablissement?.name || '',
                    etablissement_city: selectedEtablissement?.ville || '',
                    etablissement_email: selectedEtablissement?.email_contact || '',
                    etablissement_phone: selectedEtablissement?.phone || '',
                });
            },
            eventClick: async (info) => {
                if (isCountryCalendarRecord(info.event.extendedProps || {})) {
                    setSelectedRecord(info.event.extendedProps || null);
                    return;
                }

                await loadRecord(info.event.id);
            },
            eventDrop: syncMovedEvent,
            eventResize: syncMovedEvent,
            datesSet: async () => {
                updateCalendarTitle();
                await loadStatistics();
            },
            eventsSet: (events) => {
                updateUpcomingList(events);
            },
        });

        state.calendar.render();
        updateCalendarTitle();
    };

    const bindViewButtons = () => {
        document.querySelectorAll('.rdv-view-btn').forEach((button) => {
            button.addEventListener('click', () => {
                document.querySelectorAll('.rdv-view-btn').forEach((item) => item.classList.remove('is-active'));
                button.classList.add('is-active');
                state.currentView = button.dataset.view;
                state.calendar.changeView(state.currentView);
                updateCalendarTitle();
            });
        });
    };

    const bindFilters = () => {
        elements.filterStatus.addEventListener('change', () => {
            state.filters.status = elements.filterStatus.value;
            refreshCalendarData();
        });
        elements.filterMeetingType.addEventListener('change', () => {
            state.filters.meeting_type = elements.filterMeetingType.value;
            refreshCalendarData();
        });
        elements.filterSearch.addEventListener('input', debounce(() => {
            state.filters.search = elements.filterSearch.value.trim();
            refreshCalendarData();
        }, 320));
    };

    elements.todayBtn.addEventListener('click', () => {
        state.calendar.today();
        updateCalendarTitle();
    });

    elements.prevBtn.addEventListener('click', () => {
        state.calendar.prev();
        updateCalendarTitle();
    });

    elements.nextBtn.addEventListener('click', () => {
        state.calendar.next();
        updateCalendarTitle();
    });

    elements.createBtn.addEventListener('click', () => {
        const selectedEtablissement = filterEtablissementAutocomplete.getSelected();
        openCreateModal({
            etablissement_id: state.filters.etablissement_id || '',
            etablissement_name: selectedEtablissement?.name || '',
            etablissement_city: selectedEtablissement?.ville || '',
            etablissement_email: selectedEtablissement?.email_contact || '',
            etablissement_phone: selectedEtablissement?.phone || '',
        });
    });
    elements.editBtn.addEventListener('click', () => {
        if (state.selectedRecord?.id) {
            openEditModal(state.selectedRecord.id);
        }
    });
    elements.deleteBtn.addEventListener('click', () => {
        if (state.selectedRecord?.id) {
            removeRecord(state.selectedRecord.id);
        }
    });
    elements.form.addEventListener('submit', persistRecord);
    elements.modalEl.addEventListener('hidden.bs.modal', () => {
        elements.form.reset();
        clearFormValidation();
        modalEtablissementAutocomplete.clearSelection(false);
        elements.submitBtn.disabled = false;
        elements.submitBtn.classList.remove('is-loading');
    });

    bindViewButtons();
    bindFilters();
    bootCalendar();
    loadStatistics();
});
</script>

<style>
.rendezvous-page {
    --rdv-surface: #ffffff;
    --rdv-border: rgba(148, 163, 184, 0.16);
    --rdv-text: #0f172a;
    --rdv-muted: #64748b;
    --rdv-bg: linear-gradient(180deg, #eef4ff 0%, #f8fbff 28%, #f4f7fb 100%);
    background: var(--rdv-bg);
    min-height: 100vh;
    padding-bottom: 40px;
}

.rdv-hero {
    display: flex;
    align-items: end;
    justify-content: space-between;
    gap: 24px;
    padding: 10px 0 24px;
}

.rdv-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 12px;
    border-radius: 999px;
    background: rgba(79, 70, 229, 0.10);
    color: #4338ca;
    font-size: 0.82rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.rdv-title {
    margin: 16px 0 10px;
    font-size: clamp(2rem, 3vw, 2.85rem);
    font-weight: 800;
    letter-spacing: -0.03em;
    color: var(--rdv-text);
    display: flex;
    align-items: center;
    gap: 14px;
}

.rdv-title-icon {
    width: 62px;
    height: 62px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 18px;
    color: #ffffff;
    background: linear-gradient(135deg, #4f46e5, #0ea5e9);
    box-shadow: 0 22px 34px rgba(79, 70, 229, 0.28);
}

.rdv-subtitle {
    margin: 0;
    max-width: 760px;
    color: var(--rdv-muted);
    font-size: 1rem;
    line-height: 1.7;
}

.rdv-hero-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.rdv-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    border-radius: 16px;
    padding: 0.9rem 1.15rem;
    font-weight: 700;
    border: 1px solid transparent;
    box-shadow: none;
}

.rdv-btn-primary {
    background: linear-gradient(135deg, #4f46e5, #0284c7);
    border: none;
    box-shadow: 0 18px 28px rgba(2, 132, 199, 0.25);
}

.rdv-btn-light {
    background: rgba(255, 255, 255, 0.9);
    border-color: rgba(148, 163, 184, 0.18);
    color: var(--rdv-text);
}

.rdv-stats-grid {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 18px;
    margin-bottom: 24px;
}

.rdv-stat-card,
.rdv-filter-card,
.rdv-calendar-card,
.rdv-side-card {
    border: 1px solid var(--rdv-border);
    background: rgba(255, 255, 255, 0.94);
    backdrop-filter: blur(18px);
    border-radius: 26px;
    box-shadow: 0 22px 36px rgba(15, 23, 42, 0.06);
}

.rdv-stat-card {
    padding: 22px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    overflow: hidden;
    position: relative;
}

.rdv-stat-card::after {
    content: '';
    position: absolute;
    inset: auto -30px -30px auto;
    width: 120px;
    height: 120px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.18);
}

.rdv-stat-meta {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.rdv-stat-label,
.rdv-stat-foot {
    color: rgba(255, 255, 255, 0.86);
}

.rdv-stat-label {
    font-size: 0.9rem;
    font-weight: 600;
}

.rdv-stat-value {
    font-size: 2rem;
    font-weight: 800;
    color: #ffffff;
    line-height: 1;
}

.rdv-stat-foot {
    font-size: 0.82rem;
}

.rdv-stat-icon {
    width: 58px;
    height: 58px;
    border-radius: 18px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.18);
    color: #ffffff;
    font-size: 1.3rem;
}

.rdv-stat-indigo { background: linear-gradient(135deg, #4f46e5, #6366f1); }
.rdv-stat-cyan { background: linear-gradient(135deg, #0284c7, #06b6d4); }
.rdv-stat-emerald { background: linear-gradient(135deg, #059669, #10b981); }
.rdv-stat-amber { background: linear-gradient(135deg, #d97706, #f59e0b); }
.rdv-stat-rose { background: linear-gradient(135deg, #e11d48, #fb7185); }

.rdv-filter-card {
    padding: 24px;
    margin-bottom: 24px;
    position: relative;
    z-index: 80;
    overflow: visible;
}

.rdv-filter-topbar {
    display: flex;
    align-items: start;
    justify-content: space-between;
    gap: 18px;
    margin-bottom: 18px;
}

.rdv-filter-topbar h2,
.rdv-side-card-head h3 {
    margin: 0 0 6px;
    font-size: 1.15rem;
    font-weight: 800;
    color: var(--rdv-text);
}

.rdv-filter-topbar p {
    margin: 0;
    color: var(--rdv-muted);
}

.rdv-view-switch {
    display: inline-flex;
    background: #eef2ff;
    padding: 6px;
    border-radius: 18px;
    gap: 6px;
    flex-wrap: wrap;
}

.rdv-view-btn {
    border: none;
    background: transparent;
    color: #475569;
    padding: 0.7rem 0.95rem;
    border-radius: 14px;
    font-weight: 700;
    transition: all 0.2s ease;
}

.rdv-view-btn.is-active {
    background: #ffffff;
    color: #312e81;
    box-shadow: 0 8px 18px rgba(79, 70, 229, 0.12);
}

.rdv-label {
    font-weight: 700;
    color: #334155;
    margin-bottom: 8px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.rdv-field-help {
    width: 20px;
    height: 20px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    background: rgba(79, 70, 229, 0.10);
    color: #4f46e5;
    font-size: 0.8rem;
    cursor: help;
    transition: all 0.2s ease;
}

.rdv-field-help:hover,
.rdv-field-help:focus {
    background: rgba(79, 70, 229, 0.16);
    color: #312e81;
    outline: none;
}

.rdv-input {
    border-radius: 16px;
    min-height: 50px;
    border: 1px solid rgba(148, 163, 184, 0.3);
    box-shadow: none;
}

.rdv-input:focus {
    border-color: rgba(79, 70, 229, 0.38);
    box-shadow: 0 0 0 0.22rem rgba(79, 70, 229, 0.12);
}

.rdv-autocomplete {
    position: relative;
    z-index: 90;
}

.rdv-autocomplete-input {
    padding-right: 42px;
}

.rdv-autocomplete-clear {
    position: absolute;
    top: 50%;
    right: 12px;
    transform: translateY(-50%);
    width: 28px;
    height: 28px;
    border: none;
    border-radius: 999px;
    background: rgba(148, 163, 184, 0.14);
    color: #475569;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.rdv-autocomplete-clear:hover {
    background: rgba(79, 70, 229, 0.14);
    color: #312e81;
}

.rdv-autocomplete-menu {
    position: absolute;
    top: calc(100% + 10px);
    left: 0;
    right: 0;
    z-index: 120;
    border-radius: 18px;
    border: 1px solid rgba(148, 163, 184, 0.18);
    background: #ffffff;
    box-shadow: 0 22px 32px rgba(15, 23, 42, 0.12);
    overflow: hidden;
    max-height: 320px;
    overflow-y: auto;
}

.rdv-autocomplete-item {
    width: 100%;
    border: none;
    background: #ffffff;
    text-align: left;
    padding: 12px 14px;
    display: grid;
    gap: 3px;
    transition: background 0.2s ease;
}

.rdv-autocomplete-item strong {
    color: #0f172a;
    font-size: 0.96rem;
}

.rdv-autocomplete-item small {
    color: #64748b;
    font-size: 0.82rem;
}

.rdv-autocomplete-item:hover,
.rdv-autocomplete-item.is-active {
    background: rgba(79, 70, 229, 0.08);
}

.rdv-autocomplete-empty {
    padding: 14px;
    color: #64748b;
    font-size: 0.9rem;
}

.rdv-search-wrap {
    position: relative;
}

.rdv-search-wrap i {
    position: absolute;
    top: 50%;
    left: 16px;
    transform: translateY(-50%);
    color: #94a3b8;
}

.rdv-search-input {
    padding-left: 42px;
}

.rdv-layout-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.55fr) minmax(320px, 0.8fr);
    gap: 24px;
    align-items: start;
    position: relative;
    z-index: 1;
}

.rdv-calendar-card {
    padding: 22px;
    position: relative;
    z-index: 1;
}

.rdv-calendar-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
    margin-bottom: 16px;
}

.rdv-calendar-nav {
    display: flex;
    align-items: center;
    gap: 12px;
}

.rdv-calendar-label {
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #94a3b8;
    font-weight: 700;
}

.rdv-calendar-title {
    font-size: 1.2rem;
    font-weight: 800;
    color: var(--rdv-text);
}

.rdv-nav-btn {
    width: 46px;
    height: 46px;
    border-radius: 14px;
    border: 1px solid rgba(148, 163, 184, 0.2);
}

.rdv-legend {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.rdv-legend-item {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #475569;
    font-weight: 600;
    font-size: 0.9rem;
}

.rdv-dot {
    width: 11px;
    height: 11px;
    border-radius: 999px;
    display: inline-block;
    background: var(--dot);
    box-shadow: 0 0 0 4px color-mix(in srgb, var(--dot) 18%, transparent);
}

.rdv-calendar-body {
    position: relative;
    min-height: 720px;
}

.rdv-loading {
    position: absolute;
    inset: 0;
    display: none;
    align-items: center;
    justify-content: center;
    gap: 12px;
    background: rgba(255, 255, 255, 0.78);
    backdrop-filter: blur(8px);
    border-radius: 22px;
    z-index: 20;
    color: #334155;
    font-weight: 700;
}

.rdv-loading.is-visible {
    display: flex;
}

.rdv-side-column {
    display: grid;
    gap: 24px;
    position: sticky;
    top: 18px;
}

.rdv-side-card {
    padding: 22px;
}

.rdv-side-highlight {
    background: linear-gradient(180deg, rgba(255,255,255,0.96), rgba(244,248,255,0.98));
}

.rdv-side-card-head {
    display: flex;
    align-items: start;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 16px;
}

.rdv-side-card-head.compact {
    margin-bottom: 12px;
}

.rdv-panel-kicker,
.rdv-modal-kicker {
    display: inline-block;
    color: #64748b;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.rdv-side-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 8px 12px;
    border-radius: 999px;
    font-size: 0.82rem;
    font-weight: 700;
}

.rdv-side-empty {
    min-height: 240px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    gap: 14px;
    color: #64748b;
}

.rdv-side-empty i {
    font-size: 2rem;
    color: #a5b4fc;
}

.rdv-side-content h4 {
    font-weight: 800;
    color: var(--rdv-text);
    margin-bottom: 16px;
}

.rdv-side-lines {
    display: grid;
    gap: 12px;
    margin-bottom: 16px;
}

.rdv-side-lines div {
    display: flex;
    align-items: start;
    gap: 10px;
    color: #334155;
    line-height: 1.55;
}

.rdv-side-lines i {
    width: 18px;
    margin-top: 2px;
    color: #4f46e5;
}

.rdv-side-note {
    padding: 14px 16px;
    border-radius: 18px;
    background: rgba(79, 70, 229, 0.06);
    color: #475569;
    min-height: 88px;
    margin-bottom: 16px;
    white-space: pre-wrap;
}

.rdv-side-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.rdv-side-btn {
    border-radius: 14px;
    font-weight: 700;
    display: inline-flex;
    gap: 8px;
    align-items: center;
}

.rdv-upcoming-list {
    display: grid;
    gap: 12px;
}

.rdv-upcoming-item {
    border: none;
    background: #f8fafc;
    border-radius: 18px;
    padding: 14px;
    display: flex;
    gap: 12px;
    text-align: left;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.rdv-upcoming-item:hover {
    transform: translateY(-1px);
    box-shadow: 0 14px 22px rgba(15, 23, 42, 0.08);
}

.rdv-upcoming-color {
    width: 10px;
    border-radius: 999px;
    align-self: stretch;
}

.rdv-upcoming-copy {
    display: grid;
    gap: 4px;
}

.rdv-upcoming-copy strong {
    color: var(--rdv-text);
}

.rdv-upcoming-copy small,
.rdv-upcoming-empty {
    color: #64748b;
}

.rdv-modal-content {
    border: none;
    border-radius: 28px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    height: min(92vh, 980px);
}

#rdvFormModal .modal-dialog {
    max-height: calc(100vh - 2rem);
}

#rdvForm {
    display: flex;
    flex-direction: column;
    flex: 1 1 auto;
    min-height: 0;
}

.rdv-modal-header {
    background: linear-gradient(135deg, #1d4ed8, #4338ca);
    color: #ffffff;
    border: none;
    padding: 22px 26px;
}

.rdv-modal-header .modal-title {
    font-size: 1.45rem;
    font-weight: 800;
    margin-top: 4px;
}

.rdv-modal-body {
    padding: 24px 26px;
    background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    flex: 1 1 auto;
    min-height: 0;
    overflow-y: auto;
    overflow-x: hidden;
    scrollbar-gutter: stable;
}

.rdv-modal-footer {
    padding: 18px 26px 24px;
    border-top: 1px solid rgba(148, 163, 184, 0.16);
    background: #ffffff;
    flex-shrink: 0;
}

.rdv-modal-body::-webkit-scrollbar {
    width: 11px;
}

.rdv-modal-body::-webkit-scrollbar-track {
    background: rgba(226, 232, 240, 0.9);
    border-radius: 999px;
}

.rdv-modal-body::-webkit-scrollbar-thumb {
    background: linear-gradient(180deg, #94a3b8, #64748b);
    border-radius: 999px;
    border: 2px solid rgba(226, 232, 240, 0.95);
}

.rdv-modal-body::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(180deg, #64748b, #475569);
}

.rdv-textarea {
    min-height: 150px;
}

.rdv-color-wrap {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    min-height: 50px;
    padding: 6px 0;
}

.rdv-checkbox {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    font-weight: 700;
    color: #334155;
}

.rdv-checkbox input {
    width: 18px;
    height: 18px;
}

.rdv-toast {
    min-width: 320px;
    overflow: hidden;
    border: none;
    border-radius: 18px;
    box-shadow: 0 22px 32px rgba(15, 23, 42, 0.22);
}

.rdv-toast .toast-header {
    color: #ffffff;
    border: none;
}

.rdv-toast-success .toast-header { background: linear-gradient(135deg, #059669, #10b981); }
.rdv-toast-danger .toast-header { background: linear-gradient(135deg, #dc2626, #ef4444); }
.rdv-toast-warning .toast-header { background: linear-gradient(135deg, #d97706, #f59e0b); }

.rdv-toast .toast-body {
    background: #ffffff;
    color: #1e293b;
    font-weight: 600;
}

#rdvSubmitBtn.is-loading {
    pointer-events: none;
    opacity: 0.75;
}

#rdvSubmitBtn.is-loading span::after {
    content: ' ...';
}

#rendezVousCalendar .fc {
    font-family: inherit;
}

#rendezVousCalendar .fc-theme-standard td,
#rendezVousCalendar .fc-theme-standard th,
#rendezVousCalendar .fc-theme-standard .fc-scrollgrid {
    border-color: rgba(148, 163, 184, 0.18);
}

#rendezVousCalendar .fc-toolbar {
    display: none;
}

#rendezVousCalendar .fc-daygrid-day-frame {
    min-height: 118px;
}

#rendezVousCalendar .fc-col-header-cell-cushion,
#rendezVousCalendar .fc-daygrid-day-number {
    color: #334155;
    text-decoration: none;
    font-weight: 700;
}

#rendezVousCalendar .fc-day-today {
    background: rgba(79, 70, 229, 0.06);
}

#rendezVousCalendar .fc-event {
    border: none;
    border-radius: 12px;
    padding: 4px 8px;
    font-size: 0.84rem;
    font-weight: 700;
    box-shadow: 0 10px 18px rgba(15, 23, 42, 0.14);
}

#rendezVousCalendar .fc-event:hover {
    transform: translateY(-1px);
}

#rendezVousCalendar .fc-list-event:hover td {
    background: rgba(79, 70, 229, 0.05);
}

#rendezVousCalendar .fc-multimonth {
    gap: 18px;
}

#rendezVousCalendar .fc-multimonth-month {
    border-radius: 18px;
    overflow: hidden;
    border-color: rgba(148, 163, 184, 0.18);
}

@media (max-width: 1440px) {
    .rdv-stats-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media (max-width: 1200px) {
    .rdv-layout-grid {
        grid-template-columns: 1fr;
    }

    .rdv-side-column {
        position: static;
    }
}

@media (max-width: 992px) {
    .rdv-hero,
    .rdv-filter-topbar,
    .rdv-calendar-head {
        flex-direction: column;
        align-items: stretch;
    }

    .rdv-hero-actions,
    .rdv-legend {
        justify-content: flex-start;
    }

    .rdv-stats-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 640px) {
    .rdv-stats-grid {
        grid-template-columns: 1fr;
    }

    .rdv-view-switch {
        width: 100%;
    }

    .rdv-view-btn {
        flex: 1 1 calc(50% - 6px);
    }

    .rdv-calendar-nav {
        flex-wrap: wrap;
    }

    .rdv-calendar-body {
        min-height: 560px;
    }
}
</style>
@endsection
