<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@if(isset($template)){{ $template->name }} - @endif Éditeur CMS</title>
    <!-- Fonts & Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- GrapesJS CSS via CDN -->
    <link rel="stylesheet" href="https://unpkg.com/grapesjs/dist/css/grapes.min.css">
    
    <link rel="stylesheet" href="{{ asset('vendor/editor/css/editor.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/editor/css/custom.css') }}">
    
    <style>
        .preview-frame {
            width: 100%;
            height: 600px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: white;
        }
        
        .preview-modal-footer {
            display: flex;
            justify-content: flex-end;
            padding: 15px;
            border-top: 1px solid #e2e8f0;
        }
        
        .modal-content {
            max-width: 90%;
            margin: 5vh auto;
        }
        
        .preview-fullscreen-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #3b82f6;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .preview-fullscreen-btn:hover {
            background: #2563eb;
        }

        .block-preview-action {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 30px;
            height: 30px;
            border: 1px solid rgba(148, 163, 184, 0.28);
            border-radius: 8px;
            background: rgba(15, 23, 42, 0.82);
            color: #cbd5e1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0.86;
            transform: translateY(0);
            transition: opacity 0.2s ease, transform 0.2s ease, background 0.2s ease, color 0.2s ease;
            z-index: 2;
        }

        .block-preview-action:focus {
            opacity: 1;
        }

        .block-preview-action:hover {
            opacity: 1;
            background: #3b82f6;
            color: #fff;
            border-color: #60a5fa;
        }

        .block-preview-modal-content {
            width: min(1120px, 94vw);
            max-width: 1120px;
            height: min(760px, 88vh);
        }

        .block-preview-body {
            background: #e2e8f0;
            padding: 14px;
        }

        .block-preview-frame {
            width: 100%;
            height: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #fff;
        }

        .block-preview-meta {
            color: #94a3b8;
            font-size: 12px;
            margin-left: 8px;
            font-weight: 400;
        }

        .block-preview-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 14px 18px;
            border-top: 1px solid #334155;
            background: #0f172a;
        }
        
        /* Styles pour la navigation des catégories */
        .categories-scroll-container {
            position: relative;
            display: contents;
            align-items: center;
            flex: 1;
        }
        
        .categories-scroll {
            display: flex;
            overflow-x: auto;
            scroll-behavior: smooth;
            flex: 1;
            padding: 0 5px;
            scrollbar-width: none; /* Firefox */
            -ms-overflow-style: none; /* IE/Edge */
        }
        
        .categories-scroll::-webkit-scrollbar {
            display: none; /* Chrome/Safari/Opera */
        }
        
        .categories-nav-btn {
            background: #f1f5f9;
            border: none;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #64748b;
            font-size: 12px;
            flex-shrink: 0;
            transition: all 0.2s ease;
        }
        
        .categories-nav-btn:hover {
            background: #e2e8f0;
            color: #475569;
        }
        
        .categories-nav-btn.left {
            margin-right: 5px;
        }
        
        .categories-nav-btn.right {
            margin-left: 5px;
        }
        
        .categories-nav-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }
        
        .categories-nav-btn i {
            font-size: 10px;
        }
        
        /* .blocks-categories-nav {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
            position: relative;
        } */
        
        /* Animation des catégories */
        .category-tab {
            animation: fadeInSlide 0.3s ease-out forwards;
        }
        
        @keyframes fadeInSlide {
            from {
                opacity: 0;
                transform: translateY(5px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .editor-container {
            position: relative;
        }

        .sidebar-left {
            bottom: 0;
            box-shadow: 18px 0 38px rgba(15, 23, 42, 0.22);
            left: 0;
            max-width: min(380px, 92vw);
            opacity: 0;
            pointer-events: none;
            position: absolute;
            top: 0;
            transform: translateX(calc(-100% - 18px));
            transition: opacity 0.22s ease, transform 0.22s ease;
            width: min(380px, 92vw) !important;
            z-index: 30;
        }

        .editor-container.sidebar-visible .sidebar-left {
            opacity: 1;
            pointer-events: auto;
            transform: translateX(0);
        }

        .editor-main {
            flex: 1 1 auto;
            min-width: 0;
            position: relative;
            width: 100%;
        }

        .elementor-add-section-bar {
            align-items: center;
            bottom: 22px;
            display: flex;
            justify-content: center;
            left: 50%;
            pointer-events: none;
            position: absolute;
            transform: translateX(-50%);
            z-index: 24;
        }

        .elementor-add-section-btn {
            align-items: center;
            background: #7c3aed;
            border: 1px solid #8b5cf6;
            border-radius: 999px;
            box-shadow: 0 18px 36px rgba(76, 29, 149, 0.28);
            color: #fff;
            cursor: pointer;
            display: inline-flex;
            font-size: 13px;
            font-weight: 800;
            gap: 9px;
            min-height: 44px;
            padding: 0 18px;
            pointer-events: auto;
            transition: background 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
        }

        .elementor-add-section-btn:hover {
            background: #6d28d9;
            box-shadow: 0 20px 40px rgba(76, 29, 149, 0.34);
            transform: translateY(-1px);
        }

        .elementor-add-section-btn i {
            align-items: center;
            background: rgba(255, 255, 255, 0.18);
            border-radius: 999px;
            display: inline-flex;
            height: 26px;
            justify-content: center;
            width: 26px;
        }

        .sidebar-open-fab {
            align-items: center;
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: 8px;
            color: #e2e8f0;
            cursor: pointer;
            display: inline-flex;
            gap: 8px;
            height: auto;
            justify-content: center;
            min-height: 38px;
            padding: 8px 12px;
            position: static;
            transition: background 0.18s ease, border-color 0.18s ease, color 0.18s ease;
            width: auto;
        }

        .sidebar-open-fab:hover,
        .sidebar-open-fab.is-active {
            background: #2563eb;
            border-color: #3b82f6;
            color: #fff;
        }

        .sidebar-toggle {
            align-items: center;
            display: inline-flex;
            justify-content: center;
        }

        .global-loader {
            align-items: center;
            background: rgba(15, 23, 42, 0.94);
            color: #fff;
            display: none;
            inset: 0;
            justify-content: center;
            position: fixed;
            z-index: 20000;
        }

        .global-loader.is-active {
            display: flex;
        }

        .loader-content {
            align-items: center;
            background: rgba(15, 23, 42, 0.92);
            border: 1px solid rgba(148, 163, 184, 0.28);
            border-radius: 12px;
            box-shadow: 0 24px 80px rgba(0, 0, 0, 0.34);
            display: flex;
            flex-direction: column;
            gap: 16px;
            min-width: 280px;
            padding: 28px 34px;
            text-align: center;
        }

        .loader-spinner {
            animation: cmsLoaderSpin 0.85s linear infinite;
            border: 4px solid rgba(255, 255, 255, 0.18);
            border-radius: 999px;
            border-top-color: #60a5fa;
            height: 46px;
            width: 46px;
        }

        .loader-text {
            color: #e2e8f0;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0;
        }

        body.editor-booting .top-bar,
        body.editor-booting .editor-container {
            opacity: 0;
            pointer-events: none;
        }

        @keyframes cmsLoaderSpin {
            to {
                transform: rotate(360deg);
            }
        }

        .gjs-block-category .gjs-title,
        .gjs-block-category.gjs-open .gjs-title {
            align-items: center !important;
            background: #111827 !important;
            border-bottom: 1px solid rgba(148, 163, 184, 0.18) !important;
            color: #f8fafc !important;
            display: flex !important;
            font-size: 13px !important;
            font-weight: 800 !important;
            gap: 8px !important;
            letter-spacing: 0 !important;
            min-height: 40px !important;
            padding: 0 14px !important;
        }

        .gjs-block-category .gjs-title::after {
            background: rgba(20, 184, 166, 0.14);
            border: 1px solid rgba(45, 212, 191, 0.3);
            border-radius: 999px;
            color: #99f6e4;
            content: 'Drag';
            font-size: 10px;
            font-weight: 800;
            margin-left: auto;
            padding: 3px 8px;
            text-transform: uppercase;
        }

        .gjs-blocks-c {
            background: #0f172a !important;
            display: grid !important;
            gap: 8px !important;
            grid-template-columns: repeat(auto-fill, minmax(112px, 1fr)) !important;
            padding: 10px !important;
        }

        .gjs-block,
        .gjs-block.gjs-one-bg,
        .gjs-block.gjs-four-color-h {
            align-items: center !important;
            background: #172033 !important;
            border: 1px solid rgba(148, 163, 184, 0.16) !important;
            border-radius: 8px !important;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.04), 0 8px 18px rgba(2, 6, 23, 0.2) !important;
            color: #e5e7eb !important;
            display: flex !important;
            flex-direction: column !important;
            gap: 9px !important;
            justify-content: center !important;
            margin: 0 !important;
            min-height: 106px !important;
            overflow: hidden !important;
            padding: 12px 10px !important;
            position: relative !important;
            transition: transform 0.16s ease, border-color 0.16s ease, background 0.16s ease, box-shadow 0.16s ease !important;
            width: auto !important;
        }

        .gjs-block::before {
            background: linear-gradient(90deg, #38bdf8, #22c55e);
            content: '';
            height: 2px;
            left: 0;
            opacity: 0;
            position: absolute;
            right: 0;
            top: 0;
            transition: opacity 0.16s ease;
        }

        .gjs-block:hover,
        .gjs-block.gjs-one-bg:hover,
        .gjs-block.gjs-four-color-h:hover {
            background: #1f2a44 !important;
            border-color: rgba(56, 189, 248, 0.5) !important;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.05), 0 14px 28px rgba(8, 47, 73, 0.28) !important;
            color: #ffffff !important;
            transform: translateY(-2px) !important;
        }

        .gjs-block:hover::before {
            opacity: 1;
        }

        .gjs-block__media {
            align-items: center !important;
            background: #0b1220 !important;
            border: 1px solid rgba(148, 163, 184, 0.2) !important;
            border-radius: 8px !important;
            color: #bfdbfe !important;
            display: flex !important;
            height: 38px !important;
            justify-content: center !important;
            margin: 0 !important;
            width: 44px !important;
        }

        .gjs-block__media svg {
            height: 28px !important;
            width: 38px !important;
        }

        .gjs-block__media i,
        .gjs-block__media .form-block-icon {
            color: #bfdbfe !important;
            font-size: 18px !important;
            line-height: 1 !important;
        }

        .gjs-block-label {
            color: #e2e8f0 !important;
            font-size: 12px !important;
            font-weight: 800 !important;
            letter-spacing: 0 !important;
            line-height: 1.25 !important;
            max-width: 100% !important;
            min-height: 30px !important;
            text-align: center !important;
            white-space: normal !important;
            word-break: break-word !important;
        }

        .gjs-block:hover .gjs-block-label,
        .gjs-block:hover .gjs-block__media i,
        .gjs-block:hover .gjs-block__media .form-block-icon {
            color: #ffffff !important;
        }

        .gjs-resizer-h {
            background: #fff !important;
            border: 2px solid #2563eb !important;
            border-radius: 999px !important;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.28) !important;
            height: 10px !important;
            width: 10px !important;
        }

        .gjs-resizer-h:hover {
            background: #2563eb !important;
            border-color: #fff !important;
        }

        .gjs-selected,
        .gjs-hovered {
            outline-color: #2563eb !important;
        }

        .gjs-rte-toolbar .cms-rte-color-action {
            align-items: center;
            display: inline-flex;
            gap: 5px;
            height: 100%;
            padding: 0 4px;
        }

        .gjs-rte-toolbar .cms-rte-color-action i {
            color: #e2e8f0;
            font-size: 12px;
            line-height: 1;
            pointer-events: none;
        }

        .gjs-rte-toolbar .cms-rte-color-action input[type="color"] {
            appearance: none;
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.22);
            border-radius: 6px;
            cursor: pointer;
            height: 22px;
            margin: 0;
            padding: 2px;
            width: 28px;
        }

        .gjs-rte-toolbar .cms-rte-color-action input[type="color"]::-webkit-color-swatch-wrapper {
            padding: 0;
        }

        .gjs-rte-toolbar .cms-rte-color-action input[type="color"]::-webkit-color-swatch {
            border: 0;
            border-radius: 4px;
        }

        .swal2-popup.cms-section-options-modal {
            background: #f8fafc;
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 16px;
            box-shadow: 0 24px 80px rgba(15, 23, 42, 0.22);
            color: #0f172a;
            overflow: hidden;
            padding: 0;
            width: min(560px, calc(100vw - 32px)) !important;
        }

        .cms-section-options-modal .swal2-title {
            color: #0f172a;
            font-size: 20px;
            font-weight: 850;
            letter-spacing: 0;
            margin: 0;
            padding: 22px 24px 4px;
            text-align: left;
        }

        .cms-section-options-modal .swal2-html-container {
            margin: 0;
            overflow: visible;
            padding: 0 24px 18px;
        }

        .cms-section-options-modal .swal2-actions {
            background: #fff;
            border-top: 1px solid #e2e8f0;
            gap: 10px;
            justify-content: flex-end;
            margin: 0;
            padding: 16px 24px;
            width: 100%;
        }

        .cms-section-options-modal .swal2-confirm,
        .cms-section-options-modal .swal2-cancel {
            border-radius: 9px !important;
            box-shadow: none !important;
            font-size: 13px !important;
            font-weight: 800 !important;
            min-height: 40px;
            padding: 0 18px !important;
        }

        .cms-section-options-modal .swal2-confirm {
            background: #2563eb !important;
        }

        .cms-section-options-modal .swal2-cancel {
            background: #f1f5f9 !important;
            color: #334155 !important;
        }

        .cms-section-options-form {
            display: grid;
            gap: 16px;
            text-align: left;
        }

        .cms-section-options-intro {
            align-items: center;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            display: flex;
            gap: 12px;
            padding: 12px;
        }

        .cms-section-options-intro-icon {
            align-items: center;
            background: #dbeafe;
            border-radius: 10px;
            color: #2563eb;
            display: inline-flex;
            flex: 0 0 38px;
            height: 38px;
            justify-content: center;
            width: 38px;
        }

        .cms-section-options-intro strong {
            color: #0f172a;
            display: block;
            font-size: 14px;
            font-weight: 850;
            line-height: 1.2;
        }

        .cms-section-options-intro small {
            color: #64748b;
            display: block;
            font-size: 12px;
            line-height: 1.35;
            margin-top: 3px;
        }

        .cms-section-options-grid {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .cms-section-option-field {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            display: grid;
            gap: 9px;
            min-width: 0;
            padding: 12px;
        }

        .cms-section-option-label {
            color: #475569;
            font-size: 12px;
            font-weight: 850;
            letter-spacing: 0;
        }

        .cms-section-input-wrap {
            align-items: center;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 9px;
            display: flex;
            min-height: 42px;
            overflow: hidden;
        }

        .cms-section-options-modal .cms-section-input-wrap input {
            background: transparent;
            border: 0;
            box-shadow: none;
            color: #0f172a;
            flex: 1;
            font-size: 14px;
            margin: 0;
            min-width: 0;
            outline: 0;
            padding: 10px 12px;
            width: 100%;
        }

        .cms-section-input-unit {
            border-left: 1px solid #e2e8f0;
            color: #64748b;
            flex: 0 0 auto;
            font-size: 12px;
            font-weight: 800;
            padding: 0 11px;
        }

        .cms-section-color-field {
            align-items: center;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            display: grid;
            gap: 12px;
            grid-template-columns: 1fr auto;
            min-width: 0;
            padding: 12px;
        }

        .cms-section-color-meta strong {
            color: #475569;
            display: block;
            font-size: 12px;
            font-weight: 850;
        }

        .cms-section-color-meta span {
            color: #94a3b8;
            display: block;
            font-size: 12px;
            margin-top: 3px;
        }

        .cms-section-color-control {
            align-items: center;
            display: flex;
            gap: 10px;
        }

        .cms-section-options-modal .cms-section-color-control input[type="color"] {
            background: #fff;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            cursor: pointer;
            height: 42px;
            margin: 0;
            padding: 4px;
            width: 52px;
        }

        .cms-section-color-value {
            color: #334155;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
            font-size: 12px;
            font-weight: 800;
            min-width: 74px;
            text-transform: uppercase;
        }

        .cms-section-media-panel {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            display: grid;
            gap: 14px;
            padding: 14px;
        }

        .cms-section-media-head {
            align-items: center;
            display: flex;
            gap: 10px;
            justify-content: space-between;
        }

        .cms-section-media-title {
            align-items: center;
            color: #0f172a;
            display: flex;
            font-size: 13px;
            font-weight: 850;
            gap: 8px;
        }

        .cms-section-media-title i {
            color: #2563eb;
        }

        .cms-section-media-help {
            color: #94a3b8;
            font-size: 12px;
        }

        .cms-section-media-tabs {
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            display: grid;
            gap: 4px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            padding: 4px;
        }

        .cms-section-media-tab {
            align-items: center;
            border-radius: 8px;
            color: #475569;
            cursor: pointer;
            display: inline-flex;
            font-size: 12px;
            font-weight: 850;
            gap: 7px;
            justify-content: center;
            min-height: 36px;
            padding: 0 8px;
        }

        .cms-section-media-tab input {
            display: none;
        }

        .cms-section-media-tab:has(input:checked) {
            background: #fff;
            box-shadow: 0 6px 16px rgba(15, 23, 42, 0.08);
            color: #1d4ed8;
        }

        .cms-section-media-fields {
            display: grid;
            gap: 12px;
        }

        .cms-section-media-row {
            display: grid;
            gap: 12px;
            grid-template-columns: minmax(0, 1fr) 160px;
        }

        .cms-video-options-row {
            display: grid;
            gap: 8px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .cms-video-option-toggle {
            align-items: center;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            color: #334155;
            cursor: pointer;
            display: flex;
            font-size: 12px;
            font-weight: 800;
            gap: 8px;
            min-height: 38px;
            padding: 9px 10px;
        }

        .cms-video-option-toggle input {
            accent-color: #2563eb;
            height: 16px;
            margin: 0;
            width: 16px;
        }

        .cms-section-option-field.is-wide {
            grid-column: 1 / -1;
        }

        .cms-section-options-modal .cms-section-input-wrap select {
            appearance: none;
            background: transparent;
            border: 0;
            color: #0f172a;
            flex: 1;
            font-size: 14px;
            margin: 0;
            min-height: 40px;
            outline: 0;
            padding: 0 12px;
            width: 100%;
        }

        .cms-section-media-fields[data-mode="color"] .cms-media-image-field,
        .cms-section-media-fields[data-mode="color"] .cms-media-video-field,
        .cms-section-media-fields[data-mode="color"] .cms-media-layout-row,
        .cms-section-media-fields[data-mode="color"] .cms-video-options-row,
        .cms-section-media-fields[data-mode="image"] .cms-media-video-field,
        .cms-section-media-fields[data-mode="image"] .cms-video-options-row,
        .cms-section-media-fields[data-mode="video"] .cms-media-image-field {
            display: none;
        }

        .cms-section-media-note {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 10px;
            color: #1e40af;
            font-size: 12px;
            line-height: 1.45;
            padding: 10px 12px;
        }

        @media (max-width: 560px) {
            .cms-section-options-grid {
                grid-template-columns: 1fr;
            }

            .cms-section-color-field {
                grid-template-columns: 1fr;
            }

            .cms-section-options-modal .swal2-actions {
                justify-content: stretch;
            }

            .cms-section-options-modal .swal2-confirm,
            .cms-section-options-modal .swal2-cancel {
                flex: 1;
            }

            .cms-section-media-row {
                grid-template-columns: 1fr;
            }

            .cms-video-options-row {
                grid-template-columns: 1fr;
            }
        }

    </style>
</head>
<body class="editor-booting">
    <div id="global-loader" class="global-loader is-active" role="status" aria-live="polite">
        <div class="loader-content">
            <div class="loader-spinner"></div>
            <div class="loader-text">Chargement de l'editeur...</div>
        </div>
    </div>
    <!-- Barre supérieure -->
    <div class="top-bar">
        <button type="button" class="sidebar-open-fab" id="sidebarOpenFab" onclick="toggleSidebar()" title="Afficher les blocs">
            <i class="fas fa-chevron-right"></i>
            <span>Blocs</span>
        </button>
        
        <div class="menu-actions">
            <a class="menu-btn" href="{{ route('cms.admin.dashboard', ['etablissementId' => $etablissement->id, 'slug' => ($etablissement->slug ?: \Illuminate\Support\Str::slug((string) $etablissement->name)), 'section' => 'pages']) }}">
                <i class="fas fa-arrow-left"></i>
                Retour
            </a>
            <button class="menu-btn danger" onclick="clearCanvas()" title="Effacer tout">
                <i class="fas fa-trash"></i>
                Vider le canevas
            </button>
            <button class="menu-btn" onclick="showPreviewInNewTab()" title="Aperçu">
                <i class="fas fa-eye"></i>
                Afficher l'aperçu
            </button>
            <button class="menu-btn success" onclick="savePage()" title="Sauvegarder">
                <i class="fas fa-save"></i>
                Sauvegarder
            </button>
        </div>
    </div>

    <!-- Container principal -->
    <div class="editor-container">
        <!-- Barre latérale gauche - Blocks & Templates -->
        <div class="sidebar-left" aria-hidden="true">
            <div class="sidebar-header">
                <div class="sidebar-title" style="display:none;">
                    <i class="fas fa-cube"></i>
                    <span>Bibliothèque de Blocs</span>
                    <div class="sidebar-badge">PRO</div>
                </div>
                <button class="sidebar-toggle" onclick="toggleSidebar()">
                    <i class="fas fa-chevron-left"></i>
                </button>
            </div>
            
            <div class="blocks-search-container">
                <div class="search-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="blocks-search-input" 
                           id="blockSearch" 
                           placeholder="Rechercher des blocs, catégories, tags...">
                    <button class="search-clear" onclick="clearSearch()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="blocks-quick-filters">
                    <button class="filter-chip active" data-filter="all">
                        <i class="fas fa-layer-group"></i>
                        <span>Tous</span>
                    </button>
                    <button class="filter-chip" data-filter="popular">
                        <i class="fas fa-fire"></i>
                        <span>Populaires</span>
                    </button>
                    <button class="filter-chip" data-filter="free">
                        <i class="fas fa-bolt"></i>
                        <span>Gratuits</span>
                    </button>
                    <button class="filter-chip" data-filter="responsive">
                        <i class="fas fa-mobile-alt"></i>
                        <span>Mobile</span>
                    </button>
                </div>
            </div>
            
            <!-- Navigation des catégories avec boutons -->
            <div class="blocks-categories-nav">
                <button class="categories-nav-btn left" onclick="scrollCategories(-150)" title="Défiler vers la gauche">
                    <i class="fas fa-chevron-left"></i>
                </button>
                
                <div class="categories-scroll-container">
                    <div class="categories-scroll" id="categoriesScroll">
                        <!-- Les catégories seront générées dynamiquement -->
                    </div>
                </div>
                
                <button class="categories-nav-btn right" onclick="scrollCategories(150)" title="Défiler vers la droite">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
            
            <div class="blocks-content">
                <div class="blocks-stats-bar">
                    <div class="stat-item">
                        <div class="stat-value" id="blocksCount">0</div>
                        <div class="stat-label">Blocs</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="freeCount">0</div>
                        <div class="stat-label">Gratuits</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="proCount">0</div>
                        <div class="stat-label">PRO</div>
                    </div>
                </div>
                
                <div class="blocks-grid-modern" id="blocksContainer">
                    <!-- Les blocs seront chargés dynamiquement -->
                </div>
                
                <div class="blocks-empty-state" id="blocksEmptyState" style="display: none;">
                    <div class="empty-icon">
                        <i class="fas fa-cubes"></i>
                    </div>
                    <h3>Aucun bloc trouvé</h3>
                    <p>Essayez d'ajuster votre recherche ou vos filtres</p>
                    <button class="btn-primary" onclick="resetFilters()">
                        <i class="fas fa-redo"></i>
                        Réinitialiser les Filtres
                    </button>
                </div>
            </div>
        </div>

        <!-- Zone éditeur principale -->
        <div class="editor-main">
            <div id="gjs"></div>
            <div class="elementor-add-section-bar">
                <button type="button" class="elementor-add-section-btn" onclick="openSectionLibraryAtEnd()">
                    <i class="fas fa-plus"></i>
                    Ajouter une section
                </button>
            </div>
        </div>

        <!-- Panneau droit amélioré -->
        <div class="sidebar-right" style="display:none;">
            <div class="right-panel-tabs">
                <button class="right-panel-tab active" onclick="showRightPanel('layers')">
                    <i class="fas fa-layer-group"></i> Calques
                </button>
                <button class="right-panel-tab" onclick="showRightPanel('history')">
                    <i class="fas fa-history"></i> Historique
                </button>
                <button class="right-panel-tab" onclick="showRightPanel('settings')">
                    <i class="fas fa-cog"></i> Paramètres
                </button>
            </div>
            
            <!-- Panneau Couches -->
            <div class="right-panel-content active" id="right-panel-layers">
                <div class="layers-container">
                    <div class="layers-list" id="layersList">
                        <!-- Les couches seront chargées dynamiquement -->
                    </div>
                </div>
            </div>
            
            <!-- Panneau Historique -->
            <div class="right-panel-content" id="right-panel-history">
                <div class="history-container">
                    <div class="history-list" id="historyList">
                        <!-- L'historique sera chargé dynamiquement -->
                    </div>
                </div>
            </div>
            
            <!-- Panneau Paramètres -->
            <div class="right-panel-content" id="right-panel-settings">
                <div class="settings-container">
                    <div class="settings-section">
                        <div class="settings-title">Paramètres du Canevas</div>
                        <div class="settings-group">
                            <div class="setting-item">
                                <span class="setting-label">Afficher la Grille</span>
                                <div class="setting-control">
                                    <label class="switch">
                                        <input type="checkbox" id="showGrid" checked>
                                        <span class="slider"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="setting-item">
                                <span class="setting-label">Afficher les Contours</span>
                                <div class="setting-control">
                                    <label class="switch">
                                        <input type="checkbox" id="showOutlines" checked>
                                        <span class="slider"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="setting-item">
                                <span class="setting-label">Largeur du Canevas</span>
                                <div class="setting-control">
                                    <select class="control-select" id="canvasWidth">
                                        <option value="100%">100%</option>
                                        <option value="1200px">Bureau (1200px)</option>
                                        <option value="768px">Tablette (768px)</option>
                                        <option value="375px">Mobile (375px)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="settings-section">
                        <div class="settings-title">Paramètres de l'Éditeur</div>
                        <div class="settings-group">
                            <div class="setting-item">
                                <span class="setting-label">Sauvegarde auto.</span>
                                <div class="setting-control">
                                    <label class="switch">
                                        <input type="checkbox" id="autoSave">
                                        <span class="slider"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="setting-item">
                                <span class="setting-label">Aligner à la Grille</span>
                                <div class="setting-control">
                                    <label class="switch">
                                        <input type="checkbox" id="snapToGrid">
                                        <span class="slider"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="setting-item">
                                <span class="setting-label">Mode Sombre</span>
                                <div class="setting-control">
                                    <label class="switch">
                                        <input type="checkbox" id="darkMode" checked>
                                        <span class="slider"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="settings-section">
                        <div class="settings-title">Paramètres d'Export</div>
                        <div class="settings-group">
                            <div class="setting-item">
                                <span class="setting-label">Format</span>
                                <div class="setting-control">
                                    <select class="control-select" id="exportFormat">
                                        <option value="html">HTML</option>
                                        <option value="react">React</option>
                                        <option value="vue">Vue</option>
                                    </select>
                                </div>
                            </div>
                            <div class="setting-item">
                                <span class="setting-label">Minifier CSS</span>
                                <div class="setting-control">
                                    <label class="switch">
                                        <input type="checkbox" id="minifyCSS" checked>
                                        <span class="slider"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="setting-item">
                                <span class="setting-label">Minifier HTML</span>
                                <div class="setting-control">
                                    <label class="switch">
                                        <input type="checkbox" id="minifyHTML">
                                        <span class="slider"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Code -->
    <div id="codeModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">
                    <i class="fas fa-code"></i>
                    Code Généré
                </div>
                <button class="modal-close" onclick="closeModal('codeModal')">&times;</button>
            </div>
            <div class="modal-body code-modal-body">
                <div class="code-actions">
                    <button onclick="copyCode()" class="menu-btn">
                        <i class="fas fa-copy"></i> Copier le Code
                    </button>
                </div>
                <textarea id="codeEditor" class="code-editor"></textarea>
            </div>
        </div>
    </div>

    <!-- Modal Preview -->
    <div id="previewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">
                    <i class="fas fa-eye"></i>
                    Aperçu
                </div>
                <button class="modal-close" onclick="closeModal('previewModal')">&times;</button>
            </div>
            <div class="modal-body">
                <iframe id="previewFrame" class="preview-frame"></iframe>
            </div>
            <div class="preview-modal-footer">
                <button class="preview-fullscreen-btn" onclick="showPreviewInNewTab()">
                    <i class="fas fa-external-link-alt"></i>
                    Ouvrir dans un Nouvel Onglet
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Preview Block -->
    <div id="blockPreviewModal" class="modal">
        <div class="modal-content block-preview-modal-content">
            <div class="modal-header">
                <div class="modal-title">
                    <i class="fas fa-eye"></i>
                    <span id="blockPreviewTitle">Aperçu du block</span>
                    <span id="blockPreviewMeta" class="block-preview-meta"></span>
                </div>
                <button class="modal-close" onclick="closeModal('blockPreviewModal')">&times;</button>
            </div>
            <div class="modal-body block-preview-body">
                <iframe id="blockPreviewFrame" class="block-preview-frame"></iframe>
            </div>
            <div class="block-preview-footer">
                <button type="button" class="menu-btn" onclick="closeModal('blockPreviewModal')">
                    Fermer
                </button>
                <button type="button" class="menu-btn success" id="blockPreviewAddBtn">
                    <i class="fas fa-plus"></i>
                    Ajouter au canevas
                </button>
            </div>
        </div>
    </div>

    <!-- Notifications -->
    <div id="notifications"></div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <!-- Scripts CDN -->
    <script src="https://unpkg.com/grapesjs"></script>
    <script src="https://unpkg.com/grapesjs-preset-webpage"></script>
    <script src="https://unpkg.com/grapesjs-blocks-basic"></script>
    <script src="https://unpkg.com/grapesjs-plugin-forms"></script>
    <script src="https://unpkg.com/grapesjs-tabs"></script>
    <script src="https://unpkg.com/grapesjs-custom-code"></script>
    <script src="https://unpkg.com/grapesjs-tooltip"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Script principal -->
    <script>
       // === CONFIGURATION ===
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
let editor;
window.currentPageId = null;  // Changé de currentTemplateId à currentPageId
let allBlocks = [];
let allSections = [];
let dropIndicator = null;
let loadingStack = 0;
let manualResizeComponent = null;
let manualResizeHandles = [];
let manualResizeState = null;

// === INITIALISATION DE L'ÉDITEUR ===
async function initEditor() {
    if (!document.getElementById('gjs')) {
        console.error('Élément #gjs non trouvé dans le DOM');
        setTimeout(() => {
            initEditor().catch(handleEditorBootError);
        }, 100);
        return;
    }
    
    showLoading('Preparation de l editeur...');
    console.log('Initialisation de l\'éditeur GrapesJS...');
    
    editor = grapesjs.init({
        container: '#gjs',
        height: '100%',
        fromElement: true,
        storageManager: false,
        
        plugins: [
            'grapesjs-preset-webpage',
            'grapesjs-blocks-basic',
            'grapesjs-plugin-forms',
            'grapesjs-tabs',
            'grapesjs-custom-code'
        ],
        
        pluginsOpts: {
            'grapesjs-preset-webpage': {
                blocks: []
            }
        },
        
        styleManager: {
            sectors: [
                {
                    name: 'Général',
                    open: false,
                    properties: [
                        'display', 'position', 'float', 'top',
                        'right', 'left', 'bottom'
                    ]
                },
                {
                    name: 'Dimensions',
                    open: false,
                    properties: [
                        'width', 'height', 'max-width', 'min-height',
                        'margin', 'padding'
                    ]
                },
                {
                    name: 'Typographie',
                    open: false,
                    properties: [
                        'font-family', 'font-size', 'font-weight',
                        'letter-spacing', 'color', 'line-height',
                        'text-align', 'text-shadow'
                    ]
                },
                {
                    name: 'Décorations',
                    open: false,
                    properties: [
                        'border-radius', 'border', 'box-shadow',
                        'background', 'opacity'
                    ]
                },
                {
                    name: 'Extra',
                    open: false,
                    properties: [
                        'transition', 'transform', 'cursor',
                        'overflow', 'z-index'
                    ]
                }
            ]
        },
        
        deviceManager: {
            devices: [
                {
                    name: 'Bureau',
                    width: ''
                },
                {
                    name: 'Tablette',
                    width: '768px',
                    widthMedia: '768px'
                },
                {
                    name: 'Mobile',
                    width: '320px',
                    widthMedia: '480px'
                }
            ]
        },
        
        canvas: {
            styles: [
                'https://unpkg.com/grapesjs/dist/css/grapes.min.css',
                'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'
            ]
        }
    });

    await waitForEditorReady();
    initRichTextColorTools();
    registerEnhancedFormBlocks();
    initManualResizeSupport();
    initGjsToolbarSectionOptions();
    
    initLayersPanel();
    initEditorEvents();
    
    setTimeout(() => {
        if (editor && editor.Canvas) {
            initCustomDragDrop();
        } else {
            console.error('GrapesJS pas complètement initialisé, nouvelle tentative...');
            setTimeout(initCustomDragDrop, 500);
        }
    }, 300);
    
    const pageIdFromURL = getPageIdFromURL();
    const bootTasks = [];
    console.log('ID de la page depuis l\'URL:', pageIdFromURL);

    if (pageIdFromURL) {
        window.currentPageId = pageIdFromURL;
        console.log('Définition de currentPageId à:', window.currentPageId);
        bootTasks.push(initBlocksModern());
        bootTasks.push(loadPageOnStart(window.currentPageId));
    } else {
        console.log('Aucun ID de page trouvé, utilisation du contenu par défaut');
        bootTasks.push(initBlocksModern());
        editor.setComponents(`
            <section style="padding: 100px 20px; background: linear-gradient(135deg, #0f172a, #1e293b); color: white; text-align: center;">
                <div style="max-width: 800px; margin: 0 auto;">
                    <h1 style="font-size: 3rem; margin-bottom: 20px; background: linear-gradient(135deg, #8b5cf6, #3b82f6); -webkit-background-clip: text; background-clip: text; color: transparent;">
                        Bienvenue dans l'éditeur CMS
                    </h1>
                    <p style="font-size: 1.2rem; margin-bottom: 40px; opacity: 0.9;">
                        Glissez-déposez des blocs depuis le panneau de gauche pour commencer à créer votre page.
                    </p>
                </div>
            </section>
        `);
    }

    await Promise.allSettled(bootTasks);
    await waitForEditorAssets();
    await waitForEditorPaint();
    document.body.classList.remove('editor-booting');
    document.body.classList.add('editor-ready');
    hideLoading();
}

function initRichTextColorTools() {
    const rte = editor?.RichTextEditor;
    if (!rte || rte.__cmsColorToolsReady) return;

    rte.__cmsColorToolsReady = true;

    rte.add('cms-text-color', {
        icon: `
            <span class="cms-rte-color-action" title="Couleur du texte">
                <i class="fas fa-font"></i>
                <input type="color" value="#111827" aria-label="Couleur du texte">
            </span>
        `,
        event: 'change',
        result: (richTextEditor, action) => {
            const color = action?.btn?.querySelector('input[type="color"]')?.value;
            if (color) {
                richTextEditor.exec('foreColor', color);
            }
        }
    });

    rte.add('cms-background-color', {
        icon: `
            <span class="cms-rte-color-action" title="Couleur de fond">
                <i class="fas fa-fill-drip"></i>
                <input type="color" value="#ffffff" aria-label="Couleur de fond">
            </span>
        `,
        event: 'change',
        result: (richTextEditor, action) => {
            const color = action?.btn?.querySelector('input[type="color"]')?.value;
            if (!color) return;

            richTextEditor.exec('hiliteColor', color);
            richTextEditor.exec('backColor', color);
        }
    });
}

function waitForEditorReady() {
    return new Promise(resolve => {
        if (!editor || !editor.on) {
            resolve();
            return;
        }

        let resolved = false;
        const finish = () => {
            if (resolved) return;
            resolved = true;
            resolve();
        };

        editor.on('load', finish);
        setTimeout(finish, 900);
    });
}

function waitForEditorPaint() {
    return new Promise(resolve => {
        requestAnimationFrame(() => {
            setTimeout(resolve, 220);
        });
    });
}

function waitForEditorAssets(timeoutMs = 1800) {
    const assets = [
        ...document.querySelectorAll('.sidebar-left img, .sidebar-left video, .blocks-grid-modern img, .blocks-grid-modern video')
    ];

    try {
        const canvasDocument = editor?.Canvas?.getDocument?.();
        if (canvasDocument) {
            assets.push(...canvasDocument.querySelectorAll('img, video'));
        }
    } catch (error) {
        console.warn('Impossible de verifier les medias du canvas:', error);
    }

    const pendingAssets = assets.filter(asset => {
        const tagName = asset.tagName.toLowerCase();
        if (tagName === 'img') {
            return !asset.complete;
        }

        if (tagName === 'video') {
            return asset.readyState < 1;
        }

        return false;
    });

    if (!pendingAssets.length) {
        return Promise.resolve();
    }

    const assetPromises = pendingAssets.map(asset => new Promise(resolve => {
        asset.addEventListener('load', resolve, { once: true });
        asset.addEventListener('error', resolve, { once: true });
        asset.addEventListener('loadedmetadata', resolve, { once: true });
    }));

    return Promise.race([
        Promise.allSettled(assetPromises),
        new Promise(resolve => setTimeout(resolve, timeoutMs))
    ]);
}

function initManualResizeSupport() {
    if (!editor || !editor.on) return;

    editor.on('component:selected', component => {
        const target = findResizableToolbarTarget(component) || component;
        if (enableManualResize(target)) {
            showManualResizeHandles(target);
            refreshEditorTools();
            return;
        }

        hideManualResizeHandles();
    });

    editor.on('component:add', component => {
        enableManualResizeForTree([component]);
    });

    editor.on('component:deselected', hideManualResizeHandles);
    editor.on('component:remove', component => {
        if (component === manualResizeComponent) {
            hideManualResizeHandles();
        }
    });

    editor.on('component:update style:property:update', () => {
        positionManualResizeHandles();
    });

    const rootComponents = editor.DomComponents?.getComponents?.();
    enableManualResizeForTree(rootComponents);
}

function enableManualResizeForTree(components) {
    if (!components || !components.forEach) return;

    components.forEach(component => {
        enableManualResize(component);

        const children = typeof component.components === 'function'
            ? component.components()
            : component.get?.('components');

        enableManualResizeForTree(children);
    });
}

function enableManualResize(component) {
    if (!isManuallyResizableComponent(component)) {
        return false;
    }

    const tagName = String(component.get('tagName') || '').toLowerCase();
    const resizeConfig = {
        tl: 0,
        tc: 1,
        tr: 0,
        cl: 1,
        cr: 1,
        bl: 0,
        bc: 1,
        br: 1,
        minDim: 24,
        step: 1,
        keyWidth: 'width',
        keyHeight: ['section', 'article', 'main', 'header', 'footer', 'aside'].includes(tagName) ? 'min-height' : 'height'
    };

    component.set('resizable', resizeConfig);

    return true;
}

function isManuallyResizableComponent(component) {
    if (!component || component.is?.('wrapper')) {
        return false;
    }

    const tagName = String(component.get('tagName') || '').toLowerCase();
    const type = String(component.get('type') || '').toLowerCase();
    const resizableTags = ['section', 'div', 'article', 'main', 'header', 'footer', 'aside'];
    const blockedTypes = ['text', 'link', 'image', 'video', 'map', 'input', 'textarea', 'select', 'button', 'label'];

    if (blockedTypes.includes(type)) {
        return false;
    }

    return resizableTags.includes(tagName);
}

function refreshEditorTools() {
    window.setTimeout(() => {
        try {
            editor?.refresh?.();
        } catch (error) {
            console.warn('Impossible de rafraichir les outils de resize:', error);
        }
    }, 0);
}

function showManualResizeHandles(component) {
    if (!component || !isManuallyResizableComponent(component)) {
        hideManualResizeHandles();
        return;
    }

    manualResizeComponent = component;
    ensureManualResizeCanvasStyle();

    const canvasDocument = editor?.Canvas?.getDocument?.();
    if (!canvasDocument) return;

    if (!manualResizeHandles.length) {
        const handle = canvasDocument.createElement('button');
        handle.type = 'button';
        handle.className = 'cms-manual-resize-handle cms-manual-resize-handle-br';
        handle.setAttribute('aria-label', 'Redimensionner');
        handle.setAttribute('title', 'Redimensionner');
        handle.dataset.direction = 'br';
        handle.addEventListener('mousedown', startManualResize);
        canvasDocument.body.appendChild(handle);
        manualResizeHandles = [handle];
    }

    bindManualResizeCanvasEvents();
    positionManualResizeHandles();
}

function hideManualResizeHandles() {
    manualResizeState = null;
    manualResizeComponent = null;
    manualResizeHandles.forEach(handle => handle.remove());
    manualResizeHandles = [];
}

function ensureManualResizeCanvasStyle() {
    const canvasDocument = editor?.Canvas?.getDocument?.();
    if (!canvasDocument || canvasDocument.getElementById('cmsManualResizeStyles')) return;

    const style = canvasDocument.createElement('style');
    style.id = 'cmsManualResizeStyles';
    style.textContent = `
        .cms-manual-resize-handle {
            align-items: center;
            background: #2563eb;
            border: 3px solid #ffffff;
            border-radius: 999px;
            box-shadow: 0 8px 22px rgba(37, 99, 235, .32);
            cursor: nwse-resize;
            display: flex;
            height: 18px;
            justify-content: center;
            margin: 0;
            padding: 0;
            position: fixed;
            width: 18px;
            z-index: 2147483647;
        }

        .cms-manual-resize-handle::after {
            background: rgba(255, 255, 255, .95);
            border-radius: 999px;
            content: '';
            height: 6px;
            width: 6px;
        }

        .cms-manual-resize-handle:hover {
            background: #7c3aed;
            transform: scale(1.08);
        }

        body.cms-manual-resizing,
        body.cms-manual-resizing * {
            cursor: nwse-resize !important;
            user-select: none !important;
        }
    `;
    canvasDocument.head.appendChild(style);
}

function bindManualResizeCanvasEvents() {
    const canvasWindow = editor?.Canvas?.getWindow?.();
    if (!canvasWindow || canvasWindow.__cmsManualResizeEventsBound) return;

    canvasWindow.__cmsManualResizeEventsBound = true;
    canvasWindow.addEventListener('scroll', positionManualResizeHandles, true);
    canvasWindow.addEventListener('resize', positionManualResizeHandles);
}

function positionManualResizeHandles() {
    if (!manualResizeComponent || !manualResizeHandles.length) return;

    const element = getComponentElement(manualResizeComponent);
    if (!element) {
        hideManualResizeHandles();
        return;
    }

    const rect = element.getBoundingClientRect();
    const handle = manualResizeHandles[0];

    if (!rect.width || !rect.height) {
        handle.style.display = 'none';
        return;
    }

    handle.style.display = 'flex';
    handle.style.left = `${Math.max(rect.right - 9, 0)}px`;
    handle.style.top = `${Math.max(rect.bottom - 9, 0)}px`;
}

function startManualResize(event) {
    event.preventDefault();
    event.stopPropagation();

    if (!manualResizeComponent) return;

    const element = getComponentElement(manualResizeComponent);
    const canvasDocument = editor?.Canvas?.getDocument?.();
    const canvasWindow = editor?.Canvas?.getWindow?.();

    if (!element || !canvasDocument || !canvasWindow) return;

    const rect = element.getBoundingClientRect();
    const tagName = String(manualResizeComponent.get('tagName') || '').toLowerCase();
    manualResizeState = {
        component: manualResizeComponent,
        startX: event.clientX,
        startY: event.clientY,
        startWidth: rect.width,
        startHeight: rect.height,
        heightProperty: ['section', 'article', 'main', 'header', 'footer', 'aside'].includes(tagName) ? 'min-height' : 'height'
    };

    canvasDocument.body.classList.add('cms-manual-resizing');
    canvasWindow.addEventListener('mousemove', handleManualResizeMove);
    canvasWindow.addEventListener('mouseup', stopManualResize);
    window.addEventListener('mouseup', stopManualResize);
}

function handleManualResizeMove(event) {
    if (!manualResizeState) return;

    event.preventDefault();

    const nextWidth = Math.max(24, Math.round(manualResizeState.startWidth + event.clientX - manualResizeState.startX));
    const nextHeight = Math.max(24, Math.round(manualResizeState.startHeight + event.clientY - manualResizeState.startY));
    const component = manualResizeState.component;
    const currentStyle = component.getStyle ? component.getStyle() : {};

    component.setStyle({
        ...currentStyle,
        width: `${nextWidth}px`,
        [manualResizeState.heightProperty]: `${nextHeight}px`
    });

    positionManualResizeHandles();
}

function stopManualResize() {
    if (!manualResizeState) return;

    const canvasDocument = editor?.Canvas?.getDocument?.();
    const canvasWindow = editor?.Canvas?.getWindow?.();

    canvasDocument?.body?.classList.remove('cms-manual-resizing');
    canvasWindow?.removeEventListener('mousemove', handleManualResizeMove);
    canvasWindow?.removeEventListener('mouseup', stopManualResize);
    window.removeEventListener('mouseup', stopManualResize);
    manualResizeState = null;
    updateLayersPanel();
    refreshEditorTools();
}

function getComponentElement(component) {
    if (!component) return null;

    if (typeof component.getEl === 'function') {
        return component.getEl();
    }

    return component.view?.el || null;
}

function initGjsToolbarSectionOptions() {
    if (!editor?.Commands || !editor?.on) return;

    registerGjsToolbarCommand('cms-section-add-after', () => {
        const target = getSelectedToolbarTarget();
        if (target) insertToolbarSectionAfter(target);
    });

    registerGjsToolbarCommand('cms-section-duplicate', () => {
        const target = getSelectedToolbarTarget();
        if (target) duplicateToolbarComponent(target);
    });

    registerGjsToolbarCommand('cms-section-move-up', () => {
        const target = getSelectedToolbarTarget();
        if (target) moveToolbarComponent(target, -1);
    });

    registerGjsToolbarCommand('cms-section-move-down', () => {
        const target = getSelectedToolbarTarget();
        if (target) moveToolbarComponent(target, 1);
    });

    registerGjsToolbarCommand('cms-section-boxed', () => {
        const target = getSelectedToolbarTarget();
        if (target) applyToolbarLayout(target, 'boxed');
    });

    registerGjsToolbarCommand('cms-section-full', () => {
        const target = getSelectedToolbarTarget();
        if (target) applyToolbarLayout(target, 'full');
    });

    registerGjsToolbarCommand('cms-section-quick-options', () => {
        const target = getSelectedToolbarTarget();
        if (target) openToolbarQuickOptions(target);
    });

    registerGjsToolbarCommand('cms-section-reset', () => {
        const target = getSelectedToolbarTarget();
        if (target) resetToolbarSectionStyles(target);
    });

    registerGjsToolbarCommand('cms-section-delete', () => {
        const target = getSelectedToolbarTarget();
        if (target) {
            target.remove();
            updateLayersPanel();
        }
    });

    editor.on('component:selected', component => applyGjsToolbarOptions(component));
    editor.on('component:add', component => enableToolbarForTree([component]));
    enableToolbarForTree(editor.DomComponents?.getComponents?.());
}

function registerGjsToolbarCommand(commandId, run) {
    if (editor.Commands.get(commandId)) return;
    editor.Commands.add(commandId, { run });
}

function enableToolbarForTree(components) {
    if (!components || !components.forEach) return;

    components.forEach(component => {
        applyGjsToolbarOptions(component);
        const children = typeof component.components === 'function'
            ? component.components()
            : component.get?.('components');
        enableToolbarForTree(children);
    });
}

function applyGjsToolbarOptions(component) {
    const target = findResizableToolbarTarget(component);
    if (!target) return;

    target.set('toolbar', [
        { attributes: { class: 'fas fa-arrows-alt', title: 'Deplacer' }, command: 'tlb-move' },
        { attributes: { class: 'fas fa-plus', title: 'Ajouter apres' }, command: 'cms-section-add-after' },
        { attributes: { class: 'fas fa-clone', title: 'Dupliquer' }, command: 'cms-section-duplicate' },
        { attributes: { class: 'fas fa-arrow-up', title: 'Monter' }, command: 'cms-section-move-up' },
        { attributes: { class: 'fas fa-arrow-down', title: 'Descendre' }, command: 'cms-section-move-down' },
        { attributes: { class: 'fas fa-compress-alt', title: 'Boxed 1140px' }, command: 'cms-section-boxed' },
        { attributes: { class: 'fas fa-expand-alt', title: 'Pleine largeur' }, command: 'cms-section-full' },
        { attributes: { class: 'fas fa-sliders-h', title: 'Options rapides' }, command: 'cms-section-quick-options' },
        { attributes: { class: 'fas fa-eraser', title: 'Reset styles' }, command: 'cms-section-reset' },
        { attributes: { class: 'fas fa-trash', title: 'Supprimer' }, command: 'cms-section-delete' }
    ]);
}

function getSelectedToolbarTarget() {
    return findResizableToolbarTarget(editor?.getSelected?.());
}

function findResizableToolbarTarget(component) {
    let current = component;

    while (current && !current.is?.('wrapper')) {
        if (isManuallyResizableComponent(current)) {
            return current;
        }
        current = typeof current.parent === 'function' ? current.parent() : null;
    }

    return null;
}

function applyToolbarStyle(component, styleUpdate) {
    if (!component) return;

    const currentStyle = component.getStyle ? component.getStyle() : {};
    const nextStyle = { ...currentStyle };

    Object.entries(styleUpdate).forEach(([property, value]) => {
        if (value === '' || value === null || value === undefined) {
            delete nextStyle[property];
        } else {
            nextStyle[property] = value;
        }
    });

    component.setStyle(nextStyle);
    enableManualResize(component);
    updateLayersPanel();
    refreshEditorTools();
    positionManualResizeHandles();
}

function applyToolbarLayout(component, mode) {
    if (mode === 'boxed') {
        applyToolbarStyle(component, {
            width: '100%',
            'max-width': '1140px',
            'margin-left': 'auto',
            'margin-right': 'auto'
        });
        return;
    }

    if (mode === 'full') {
        applyToolbarStyle(component, {
            width: '100%',
            'max-width': 'none',
            'margin-left': '0',
            'margin-right': '0'
        });
    }
}

function openToolbarQuickOptions(component) {
    const style = component.getStyle ? component.getStyle() : {};
    const detectedMedia = findSectionExistingMedia(component);
    const backgroundImageUrl = extractCssUrl(style['background-image'] || style.background || '') || detectedMedia.imageUrl || '';
    const backgroundVideo = findSectionBackgroundVideo(component);
    const backgroundVideoUrl = getSectionBackgroundVideoUrl(backgroundVideo) || detectedMedia.videoUrl || '';
    const videoOptions = getSectionBackgroundVideoOptions(backgroundVideo);
    const mediaMode = backgroundVideoUrl ? 'video' : (backgroundImageUrl ? 'image' : 'color');
    const values = {
        minHeight: extractPxNumber(style['min-height'] || style.height),
        padding: extractPxNumber(style.padding),
        margin: extractPxNumber(style.margin),
        radius: extractPxNumber(style['border-radius']),
        background: normalizeColorValue(style.background || style['background-color'] || '#ffffff'),
        mediaMode,
        backgroundImageUrl,
        backgroundVideoUrl,
        videoOptions,
        backgroundSize: style['background-size'] || 'cover',
        backgroundPosition: style['background-position'] || 'center center'
    };

    if (!window.Swal) {
        const minHeight = prompt('Hauteur min en px', values.minHeight || '');
        if (minHeight !== null) applyToolbarStyle(component, { 'min-height': minHeight ? `${Number(minHeight)}px` : '' });
        return;
    }

    Swal.fire({
        title: 'Options section',
        width: 560,
        customClass: {
            popup: 'cms-section-options-modal'
        },
        focusConfirm: false,
        html: `
            <div class="cms-section-options-form">
                <div class="cms-section-options-intro">
                    <span class="cms-section-options-intro-icon"><i class="fas fa-sliders-h"></i></span>
                    <div>
                        <strong>Style rapide de la section</strong>
                        <small>Modifiez les dimensions, les espacements et l'arriere-plan sans quitter l'editeur.</small>
                    </div>
                </div>
                <div class="cms-section-options-grid">
                    <label class="cms-section-option-field">
                        <span class="cms-section-option-label">Hauteur min</span>
                        <span class="cms-section-input-wrap">
                            <input id="swal-section-min-height" type="number" min="0" step="10" value="${values.minHeight}">
                            <span class="cms-section-input-unit">px</span>
                        </span>
                    </label>
                    <label class="cms-section-option-field">
                        <span class="cms-section-option-label">Padding</span>
                        <span class="cms-section-input-wrap">
                            <input id="swal-section-padding" type="number" min="0" step="5" value="${values.padding}">
                            <span class="cms-section-input-unit">px</span>
                        </span>
                    </label>
                    <label class="cms-section-option-field">
                        <span class="cms-section-option-label">Margin</span>
                        <span class="cms-section-input-wrap">
                            <input id="swal-section-margin" type="number" min="0" step="5" value="${values.margin}">
                            <span class="cms-section-input-unit">px</span>
                        </span>
                    </label>
                    <label class="cms-section-option-field">
                        <span class="cms-section-option-label">Radius</span>
                        <span class="cms-section-input-wrap">
                            <input id="swal-section-radius" type="number" min="0" step="2" value="${values.radius}">
                            <span class="cms-section-input-unit">px</span>
                        </span>
                    </label>
                </div>
                <label class="cms-section-color-field">
                    <span class="cms-section-color-meta">
                        <strong>Couleur fond</strong>
                        <span>Background applique a la section</span>
                    </span>
                    <span class="cms-section-color-control">
                        <input id="swal-section-background" type="color" value="${values.background}">
                        <span id="swal-section-background-value" class="cms-section-color-value">${values.background}</span>
                    </span>
                </label>
                <div class="cms-section-media-panel">
                    <div class="cms-section-media-head">
                        <span class="cms-section-media-title"><i class="fas fa-photo-video"></i> Media de fond</span>
                        <span class="cms-section-media-help">Image ou video locale / URL</span>
                    </div>
                    <div class="cms-section-media-tabs">
                        <label class="cms-section-media-tab">
                            <input type="radio" name="swal-section-media-mode" value="color" ${values.mediaMode === 'color' ? 'checked' : ''}>
                            <i class="fas fa-fill-drip"></i> Couleur
                        </label>
                        <label class="cms-section-media-tab">
                            <input type="radio" name="swal-section-media-mode" value="image" ${values.mediaMode === 'image' ? 'checked' : ''}>
                            <i class="fas fa-image"></i> Image
                        </label>
                        <label class="cms-section-media-tab">
                            <input type="radio" name="swal-section-media-mode" value="video" ${values.mediaMode === 'video' ? 'checked' : ''}>
                            <i class="fas fa-video"></i> Video
                        </label>
                    </div>
                    <div id="swal-section-media-fields" class="cms-section-media-fields" data-mode="${values.mediaMode}">
                        <label class="cms-section-option-field is-wide cms-media-image-field">
                            <span class="cms-section-option-label">URL image de fond</span>
                            <span class="cms-section-input-wrap">
                                <input id="swal-section-background-image" type="url" placeholder="https://.../image.jpg ou /storage/..." value="${escapeHtmlAttr(values.backgroundImageUrl)}">
                            </span>
                        </label>
                        <label class="cms-section-option-field is-wide cms-media-video-field">
                            <span class="cms-section-option-label">Video de fond</span>
                            <span class="cms-section-input-wrap">
                                <input id="swal-section-background-video" type="text" placeholder="https://.../video.mp4, lien YouTube ou iframe YouTube" value="${escapeHtmlAttr(values.backgroundVideoUrl)}">
                            </span>
                        </label>
                        <div class="cms-video-options-row">
                            <label class="cms-video-option-toggle">
                                <input id="swal-section-video-autoplay" type="checkbox" ${values.videoOptions.autoplay ? 'checked' : ''}>
                                <span>Autoplay</span>
                            </label>
                            <label class="cms-video-option-toggle">
                                <input id="swal-section-video-muted" type="checkbox" ${values.videoOptions.muted ? 'checked' : ''}>
                                <span>Mute</span>
                            </label>
                            <label class="cms-video-option-toggle">
                                <input id="swal-section-video-loop" type="checkbox" ${values.videoOptions.loop ? 'checked' : ''}>
                                <span>Loop</span>
                            </label>
                            <label class="cms-video-option-toggle">
                                <input id="swal-section-video-controls" type="checkbox" ${values.videoOptions.controls ? 'checked' : ''}>
                                <span>Controls</span>
                            </label>
                            <label class="cms-video-option-toggle">
                                <input id="swal-section-video-playsinline" type="checkbox" ${values.videoOptions.playsinline ? 'checked' : ''}>
                                <span>Plays inline</span>
                            </label>
                        </div>
                        <div class="cms-section-media-row cms-media-layout-row">
                            <label class="cms-section-option-field">
                                <span class="cms-section-option-label">Adaptation</span>
                                <span class="cms-section-input-wrap">
                                    <select id="swal-section-background-size">
                                        <option value="cover" ${values.backgroundSize === 'cover' ? 'selected' : ''}>Cover</option>
                                        <option value="contain" ${values.backgroundSize === 'contain' ? 'selected' : ''}>Contain</option>
                                        <option value="auto" ${values.backgroundSize === 'auto' ? 'selected' : ''}>Auto</option>
                                    </select>
                                </span>
                            </label>
                            <label class="cms-section-option-field">
                                <span class="cms-section-option-label">Position</span>
                                <span class="cms-section-input-wrap">
                                    <select id="swal-section-background-position">
                                        <option value="center center" ${values.backgroundPosition === 'center center' ? 'selected' : ''}>Centre</option>
                                        <option value="top center" ${values.backgroundPosition === 'top center' ? 'selected' : ''}>Haut</option>
                                        <option value="bottom center" ${values.backgroundPosition === 'bottom center' ? 'selected' : ''}>Bas</option>
                                        <option value="center left" ${values.backgroundPosition === 'center left' ? 'selected' : ''}>Gauche</option>
                                        <option value="center right" ${values.backgroundPosition === 'center right' ? 'selected' : ''}>Droite</option>
                                    </select>
                                </span>
                            </label>
                        </div>
                        <div class="cms-section-media-note">
                            Accepte une URL video directe (.mp4, .webm), un lien YouTube ou un code iframe YouTube.
                        </div>
                    </div>
                </div>
            </div>
        `,
        confirmButtonText: 'Appliquer',
        showCancelButton: true,
        cancelButtonText: 'Annuler',
        didOpen: () => {
            const colorInput = document.getElementById('swal-section-background');
            const colorValue = document.getElementById('swal-section-background-value');
            const mediaFields = document.getElementById('swal-section-media-fields');
            colorInput?.addEventListener('input', () => {
                if (colorValue) colorValue.textContent = colorInput.value;
            });
            document.querySelectorAll('input[name="swal-section-media-mode"]').forEach(input => {
                input.addEventListener('change', () => {
                    if (mediaFields) mediaFields.dataset.mode = input.value;
                });
            });

            const autoplayInput = document.getElementById('swal-section-video-autoplay');
            const mutedInput = document.getElementById('swal-section-video-muted');
            const syncAutoplayMute = () => {
                if (!autoplayInput || !mutedInput) return;
                if (autoplayInput.checked) {
                    mutedInput.checked = true;
                    mutedInput.disabled = true;
                } else {
                    mutedInput.disabled = false;
                }
            };
            autoplayInput?.addEventListener('change', syncAutoplayMute);
            syncAutoplayMute();
        },
        preConfirm: () => {
            const popup = Swal.getPopup();
            const valueOf = selector => popup?.querySelector(selector)?.value || '';
            const checkedOf = selector => Boolean(popup?.querySelector(selector)?.checked);

            return {
                minHeight: valueOf('#swal-section-min-height'),
                padding: valueOf('#swal-section-padding'),
                margin: valueOf('#swal-section-margin'),
                radius: valueOf('#swal-section-radius'),
                background: valueOf('#swal-section-background'),
                mediaMode: popup?.querySelector('input[name="swal-section-media-mode"]:checked')?.value || 'color',
                backgroundImageUrl: valueOf('#swal-section-background-image').trim(),
                backgroundVideoUrl: valueOf('#swal-section-background-video').trim(),
                videoOptions: {
                    autoplay: checkedOf('#swal-section-video-autoplay'),
                    muted: checkedOf('#swal-section-video-muted'),
                    loop: checkedOf('#swal-section-video-loop'),
                    controls: checkedOf('#swal-section-video-controls'),
                    playsinline: checkedOf('#swal-section-video-playsinline')
                },
                backgroundSize: valueOf('#swal-section-background-size') || 'cover',
                backgroundPosition: valueOf('#swal-section-background-position') || 'center center'
            };
        }
    }).then(result => {
        if (!result.isConfirmed) return;
        applyToolbarStyle(component, {
            'min-height': result.value.minHeight ? `${Number(result.value.minHeight)}px` : '',
            padding: result.value.padding ? `${Number(result.value.padding)}px` : '',
            margin: result.value.margin ? `${Number(result.value.margin)}px` : '',
            'border-radius': result.value.radius ? `${Number(result.value.radius)}px` : '',
            background: result.value.background || ''
        });
        applyToolbarBackgroundMedia(component, result.value);
    });
}

function extractPxNumber(value) {
    if (!value) return '';
    const match = String(value).match(/-?\d+(\.\d+)?/);
    return match ? match[0] : '';
}

function normalizeColorValue(value) {
    if (!value) return '#ffffff';
    const color = String(value).trim();

    if (/^#[0-9a-f]{6}$/i.test(color)) {
        return color;
    }

    const rgbMatch = color.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)/i);
    if (!rgbMatch) {
        return '#ffffff';
    }

    return '#' + [rgbMatch[1], rgbMatch[2], rgbMatch[3]]
        .map(channel => Number(channel).toString(16).padStart(2, '0'))
        .join('');
}

