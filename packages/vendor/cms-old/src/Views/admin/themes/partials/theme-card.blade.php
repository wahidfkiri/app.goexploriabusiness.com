@php
    $template = $template ?? $theme;
@endphp

<div class="global-template-card" id="gtc-{{ $template->id }}">
    <div class="gtc-preview">
        @if($template->getPreviewImageUrl())
            <img src="{{ $template->getPreviewImageUrl() }}" alt="{{ $template->name }}" loading="lazy">
        @else
            <div class="gtc-placeholder">
                <i class="fas fa-file-code"></i>
            </div>
        @endif

        <div class="gtc-status {{ $template->status }}">
            {{ strtoupper($template->status) }}
        </div>
    </div>

    <div class="gtc-body">
        <div class="gtc-header">
            <h5 class="gtc-name">{{ $template->name }}</h5>
            <span class="gtc-version">v{{ $template->version ?? '1.0.0' }}</span>
        </div>

        @if($template->description)
            <p class="gtc-desc">{{ Str::limit($template->description, 90) }}</p>
        @endif

        <div class="gtc-meta">
            <span><i class="fas fa-folder"></i> {{ $template->category ?: 'General' }}</span>
            <span><i class="fas fa-calendar-alt"></i> {{ optional($template->created_at)->format('d/m/Y') ?: 'N/A' }}</span>
        </div>

        @if($template->site_url)
            <a href="{{ $template->site_url }}" target="_blank" class="gtc-site-link">
                <i class="fas fa-link"></i> Site source
            </a>
        @endif

        <div class="gtc-actions">
            <button class="gtc-btn gtc-btn-preview" onclick="previewGlobalTemplate({{ $template->id }})">
                <i class="fas fa-eye"></i> Preview
            </button>
            <button class="gtc-btn gtc-btn-edit" onclick="openTemplateEdit({{ $template->id }})">
                <i class="fas fa-edit"></i>
            </button>
            <button class="gtc-btn gtc-btn-duplicate" onclick="duplicateGlobalTheme({{ $template->id }}, this)">
                <i class="fas fa-copy"></i>
            </button>
            <button class="gtc-btn gtc-btn-delete" onclick="deleteGlobalTheme({{ $template->id }}, this)">
                <i class="fas fa-trash-alt"></i>
            </button>
        </div>
    </div>
</div>
