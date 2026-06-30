@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('vendor/maps-data-engine/css/admin.css') }}">

<main class="dashboard-content mde-shell" id="mdeResultsPage"
      data-results-endpoint="{{ route('maps-data-engine.api.listings.index') }}"
      data-detail-base="{{ url(config('maps-data-engine.admin.prefix') . '/api/listings') }}"
      data-export-csv="{{ route('maps-data-engine.api.listings.export.csv') }}"
      data-export-excel="{{ route('maps-data-engine.api.listings.export.excel') }}">
    <div class="mde-hero compact">
        <div>
            <span class="mde-kicker">Maps Data Engine</span>
            <h1 class="mde-title">Listings explorer</h1>
            <p class="mde-subtitle">Filter extracted businesses, places, destinations and companies without reloading the page.</p>
        </div>
        <a href="{{ route('maps-data-engine.admin.dashboard') }}" class="btn btn-outline-secondary mde-cta"><i class="fas fa-arrow-left me-2"></i>Back</a>
    </div>

    @include('maps-data-engine::admin.partials.nav')

    <div class="mde-panel mt-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-3"><label class="form-label">Search</label><input id="mdeSearch" class="form-control" placeholder="Name, address, website"></div>
            <div class="col-md-2"><label class="form-label">Country</label><input id="mdeCountryFilter" class="form-control" placeholder="Canada"></div>
            <div class="col-md-2"><label class="form-label">Province</label><input id="mdeProvinceFilter" class="form-control" placeholder="Quebec"></div>
            <div class="col-md-2"><label class="form-label">City</label><input id="mdeCityFilter" class="form-control" placeholder="Montreal"></div>
            <div class="col-md-2"><label class="form-label">Category</label><select id="mdeCategoryFilter" class="form-select"><option value="">All</option>@foreach($categories as $key => $queries)<option value="{{ $key }}">{{ ucfirst($key) }}</option>@endforeach</select></div>
            <div class="col-md-1"><label class="form-label">Per page</label><select id="mdePerPage" class="form-select"><option value="{{ $pageSize }}">{{ $pageSize }}</option><option value="12">12</option><option value="24">24</option><option value="48">48</option></select></div>
            <div class="col-12 d-flex justify-content-between flex-wrap gap-2">
                <div class="d-flex gap-2">
                    <button id="mdeApplyFilters" class="btn btn-primary"><i class="fas fa-filter me-2"></i>Apply</button>
                    <button id="mdeResetFilters" class="btn btn-outline-secondary"><i class="fas fa-rotate-left me-2"></i>Reset</button>
                </div>
                <div class="d-flex gap-2">
                    <button id="mdeCardView" class="btn btn-outline-dark"><i class="fas fa-grip"></i></button>
                    <button id="mdeTableView" class="btn btn-outline-dark"><i class="fas fa-table"></i></button>
                    <a id="mdeExportCsv" href="#" class="btn btn-outline-primary"><i class="fas fa-file-csv me-2"></i>CSV</a>
                    <a id="mdeExportExcel" href="#" class="btn btn-outline-success"><i class="fas fa-file-excel me-2"></i>Excel</a>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-4"><strong id="mdeResultsCount">0 results</strong><span id="mdeResultsPager" class="text-muted">Page 1</span></div>
    <div id="mdeCards" class="mde-card-grid mt-3"></div>
    <div id="mdeTableWrap" class="mde-panel mt-3 d-none"><div class="table-responsive"><table class="table mde-table"><thead><tr><th>Name</th><th>Address</th><th>Website</th><th>Phone</th><th>Rating</th><th></th></tr></thead><tbody id="mdeTableBody"></tbody></table></div></div>
    <div class="text-center mt-4"><button id="mdeLoadMore" class="btn btn-outline-primary d-none">Load more</button></div>
</main>

<div class="modal fade" id="mdeDetailModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-xl modal-dialog-scrollable"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Listing details</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body" id="mdeDetailBody"></div></div></div></div>

<script src="{{ asset('vendor/maps-data-engine/js/results.js') }}" defer></script>
@endsection