function extractCssUrl(value) {
    if (!value) return '';
    const match = String(value).match(/url\((['"]?)(.*?)\1\)/i);
    return match ? match[2] : '';
}

function getComponentTagName(component) {
    return String(component?.get?.('tagName') || component?.get?.('type') || '').toLowerCase();
}

function getComponentAttributes(component) {
    return component?.getAttributes ? component.getAttributes() : {};
}

function getComponentBackgroundImageUrl(component) {
    const style = component?.getStyle ? component.getStyle() : {};
    return extractCssUrl(style['background-image'] || style.background || '');
}

function getComponentVideoUrl(component) {
    const tagName = getComponentTagName(component);
    const attributes = getComponentAttributes(component);

    if (attributes['data-cms-original-video']) {
        return attributes['data-cms-original-video'];
    }

    if (tagName === 'iframe') {
        const iframeSrc = attributes.src || '';
        return getYoutubeVideoId(iframeSrc) ? iframeSrc : '';
    }

    if (tagName === 'video') {
        if (attributes.src) return attributes.src;

        const source = typeof component.components === 'function'
            ? component.components().find(child => getComponentTagName(child) === 'source')
            : null;

        return getComponentAttributes(source).src || '';
    }

    if (tagName === 'source') {
        return attributes.src || '';
    }

    return '';
}

function getComponentImageUrl(component) {
    const tagName = getComponentTagName(component);
    const attributes = getComponentAttributes(component);

    if (tagName === 'img' || tagName === 'image') {
        return attributes.src || attributes.href || '';
    }

    return getComponentBackgroundImageUrl(component);
}

function findSectionExistingMedia(component) {
    const detected = { imageUrl: '', videoUrl: '' };
    const visit = item => {
        if (!item || (detected.imageUrl && detected.videoUrl)) return;

        if (!detected.videoUrl) {
            detected.videoUrl = getComponentVideoUrl(item);
        }

        if (!detected.imageUrl) {
            detected.imageUrl = getComponentImageUrl(item);
        }

        if (typeof item.components === 'function') {
            item.components().forEach(visit);
        }
    };

    visit(component);
    return detected;
}

function escapeCssUrl(value) {
    return String(value || '').replace(/\\/g, '\\\\').replace(/"/g, '\\"');
}

function escapeHtmlAttr(value) {
    return String(value || '')
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}

function findSectionBackgroundVideo(component) {
    if (!component || typeof component.components !== 'function') return null;

    let found = null;
    component.components().forEach(child => {
        if (found) return;

        const attributes = child.getAttributes ? child.getAttributes() : {};
        const tagName = child.get ? child.get('tagName') : '';
        const classes = typeof child.getClasses === 'function' ? child.getClasses() : [];

        if (
            attributes['data-cms-bg-video'] === 'true' ||
            attributes['data-cms-bg-iframe'] === 'true' ||
            classes.includes('cms-section-bg-video') ||
            tagName === 'video' ||
            tagName === 'iframe'
        ) {
            found = child;
        }
    });

    return found;
}

function getSectionBackgroundVideoUrl(videoComponent) {
    if (!videoComponent) return '';

    const attributes = videoComponent.getAttributes ? videoComponent.getAttributes() : {};
    if (attributes['data-cms-original-video']) return attributes['data-cms-original-video'];
    if (attributes.src) return attributes.src;

    const childSource = typeof videoComponent.components === 'function'
        ? videoComponent.components().find(child => (child.get ? child.get('tagName') : '') === 'source')
        : null;

    return childSource?.getAttributes?.()?.src || '';
}

function getDefaultSectionVideoOptions() {
    return {
        autoplay: true,
        muted: true,
        loop: true,
        controls: false,
        playsinline: true
    };
}

function normalizeSectionVideoOptions(options = {}) {
    const defaults = getDefaultSectionVideoOptions();
    const normalized = Object.keys(defaults).reduce((normalized, key) => {
        normalized[key] = normalizeBooleanOption(options[key], defaults[key]);
        return normalized;
    }, {});

    if (normalized.autoplay) {
        normalized.muted = true;
    }

    return normalized;
}

function normalizeBooleanOption(value, fallback = false) {
    if (value === undefined || value === null || value === '') return fallback;
    if (typeof value === 'boolean') return value;
    if (typeof value === 'number') return value === 1;

    const normalized = String(value).trim().toLowerCase();
    if (['true', '1', 'yes', 'on', 'checked'].includes(normalized)) return true;
    if (['false', '0', 'no', 'off', 'unchecked'].includes(normalized)) return false;

    return Boolean(value);
}

function getBooleanAttributeOption(attributes, key, fallback) {
    if (!attributes || attributes[key] === undefined) return fallback;
    return !['false', '0', 'off', 'no'].includes(String(attributes[key]).toLowerCase());
}

function getSectionBackgroundVideoOptions(videoComponent) {
    const defaults = getDefaultSectionVideoOptions();
    if (!videoComponent) return defaults;

    const attributes = videoComponent.getAttributes ? videoComponent.getAttributes() : {};
    if (attributes['data-cms-video-options']) {
        try {
            return normalizeSectionVideoOptions(JSON.parse(attributes['data-cms-video-options']));
        } catch (error) {
            // Fallback to attribute parsing below.
        }
    }

    const src = attributes.src || attributes['data-cms-original-video'] || '';
    if (src) {
        try {
            const url = new URL(src, window.location.origin);
            if (getYoutubeVideoId(src)) {
                return normalizeSectionVideoOptions({
                    autoplay: url.searchParams.get('autoplay') !== '0',
                    muted: url.searchParams.get('mute') !== '0',
                    loop: url.searchParams.get('loop') !== '0',
                    controls: url.searchParams.get('controls') === '1',
                    playsinline: url.searchParams.get('playsinline') !== '0'
                });
            }
        } catch (error) {
            // Continue with direct video attributes.
        }
    }

    return normalizeSectionVideoOptions({
        autoplay: getBooleanAttributeOption(attributes, 'autoplay', defaults.autoplay),
        muted: getBooleanAttributeOption(attributes, 'muted', defaults.muted),
        loop: getBooleanAttributeOption(attributes, 'loop', defaults.loop),
        controls: getBooleanAttributeOption(attributes, 'controls', defaults.controls),
        playsinline: getBooleanAttributeOption(attributes, 'playsinline', defaults.playsinline)
    });
}

function removeSectionBackgroundVideo(component) {
    const videoComponent = findSectionBackgroundVideo(component);
    if (videoComponent) {
        videoComponent.remove();
    }
}

function ensureSectionContentAboveBackground(component) {
    if (!component || typeof component.components !== 'function') return;

    component.components().forEach(child => {
        const attributes = child.getAttributes ? child.getAttributes() : {};
        if (attributes['data-cms-bg-video'] === 'true') return;
        if (attributes['data-cms-bg-iframe'] === 'true') return;

        const style = child.getStyle ? child.getStyle() : {};
        child.setStyle({
            ...style,
            position: style.position || 'relative',
            'z-index': style['z-index'] || '1'
        });
    });
}

function extractIframeSrc(value) {
    const input = String(value || '').trim();
    if (!input) return '';

    const srcMatch = input.match(/<iframe\b[^>]*\ssrc=(["'])(.*?)\1/i);
    return srcMatch ? srcMatch[2] : '';
}

function getYoutubeVideoId(value) {
    const raw = String(value || '').trim();
    if (!raw) return '';

    try {
        const url = new URL(raw, window.location.origin);
        const host = url.hostname.replace(/^www\./, '').toLowerCase();

        if (host === 'youtu.be') {
            return url.pathname.split('/').filter(Boolean)[0] || '';
        }

        if (host.endsWith('youtube.com') || host.endsWith('youtube-nocookie.com')) {
            if (url.pathname === '/watch') {
                return url.searchParams.get('v') || '';
            }

            const parts = url.pathname.split('/').filter(Boolean);
            const embedIndex = parts.findIndex(part => ['embed', 'shorts', 'live'].includes(part));
            if (embedIndex !== -1 && parts[embedIndex + 1]) {
                return parts[embedIndex + 1];
            }
        }
    } catch (error) {
        const fallback = raw.match(/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/|live\/)|youtu\.be\/)([A-Za-z0-9_-]{6,})/i);
        return fallback ? fallback[1] : '';
    }

    return '';
}

function buildYoutubeEmbedUrl(input, options = {}) {
    const videoId = getYoutubeVideoId(input);
    if (!videoId) return '';

    const videoOptions = normalizeSectionVideoOptions(options);
    const params = new URLSearchParams({
        autoplay: videoOptions.autoplay ? '1' : '0',
        mute: videoOptions.muted ? '1' : '0',
        loop: videoOptions.loop ? '1' : '0',
        controls: videoOptions.controls ? '1' : '0',
        playsinline: videoOptions.playsinline ? '1' : '0',
        rel: '0',
        modestbranding: '1'
    });

    if (videoOptions.loop) {
        params.set('playlist', videoId);
    }

    return `https://www.youtube.com/embed/${videoId}?${params.toString()}`;
}

function isDirectVideoUrl(value) {
    const input = String(value || '').trim().split('?')[0].toLowerCase();
    return /\.(mp4|webm|ogg|ogv|mov|m4v)$/.test(input) || input.startsWith('blob:') || input.startsWith('data:video/');
}

function resolveSectionBackgroundMedia(value, videoOptions = {}) {
    const input = String(value || '').trim();
    if (!input) return { type: '', src: '', original: '' };

    const iframeSrc = extractIframeSrc(input);
    const source = iframeSrc || input;
    const youtubeSrc = buildYoutubeEmbedUrl(source, videoOptions);

    if (youtubeSrc) {
        return { type: 'iframe', src: youtubeSrc, original: source };
    }

    if (isDirectVideoUrl(source)) {
        return { type: 'video', src: source, original: source };
    }

    return { type: 'iframe', src: source, original: source };
}

function upsertSectionBackgroundVideo(component, url, fit = 'cover', position = 'center center', options = {}) {
    if (!component || !url) return;

    const style = component.getStyle ? component.getStyle() : {};
    component.setStyle({
        ...style,
        position: style.position || 'relative',
        overflow: 'hidden'
    });

    const videoOptions = normalizeSectionVideoOptions(options);
    const media = resolveSectionBackgroundMedia(url, videoOptions);
    if (!media.src) return;

    let videoComponent = findSectionBackgroundVideo(component);
    const currentTagName = videoComponent?.get ? videoComponent.get('tagName') : '';
    const targetTagName = media.type === 'iframe' ? 'iframe' : 'video';

    if (videoComponent && currentTagName !== targetTagName) {
        videoComponent.remove();
        videoComponent = null;
    }

    const baseStyle = {
        border: '0',
        height: '100%',
        left: '0',
        'pointer-events': 'none',
        position: 'absolute',
        top: '0',
        width: '100%',
        'z-index': '0'
    };
    const iframeCoverStyle = fit === 'cover'
        ? {
            height: 'max(56.25vw, 100vh)',
            left: '50%',
            'min-height': '100%',
            'min-width': '100%',
            top: '50%',
            transform: 'translate(-50%, -50%)',
            width: 'max(100vw, 177.7778vh)'
        }
        : {
            height: '100%',
            left: '0',
            top: '0',
            transform: '',
            width: '100%'
        };
    const videoStyle = media.type === 'video'
        ? {
            ...baseStyle,
            'object-fit': fit || 'cover',
            'object-position': position || 'center center'
        }
        : {
            ...baseStyle,
            ...iframeCoverStyle
        };
    const videoAttributes = media.type === 'video'
        ? Object.entries({
            'data-cms-bg-video': 'true',
            'data-cms-video-options': JSON.stringify(videoOptions),
            'data-cms-original-video': media.original,
            class: 'cms-section-bg-video',
            src: media.src,
            autoplay: videoOptions.autoplay ? 'autoplay' : null,
            muted: videoOptions.muted ? 'muted' : null,
            loop: videoOptions.loop ? 'loop' : null,
            controls: videoOptions.controls ? 'controls' : null,
            playsinline: videoOptions.playsinline ? 'playsinline' : null
        }).reduce((attrs, [key, value]) => {
            if (value !== null && value !== undefined && value !== false) attrs[key] = value;
            return attrs;
        }, {})
        : {
            'data-cms-bg-iframe': 'true',
            'data-cms-video-options': JSON.stringify(videoOptions),
            'data-cms-original-video': media.original,
            allow: 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share; fullscreen',
            allowfullscreen: 'allowfullscreen',
            class: 'cms-section-bg-iframe cms-section-bg-video',
            frameborder: '0',
            loading: videoOptions.autoplay ? 'eager' : 'lazy',
            referrerpolicy: 'strict-origin-when-cross-origin',
            src: media.src
        };

    if (videoComponent) {
        videoComponent.setAttributes(videoAttributes);
        videoComponent.setStyle(videoStyle);
    } else {
        const added = component.components().add({
            tagName: targetTagName,
            attributes: videoAttributes,
            style: videoStyle
        }, { at: 0 });
        videoComponent = added;
    }

    ensureSectionContentAboveBackground(component);
}

function applyToolbarBackgroundMedia(component, options) {
    if (!component || !options) return;

    const mode = options.mediaMode || 'color';
    const baseBackground = options.background || '';

    if (mode === 'image') {
        removeSectionBackgroundVideo(component);

        if (!options.backgroundImageUrl) {
            applyToolbarStyle(component, {
                background: baseBackground,
                'background-image': '',
                'background-size': '',
                'background-position': '',
                'background-repeat': ''
            });
            return;
        }

        applyToolbarStyle(component, {
            background: baseBackground,
            'background-image': `url("${escapeCssUrl(options.backgroundImageUrl)}")`,
            'background-size': options.backgroundSize || 'cover',
            'background-position': options.backgroundPosition || 'center center',
            'background-repeat': 'no-repeat'
        });
        return;
    }

    if (mode === 'video') {
        applyToolbarStyle(component, {
            background: baseBackground,
            'background-image': '',
            'background-size': '',
            'background-position': '',
            'background-repeat': ''
        });

        if (options.backgroundVideoUrl) {
            upsertSectionBackgroundVideo(
                component,
                options.backgroundVideoUrl,
                options.backgroundSize || 'cover',
                options.backgroundPosition || 'center center',
                options.videoOptions || {}
            );
        } else {
            removeSectionBackgroundVideo(component);
        }
        return;
    }

    removeSectionBackgroundVideo(component);
    applyToolbarStyle(component, {
        background: baseBackground,
        'background-image': '',
        'background-size': '',
        'background-position': '',
        'background-repeat': ''
    });
}

function resetToolbarSectionStyles(component) {
    if (!component) return;

    const style = component.getStyle ? component.getStyle() : {};
    ['width', 'height', 'min-height', 'max-width', 'margin', 'margin-left', 'margin-right', 'padding', 'border-radius', 'background', 'background-color', 'background-image', 'background-size', 'background-position', 'background-repeat'].forEach(property => {
        delete style[property];
    });

    removeSectionBackgroundVideo(component);
    component.setStyle(style);
    refreshEditorTools();
    positionManualResizeHandles();
}

function insertToolbarSectionAfter(component) {
    const collection = component.collection;
    if (!collection) return;

    const index = collection.indexOf(component);
    const added = collection.add({
        tagName: 'section',
        attributes: { class: 'cms-editor-section' },
        style: {
            padding: '60px 20px',
            'min-height': '160px',
            background: '#ffffff'
        },
        components: '<div style="max-width:1140px;margin:0 auto;min-height:60px;"></div>'
    }, { at: index + 1 });

    enableManualResizeForTree([added]);
    applyGjsToolbarOptions(added);
    editor.select(added);
    scrollCanvasToEnd();
}

function duplicateToolbarComponent(component) {
    const collection = component.collection;
    if (!collection) return;

    const index = collection.indexOf(component);
    const clone = component.clone();
    const added = collection.add(clone, { at: index + 1 });
    enableManualResizeForTree([added]);
    applyGjsToolbarOptions(added);
    editor.select(added);
}

function moveToolbarComponent(component, direction) {
    const collection = component.collection;
    if (!collection) return;

    const index = collection.indexOf(component);
    const nextIndex = index + direction;

    if (nextIndex < 0 || nextIndex >= collection.length) {
        return;
    }

    const componentData = component.toJSON();
    component.remove();

    const movedComponent = collection.add(componentData, { at: nextIndex });
    enableManualResizeForTree([movedComponent]);
    applyGjsToolbarOptions(movedComponent);
    editor.select(movedComponent);
    updateLayersPanel();
}

function registerEnhancedFormBlocks() {
    if (!editor || !editor.BlockManager) {
        return;
    }

    const formCategory = 'Forms';
    const media = icon => `<i class="fas ${icon} form-block-icon"></i>`;
    const baseFieldStyle = 'width:100%;border:1px solid #cbd5e1;border-radius:8px;padding:12px 14px;font-size:14px;color:#0f172a;background:#fff;outline:none;';
    const baseLabelStyle = 'display:block;margin-bottom:8px;font-size:13px;font-weight:700;color:#334155;';

    const blocks = [
        {
            id: 'cms-form-contact-pro',
            label: 'Contact pro',
            media: media('fa-address-card'),
            content: `
                <form class="cms-contact-form" style="max-width:680px;margin:0 auto;padding:28px;background:#fff;border:1px solid #e2e8f0;border-radius:14px;box-shadow:0 18px 45px rgba(15,23,42,.08);">
                    <h3 style="margin:0 0 8px;font-size:24px;color:#0f172a;">Contactez-nous</h3>
                    <p style="margin:0 0 22px;color:#64748b;">Envoyez votre demande, nous vous repondrons rapidement.</p>
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;margin-bottom:16px;">
                        <label style="${baseLabelStyle}">Nom complet<input type="text" name="name" placeholder="Votre nom" style="${baseFieldStyle}margin-top:8px;"></label>
                        <label style="${baseLabelStyle}">Email<input type="email" name="email" placeholder="nom@email.com" style="${baseFieldStyle}margin-top:8px;"></label>
                    </div>
                    <label style="${baseLabelStyle}">Sujet<input type="text" name="subject" placeholder="Sujet de votre message" style="${baseFieldStyle}margin-top:8px;margin-bottom:16px;"></label>
                    <label style="${baseLabelStyle}">Message<textarea name="message" rows="5" placeholder="Votre message" style="${baseFieldStyle}margin-top:8px;resize:vertical;"></textarea></label>
                    <button type="submit" style="margin-top:18px;border:0;border-radius:9px;background:#2563eb;color:#fff;font-weight:800;padding:13px 22px;cursor:pointer;">Envoyer le message</button>
                </form>
            `
        },
        {
            id: 'cms-form-newsletter',
            label: 'Newsletter',
            media: media('fa-envelope-open-text'),
            content: `
                <form class="cms-newsletter-form" style="display:flex;gap:10px;max-width:560px;margin:0 auto;padding:18px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;">
                    <input type="email" name="newsletter_email" placeholder="Votre adresse email" style="flex:1;min-width:0;border:1px solid #cbd5e1;border-radius:9px;padding:13px 14px;font-size:14px;">
                    <button type="submit" style="border:0;border-radius:9px;background:#0f172a;color:#fff;font-weight:800;padding:0 18px;cursor:pointer;">S'inscrire</button>
                </form>
            `
        },
        {
            id: 'cms-form-appointment',
            label: 'Rendez-vous',
            media: media('fa-calendar-check'),
            content: `
                <form class="cms-appointment-form" style="max-width:720px;margin:0 auto;padding:26px;background:#fff;border:1px solid #e2e8f0;border-radius:14px;box-shadow:0 18px 45px rgba(15,23,42,.08);">
                    <h3 style="margin:0 0 18px;font-size:22px;color:#0f172a;">Demande de rendez-vous</h3>
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:16px;">
                        <label style="${baseLabelStyle}">Nom<input type="text" name="name" style="${baseFieldStyle}margin-top:8px;"></label>
                        <label style="${baseLabelStyle}">Telephone<input type="tel" name="phone" style="${baseFieldStyle}margin-top:8px;"></label>
                        <label style="${baseLabelStyle}">Date souhaitee<input type="date" name="appointment_date" style="${baseFieldStyle}margin-top:8px;"></label>
                        <label style="${baseLabelStyle}">Heure souhaitee<input type="time" name="appointment_time" style="${baseFieldStyle}margin-top:8px;"></label>
                    </div>
                    <button type="submit" style="margin-top:18px;border:0;border-radius:9px;background:#16a34a;color:#fff;font-weight:800;padding:13px 22px;cursor:pointer;">Demander un rendez-vous</button>
                </form>
            `
        },
        {
            id: 'cms-form-search',
            label: 'Recherche',
            media: media('fa-magnifying-glass'),
            content: `
                <form class="cms-search-form" style="display:flex;gap:8px;width:100%;max-width:620px;margin:0 auto;">
                    <input type="search" name="search" placeholder="Rechercher..." style="flex:1;min-width:0;border:1px solid #cbd5e1;border-radius:999px;padding:13px 18px;font-size:14px;">
                    <button type="submit" style="border:0;border-radius:999px;background:#2563eb;color:#fff;font-weight:800;padding:0 20px;cursor:pointer;">Rechercher</button>
                </form>
            `
        },
        {
            id: 'cms-form-address',
            label: 'Adresse',
            media: media('fa-location-dot'),
            content: `
                <div style="display:grid;grid-template-columns:1fr;gap:14px;">
                    <label style="${baseLabelStyle}">Adresse<input type="text" name="address" placeholder="Numero et rue" style="${baseFieldStyle}margin-top:8px;"></label>
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:14px;">
                        <label style="${baseLabelStyle}">Ville<input type="text" name="city" style="${baseFieldStyle}margin-top:8px;"></label>
                        <label style="${baseLabelStyle}">Code postal<input type="text" name="postal_code" style="${baseFieldStyle}margin-top:8px;"></label>
                    </div>
                </div>
            `
        },
        {
            id: 'cms-form-field-text',
            label: 'Champ texte',
            media: media('fa-font'),
            content: `<label style="${baseLabelStyle}">Texte<input type="text" name="text" placeholder="Saisir un texte" style="${baseFieldStyle}margin-top:8px;"></label>`
        },
        {
            id: 'cms-form-field-email',
            label: 'Email',
            media: media('fa-at'),
            content: `<label style="${baseLabelStyle}">Email<input type="email" name="email" placeholder="nom@email.com" style="${baseFieldStyle}margin-top:8px;"></label>`
        },
        {
            id: 'cms-form-field-phone',
            label: 'Telephone',
            media: media('fa-phone'),
            content: `<label style="${baseLabelStyle}">Telephone<input type="tel" name="phone" placeholder="+1 000 000 0000" style="${baseFieldStyle}margin-top:8px;"></label>`
        },
        {
            id: 'cms-form-field-textarea',
            label: 'Message',
            media: media('fa-align-left'),
            content: `<label style="${baseLabelStyle}">Message<textarea name="message" rows="5" placeholder="Votre message" style="${baseFieldStyle}margin-top:8px;resize:vertical;"></textarea></label>`
        },
        {
            id: 'cms-form-field-select',
            label: 'Liste choix',
            media: media('fa-list-ul'),
            content: `
                <label style="${baseLabelStyle}">Choisir une option
                    <select name="option" style="${baseFieldStyle}margin-top:8px;">
                        <option>Option 1</option>
                        <option>Option 2</option>
                        <option>Option 3</option>
                    </select>
                </label>
            `
        },
        {
            id: 'cms-form-field-checkbox',
            label: 'Case a cocher',
            media: media('fa-square-check'),
            content: `
                <label style="display:flex;align-items:flex-start;gap:10px;color:#334155;font-size:14px;line-height:1.5;">
                    <input type="checkbox" name="agreement" style="margin-top:3px;">
                    <span>J'accepte les conditions et la politique de confidentialite.</span>
                </label>
            `
        },
        {
            id: 'cms-form-field-radio',
            label: 'Choix radio',
            media: media('fa-circle-dot'),
            content: `
                <fieldset style="border:1px solid #e2e8f0;border-radius:10px;padding:14px 16px;">
                    <legend style="font-size:13px;font-weight:800;color:#334155;padding:0 6px;">Choisissez une option</legend>
                    <label style="display:flex;gap:10px;margin:10px 0;color:#334155;"><input type="radio" name="radio_option" value="1"> Option 1</label>
                    <label style="display:flex;gap:10px;margin:10px 0;color:#334155;"><input type="radio" name="radio_option" value="2"> Option 2</label>
                </fieldset>
            `
        },
        {
            id: 'cms-form-field-file',
            label: 'Fichier',
            media: media('fa-paperclip'),
            content: `<label style="${baseLabelStyle}">Ajouter un fichier<input type="file" name="attachment" style="${baseFieldStyle}margin-top:8px;padding:10px;background:#f8fafc;"></label>`
        },
        {
            id: 'cms-form-field-date',
            label: 'Date',
            media: media('fa-calendar-days'),
            content: `<label style="${baseLabelStyle}">Date<input type="date" name="date" style="${baseFieldStyle}margin-top:8px;"></label>`
        },
        {
            id: 'cms-form-submit-button',
            label: 'Bouton submit',
            media: media('fa-paper-plane'),
            content: `<button type="submit" style="border:0;border-radius:9px;background:#2563eb;color:#fff;font-weight:800;padding:13px 22px;cursor:pointer;">Envoyer</button>`
        },
        {
            id: 'cms-form-two-columns',
            label: '2 colonnes',
            media: media('fa-table-columns'),
            content: `
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;">
                    <label style="${baseLabelStyle}">Prenom<input type="text" name="first_name" style="${baseFieldStyle}margin-top:8px;"></label>
                    <label style="${baseLabelStyle}">Nom<input type="text" name="last_name" style="${baseFieldStyle}margin-top:8px;"></label>
                </div>
            `
        }
    ];

    blocks.forEach(block => {
        if (!editor.BlockManager.get(block.id)) {
            editor.BlockManager.add(block.id, {
                category: formCategory,
                label: block.label,
                media: block.media,
                content: block.content
            });
        }
    });
}

function handleEditorBootError(error) {
    console.error('Erreur d initialisation de l editeur:', error);
    document.body.classList.remove('editor-booting');
    document.body.classList.add('editor-ready');
    hideLoading(true);
    showNotification('Erreur d initialisation de l editeur: ' + error.message, 'error');
}

// === FONCTIONS DE GESTION DES PAGES ===
async function loadPageOnStart(pageId) {
    try {
        console.log('Chargement de la page avec ID:', pageId);
        showLoading('Chargement de la page...');
        
        // Correction de l'URL - utiliser le bon endpoint
        const response = await fetch(`/admin/cms/api/pages/${pageId}/load`);
        
        if (!response.ok) {
            throw new Error(`Erreur HTTP ! statut: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('Réponse API Page:', data);
        
        // Vérifier la structure de la réponse
        if (data.success) {
            // Extraire les données selon la structure retournée
            const pageData = data.data;
            const htmlContent = pageData.html_content || pageData.content || '';
            const cssContent = pageData.css_content || '';
            const title = pageData.title || '';
            
            console.log('Longueur du contenu HTML:', htmlContent.length);
            console.log('Longueur du contenu CSS:', cssContent.length);
            
            // Mettre à jour le titre dans le champ
            const titleInput = document.getElementById('title');
            if (titleInput && title) {
                titleInput.value = title;
            }
            
            // Mettre à jour le slug
            const slugInput = document.getElementById('slug');
            if (slugInput && pageData.slug) {
                slugInput.value = pageData.slug;
            }
            
            // Mettre à jour le statut
            const statusSelect = document.getElementById('status');
            if (statusSelect && pageData.status) {
                statusSelect.value = pageData.status;
            }
            
            // Mettre à jour la visibilité
            const visibilitySelect = document.getElementById('visibility');
            if (visibilitySelect && pageData.visibility) {
                visibilitySelect.value = pageData.visibility;
                // Afficher/masquer le champ mot de passe
                const passwordField = document.getElementById('passwordField');
                if (passwordField) {
                    passwordField.style.display = pageData.visibility === 'password' ? 'block' : 'none';
                }
            }
            
            // Mettre à jour la case "page d'accueil"
            const isHomeCheckbox = document.getElementById('is_home');
            if (isHomeCheckbox && pageData.is_home) {
                isHomeCheckbox.checked = pageData.is_home;
            }
            
            // Nettoyer et charger le contenu HTML/CSS
            if (htmlContent && htmlContent.trim()) {
                let cleanHtml = htmlContent;
                let cleanCss = cssContent;
                
                // Nettoyer les éventuels caractères d'échappement
                if (typeof cleanHtml === 'string') {
                    cleanHtml = cleanHtml
                        .replace(/\\r\\n/g, '\n')
                        .replace(/\\n/g, '\n')
                        .replace(/\\t/g, '\t')
                        .replace(/\\"/g, '"')
                        .replace(/\\'/g, "'")
                        .replace(/\\\\/g, '\\');
                }
                
                if (typeof cleanCss === 'string') {
                    cleanCss = cleanCss
                        .replace(/\\r\\n/g, '\n')
                        .replace(/\\n/g, '\n')
                        .replace(/\\t/g, '\t')
                        .replace(/\\"/g, '"')
                        .replace(/\\'/g, "'")
                        .replace(/\\\\/g, '\\');
                }
                
                console.log('Définition des composants dans l\'éditeur...');
                
                // Vider l'éditeur avant de charger le nouveau contenu
                editor.setComponents('');
                
                const contentParts = splitHtmlAndScripts(cleanHtml);
                cleanHtml = contentParts.html;

                // Charger le contenu avec ou sans CSS
                if (cleanCss && cleanCss.trim()) {
                    editor.setComponents(cleanHtml + '<style>' + cleanCss + '</style>');
                    editor.setStyle(cleanCss);
                } else {
                    editor.setComponents(cleanHtml);
                }

                attachSavedScriptsToEditor(contentParts.js);

            } else {
                console.log('Le HTML de la page est vide, canevas vide conserve');
                editor.setComponents('');
                editor.setStyle('');
            }
            
            window.currentPageId = pageId;
            
            if (title) {
                document.title = `${title} - Éditeur CMS`;
            }
            
            enableManualResizeForTree(editor.DomComponents?.getComponents?.());
            updateLayersPanel();
            
        } else {
            throw new Error(data.message || 'Échec du chargement de la page: Réponse invalide');
        }
    } catch (error) {
        console.error('Erreur de chargement de la page:', error);
        showNotification('Erreur de chargement de la page: ' + error.message, 'error');
        
        editor.setComponents('');
        editor.setStyle('');
    } finally {
        hideLoading();
    }
}

async function savePage() {
    try {
        console.log('ID de la page actuel avant sauvegarde:', window.currentPageId);
        
        if (!window.currentPageId) {
            window.currentPageId = getPageIdFromURL();
            console.log('ID de la page récupéré:', window.currentPageId);
            
            if (!window.currentPageId) {
                showNotification('Aucun ID de page trouvé. Impossible de sauvegarder.', 'error');
                return;
            }
        }
        
        showLoading('Sauvegarde de la page...');
        
        const htmlContent = editor.getHtml();
        const cssContent = editor.getCss();
        const jsContent = editor.getJs ? editor.getJs() : '';
        const title = document.getElementById('title') ? document.getElementById('title').value : '';
        const slug = document.getElementById('slug') ? document.getElementById('slug').value : '';
        const status = document.getElementById('status') ? document.getElementById('status').value : 'draft';
        const visibility = document.getElementById('visibility') ? document.getElementById('visibility').value : 'public';
        const isHome = document.getElementById('is_home') ? document.getElementById('is_home').checked : false;
        const password = document.getElementById('password') ? document.getElementById('password').value : '';
        
        console.log('Sauvegarde de la page ID:', window.currentPageId);
        console.log('Titre:', title);
        console.log('Statut:', status);
        
        // Construire le contenu complet
        const fullContent = [
            cssContent && cssContent.trim() ? `<style>${cssContent}</style>` : '',
            htmlContent,
            jsContent && jsContent.trim() ? buildBlockScriptTag(jsContent) : ''
        ].join('');
        
        const formData = {
            title: title,
            slug: slug,
            status: status,
            visibility: visibility,
            is_home: isHome,
            content: fullContent,
            html_content: htmlContent,
            css_content: cssContent,
            password: password
        };
        
        const response = await fetch(`/admin/cms/api/pages/${window.currentPageId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: JSON.stringify(formData)
        });
        
        const data = await response.json();
        hideLoading();
        
        if (data.success) {
            showNotification('Page sauvegardée avec succès !', 'success');
        } else {
            throw new Error(data.message || 'Échec de la sauvegarde de la page');
        }
    } catch (error) {
        console.error('Erreur de sauvegarde de la page:', error);
        hideLoading();
        showNotification('Erreur de sauvegarde: ' + error.message, 'error');
    }
}

function getPageIdFromURL() {
    const url = window.location.pathname;
    console.log('Récupération de l\'ID de la page depuis l\'URL:', url);
    
    // Pattern pour /admin/cms/11/pages/5/edit-content
    const pattern = /\/admin\/cms\/\d+\/pages\/(\d+)\/edit-content/;
    const match = url.match(pattern);
    
    if (match && match[1]) {
        console.log('ID de la page trouvé:', match[1]);
        return parseInt(match[1]);
    }
    
    // Pattern alternatif pour /pages/{id}/edit
    const altPattern = /\/pages\/(\d+)\/edit/;
    const altMatch = url.match(altPattern);
    
    if (altMatch && altMatch[1]) {
        console.log('ID de la page trouvé (format alternatif):', altMatch[1]);
        return parseInt(altMatch[1]);
    }
    
    // Vérifier les paramètres URL
    const urlParams = new URLSearchParams(window.location.search);
    const pageIdParam = urlParams.get('page_id');
    if (pageIdParam) {
        console.log('ID de la page trouvé dans les paramètres:', pageIdParam);
        return parseInt(pageIdParam);
    }
    
    console.log('Aucun ID de page trouvé dans l\'URL');
    return null;
}

// === FONCTIONS POUR LE DÉFILEMENT DES CATÉGORIES ===
function scrollCategories(amount) {
    const scrollContainer = document.getElementById('categoriesScroll');
    if (!scrollContainer) return;
    
    scrollContainer.scrollBy({
        left: amount,
        behavior: 'smooth'
    });
    
    setTimeout(updateCategoryNavButtons, 300);
}

function updateCategoryNavButtons() {
    const scrollContainer = document.getElementById('categoriesScroll');
    if (!scrollContainer) return;
    
    const leftBtn = document.querySelector('.categories-nav-btn.left');
    const rightBtn = document.querySelector('.categories-nav-btn.right');
    
    if (leftBtn && rightBtn) {
        if (scrollContainer.scrollLeft <= 10) {
            leftBtn.disabled = true;
            leftBtn.style.opacity = '0.4';
        } else {
            leftBtn.disabled = false;
            leftBtn.style.opacity = '1';
        }
        
        if (scrollContainer.scrollLeft + scrollContainer.clientWidth >= scrollContainer.scrollWidth - 10) {
            rightBtn.disabled = true;
            rightBtn.style.opacity = '0.4';
        } else {
            rightBtn.disabled = false;
            rightBtn.style.opacity = '1';
        }
    }
}

// === FONCTIONS POUR L'INTERFACE MODERNE ===
async function loadBlocksModern(pageId) {
    try {
        showLoading('Chargement de la bibliothèque de blocs...');
        
        console.log('Récupération des blocs depuis l\'API avec pageId:', pageId);
        
        let apiUrl = '/api/blocks/data';
        
        if (pageId) {
            apiUrl += '?page_id=' + pageId;
        }
        
        console.log('Récupération des blocs depuis:', apiUrl);
        
        const response = await fetch(apiUrl);
        
        if (!response.ok) {
            throw new Error(`Erreur API: ${response.status}`);
        }
        
        const responseText = await response.text();
        
        if (responseText.trim().startsWith('<!DOCTYPE') || 
            responseText.trim().startsWith('<!--') || 
            responseText.includes('<html')) {
            console.error('Le serveur a retourné du HTML au lieu du JSON:', responseText.substring(0, 200));
            throw new Error('L\'API a retourné du HTML au lieu du JSON. Vérifiez vos routes.');
        }
        
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (parseError) {
            console.error('Erreur d\'analyse JSON:', parseError);
            console.error('Texte de réponse:', responseText.substring(0, 500));
            throw new Error('Réponse JSON invalide du serveur');
        }
        
        console.log('Données de réponse API:', data);
        
        if (data.success) {
            allBlocks = data.blocks || [];
            allSections = data.sections || [];
            
            console.log(`${allBlocks.length} blocs et ${allSections.length} sections chargés`);
            
            updateStats(allBlocks);
            renderCategories(allSections, allBlocks);
            renderBlocksModern(allBlocks);
            initModernFilters();
            
            hideLoading();
            showNotification(`${allBlocks.length} blocs chargés`, 'success');
            
            setTimeout(updateCategoryNavButtons, 500);
            
        } else {
            throw new Error(data.message || 'Échec du chargement des blocs');
        }
    } catch (error) {
        console.error('Erreur de chargement des blocs:', error);
        hideLoading();
        showNotification('Erreur de chargement des blocs: ' + error.message, 'error');
        renderEmptyState();
    }
}

function updateStats(blocks) {
    const total = blocks.length;
    const free = blocks.filter(b => b.is_free).length;
    const pro = total - free;
    
    const blocksCount = document.getElementById('blocksCount');
    const freeCount = document.getElementById('freeCount');
    const proCount = document.getElementById('proCount');
    
    if (blocksCount) blocksCount.textContent = total;
    if (freeCount) freeCount.textContent = free;
    if (proCount) proCount.textContent = pro;
}

function renderCategories(sections, blocks) {
    const container = document.getElementById('categoriesScroll');
    if (!container) return;
    
    container.innerHTML = '';
    
    const allCount = blocks.length;
    const allButton = createCategoryTab('all', 'Tous les Blocs', 'fa-layer-group', allCount, true);
    container.appendChild(allButton);
    
    sections.forEach(section => {
        const sectionBlocks = blocks.filter(b => b.section_id === section.id);
        if (sectionBlocks.length > 0) {
            const button = createCategoryTab(
                section.slug,
                section.name,
                section.icon || 'fa-folder',
                sectionBlocks.length,
                false
            );
            container.appendChild(button);
        }
    });
    
    const websiteTypes = [...new Set(blocks.map(b => b.website_type))];
    websiteTypes.forEach(type => {
        const typeBlocks = blocks.filter(b => b.website_type === type);
        if (typeBlocks.length > 0 && type !== 'General') {
            const icon = getWebsiteTypeIcon(type);
            const button = createCategoryTab(
                `type-${type.toLowerCase()}`,
                type,
                icon,
                typeBlocks.length,
                false
            );
            container.appendChild(button);
        }
    });
    
    initCategoryEvents();
}

function createCategoryTab(id, name, icon, count, isActive) {
    const button = document.createElement('button');
    button.className = `category-tab ${isActive ? 'active' : ''}`;
    button.dataset.category = id;
    button.innerHTML = `
        <i class="fas ${icon}"></i>
        <span>${name}</span>
        <span class="category-count">${count}</span>
    `;
    return button;
}

function getWebsiteTypeIcon(type) {
    const icons = {
        'SaaS': 'fa-cloud',
        'Ecommerce': 'fa-shopping-cart',
        'Portfolio': 'fa-briefcase',
        'Restaurant': 'fa-utensils',
        'Blog': 'fa-blog',
        'Corporate': 'fa-building',
        'Landing': 'fa-flag',
        'Dashboard': 'fa-chart-line',
        'Editor': 'fa-edit',
        'General': 'fa-globe'
    };
    return icons[type] || 'fa-globe';
}

function initCategoryEvents() {
    document.querySelectorAll('.category-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.category-tab').forEach(t => {
                t.classList.remove('active');
            });
            
            tab.classList.add('active');
            
            const category = tab.dataset.category;
            filterBlocksByCategory(category);
        });
    });
}

function filterBlocksByCategory(category) {
    const blocksGrid = document.getElementById('blocksContainer');
    if (!blocksGrid) return;
    
    const allBlockCards = document.querySelectorAll('.block-card-modern');
    
    allBlockCards.forEach(card => {
        if (category === 'all') {
            card.style.display = 'block';
        } else if (category.startsWith('type-')) {
            const type = category.replace('type-', '');
            const blockType = card.dataset.websiteType || '';
            card.style.display = blockType.toLowerCase() === type.toLowerCase() ? 'block' : 'none';
        } else {
            const blockSection = card.dataset.section || '';
            card.style.display = blockSection === category ? 'block' : 'none';
        }
        
        card.style.opacity = '0';
        card.style.transform = 'translateY(10px)';
        
        setTimeout(() => {
            if (card.style.display !== 'none') {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }
        }, 10);
    });
    
    const visibleBlocks = Array.from(allBlockCards).filter(b => b.style.display !== 'none');
    const emptyState = document.getElementById('blocksEmptyState');
    
    if (visibleBlocks.length === 0 && emptyState) {
        emptyState.style.display = 'block';
        blocksGrid.style.display = 'none';
    } else {
        if (emptyState) emptyState.style.display = 'none';
        blocksGrid.style.display = 'grid';
    }
}

function renderBlocksModern(blocks) {
    const container = document.getElementById('blocksContainer');
    if (!container) return;
    
    container.innerHTML = '';
    
    if (!blocks || blocks.length === 0) {
        renderEmptyState();
        return;
    }
    
    const sortedBlocks = [...blocks].sort((a, b) => Number(b.id || 0) - Number(a.id || 0));
    
    sortedBlocks.forEach((block, index) => {
        const card = createBlockCardModern(block, index);
        container.appendChild(card);
    });
}

function createBlockCardModern(block, index) {
    const card = document.createElement('div');
    card.className = 'block-card-modern';
    card.dataset.blockId = block.id;
    card.dataset.section = block.section_slug || 'general';
    card.dataset.websiteType = block.website_type || 'General';
    card.dataset.category = block.category || 'Basic';
    card.style.animationDelay = `${index * 0.05}s`;
    card.style.opacity = '0';
    card.style.transform = 'translateY(20px)';
    
    setTimeout(() => {
        card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
        card.style.opacity = '1';
        card.style.transform = 'translateY(0)';
    }, index * 50);
    
    const shortDesc = block.description 
        ? block.description.substring(0, 60) + (block.description.length > 60 ? '...' : '')
        : 'Pas de description';
    
    const categoryBadge = `<span class="block-badge badge-category">${block.category || 'Basic'}</span>`;
    const proBadge = !block.is_free ? '<span class="block-badge badge-pro">PRO</span>' : '';
    const freeBadge = block.is_free ? '<span class="block-badge badge-free">Gratuit</span>' : '';
    const usageBadge = block.usage_count > 0 ? 
        `<span class="block-badge badge-usage"><i class="fas fa-download"></i> ${block.usage_count}</span>` : '';
    
    card.innerHTML = `
        <button type="button" class="block-preview-action" title="Prévisualiser le design" aria-label="Prévisualiser le design">
            <i class="fas fa-eye"></i>
        </button>
        <div class="block-icon-modern">
            <i class="fas ${block.icon || 'fa-cube'}"></i>
        </div>
        <div class="block-name">${escapeHtml(block.name)}</div>
        <div class="block-description">${escapeHtml(shortDesc)}</div>
        <div class="block-meta-modern">
            ${categoryBadge}
            ${proBadge}
            ${freeBadge}
            ${usageBadge}
        </div>
        <div class="block-stats">
            ${block.is_responsive ? 
                '<div class="block-stat" title="Responsive"><i class="fas fa-mobile-alt"></i></div>' : ''}
            ${block.views_count > 0 ? 
                `<div class="block-stat" title="${block.views_count} vues">
                    <i class="fas fa-eye"></i>
                </div>` : ''}
        </div>
    `;
    
    card.draggable = true;
    
    card.addEventListener('dragstart', (e) => {
        const blockHtml = buildBlockHtmlFromBlock(block);
        
        e.dataTransfer.setData('text/html', blockHtml);
        e.dataTransfer.setData('text/plain', blockHtml);
        e.dataTransfer.setData('block-id', block.id.toString());
        
        e.dataTransfer.effectAllowed = 'copy';
        card.classList.add('dragging');
        
        e.dataTransfer.setDragImage(card, 75, 75);
        
        card.style.transform = 'scale(0.95) rotate(2deg)';
        card.style.boxShadow = '0 30px 60px rgba(0, 0, 0, 0.5)';
    });
    
    card.addEventListener('dragend', () => {
        card.classList.remove('dragging');
        card.style.transform = '';
        card.style.boxShadow = '';
    });

    const previewButton = card.querySelector('.block-preview-action');
    if (previewButton) {
        previewButton.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            previewBlock(block.id);
        });
    }
    
    card.addEventListener('click', async (e) => {
        if (!e.target.closest('.block-badge, .block-preview-action')) {
            await addBlockToEditor(block.id);
            card.style.transform = 'scale(0.95)';
            setTimeout(() => {
                card.style.transform = '';
            }, 200);
        }
    });
    
    card.addEventListener('mouseenter', () => {
        const icon = card.querySelector('.block-icon-modern i');
        if (icon) {
            icon.style.transform = 'rotate(10deg) scale(1.1)';
        }
        card.style.zIndex = '10';
    });
    
    card.addEventListener('mouseleave', () => {
        const icon = card.querySelector('.block-icon-modern i');
        if (icon) {
            icon.style.transform = '';
        }
        card.style.zIndex = '1';
    });
    
    return card;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// === FONCTIONS DE FILTRES MODERNES ===
function initModernFilters() {
    const searchInput = document.getElementById('blockSearch');
    if (searchInput) {
        searchInput.addEventListener('input', debounce((e) => {
            filterBlocksBySearch(e.target.value);
        }, 300));
    }
    
    const clearBtn = document.querySelector('.search-clear');
    if (clearBtn) {
        clearBtn.addEventListener('click', clearSearch);
    }
    
    document.querySelectorAll('.filter-chip').forEach(chip => {
        chip.addEventListener('click', () => {
            if (chip.classList.contains('active')) {
                chip.classList.remove('active');
                filterByQuickFilter('all');
            } else {
                document.querySelectorAll('.filter-chip').forEach(c => {
                    c.classList.remove('active');
                });
                chip.classList.add('active');
                filterByQuickFilter(chip.dataset.filter);
            }
        });
    });
}

function filterBlocksBySearch(term) {
    const cards = document.querySelectorAll('.block-card-modern');
    const emptyState = document.getElementById('blocksEmptyState');
    const blocksGrid = document.getElementById('blocksContainer');
    
    const clearBtn = document.querySelector('.search-clear');
    if (clearBtn) {
        clearBtn.style.display = term ? 'block' : 'none';
    }
    
    let visibleCount = 0;
    
    cards.forEach(card => {
        const name = card.querySelector('.block-name').textContent.toLowerCase();
        const desc = card.querySelector('.block-description').textContent.toLowerCase();
        const category = card.dataset.category.toLowerCase();
        const websiteType = card.dataset.websiteType.toLowerCase();
        
        const matches = name.includes(term.toLowerCase()) || 
                       desc.includes(term.toLowerCase()) || 
                       category.includes(term.toLowerCase()) ||
                       websiteType.includes(term.toLowerCase());
        
        if (matches) {
            card.style.display = 'block';
            visibleCount++;
            
            card.style.opacity = '0';
            card.style.transform = 'translateY(10px)';
            
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 10);
        } else {
            card.style.display = 'none';
        }
    });
    
    if (emptyState && blocksGrid) {
        if (visibleCount === 0) {
            emptyState.style.display = 'block';
            blocksGrid.style.display = 'none';
        } else {
            emptyState.style.display = 'none';
            blocksGrid.style.display = 'grid';
        }
    }
}

function filterByQuickFilter(filter) {
    const cards = document.querySelectorAll('.block-card-modern');
    
    cards.forEach(card => {
        switch(filter) {
            case 'all':
                card.style.display = 'block';
                break;
            case 'popular':
                const usageElement = card.querySelector('.badge-usage');
                const usageText = usageElement ? usageElement.textContent : '';
                const usageMatch = usageText.match(/\d+/);
                const usage = usageMatch ? parseInt(usageMatch[0]) : 0;
                card.style.display = usage > 5 ? 'block' : 'none';
                break;
            case 'free':
                const hasFreeBadge = card.querySelector('.badge-free');
                card.style.display = hasFreeBadge ? 'block' : 'none';
                break;
            case 'responsive':
                const hasMobileIcon = card.querySelector('.block-stat .fa-mobile-alt');
                card.style.display = hasMobileIcon ? 'block' : 'none';
                break;
        }
        
        if (card.style.display !== 'none') {
            card.style.opacity = '0';
            card.style.transform = 'translateY(10px)';
            
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 10);
        }
    });
}

