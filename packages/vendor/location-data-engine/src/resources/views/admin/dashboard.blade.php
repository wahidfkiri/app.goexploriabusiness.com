@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('vendor/location-data-engine/css/admin.css') }}">

<main class="dashboard-content lde-shell" id="ldeDashboardPage"
      data-scan-endpoint="{{ route('location-data-engine.api.scan-sessions.store') }}"
      data-status-base="{{ url(config('location-data-engine.admin.prefix') . '/api/scan-sessions') }}"
      data-reference-base="{{ url(config('location-data-engine.admin.prefix') . '/api/reference') }}">
    <div class="lde-header">
        <div>
            <span class="lde-kicker">Location Data Engine</span>
            <h1 class="lde-title">Business and destinations scan dashboard</h1>
            <p class="lde-subtitle">Scan regions, collect places, enrich websites and prepare business listings for your teams.</p>
        </div>
        <a href="{{ route('location-data-engine.admin.results.index') }}" class="btn btn-primary lde-header-btn">
            <i class="fas fa-table me-2"></i>Open results
        </a>
    </div>

    @include('location-data-engine::admin.partials.nav')

    <div class="lde-stats-grid mt-4">
        <div class="lde-stat-card"><span>Total sessions</span><strong>{{ $stats['total_sessions'] }}</strong></div>
        <div class="lde-stat-card"><span>Running sessions</span><strong>{{ $stats['running_sessions'] }}</strong></div>
        <div class="lde-stat-card"><span>Total businesses</span><strong>{{ $stats['total_businesses'] }}</strong></div>
        <div class="lde-stat-card"><span>API calls</span><strong>{{ $stats['api_calls'] }}</strong></div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-lg-7">
            <div class="lde-panel">
                <div class="lde-panel-head">
                    <div>
                        <h3>Start a smart scan</h3>
                        <p>Use Google Places data to build businesses, places and destinations listings.</p>
                    </div>
                </div>
                <form id="ldeScanForm" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select" required>
                            @foreach($categories as $key => $category)
                                <option value="{{ $key }}">{{ ucfirst($key) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Keyword override</label>
                        <input type="text" class="form-control" name="query" placeholder="Optional query like boutique hotels">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Country</label>
                        <select name="country_id" id="ldeCountry" class="form-select">
                            <option value="">Select country</option>
                            @foreach($countries as $country)
                                <option value="{{ $country->id }}">{{ $country->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Province</label>
                        <select name="province_id" id="ldeProvince" class="form-select"><option value="">Select province</option></select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Region</label>
                        <select name="region_id" id="ldeRegion" class="form-select"><option value="">Select region</option></select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">City</label>
                        <select name="city_id" id="ldeCity" class="form-select"><option value="">Select city</option></select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Sector</label>
                        <select name="sector_id" id="ldeSector" class="form-select"><option value="">Select sector</option></select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Radius</label>
                        <input type="number" class="form-control" name="radius" value="25000" min="1000" max="50000">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Limit</label>
                        <input type="number" class="form-control" name="limit" value="250" min="1" max="1000">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Grid precision</label>
                        <input type="number" class="form-control" name="grid_precision" value="5" min="1" max="9">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check me-3">
                            <input class="form-check-input" type="checkbox" name="with_enrichment" id="withEnrichment" value="1">
                            <label class="form-check-label" for="withEnrichment">Website enrichment</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="with_images" id="withImages" value="1">
                            <label class="form-check-label" for="withImages">Images</label>
                        </div>
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-play me-2"></i>Launch scan
                        </button>
                        <a href="{{ route('location-data-engine.admin.logs.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-list-alt me-2"></i>View logs
                        </a>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="lde-panel">
                <div class="lde-panel-head">
                    <div>
                        <h3>Live session monitor</h3>
                        <p>Progress, quotas and latest logs refresh automatically.</p>
                    </div>
                </div>
                <div id="ldeNoSession" class="lde-empty-box">Start a scan to see live progress here.</div>
                <div id="ldeSessionStatus" class="d-none">
                    <div class="lde-session-meta">
                        <div><span>Status</span><strong id="ldeSessionState">pending</strong></div>
                        <div><span>Target</span><strong id="ldeSessionTarget">-</strong></div>
                        <div><span>Results</span><strong id="ldeSessionResults">0</strong></div>
                    </div>
                    <div class="progress lde-progress-bar mt-3">
                        <div id="ldeProgressBar" class="progress-bar" role="progressbar" style="width: 0%">0%</div>
                    </div>
                    <ul id="ldeLiveLogs" class="lde-live-log-list mt-3"></ul>
                </div>
            </div>
        </div>
    </div>

    <div class="lde-panel mt-4">
        <div class="lde-panel-head">
            <div>
                <h3>Recent sessions</h3>
                <p>Quick view of the latest scan batches.</p>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table lde-table">
                <thead>
                    <tr>
                        <th>UUID</th>
                        <th>Category</th>
                        <th>Target</th>
                        <th>Status</th>
                        <th>Results</th>
                        <th>Updated</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentSessions as $session)
                        <tr>
                            <td>{{ $session->uuid }}</td>
                            <td>{{ $session->category }}</td>
                            <td>{{ $session->target_label ?: '-' }}</td>
                            <td><span class="badge text-bg-dark">{{ $session->status }}</span></td>
                            <td>{{ $session->results_count }}</td>
                            <td>{{ optional($session->updated_at)->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No scan session yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</main>

<script src="{{ asset('vendor/location-data-engine/js/dashboard.js') }}" defer></script>
@endsection
