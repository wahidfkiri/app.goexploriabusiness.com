@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('vendor/location-data-engine/css/admin.css') }}">

<main class="dashboard-content lde-shell" id="ldeResultsPage"
      data-results-endpoint="{{ route('location-data-engine.api.business-locations.index') }}"
      data-detail-base="{{ url(config('location-data-engine.admin.prefix') . '/api/business-locations') }}"
      data-export-csv="{{ route('location-data-engine.api.business-locations.export.csv') }}"
      data-export-excel="{{ route('location-data-engine.api.business-locations.export.excel') }}">
    <div class="lde-header">
        <div>
            <span class="lde-kicker">Location Data Engine</span>
            <h1 class="lde-title">Results explorer</h1>
            <p class="lde-subtitle">Review business data, browse destinations and inspect places with a live AJAX explorer.</p>
        </div>
        <a href="{{ route('location-data-engine.admin.dashboard') }}" class="btn btn-outline-secondary lde-header-btn">
            <i class="fas fa-arrow-left me-2"></i>Back to dashboard
        </a>
    </div>

    @include('location-data-engine::admin.partials.nav')

    <div class="lde-panel mt-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" class="form-control" id="ldeResultsSearch" placeholder="Business name, email, website">
            </div>
            <div class="col-md-2">
                <label class="form-label">Country</label>
                <input type="text" class="form-control" id="ldeFilterCountry" placeholder="Canada">
            </div>
            <div class="col-md-2">
                <label class="form-label">Province</label>
                <input type="text" class="form-control" id="ldeFilterProvince" placeholder="Quebec">
            </div>
            <div class="col-md-2">
                <label class="form-label">City</label>
                <input type="text" class="form-control" id="ldeFilterCity" placeholder="Quebec City">
            </div>
            <div class="col-md-2">
                <label class="form-label">Category</label>
                <select class="form-select" id="ldeFilterCategory">
                    <option value="">All</option>
                    @foreach($categories as $key => $category)
                        <option value="{{ $key }}">{{ ucfirst($key) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label">Per page</label>
                <select class="form-select" id="ldePerPage">
                    <option value="{{ $pageSize }}">{{ $pageSize }}</option>
                    <option value="12">12</option>
                    <option value="24">24</option>
                    <option value="48">48</option>
                </select>
            </div>
            <div class="col-12 d-flex flex-wrap gap-2 justify-content-between">
                <div class="d-flex gap-2">
                    <button class="btn btn-primary" id="ldeApplyFilters"><i class="fas fa-filter me-2"></i>Apply filters</button>
                    <button class="btn btn-outline-secondary" id="ldeResetFilters"><i class="fas fa-rotate-left me-2"></i>Reset</button>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-dark" id="ldeViewCards"><i class="fas fa-grip"></i></button>
                    <button class="btn btn-outline-dark" id="ldeViewTable"><i class="fas fa-table"></i></button>
                    <a class="btn btn-outline-primary" id="ldeExportCsv" href="#"><i class="fas fa-file-csv me-2"></i>CSV</a>
                    <a class="btn btn-outline-success" id="ldeExportExcel" href="#"><i class="fas fa-file-excel me-2"></i>Excel</a>
                </div>
            </div>
        </div>
    </div>

    <div class="lde-results-meta mt-4 d-flex justify-content-between align-items-center">
        <strong id="ldeResultsCount">0 results</strong>
        <span class="text-muted" id="ldeResultsPageInfo">Page 1</span>
    </div>

    <div id="ldeResultsCards" class="lde-cards-grid mt-3"></div>
    <div id="ldeResultsTableWrap" class="lde-panel mt-3 d-none">
        <div class="table-responsive">
            <table class="table lde-table" id="ldeResultsTable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Address</th>
                        <th>Contacts</th>
                        <th>Rating</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="text-center mt-4">
        <button class="btn btn-outline-primary d-none" id="ldeLoadMore">Load more</button>
    </div>
</main>

<div class="modal fade" id="ldeDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Business details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="ldeDetailBody"></div>
        </div>
    </div>
</div>

<script src="{{ asset('vendor/location-data-engine/js/results.js') }}" defer></script>
@endsection