function renderEmptyState() {
    const container = document.getElementById('blocksContainer');
    const emptyState = document.getElementById('blocksEmptyState');
    
    if (container) {
        container.style.display = 'none';
    }
    
    if (emptyState) {
        emptyState.style.display = 'block';
    }
}

function clearSearch() {
    const searchInput = document.getElementById('blockSearch');
    if (searchInput) {
        searchInput.value = '';
        filterBlocksBySearch('');
        searchInput.focus();
    }
}

function resetFilters() {
    clearSearch();
    
    document.querySelectorAll('.filter-chip').forEach(chip => {
        chip.classList.remove('active');
    });
    const allChip = document.querySelector('.filter-chip[data-filter="all"]');
    if (allChip) allChip.classList.add('active');
    
    document.querySelectorAll('.category-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    const allTab = document.querySelector('.category-tab[data-category="all"]');
    if (allTab) allTab.classList.add('active');
    
    filterBlocksByCategory('all');
    filterByQuickFilter('all');
}

function toggleSidebar() {
    const container = document.querySelector('.editor-container');
    if (!container) return;

    if (container.classList.contains('sidebar-visible')) {
        hideBlocksSidebar();
        return;
    }

    showBlocksSidebar();
}

function showBlocksSidebar() {
    const container = document.querySelector('.editor-container');
    const sidebar = document.querySelector('.sidebar-left');
    const toggleBtn = document.querySelector('.sidebar-toggle i');
    const topbarToggle = document.querySelector('#sidebarOpenFab');
    const topbarToggleIcon = document.querySelector('#sidebarOpenFab i');

    if (!container || !sidebar) return;

    container.classList.add('sidebar-visible');
    sidebar.setAttribute('aria-hidden', 'false');
    topbarToggle?.classList.add('is-active');
    topbarToggle?.setAttribute('title', 'Masquer les blocs');

    if (toggleBtn) {
        toggleBtn.className = 'fas fa-chevron-left';
    }

    if (topbarToggleIcon) {
        topbarToggleIcon.className = 'fas fa-chevron-left';
    }
}

function hideBlocksSidebar() {
    const container = document.querySelector('.editor-container');
    const sidebar = document.querySelector('.sidebar-left');
    const toggleBtn = document.querySelector('.sidebar-toggle i');
    const topbarToggle = document.querySelector('#sidebarOpenFab');
    const topbarToggleIcon = document.querySelector('#sidebarOpenFab i');

    if (!container || !sidebar) return;

    container.classList.remove('sidebar-visible');
    sidebar.setAttribute('aria-hidden', 'true');
    topbarToggle?.classList.remove('is-active');
    topbarToggle?.setAttribute('title', 'Afficher les blocs');

    if (toggleBtn) {
        toggleBtn.className = 'fas fa-chevron-right';
    }

    if (topbarToggleIcon) {
        topbarToggleIcon.className = 'fas fa-chevron-right';
    }
}

function openSectionLibraryAtEnd() {
    showBlocksSidebar();
    scrollCanvasToEnd();
    showNotification('Choisissez une section: elle sera ajoutée à la fin de la page.', 'info');
}

function appendSectionToPageEnd(sectionHtml, block = null) {
    if (!editor || !sectionHtml) return null;

    const wrapper = editor.getWrapper ? editor.getWrapper() : null;
    const added = wrapper && wrapper.append ? wrapper.append(sectionHtml) : editor.addComponents(sectionHtml);
    const firstAdded = Array.isArray(added) ? added[0] : added;

    if (firstAdded && editor.select) {
        attachBlockScriptToComponent(firstAdded, block);
        enableManualResizeForTree(Array.isArray(added) ? added : [firstAdded]);
        editor.select(firstAdded);
    }

    scrollCanvasToEnd();
    return added;
}

function scrollCanvasToEnd() {
    window.setTimeout(() => {
        try {
            const canvasWindow = editor?.Canvas?.getWindow ? editor.Canvas.getWindow() : null;
            const canvasDocument = canvasWindow?.document;
            const maxScroll = canvasDocument
                ? Math.max(canvasDocument.body.scrollHeight, canvasDocument.documentElement.scrollHeight)
                : 0;

            if (canvasWindow && maxScroll) {
                canvasWindow.scrollTo({ top: maxScroll, behavior: 'smooth' });
            }
        } catch (error) {
            console.warn('Impossible de scroller vers la fin du canvas:', error);
        }
    }, 120);
}

// === FONCTIONS DE GESTION DES BLOCS ===
async function addBlockToEditor(blockId) {
    try {
        const block = findLoadedBlock(blockId);

        if (!block) {
            throw new Error('Bloc introuvable dans la bibliotheque chargee');
        }

        const fullHtml = buildBlockHtmlFromBlock(block);

        if (!fullHtml.trim()) {
            throw new Error('Ce bloc ne contient aucun HTML valide');
        }

        appendSectionToPageEnd(fullHtml, block);
        executeBlockScript(block);
        updateLayersPanel();
        updateBlockUsageInUI(blockId);
        showNotification('Section ajoutee a la fin de la page', 'success');
        return;

        showLoading('Ajout du bloc...');
        
        const response = await fetch('/api/blocks/add-to-editor', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: JSON.stringify({ block_id: blockId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            let fullHtml = data.block.html;
            if (data.block.css && data.block.css.trim()) {
                fullHtml = data.block.html + '\n<style>\n' + data.block.css + '\n</style>';
            }
            
            appendSectionToPageEnd(fullHtml);
            
            if (data.block.js && data.block.js.trim()) {
                try {
                    const script = document.createElement('script');
                    script.textContent = data.block.js;
                    document.body.appendChild(script);
                } catch (jsError) {
                    console.warn('Erreur d\'exécution du JS du bloc:', jsError);
                }
            }
            
            updateLayersPanel();
            updateBlockUsageInUI(blockId);
            
            hideLoading();
            showNotification('Section ajoutée à la fin de la page', 'success');
            
        } else {
            throw new Error(data.message || 'Échec de l\'ajout du bloc');
        }
    } catch (error) {
        console.error('Erreur d\'ajout du bloc:', error);
        hideLoading();
        showNotification('Erreur d\'ajout du bloc: ' + error.message, 'error');
    }
}

function updateBlockUsageInUI(blockId) {
    const blockElement = document.querySelector(`.block-card-modern[data-block-id="${blockId}"]`);
    if (blockElement) {
        const usageElement = blockElement.querySelector('.badge-usage');
        if (usageElement) {
            const currentCount = parseInt(usageElement.textContent.match(/\d+/)[0]) || 0;
            usageElement.innerHTML = `<i class="fas fa-download"></i> ${currentCount + 1}`;
            
            usageElement.style.transform = 'scale(1.2)';
            setTimeout(() => {
                usageElement.style.transform = 'scale(1)';
            }, 300);
        } else {
            const metaElement = blockElement.querySelector('.block-meta-modern');
            if (metaElement) {
                const usageSpan = document.createElement('span');
                usageSpan.className = 'block-badge badge-usage';
                usageSpan.innerHTML = '<i class="fas fa-download"></i> 1';
                metaElement.appendChild(usageSpan);
            }
        }
    }
}

function findLoadedBlock(blockId) {
    return allBlocks.find(item => String(item.id) === String(blockId));
}

function buildBlockHtmlFromBlock(block) {
    const html = normalizeBlockSource(block?.html_content);
    const css = normalizeBlockSource(block?.css_content);
    let blockContent = html;

    if (css.trim()) {
        blockContent += '\n<style>\n' + css + '\n</style>';
    }

    return blockContent;
}

function buildBlockScriptTag(js, block = null) {
    const script = normalizeBlockSource(js).trim();
    if (!script) return '';

    if (/<script\b/i.test(script)) {
        return script;
    }

    const blockId = block?.id ? ` data-cms-block-id="${escapeHtmlAttr(block.id)}"` : '';
    return `<script data-cms-block-js="true"${blockId}>\n${script.replace(/<\/script/gi, '<\\/script')}\n</script>`;
}

function splitHtmlAndScripts(source) {
    const scripts = [];
    const html = normalizeBlockSource(source).replace(/<script\b[^>]*>([\s\S]*?)<\/script>/gi, (match, content) => {
        scripts.push(content);
        return '';
    });

    return {
        html,
        js: scripts.join('\n')
    };
}

function getFirstRootComponent() {
    const components = editor?.DomComponents?.getComponents?.();
    if (!components || typeof components.at !== 'function') {
        return null;
    }

    return components.at(0) || null;
}

function attachSavedScriptsToEditor(js) {
    const script = extractExecutableBlockScript(js);
    if (!script.trim()) return;

    attachScriptToComponent(getFirstRootComponent(), script, {
        'data-cms-page-js': 'true'
    });
}

function attachBlockScriptToComponent(component, block) {
    const script = extractExecutableBlockScript(block?.js_content);
    if (!script.trim()) return;

    attachScriptToComponent(component, script, {
        'data-cms-block-js': 'true',
        ...(block?.id ? { 'data-cms-block-id': String(block.id) } : {})
    });
}

function attachScriptToComponent(component, script, extraAttributes = {}) {
    if (!component || !script.trim()) return;

    const attributes = component.getAttributes ? component.getAttributes() : {};
    component.setAttributes({
        ...attributes,
        ...extraAttributes
    });

    component.set('script', script);

    if (typeof component.view?.updateScript === 'function') {
        component.view.updateScript();
    }

    editor?.trigger?.('component:update', component);
}

function executeBlockScript(block) {
    const js = extractExecutableBlockScript(block?.js_content);

    if (!js.trim()) {
        return;
    }

    try {
        const script = document.createElement('script');
        script.textContent = js;
        document.body.appendChild(script);
    } catch (jsError) {
        console.warn('Erreur d execution du JS du bloc:', jsError);
    }
}

function extractExecutableBlockScript(source) {
    const js = normalizeBlockSource(source);
    if (!/<script\b/i.test(js)) {
        return js;
    }

    const scripts = [];
    js.replace(/<script\b[^>]*>([\s\S]*?)<\/script>/gi, (match, content) => {
        scripts.push(content);
        return match;
    });

    return scripts.join('\n');
}

async function updateBlockUsage(blockId) {
    updateBlockUsageInUI(blockId);
    return;

    try {
        const response = await fetch('/api/blocks/add-to-editor', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: JSON.stringify({ block_id: blockId })
        });
        
        const data = await response.json();
        if (data.success) {
            updateBlockUsageInUI(blockId);
        }
    } catch (error) {
        console.error('Erreur de mise à jour de l\'utilisation du bloc:', error);
    }
}

// === FONCTIONS UTILITAIRES ===
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function showLoading(message = 'Chargement...') {
    loadingStack += 1;
    let loader = document.getElementById('global-loader');
    if (!loader) {
        loader = document.createElement('div');
        loader.id = 'global-loader';
        loader.className = 'global-loader';
        loader.innerHTML = `
            <div class="loader-content">
                <div class="loader-spinner"></div>
                <div class="loader-text">${message}</div>
            </div>
        `;
        document.body.appendChild(loader);
    } else {
        const loaderText = loader.querySelector('.loader-text');
        if (loaderText) {
            loaderText.textContent = message;
        }
    }
    loader.style.display = 'flex';
    loader.classList.add('is-active');
}

function hideLoading(force = false) {
    if (force) {
        loadingStack = 0;
    } else {
        loadingStack = Math.max(loadingStack - 1, 0);
    }

    const loader = document.getElementById('global-loader');
    if (loader && loadingStack === 0) {
        loader.style.display = 'none';
        loader.classList.remove('is-active');
    }
}

function showNotification(message, type = 'info') {
    document.querySelectorAll('.notification').forEach(n => n.remove());
    
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle'}"></i>
        <span>${message}</span>
        <button class="notification-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '1';
        notification.style.transform = 'translateY(0)';
    }, 10);
    
    setTimeout(() => {
        if (notification.parentElement) {
            notification.style.opacity = '0';
            notification.style.transform = 'translateY(-10px)';
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 300);
        }
    }, 4000);
}

