@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('vendor/maps-data-engine/css/admin.css') }}">

<main class="dashboard-content mde-shell" id="mdeDashboardPage"
      data-scan-endpoint="{{ route('maps-data-engine.api.scan-sessions.store') }}"
      data-status-base="{{ url(config('maps-data-engine.admin.prefix') . '/api/scan-sessions') }}"
      data-reference-base="{{ url(config('maps-data-engine.admin.prefix') . '/api/reference') }}"
      data-infrastructure-endpoint="{{ route('maps-data-engine.api.infrastructure') }}">
    <div class="mde-hero">
        <div>
            <span class="mde-kicker">Maps Data Engine</span>
            <h1 class="mde-title">Google Maps business intelligence crawler</h1>
            <p class="mde-subtitle">Stable, human-like browser automation with proxy rotation, session persistence and distributed Laravel queues.</p>
        </div>
        <a href="{{ route('maps-data-engine.admin.results.index') }}" class="btn btn-primary mde-cta"><i class="fas fa-table me-2"></i>Browse results</a>
    </div>

    @include('maps-data-engine::admin.partials.nav')

    <div class="mde-metrics-grid mt-4">
        <div class="mde-metric-card"><span>Scan sessions</span><strong>{{ $stats['total_sessions'] }}</strong></div>
        <div class="mde-metric-card"><span>Running now</span><strong>{{ $stats['running_sessions'] }}</strong></div>
        <div class="mde-metric-card"><span>Stored listings</span><strong>{{ $stats['total_listings'] }}</strong></div>
        <div class="mde-metric-card"><span>Active proxies</span><strong>{{ $stats['active_proxies'] }}</strong></div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-lg-7">
            <div class="mde-panel">
                <div class="mde-panel-header">
                    <div>
                        <h3>Launch a segmented scan</h3>
                        <p>Choose a category, a target geography and extraction options. The engine will split work into safer batches.</p>
                    </div>
                </div>
                <form id="mdeScanForm" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Category</label>
                        <select class="form-select" name="category" required>
                            @foreach($categories as $key => $queries)
                                <option value="{{ $key }}">{{ ucfirst($key) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Custom query</label>
                        <input class="form-control" name="query" type="text" placeholder="Optional: Montreal boutique hotels">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Country</label>
                        <select class="form-select" id="mdeCountry" name="country_id">
                            <option value="">Select country</option>
                            @foreach($countries as $country)
                                <option value="{{ $country->id }}">{{ $country->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Province</label>
                        <select class="form-select" id="mdeProvince" name="province_id"><option value="">Select province</option></select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Region</label>
                        <select class="form-select" id="mdeRegion" name="region_id"><option value="">Select region</option></select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">City</label>
                        <select class="form-select" id="mdeCity" name="city_id"><option value="">Select city</option></select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Radius</label>
                        <input class="form-control" name="radius" type="number" value="18000" min="1000" max="50000">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Limit</label>
                        <input class="form-control" name="limit" type="number" value="120" min="1" max="250">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="mde-checkbox-group">
                            <label><input type="checkbox" name="with_images" value="1"> Images</label>
                            <label><input type="checkbox" name="with_reviews" value="1"> Reviews</label>
                            <label><input type="checkbox" name="with_social_links" value="1"> Social links</label>
                        </div>
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-play me-2"></i>Start scrape</button>
                        <a href="{{ route('maps-data-engine.admin.logs.index') }}" class="btn btn-outline-secondary"><i class="fas fa-satellite-dish me-2"></i>Open monitoring</a>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="mde-panel">
                <div class="mde-panel-header">
                    <div>
                        <h3>Live session status</h3>
                        <p>Track progress, latest logs, captcha incidents and proxy rotations.</p>
                    </div>
                </div>
                <div id="mdeNoSession" class="mde-empty">No active session yet.</div>
                <div id="mdeSessionBox" class="d-none">
                    <div class="mde-session-stats">
                        <div><span>Status</span><strong id="mdeStatus">pending</strong></div>
                        <div><span>Target</span><strong id="mdeTarget">-</strong></div>
                        <div><span>Results</span><strong id="mdeResults">0</strong></div>
                        <div><span>Captcha</span><strong id="mdeCaptcha">0</strong></div>
                    </div>
                    <div class="progress mt-3 mde-progress"><div class="progress-bar" id="mdeProgressBar" style="width:0%">0%</div></div>
                    <div id="mdeQueueHint" class="mde-queue-hint mt-3 d-none"></div>
                    <ul id="mdeLiveLogs" class="mde-live-logs mt-3"></ul>
                </div>
            </div>
            <div class="mde-panel mt-4">
                <div class="mde-panel-header">
                    <div>
                        <h3>Infrastructure snapshot</h3>
                        <p>Quick proxy and browser session visibility.</p>
                    </div>
                </div>
                <div id="mdeInfraBox" class="mde-infra-grid">
                    <div><span>Proxy pool</span><strong id="mdeProxyPool">{{ $proxyCount }}</strong></div>
                    <div><span>Browser sessions</span><strong id="mdeBrowserPool">{{ $stats['healthy_browser_sessions'] }}</strong></div>
                </div>
            </div>
        </div>
    </div>

    <div class="mde-panel mt-4">
        <div class="mde-panel-header"><div><h3>Recent sessions</h3><p>Latest queued and completed scraping waves.</p></div></div>
        <div class="table-responsive">
            <table class="table mde-table">
                <thead><tr><th>UUID</th><th>Category</th><th>Target</th><th>Status</th><th>Results</th><th>Updated</th></tr></thead>
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
                        <tr><td colspan="6" class="text-center text-muted py-4">No sessions yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</main>

<script src="{{ asset('vendor/maps-data-engine/js/dashboard.js') }}" defer></script>
@endsection
