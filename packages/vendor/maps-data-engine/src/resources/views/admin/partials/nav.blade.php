<div class="mde-nav-strip">
    <a href="{{ route('maps-data-engine.admin.dashboard') }}" class="mde-nav-link {{ request()->routeIs('maps-data-engine.admin.dashboard') ? 'active' : '' }}"><i class="fas fa-map-location-dot"></i><span>Dashboard</span></a>
    <a href="{{ route('maps-data-engine.admin.results.index') }}" class="mde-nav-link {{ request()->routeIs('maps-data-engine.admin.results.*') ? 'active' : '' }}"><i class="fas fa-building-circle-check"></i><span>Results</span></a>
    <a href="{{ route('maps-data-engine.admin.logs.index') }}" class="mde-nav-link {{ request()->routeIs('maps-data-engine.admin.logs.*') ? 'active' : '' }}"><i class="fas fa-shield-halved"></i><span>Logs & Infra</span></a>
</div>