function initBlocksModern() {
    const pageId = window.currentPageId || null;
    console.log('Initialisation des blocs avec pageId:', pageId);
    return loadBlocksModern(pageId);
}

function initLayersPanel() {
    updateLayersPanel();
    
    editor.on('component:selected', component => {
        updateLayersPanel();
    });
    editor.on('component:add', updateLayersPanel);
    editor.on('component:remove', updateLayersPanel);
    editor.on('component:update', updateLayersPanel);
}

function updateLayersPanel() {
    const layersList = document.getElementById('layersList');
    if (!layersList) return;
    
    const components = editor.DomComponents.getComponents();
    
    layersList.innerHTML = '';
    
    if (components.length === 0) {
        layersList.innerHTML = '<div style="color: #94a3b8; text-align: center; padding: 20px;">Aucun calque pour l\'instant</div>';
        return;
    }
    
    function renderLayers(components, level = 0) {
        components.forEach(component => {
            const layerDiv = document.createElement('div');
            layerDiv.className = 'layer-item';
            layerDiv.style.paddingLeft = (level * 20) + 'px';
            
            const selectedComponent = editor.getSelected();
            if (selectedComponent && selectedComponent === component) {
                layerDiv.classList.add('active');
            }
            
            let icon = 'fa-cube';
            const tagName = component.get('tagName');
            if (tagName === 'img') icon = 'fa-image';
            else if (tagName === 'button' || tagName === 'a') icon = 'fa-square';
            else if (tagName === 'h1' || tagName === 'h2' || tagName === 'h3') icon = 'fa-heading';
            else if (tagName === 'p') icon = 'fa-paragraph';
            else if (tagName === 'section' || tagName === 'div') icon = 'fa-square-full';
            
            layerDiv.innerHTML = `
                <div class="layer-icon">
                    <i class="fas ${icon}"></i>
                </div>
                <div class="layer-name">
                    ${component.get('type') || tagName || 'Composant'}
                </div>
                <div class="layer-badge">
                    ${tagName || 'div'}
                </div>
            `;
            
            layerDiv.addEventListener('click', (e) => {
                e.stopPropagation();
                editor.select(component);
            });
            
            layersList.appendChild(layerDiv);
            
            const children = component.get('components');
            if (children && children.length > 0) {
                renderLayers(children, level + 1);
            }
        });
    }
    
    renderLayers(components);
}

