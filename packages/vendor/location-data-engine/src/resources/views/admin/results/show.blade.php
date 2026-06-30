@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('vendor/location-data-engine/css/admin.css') }}">

<main class="dashboard-content lde-shell">
    <div class="lde-header">
        <div>
            <span class="lde-kicker">Location Data Engine</span>
            <h1 class="lde-title">{{ $business->name }}</h1>
            <p class="lde-subtitle">Detailed business data, destination links, social enrichment and reviews.</p>
        </div>
        <a href="{{ route('location-data-engine.admin.results.index') }}" class="btn btn-outline-secondary lde-header-btn">
            <i class="fas fa-arrow-left me-2"></i>Back to results
        </a>
    </div>

    @include('location-data-engine::admin.partials.nav')

    <div class="row g-4 mt-2">
        <div class="col-lg-8">
            <div class="lde-panel">
                <h3 class="mb-3">Business profile</h3>
                <dl class="row mb-0">
                    <dt class="col-sm-3">Address</dt><dd class="col-sm-9">{{ $business->address ?: '-' }}</dd>
                    <dt class="col-sm-3">Website</dt><dd class="col-sm-9">@if($business->website)<a href="{{ $business->website }}" target="_blank">{{ $business->website }}</a>@else - @endif</dd>
                    <dt class="col-sm-3">Email</dt><dd class="col-sm-9">{{ $business->email ?: '-' }}</dd>
                    <dt class="col-sm-3">Phone</dt><dd class="col-sm-9">{{ $business->phone ?: $business->international_phone ?: '-' }}</dd>
                    <dt class="col-sm-3">Location</dt><dd class="col-sm-9">{{ $business->city }}, {{ $business->province }}, {{ $business->country }}</dd>
                    <dt class="col-sm-3">Categories</dt><dd class="col-sm-9">{{ implode(', ', $business->categories ?: []) }}</dd>
                    <dt class="col-sm-3">Maps</dt><dd class="col-sm-9">@if($business->google_maps_url)<a href="{{ $business->google_maps_url }}" target="_blank">Open in Google Maps</a>@else - @endif</dd>
                </dl>
            </div>
            <div class="lde-panel mt-4">
                <h3 class="mb-3">Reviews</h3>
                <div class="row g-3">
                    @forelse($business->reviews as $review)
                        <div class="col-md-6">
                            <div class="lde-review-card">
                                <strong>{{ $review->author_name ?: 'Google review' }}</strong>
                                <div class="text-warning small">Rating: {{ $review->rating ?? '-' }}</div>
                                <p class="mb-0 mt-2">{{ $review->text ?: 'No review body available.' }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-muted">No reviews saved for this listing.</div>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="lde-panel">
                <h3 class="mb-3">Metrics</h3>
                <div class="lde-mini-stat"><span>Rating</span><strong>{{ $business->rating ?: '-' }}</strong></div>
                <div class="lde-mini-stat"><span>Reviews count</span><strong>{{ $business->reviews_count }}</strong></div>
                <div class="lde-mini-stat"><span>Status</span><strong>{{ $business->business_status ?: '-' }}</strong></div>
                <div class="lde-mini-stat"><span>Last scan</span><strong>{{ optional($business->last_scanned_at)->diffForHumans() ?: '-' }}</strong></div>
            </div>
            <div class="lde-panel mt-4">
                <h3 class="mb-3">Photos</h3>
                <div class="row g-2">
                    @forelse($business->photos as $photo)
                        <div class="col-6">
                            <img src="{{ $photo->cdn_url ?: asset('storage/' . ltrim((string) $photo->file_path, '/')) }}" alt="{{ $business->name }}" class="img-fluid rounded-3">
                        </div>
                    @empty
                        <div class="col-12 text-muted">No stored photos for this listing.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
