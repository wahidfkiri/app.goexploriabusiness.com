@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('vendor/maps-data-engine/css/admin.css') }}">

<main class="dashboard-content mde-shell">
    <div class="mde-hero compact">
        <div>
            <span class="mde-kicker">Maps Data Engine</span>
            <h1 class="mde-title">{{ $listing->name }}</h1>
            <p class="mde-subtitle">Detailed listing snapshot from Google Maps scraping workflow.</p>
        </div>
        <a href="{{ route('maps-data-engine.admin.results.index') }}" class="btn btn-outline-secondary mde-cta"><i class="fas fa-arrow-left me-2"></i>Back</a>
    </div>

    @include('maps-data-engine::admin.partials.nav')

    <div class="row g-4 mt-2">
        <div class="col-lg-8">
            <div class="mde-panel">
                <h3 class="mb-3">Listing profile</h3>
                <dl class="row mb-0">
                    <dt class="col-sm-3">Address</dt><dd class="col-sm-9">{{ $listing->address ?: '-' }}</dd>
                    <dt class="col-sm-3">Website</dt><dd class="col-sm-9">@if($listing->website)<a href="{{ $listing->website }}" target="_blank">{{ $listing->website }}</a>@else - @endif</dd>
                    <dt class="col-sm-3">Phone</dt><dd class="col-sm-9">{{ $listing->phone ?: '-' }}</dd>
                    <dt class="col-sm-3">Categories</dt><dd class="col-sm-9">{{ implode(', ', $listing->categories ?: []) }}</dd>
                    <dt class="col-sm-3">Google Maps</dt><dd class="col-sm-9">@if($listing->google_maps_url)<a href="{{ $listing->google_maps_url }}" target="_blank">Open listing</a>@else - @endif</dd>
                </dl>
            </div>
            <div class="mde-panel mt-4">
                <h3 class="mb-3">Reviews preview</h3>
                @forelse(($listing->reviews_preview ?: []) as $review)
                    <div class="mde-review-card mb-3">
                        <strong>{{ $review['author'] ?? 'Review' }}</strong>
                        <div class="small text-muted">{{ $review['rating'] ?? '-' }} stars</div>
                        <p class="mb-0 mt-2">{{ $review['text'] ?? 'No preview text.' }}</p>
                    </div>
                @empty
                    <div class="text-muted">No reviews preview stored.</div>
                @endforelse
            </div>
        </div>
        <div class="col-lg-4">
            <div class="mde-panel">
                <h3 class="mb-3">Metrics</h3>
                <div class="mde-micro-stat"><span>Rating</span><strong>{{ $listing->rating ?: '-' }}</strong></div>
                <div class="mde-micro-stat"><span>Reviews count</span><strong>{{ $listing->reviews_count }}</strong></div>
                <div class="mde-micro-stat"><span>Location</span><strong>{{ $listing->city ?: '-' }}, {{ $listing->province ?: '-' }}</strong></div>
                <div class="mde-micro-stat"><span>Last scraped</span><strong>{{ optional($listing->last_scraped_at)->diffForHumans() ?: '-' }}</strong></div>
            </div>
            <div class="mde-panel mt-4">
                <h3 class="mb-3">Image references</h3>
                @if(!empty($listing->images))
                    <div class="d-grid gap-2">
                        @foreach($listing->images as $image)
                            <div class="mde-inline-chip">{{ is_array($image) ? ($image['url'] ?? json_encode($image)) : $image }}</div>
                        @endforeach
                    </div>
                @else
                    <div class="text-muted">No image references stored.</div>
                @endif
            </div>
        </div>
    </div>
</main>
@endsection