function initEditorEvents() {
    let history = [];
    const maxHistory = 50;
    
    editor.on('component:add component:remove component:update style:property:update', () => {
        const action = {
            time: new Date().toLocaleTimeString(),
            html: editor.getHtml(),
            css: editor.getCss()
        };
        
        history.unshift(action);
        if (history.length > maxHistory) {
            history.pop();
        }
        
        updateHistoryPanel();
    });
    
    function updateHistoryPanel() {
        const historyList = document.getElementById('historyList');
        if (!historyList) return;
        
        historyList.innerHTML = '';
        
        if (history.length === 0) {
            historyList.innerHTML = '<div style="color: #94a3b8; text-align: center; padding: 20px;">Aucun historique pour l\'instant</div>';
            return;
        }
        
        history.slice(0, 10).forEach((action, index) => {
            const itemDiv = document.createElement('div');
            itemDiv.className = 'history-item';
            
            let icon = 'fa-edit';
            if (index === 0) icon = 'fa-clock';
            
            itemDiv.innerHTML = `
                <div class="history-icon">
                    <i class="fas ${icon}"></i>
                </div>
                <div>
                    ${index === 0 ? 'Actuel' : 'Action ' + index}
                </div>
                <div class="history-time">
                    ${action.time}
                </div>
            `;
            
            historyList.appendChild(itemDiv);
        });
    }
    
    updateHistoryPanel();
}

