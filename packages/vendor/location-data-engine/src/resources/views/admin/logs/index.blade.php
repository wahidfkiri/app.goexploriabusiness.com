@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('vendor/location-data-engine/css/admin.css') }}">

<main class="dashboard-content lde-shell" id="ldeLogsPage"
      data-logs-base="{{ url(config('location-data-engine.admin.prefix') . '/api/scan-sessions') }}">
    <div class="lde-header">
        <div>
            <span class="lde-kicker">Location Data Engine</span>
            <h1 class="lde-title">Scan logs and usage</h1>
            <p class="lde-subtitle">Monitor scans, quotas, retries and API usage from one place.</p>
        </div>
        <a href="{{ route('location-data-engine.admin.dashboard') }}" class="btn btn-outline-secondary lde-header-btn">
            <i class="fas fa-arrow-left me-2"></i>Back to dashboard
        </a>
    </div>

    @include('location-data-engine::admin.partials.nav')

    <div class="row g-4 mt-2">
        <div class="col-lg-4">
            <div class="lde-panel">
                <h3 class="mb-3">Recent sessions</h3>
                <div class="list-group lde-session-list" id="ldeSessionList">
                    @foreach($sessions as $session)
                        <button type="button" class="list-group-item list-group-item-action lde-session-item" data-session-id="{{ $session->id }}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong>{{ $session->category }}</strong>
                                    <div class="small text-muted">{{ $session->target_label ?: '-' }}</div>
                                </div>
                                <span class="badge text-bg-dark">{{ $session->status }}</span>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="lde-panel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="mb-0">Live logs</h3>
                    <small class="text-muted">Auto refresh every 5s</small>
                </div>
                <div id="ldeLogsEmpty" class="lde-empty-box">Select a session to inspect its logs.</div>
                <div class="table-responsive d-none" id="ldeLogsTableWrap">
                    <table class="table lde-table">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Level</th>
                                <th>Event</th>
                                <th>Message</th>
                            </tr>
                        </thead>
                        <tbody id="ldeLogsTableBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="{{ asset('vendor/location-data-engine/js/logs.js') }}" defer></script>
@endsection
