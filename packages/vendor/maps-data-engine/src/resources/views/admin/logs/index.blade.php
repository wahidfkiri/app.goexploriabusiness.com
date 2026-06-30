@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('vendor/maps-data-engine/css/admin.css') }}">

<main class="dashboard-content mde-shell" id="mdeLogsPage" data-logs-base="{{ url(config('maps-data-engine.admin.prefix') . '/api/scan-sessions') }}" data-infrastructure-endpoint="{{ route('maps-data-engine.api.infrastructure') }}">
    <div class="mde-hero compact">
        <div>
            <span class="mde-kicker">Maps Data Engine</span>
            <h1 class="mde-title">Monitoring, logs and infrastructure</h1>
            <p class="mde-subtitle">Inspect sessions, proxy health, browser states and failure incidents.</p>
        </div>
        <a href="{{ route('maps-data-engine.admin.dashboard') }}" class="btn btn-outline-secondary mde-cta"><i class="fas fa-arrow-left me-2"></i>Back</a>
    </div>

    @include('maps-data-engine::admin.partials.nav')

    <div class="row g-4 mt-2">
        <div class="col-lg-4">
            <div class="mde-panel">
                <h3 class="mb-3">Recent sessions</h3>
                <div class="list-group mde-session-list">
                    @foreach($sessions as $session)
                        <button type="button" class="list-group-item list-group-item-action mde-session-item" data-session-id="{{ $session->id }}">
                            <div class="d-flex justify-content-between"><strong>{{ $session->category }}</strong><span class="badge text-bg-dark">{{ $session->status }}</span></div>
                            <div class="small text-muted">{{ $session->target_label ?: '-' }}</div>
                        </button>
                    @endforeach
                </div>
            </div>
            <div class="mde-panel mt-4">
                <h3 class="mb-3">Proxy health</h3>
                <div id="mdeProxyHealthList" class="d-grid gap-2">
                    @foreach($proxies as $proxy)
                        <div class="mde-inline-chip d-flex justify-content-between"><span>{{ $proxy->label }}</span><strong>{{ $proxy->health_score }}</strong></div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="mde-panel">
                <h3 class="mb-3">Live logs</h3>
                <div id="mdeLogsEmpty" class="mde-empty">Select a session to inspect logs.</div>
                <div id="mdeLogsWrap" class="table-responsive d-none"><table class="table mde-table"><thead><tr><th>Time</th><th>Level</th><th>Event</th><th>Message</th></tr></thead><tbody id="mdeLogsBody"></tbody></table></div>
            </div>
            <div class="mde-panel mt-4">
                <h3 class="mb-3">Browser sessions</h3>
                <div id="mdeBrowserSessions" class="row g-2">
                    @foreach($browserSessions as $browserSession)
                        <div class="col-md-6"><div class="mde-review-card"><strong>{{ $browserSession->session_key }}</strong><div class="small text-muted">Locked: {{ $browserSession->is_locked ? 'yes' : 'no' }} | Banned: {{ $browserSession->is_banned ? 'yes' : 'no' }}</div></div></div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</main>

<script src="{{ asset('vendor/maps-data-engine/js/logs.js') }}" defer></script>
@endsection