// === FONCTIONS DE GESTION DES MODALES ===
async function clearCanvas() {
    const { isConfirmed } = await Swal.fire({
        title: 'Vider le Canevas ?',
        text: 'Êtes-vous sûr de vouloir vider le canevas ? Tout votre travail actuel sera perdu.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Oui, le vider !',
        cancelButtonText: 'Annuler',
        reverseButtons: true
    });

    if (isConfirmed) {
        editor.setComponents('');
        showNotification('Canevas vidé', 'info');
        
        Swal.fire({
            title: 'Vidé !',
            text: 'Le canevas a été vidé.',
            icon: 'success',
            timer: 1500,
            showConfirmButton: false
        });
    }
}

function showPreviewInModal() {
    const html = editor.getHtml();
    const css = editor.getCss();
    
    const previewFrame = document.getElementById('previewFrame');
    if (previewFrame) {
        const previewDoc = previewFrame.contentDocument || previewFrame.contentWindow.document;
        previewDoc.open();
        previewDoc.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Aperçu</title>
                <style>${css}</style>
            </head>
            <body style="margin: 0; padding: 20px; background: #f8fafc;">${html}</body>
            </html>
        `);
        previewDoc.close();
        
        const modal = document.getElementById('previewModal');
        if (modal) {
            modal.style.display = 'block';
        }
    }
}

function showPreviewInNewTab() {
    const html = editor.getHtml();
    const css = editor.getCss();
    
    const fullHtml = `<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aperçu - Éditeur CMS</title>
    <style>
        ${css}
        
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: #f8fafc;
        }
        
        .preview-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .preview-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .preview-header h1 {
            color: #1e293b;
            margin: 0;
        }
        
        .preview-note {
            background: #f1f5f9;
            padding: 10px;
            border-radius: 6px;
            margin-top: 10px;
            font-size: 14px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="preview-container">
        <div class="preview-header">
            <h1><i class="fas fa-eye"></i> Mode Aperçu</h1>
            <div class="preview-note">
                Ceci est un aperçu de votre page. Les modifications ne sont pas sauvegardées automatiquement.
            </div>
        </div>
        ${html}
    </div>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</body>
</html>`;
    
    const newTab = window.open();
    newTab.document.open();
    newTab.document.write(fullHtml);
    newTab.document.close();
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    }
}

function copyCode() {
    const codeEditor = document.getElementById('codeEditor');
    if (codeEditor) {
        codeEditor.select();
        document.execCommand('copy');
        showNotification('Code copié dans le presse-papier', 'success');
    }
}

// === DRAG AND DROP PERSONNALISÉ ===
function initCustomDragDrop() {
    if (!editor || !editor.Canvas) {
        console.error('Éditeur ou Canvas non initialisé, nouvelle tentative dans 500ms...');
        setTimeout(initCustomDragDrop, 500);
        return;
    }
    
    try {
        let canvas = null;
        
        if (editor.Canvas.getFrameEl) {
            canvas = editor.Canvas.getFrameEl();
        }
        
        if (!canvas && editor.Canvas.getWindow) {
            const win = editor.Canvas.getWindow();
            if (win && win.document) {
                canvas = win.document.body;
            }
        }
        
        if (!canvas) {
            const iframe = document.querySelector('.gjs-frame');
            if (iframe && iframe.contentDocument) {
                canvas = iframe.contentDocument.body;
            }
        }
        
        if (!canvas) {
            const frame = document.querySelector('#gjs iframe, .gjs-frame');
            if (frame && frame.contentDocument) {
                canvas = frame.contentDocument.body;
            }
        }
        
        if (!canvas) {
            console.error('Élément Canvas non trouvé, nouvelles tentatives...');
            setTimeout(initCustomDragDrop, 500);
            return;
        }
        
        console.log('Canvas trouvé:', canvas);
        
        dropIndicator = document.createElement('div');
        dropIndicator.className = 'drop-indicator';
        dropIndicator.style.display = 'none';
        
        const canvasContainer = document.querySelector('.gjs-editor-cont');
        if (canvasContainer) {
            canvasContainer.appendChild(dropIndicator);
        } else {
            document.body.appendChild(dropIndicator);
        }
        
        canvas.addEventListener('dragover', handleCanvasDragOver);
        canvas.addEventListener('dragleave', handleCanvasDragLeave);
        canvas.addEventListener('drop', handleCanvasDrop);
        
        console.log('Drag and drop personnalisé initialisé avec succès');
        
        const iframe = document.querySelector('#gjs iframe, .gjs-frame');
        if (iframe) {
            iframe.addEventListener('dragover', handleCanvasDragOver);
            iframe.addEventListener('dragleave', handleCanvasDragLeave);
            iframe.addEventListener('drop', handleCanvasDrop);
        }
        
    } catch (error) {
        console.error('Erreur d\'initialisation du drag and drop personnalisé:', error);
        setTimeout(initCustomDragDrop, 500);
    }
}

function handleCanvasDragOver(e) {
    e.preventDefault();
    e.stopPropagation();
    
    if (!dropIndicator) return false;
    
    const editorContainer = document.querySelector('.gjs-editor-cont') || document.querySelector('#gjs');
    if (!editorContainer) return false;
    
    const rect = editorContainer.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;
    
    const target = document.elementFromPoint(e.clientX, e.clientY);
    const closestComponent = findClosestComponent(target);
    
    if (closestComponent && closestComponent !== editorContainer) {
        const componentRect = closestComponent.getBoundingClientRect();
        const relativeY = e.clientY - componentRect.top;
        const isBefore = relativeY < componentRect.height / 2;
        
        dropIndicator.style.display = 'block';
        dropIndicator.style.width = componentRect.width + 'px';
        dropIndicator.style.left = (componentRect.left - rect.left) + 'px';
        
        if (isBefore) {
            dropIndicator.style.top = (componentRect.top - rect.top - 1) + 'px';
            dropIndicator.className = 'drop-indicator before';
        } else {
            dropIndicator.style.top = (componentRect.bottom - rect.top - 1) + 'px';
            dropIndicator.className = 'drop-indicator after';
        }
        
        dropIndicator.dataset.targetId = closestComponent.id || '';
        dropIndicator.dataset.position = isBefore ? 'before' : 'after';
    } else {
        dropIndicator.style.display = 'none';
    }
    
    return false;
}

function handleCanvasDragLeave(e) {
    e.preventDefault();
    e.stopPropagation();
    if (dropIndicator) {
        dropIndicator.style.display = 'none';
    }
    return false;
}

async function handleCanvasDrop(e) {
    e.preventDefault();
    e.stopPropagation();
    
    if (dropIndicator) {
        dropIndicator.style.display = 'none';
    }
    
    let blockHtml = e.dataTransfer.getData('text/html');
    const blockId = e.dataTransfer.getData('block-id');
    
    if (!blockHtml || blockHtml.trim() === '') {
        blockHtml = e.dataTransfer.getData('text/plain');
    }
    
    if (blockHtml && blockHtml.trim()) {
        const block = blockId ? findLoadedBlock(blockId) : null;
        appendSectionToPageEnd(blockHtml, block);
        
        if (blockId) {
            executeBlockScript(block);
            updateBlockUsage(parseInt(blockId));
        }
        
        showNotification('Section ajoutée à la fin de la page', 'success');
    } else {
        showNotification('Impossible d\'ajouter le bloc: Aucun HTML valide trouvé', 'error');
    }
    
    return false;
}

function findClosestComponent(element) {
    while (element && element !== document) {
        if (element.classList && element.classList.contains('gjs-comp-selected')) {
            return element;
        }
        element = element.parentElement;
    }
    return null;
}

// === FONCTIONS DIVERSES ===
function previewBlock(blockId) {
    const block = allBlocks.find(item => String(item.id) === String(blockId));

    if (!block) {
        showNotification('Bloc introuvable pour la prévisualisation', 'error');
        return;
    }

    const html = normalizeBlockSource(block.html_content);
    if (!html.trim()) {
        showNotification('Ce bloc ne contient aucun HTML à prévisualiser', 'warning');
        return;
    }

    const css = normalizeBlockSource(block.css_content);
    const js = extractExecutableBlockScript(block.js_content);
    const title = document.getElementById('blockPreviewTitle');
    const meta = document.getElementById('blockPreviewMeta');
    const frame = document.getElementById('blockPreviewFrame');
    const addButton = document.getElementById('blockPreviewAddBtn');
    const modal = document.getElementById('blockPreviewModal');

    if (title) title.textContent = block.name || 'Aperçu du block';
    if (meta) meta.textContent = [block.section_name, block.category].filter(Boolean).join(' • ');

    if (addButton) {
        addButton.onclick = async () => {
            await addBlockToEditor(block.id);
            closeModal('blockPreviewModal');
        };
    }

    if (!frame || !modal) {
        return;
    }

    const previewDoc = frame.contentDocument || frame.contentWindow.document;
    previewDoc.open();
    previewDoc.write(buildBlockPreviewDocument(block, html, css));
    previewDoc.close();

    if (js.trim()) {
        const script = previewDoc.createElement('script');
        script.textContent = js;
        previewDoc.body.appendChild(script);
    }

    modal.style.display = 'flex';
}

function normalizeBlockSource(source) {
    return String(source || '')
        .replace(/\\r\\n/g, '\n')
        .replace(/\\n/g, '\n')
        .replace(/\\t/g, '\t')
        .replace(/\\"/g, '"');
}

function buildBlockPreviewDocument(block, html, css) {
    return `<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>${escapeHtml(block.name || 'Aperçu du block')}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        html,
        body {
            min-height: 100%;
            margin: 0;
            background: #f8fafc;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, sans-serif;
        }

        body {
            padding: 24px;
        }

        .block-preview-shell {
            max-width: 1180px;
            margin: 0 auto;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.12);
        }

        ${css}
    </style>
</head>
<body>
    <main class="block-preview-shell">
        ${html}
    </main>
</body>
</html>`;
}

function showBlockCode(blockId) {
    console.log('Afficher le code pour le bloc:', blockId);
}

async function importBlocks() {
    console.log('Importation de blocs');
}

async function exportBlocks() {
    console.log('Exportation de blocs');
}

function showAllCategories() {
    console.log('Afficher toutes les catégories');
}

function showRightPanel(panel) {
    console.log('Afficher le panneau:', panel);
}

// === INITIALISATION ===
document.addEventListener('DOMContentLoaded', function() {
    initEditor().catch(handleEditorBootError);
    
    console.log('Éditeur CMS moderne initialisé');
    
    window.addEventListener('resize', updateCategoryNavButtons);
    
    const scrollContainer = document.getElementById('categoriesScroll');
    if (scrollContainer) {
        scrollContainer.addEventListener('scroll', updateCategoryNavButtons);
    }
    
    setTimeout(updateCategoryNavButtons, 1000);
});
    </script>
</body>
</html>
