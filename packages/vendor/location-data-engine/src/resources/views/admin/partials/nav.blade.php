<div class="lde-page-nav">
    <a href="{{ route('location-data-engine.admin.dashboard') }}" class="lde-nav-link {{ request()->routeIs('location-data-engine.admin.dashboard') ? 'active' : '' }}">
        <i class="fas fa-radar"></i>
        <span>Scan Dashboard</span>
    </a>
    <a href="{{ route('location-data-engine.admin.results.index') }}" class="lde-nav-link {{ request()->routeIs('location-data-engine.admin.results.*') ? 'active' : '' }}">
        <i class="fas fa-building"></i>
        <span>Results</span>
    </a>
    <a href="{{ route('location-data-engine.admin.logs.index') }}" class="lde-nav-link {{ request()->routeIs('location-data-engine.admin.logs.*') ? 'active' : '' }}">
        <i class="fas fa-wave-square"></i>
        <span>Logs</span>
    </a>
</div>
