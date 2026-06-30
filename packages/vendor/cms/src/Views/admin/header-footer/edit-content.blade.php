<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $type === 'header' ? 'Éditeur Header' : 'Éditeur Footer' }} - {{ $etablissement->name }}</title>
    <link rel="stylesheet" href="https://unpkg.com/grapesjs/dist/css/grapes.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        html,
        body {
            height: 100%;
            margin: 0;
            overflow: hidden;
            font-family: Inter, Arial, sans-serif;
            background: #e2e8f0;
        }
        .hf-editor-shell {
            display: flex;
            flex-direction: column;
            height: 100vh;
        }
        .hf-editor-topbar {
            align-items: center;
            background: #0f172a;
            border-bottom: 1px solid #1e293b;
            box-shadow: 0 14px 34px rgba(2, 6, 23, 0.24);
            color: #f8fafc;
            display: flex;
            gap: 16px;
            justify-content: space-between;
            padding: 14px 18px;
            position: relative;
            z-index: 30;
        }
        .hf-editor-title {
            align-items: center;
            display: flex;
            gap: 12px;
            min-width: 0;
        }
        .hf-editor-kicker {
            color: #93c5fd;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .08em;
            text-transform: uppercase;
        }
        .hf-editor-title h1 {
            font-size: 18px;
            font-weight: 800;
            margin: 0;
        }
        .hf-editor-title span {
            color: #94a3b8;
            display: block;
            font-size: 12px;
            margin-top: 2px;
        }
        .hf-editor-actions {
            align-items: center;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }
        .hf-device-switcher {
            align-items: center;
            background: #111827;
            border: 1px solid #334155;
            border-radius: 12px;
            display: inline-flex;
            gap: 4px;
            padding: 4px;
        }
        .hf-device-btn {
            align-items: center;
            background: transparent;
            border: 0;
            border-radius: 9px;
            color: #94a3b8;
            cursor: pointer;
            display: inline-flex;
            font-size: 13px;
            height: 32px;
            justify-content: center;
            width: 36px;
        }
        .hf-device-btn:hover,
        .hf-device-btn.active {
            background: #1e293b;
            color: #bfdbfe;
            box-shadow: 0 5px 14px rgba(2, 6, 23, 0.24);
        }
        .hf-btn {
            align-items: center;
            background: #111827;
            border: 1px solid #334155;
            border-radius: 8px;
            color: #e2e8f0;
            cursor: pointer;
            display: inline-flex;
            gap: 8px;
            min-height: 38px;
            padding: 8px 12px;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .hf-btn:hover {
            background: #1e293b;
            border-color: #475569;
            color: #fff;
        }
        .hf-btn.is-active {
            background: #1d4ed8;
            border-color: #3b82f6;
            color: #fff;
        }
        .hf-btn-primary {
            background: #2563eb;
            border-color: #2563eb;
            color: #fff;
        }
        .hf-btn-primary:hover {
            background: #1d4ed8;
        }
        .hf-editor-workspace {
            display: flex;
            flex: 1;
            min-height: 0;
            overflow: hidden;
            position: relative;
        }
        .hf-editor-main {
            display: flex;
            flex-direction: column;
            flex: 1;
            min-width: 0;
            position: relative;
        }
        .hf-editor-main.is-drag-over::after {
            align-items: center;
            background: rgba(37, 99, 235, 0.1);
            border: 2px dashed #2563eb;
            color: #1d4ed8;
            content: 'Déposer le bloc ici';
            display: flex;
            font-size: 16px;
            font-weight: 800;
            inset: 14px;
            justify-content: center;
            pointer-events: none;
            position: absolute;
            z-index: 20;
        }
        #gjs {
            flex: 1;
            min-height: 0;
        }
        .hf-editor-guide {
            align-items: stretch;
            background: #f8fafc;
            border-bottom: 1px solid #dbe3ef;
            display: grid;
            gap: 10px;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            padding: 12px 14px;
        }
        .hf-guide-step {
            align-items: center;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            display: grid;
            gap: 8px;
            grid-template-columns: auto 1fr;
            padding: 10px 12px;
        }
        .hf-guide-step strong {
            align-items: center;
            background: #2563eb;
            border-radius: 999px;
            color: #fff;
            display: inline-flex;
            font-size: 12px;
            height: 26px;
            justify-content: center;
            width: 26px;
        }
        .hf-guide-step span {
            color: #0f172a;
            display: block;
            font-size: 13px;
            font-weight: 800;
        }
        .hf-guide-step small {
            color: #64748b;
            display: block;
            font-size: 12px;
            margin-top: 2px;
        }
        .hf-editor-status {
            align-items: center;
            background: #ffffff;
            border-top: 1px solid #dbe3ef;
            color: #64748b;
            display: flex;
            font-size: 12px;
            font-weight: 700;
            justify-content: space-between;
            min-height: 34px;
            padding: 0 14px;
        }
        .hf-blocks-sidebar {
            background: #0b1220;
            border-right: 1px solid #1e293b;
            box-shadow: 24px 0 44px rgba(2, 6, 23, 0.34);
            color: #e2e8f0;
            display: flex;
            flex-direction: column;
            height: 100%;
            left: 0;
            min-height: 0;
            opacity: 0;
            pointer-events: none;
            position: absolute;
            top: 0;
            transform: translateX(calc(-100% - 18px));
            transition: opacity 0.22s ease, transform 0.22s ease;
            width: min(390px, 92vw);
            z-index: 25;
        }
        .hf-editor-shell.blocks-visible .hf-blocks-sidebar {
            opacity: 1;
            pointer-events: auto;
            transform: translateX(0);
        }
        .hf-blocks-header {
            align-items: center;
            border-bottom: 1px solid #1e293b;
            display: flex;
            gap: 12px;
            justify-content: space-between;
            padding: 18px 18px 14px;
        }
        .hf-sidebar-eyebrow {
            color: #93c5fd;
            display: block;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .08em;
            margin-bottom: 4px;
            text-transform: uppercase;
        }
        .hf-blocks-header h2 {
            color: #f8fafc;
            font-size: 18px;
            font-weight: 800;
            margin: 0;
        }
        .hf-blocks-header p {
            color: #94a3b8;
            font-size: 12px;
            line-height: 1.45;
            margin: 6px 0 0;
            max-width: 260px;
        }
        .hf-blocks-count {
            background: rgba(59, 130, 246, 0.16);
            border-radius: 999px;
            color: #bfdbfe;
            font-size: 12px;
            font-weight: 800;
            padding: 6px 10px;
        }
        .hf-blocks-refresh {
            align-items: center;
            background: #111827;
            border: 1px solid #334155;
            border-radius: 8px;
            color: #cbd5e1;
            cursor: pointer;
            display: inline-flex;
            height: 34px;
            justify-content: center;
            width: 34px;
        }
        .hf-blocks-refresh:hover {
            background: #1e293b;
            border-color: #475569;
            color: #fff;
        }
        .hf-blocks-close {
            align-items: center;
            background: #111827;
            border: 1px solid #334155;
            border-radius: 8px;
            color: #cbd5e1;
            cursor: pointer;
            display: inline-flex;
            height: 34px;
            justify-content: center;
            width: 34px;
        }
        .hf-blocks-close:hover {
            background: #1e293b;
            color: #fff;
        }
        .hf-blocks-filters {
            display: grid;
            gap: 8px;
            padding: 14px 18px;
        }
        .hf-field-label {
            color: #cbd5e1;
            font-size: 12px;
            font-weight: 800;
        }
        .hf-search-field {
            position: relative;
        }
        .hf-search-field i {
            color: #64748b;
            left: 12px;
            pointer-events: none;
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
        }
        .hf-blocks-input,
        .hf-blocks-select {
            background: #fff;
            border: 1px solid #334155;
            border-radius: 8px;
            color: #e2e8f0;
            font-size: 13px;
            min-height: 38px;
            outline: none;
            padding: 8px 10px;
            width: 100%;
        }
        .hf-blocks-input,
        .hf-blocks-select {
            background: #111827;
        }
        .hf-blocks-input::placeholder {
            color: #64748b;
        }
        .hf-blocks-input:focus,
        .hf-blocks-select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }
        .hf-search-field .hf-blocks-input {
            padding-left: 36px;
        }
        .hf-blocks-list {
            display: grid;
            gap: 10px;
            min-height: 0;
            overflow-y: auto;
            padding: 0 18px 16px;
        }
        .hf-sidebar-tip {
            background: #111827;
            border-top: 1px solid #1e293b;
            color: #94a3b8;
            font-size: 12px;
            line-height: 1.45;
            padding: 12px 18px;
        }
        .hf-block-card {
            background: #111827;
            border: 1px solid #1e293b;
            border-radius: 8px;
            box-shadow: 0 10px 24px rgba(2, 6, 23, 0.18);
            cursor: grab;
            display: grid;
            gap: 10px;
            grid-template-columns: 98px 1fr;
            padding: 12px;
            transition: border-color 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
        }
        .hf-block-card:hover {
            border-color: #3b82f6;
            box-shadow: 0 14px 28px rgba(59, 130, 246, 0.18);
            transform: translateY(-1px);
        }
        .hf-block-card:active {
            cursor: grabbing;
        }
        .hf-block-card.is-dragging {
            opacity: 0.65;
        }
        .hf-block-preview {
            align-items: center;
            background: #0f172a;
            border: 1px solid #1e293b;
            border-radius: 6px;
            color: #94a3b8;
            display: flex;
            height: 82px;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }
        .hf-block-preview iframe {
            background: #fff;
            border: 0;
            height: 100%;
            pointer-events: none;
            transform-origin: top left;
            width: 100%;
        }
        .hf-block-preview-empty {
            align-items: center;
            display: inline-flex;
            flex-direction: column;
            font-size: 11px;
            gap: 6px;
            text-align: center;
        }
        .hf-block-meta {
            align-content: center;
            display: grid;
            min-width: 0;
        }
        .hf-block-title {
            color: #f8fafc;
            font-size: 13px;
            font-weight: 800;
            line-height: 1.25;
            margin: 0 0 6px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .hf-block-category {
            color: #94a3b8;
            font-size: 12px;
            font-weight: 700;
        }
        .hf-block-actions {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 8px;
        }
        .hf-block-action,
        .hf-block-preview-button {
            align-items: center;
            background: transparent;
            border: 0;
            color: #93c5fd;
            cursor: pointer;
            display: inline-flex;
            font-size: 12px;
            font-weight: 800;
            gap: 6px;
            padding: 0;
        }
        .hf-block-preview-button {
            color: #cbd5e1;
        }
        .hf-block-action:hover,
        .hf-block-preview-button:hover {
            color: #fff;
        }
        .hf-block-state {
            align-items: center;
            background: #111827;
            border: 1px dashed #334155;
            border-radius: 8px;
            color: #94a3b8;
            display: flex;
            font-size: 13px;
            justify-content: center;
            min-height: 120px;
            padding: 18px;
            text-align: center;
        }
        @media (max-width: 1100px) {
            .hf-editor-guide {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .hf-blocks-sidebar {
                width: min(380px, 94vw);
            }
        }
        @media (max-width: 760px) {
            body {
                overflow: auto;
            }
            .hf-editor-shell {
                height: auto;
                min-height: 100vh;
            }
            .hf-editor-topbar,
            .hf-editor-actions {
                align-items: stretch;
                flex-direction: column;
            }
            .hf-device-switcher {
                justify-content: center;
            }
            .hf-editor-guide {
                grid-template-columns: 1fr;
            }
            .hf-block-card {
                grid-template-columns: 86px 1fr;
            }
            .hf-blocks-sidebar {
                width: 100%;
            }
        }
        .hf-toast {
            background: #fff;
            border-left: 4px solid #10b981;
            border-radius: 10px;
            box-shadow: 0 14px 34px rgba(15, 23, 42, 0.22);
            color: #0f172a;
            min-width: 280px;
            opacity: 0;
            padding: 14px 16px;
            position: fixed;
            right: 18px;
            top: 70px;
            transform: translateX(24px);
            transition: all 0.22s ease;
            z-index: 9999;
        }
        .hf-toast.show {
            opacity: 1;
            transform: translateX(0);
        }
        .hf-toast.error {
            border-left-color: #ef4444;
        }
        .hf-preview-modal {
            align-items: center;
            background: rgba(2, 6, 23, 0.72);
            display: none;
            inset: 0;
            justify-content: center;
            padding: 24px;
            position: fixed;
            z-index: 10000;
        }
        .hf-preview-modal.is-visible {
            display: flex;
        }
        .hf-preview-dialog {
            background: #0b1220;
            border: 1px solid #334155;
            border-radius: 14px;
            box-shadow: 0 28px 70px rgba(2, 6, 23, 0.5);
            display: flex;
            flex-direction: column;
            max-height: 88vh;
            overflow: hidden;
            width: min(980px, 96vw);
        }
        .hf-preview-header {
            align-items: center;
            border-bottom: 1px solid #1e293b;
            color: #f8fafc;
            display: flex;
            gap: 12px;
            justify-content: space-between;
            padding: 14px 16px;
        }
        .hf-preview-header h3 {
            font-size: 15px;
            font-weight: 800;
            margin: 0;
        }
        .hf-preview-header span {
            color: #94a3b8;
            display: block;
            font-size: 12px;
            margin-top: 3px;
        }
        .hf-preview-close {
            align-items: center;
            background: #111827;
            border: 1px solid #334155;
            border-radius: 8px;
            color: #cbd5e1;
            cursor: pointer;
            display: inline-flex;
            height: 36px;
            justify-content: center;
            width: 36px;
        }
        .hf-preview-close:hover {
            background: #1e293b;
            color: #fff;
        }
        .hf-preview-frame-wrap {
            background: #e2e8f0;
            padding: 18px;
        }
        .hf-preview-frame {
            background: #fff;
            border: 0;
            border-radius: 10px;
            box-shadow: 0 16px 34px rgba(15, 23, 42, 0.18);
            height: min(620px, 68vh);
            width: 100%;
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
        .cms-section-options-intro,
        .cms-section-option-field,
        .cms-section-color-field,
        .cms-section-media-panel {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
        }
        .cms-section-options-intro {
            align-items: center;
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
            display: grid;
            gap: 9px;
            min-width: 0;
            padding: 12px;
        }
        .cms-section-option-field.is-wide {
            grid-column: 1 / -1;
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
        .cms-section-options-modal .cms-section-input-wrap input,
        .cms-section-options-modal .cms-section-input-wrap select {
            background: transparent;
            border: 0;
            box-shadow: none;
            color: #0f172a;
            flex: 1;
            font-size: 14px;
            margin: 0;
            min-height: 40px;
            min-width: 0;
            outline: 0;
            padding: 0 12px;
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
            display: grid;
            gap: 12px;
            grid-template-columns: 1fr auto;
            min-width: 0;
            padding: 12px;
        }
        .cms-section-color-meta strong,
        .cms-section-media-title {
            color: #475569;
            display: block;
            font-size: 12px;
            font-weight: 850;
        }
        .cms-section-color-meta span,
        .cms-section-media-help {
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
            gap: 8px;
        }
        .cms-section-media-title i {
            color: #2563eb;
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
        .cms-section-media-fields[data-mode="color"] .cms-media-image-field,
        .cms-section-media-fields[data-mode="color"] .cms-media-video-field,
        .cms-section-media-fields[data-mode="color"] .cms-media-layout-row,
        .cms-section-media-fields[data-mode="image"] .cms-media-video-field,
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
            .cms-section-options-grid,
            .cms-section-color-field,
            .cms-section-media-row {
                grid-template-columns: 1fr;
            }
            .cms-section-options-modal .swal2-actions {
                justify-content: stretch;
            }
            .cms-section-options-modal .swal2-confirm,
            .cms-section-options-modal .swal2-cancel {
                flex: 1;
            }
        }
    </style>
</head>
<body>
    <div
        class="hf-editor-shell"
        data-etablissement-id="{{ $etablissement->id }}"
        data-type="{{ $type }}"
        data-load-url="{{ route('cms.admin.header-footer.api.load', ['etablissementId' => $etablissement->id, 'type' => $type]) }}"
        data-save-url="{{ route('cms.admin.header-footer.api.save', ['etablissementId' => $etablissement->id, 'type' => $type]) }}"
>
        <div class="hf-editor-topbar">
            <div class="hf-editor-title">
                <div>
                    <div class="hf-editor-kicker">Espace CMS</div>
                    <h1>{{ $type === 'header' ? 'Éditeur Header' : 'Éditeur Footer' }}</h1>
                    <span>{{ $etablissement->name }}</span>
                </div>
            </div>
            <div class="hf-editor-actions">
                <div class="hf-device-switcher" aria-label="Mode d'affichage">
                    <button type="button" class="hf-device-btn active" data-device="Bureau" title="Vue bureau">
                        <i class="fas fa-desktop"></i>
                    </button>
                    <button type="button" class="hf-device-btn" data-device="Tablette" title="Vue tablette">
                        <i class="fas fa-tablet-screen-button"></i>
                    </button>
                    <button type="button" class="hf-device-btn" data-device="Mobile" title="Vue mobile">
                        <i class="fas fa-mobile-screen-button"></i>
                    </button>
                </div>
                <button type="button" class="hf-btn" id="toggleBlocksBtn">
                    <i class="fas fa-layer-group"></i>Blocs
                </button>
                <a class="hf-btn" href="{{ route('cms.admin.dashboard', ['etablissementId' => $etablissement->id, 'slug' => ($etablissement->slug ?: \Illuminate\Support\Str::slug((string) $etablissement->name)), 'section' => 'header-footer']) }}">
                    <i class="fas fa-arrow-left"></i>Retour
                </a>
                <button type="button" class="hf-btn" id="previewBtn">
                    <i class="fas fa-eye"></i>Aperçu
                </button>
                <button type="button" class="hf-btn hf-btn-primary" id="saveBtn">
                    <i class="fas fa-save"></i>Sauvegarder
                </button>
            </div>
        </div>
        <div class="hf-editor-workspace">
            <main class="hf-editor-main" id="hfEditorMain">
                <div class="hf-editor-guide">
                    <div class="hf-guide-step">
                        <strong>1</strong>
                        <div><span>Choisir un bloc</span><small>Utilisez la recherche à droite.</small></div>
                    </div>
                    <div class="hf-guide-step">
                        <strong>2</strong>
                        <div><span>Ajouter au design</span><small>Cliquez ou glissez dans la zone.</small></div>
                    </div>
                    <div class="hf-guide-step">
                        <strong>3</strong>
                        <div><span>Modifier le contenu</span><small>Cliquez directement sur les textes.</small></div>
                    </div>
                    <div class="hf-guide-step">
                        <strong>4</strong>
                        <div><span>Sauvegarder</span><small>Publiez quand le rendu est prêt.</small></div>
                    </div>
                </div>
                <div id="gjs"></div>
                <div class="hf-editor-status">
                    <span id="hfEditorStatus">Prêt à modifier</span>
                    <span>{{ $type === 'header' ? 'Zone header' : 'Zone footer' }}</span>
                </div>
            </main>
            <aside class="hf-blocks-sidebar" id="hfBlocksSidebar" aria-hidden="true">
                <div class="hf-blocks-header">
                    <div>
                        <span class="hf-sidebar-eyebrow">Bibliothèque</span>
                        <h2>Blocs prêts à utiliser</h2>
                        <p>Cliquez sur un bloc pour l'ajouter, ou glissez-le dans l'éditeur.</p>
                    </div>
                    <span class="hf-blocks-count" id="hfBlocksCount">0</span>
                    <button type="button" class="hf-blocks-refresh" id="refreshBlocksBtn" title="Actualiser">
                        <i class="fas fa-rotate"></i>
                    </button>
                    <button type="button" class="hf-blocks-close" id="closeBlocksSidebarBtn" title="Masquer">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="hf-blocks-filters">
                    <label class="hf-field-label" for="hfBlockSearch">Recherche</label>
                    <div class="hf-search-field">
                        <i class="fas fa-search"></i>
                        <input type="search" class="hf-blocks-input" id="hfBlockSearch" placeholder="Nom ou catégorie du bloc">
                    </div>
                    <label class="hf-field-label" for="hfBlockCategory">Catégorie</label>
                    <select class="hf-blocks-select" id="hfBlockCategory">
                        <option value="all">Toutes les catégories</option>
                    </select>
                </div>
                <div class="hf-blocks-list" id="hfBlocksList">
                    <div class="hf-block-state">Chargement des blocs...</div>
                </div>
                <div class="hf-sidebar-tip">
                    Astuce : commencez avec un bloc complet, puis ajustez les textes, couleurs et liens dans l'éditeur.
                </div>
            </aside>
        </div>
    </div>

    <div class="hf-preview-modal" id="blockPreviewModal" aria-hidden="true">
        <div class="hf-preview-dialog" role="dialog" aria-modal="true" aria-labelledby="blockPreviewTitle">
            <div class="hf-preview-header">
                <div>
                    <h3 id="blockPreviewTitle">Aperçu du bloc</h3>
                    <span id="blockPreviewCategory">Header/Footer</span>
                </div>
                <button type="button" class="hf-preview-close" id="closeBlockPreviewModal" title="Fermer">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="hf-preview-frame-wrap">
                <iframe class="hf-preview-frame" id="blockPreviewFrame" title="Aperçu design du bloc" sandbox></iframe>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/grapesjs"></script>
    <script src="https://unpkg.com/grapesjs-preset-webpage"></script>
    <script src="https://unpkg.com/grapesjs-blocks-basic"></script>
    <script src="https://unpkg.com/grapesjs-plugin-forms"></script>
    <script src="https://unpkg.com/grapesjs-custom-code"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const shell = document.querySelector('.hf-editor-shell');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const zoneType = shell.dataset.type;
        const allowedBlockCategories = ['header', 'footer'];
        let editor = null;
        let cmsBlocks = [];
        let activeBlockCategory = 'all';
        let lastSavedAt = null;

        document.addEventListener('DOMContentLoaded', async () => {
            editor = grapesjs.init({
                container: '#gjs',
                height: '100%',
                fromElement: false,
                storageManager: false,
                plugins: [
                    'grapesjs-preset-webpage',
                    'grapesjs-blocks-basic',
                    'grapesjs-plugin-forms',
                    'grapesjs-custom-code'
                ],
                pluginsOpts: {
                    'grapesjs-preset-webpage': {
                        blocks: ['link-block', 'quote', 'text-basic']
                    }
                },
                canvas: {
                    styles: [
                        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'
                    ]
                },
                deviceManager: {
                    devices: [
                        { name: 'Bureau', width: '' },
                        { name: 'Tablette', width: '768px', widthMedia: '768px' },
                        { name: 'Mobile', width: '375px', widthMedia: '480px' }
                    ]
                }
            });

            addHeaderFooterBlocks();
            initBlocksSidebar();
            initDeviceSwitcher();
            initBlocksSidebarVisibility();
            initBlockPreviewModal();
            initEditorDropZone();
            initGjsToolbarSectionOptions();
            await loadCmsBlocks();
            await loadHeaderFooter();
            document.getElementById('saveBtn').addEventListener('click', saveHeaderFooter);
            document.getElementById('previewBtn').addEventListener('click', () => editor.runCommand('preview'));
        });

        function addHeaderFooterBlocks() {
            const bm = editor.BlockManager;

            bm.add('hf-header-simple', {
                label: 'Header simple',
                category: 'Header/Footer',
                media: '<i class="fas fa-window-maximize"></i>',
                content: `<header style="display:flex;align-items:center;justify-content:space-between;padding:18px 32px;background:#0f172a;color:#fff;">
                    <strong style="font-size:20px;">Nom entreprise</strong>
                    <nav style="display:flex;gap:18px;"><a href="#" style="color:#fff;text-decoration:none;">Accueil</a><a href="#" style="color:#fff;text-decoration:none;">Services</a><a href="#" style="color:#fff;text-decoration:none;">Contact</a></nav>
                </header>`
            });

            bm.add('hf-header-cta', {
                label: 'Header CTA',
                category: 'Header/Footer',
                media: '<i class="fas fa-bullhorn"></i>',
                content: `<header style="display:flex;align-items:center;justify-content:space-between;padding:20px 36px;background:#fff;border-bottom:1px solid #e5e7eb;">
                    <strong style="font-size:22px;color:#111827;">Nom entreprise</strong>
                    <a href="#contact" style="background:#2563eb;color:#fff;padding:10px 16px;border-radius:8px;text-decoration:none;">Contactez-nous</a>
                </header>`
            });

            bm.add('hf-footer-simple', {
                label: 'Footer simple',
                category: 'Header/Footer',
                media: '<i class="fas fa-window-minimize"></i>',
                content: `<footer style="display:flex;align-items:center;justify-content:space-between;padding:32px;background:#111827;color:#f8fafc;">
                    <div><strong>Nom entreprise</strong><p style="color:#cbd5e1;margin:6px 0 0;">Merci de votre visite.</p></div>
                    <div style="display:flex;gap:16px;"><a href="#" style="color:#bfdbfe;">Contact</a><a href="#" style="color:#bfdbfe;">Mentions légales</a></div>
                </footer>`
            });
        }

        function initGjsToolbarSectionOptions() {
            if (!editor?.Commands || !editor?.on) return;

            registerGjsToolbarCommand('cms-hf-add-after', () => {
                const target = getSelectedToolbarTarget();
                if (target) insertToolbarSectionAfter(target);
            });
            registerGjsToolbarCommand('cms-hf-duplicate', () => {
                const target = getSelectedToolbarTarget();
                if (target) duplicateToolbarComponent(target);
            });
            registerGjsToolbarCommand('cms-hf-move-up', () => {
                const target = getSelectedToolbarTarget();
                if (target) moveToolbarComponent(target, -1);
            });
            registerGjsToolbarCommand('cms-hf-move-down', () => {
                const target = getSelectedToolbarTarget();
                if (target) moveToolbarComponent(target, 1);
            });
            registerGjsToolbarCommand('cms-hf-boxed', () => {
                const target = getSelectedToolbarTarget();
                if (target) applyToolbarLayout(target, 'boxed');
            });
            registerGjsToolbarCommand('cms-hf-full', () => {
                const target = getSelectedToolbarTarget();
                if (target) applyToolbarLayout(target, 'full');
            });
            registerGjsToolbarCommand('cms-hf-options', () => {
                const target = getSelectedToolbarTarget();
                if (target) openToolbarQuickOptions(target);
            });
            registerGjsToolbarCommand('cms-hf-reset', () => {
                const target = getSelectedToolbarTarget();
                if (target) resetToolbarSectionStyles(target);
            });
            registerGjsToolbarCommand('cms-hf-delete', () => {
                const target = getSelectedToolbarTarget();
                if (target) {
                    target.remove();
                    setEditorStatus('Element supprime');
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
            const target = findToolbarTarget(component);
            if (!target) return;

            target.set({
                resizable: {
                    tl: 0,
                    tc: 0,
                    tr: 0,
                    cl: 0,
                    cr: 0,
                    bl: 0,
                    bc: 0,
                    br: 1,
                    keyHeight: 'min-height',
                    keyWidth: 'width'
                }
            });
            target.set('toolbar', [
                { attributes: { class: 'fas fa-arrows-alt', title: 'Deplacer' }, command: 'tlb-move' },
                { attributes: { class: 'fas fa-plus', title: 'Ajouter apres' }, command: 'cms-hf-add-after' },
                { attributes: { class: 'fas fa-clone', title: 'Dupliquer' }, command: 'cms-hf-duplicate' },
                { attributes: { class: 'fas fa-arrow-up', title: 'Monter' }, command: 'cms-hf-move-up' },
                { attributes: { class: 'fas fa-arrow-down', title: 'Descendre' }, command: 'cms-hf-move-down' },
                { attributes: { class: 'fas fa-compress-alt', title: 'Boxed 1140px' }, command: 'cms-hf-boxed' },
                { attributes: { class: 'fas fa-expand-alt', title: 'Pleine largeur' }, command: 'cms-hf-full' },
                { attributes: { class: 'fas fa-sliders-h', title: 'Options rapides' }, command: 'cms-hf-options' },
                { attributes: { class: 'fas fa-eraser', title: 'Reset styles' }, command: 'cms-hf-reset' },
                { attributes: { class: 'fas fa-trash', title: 'Supprimer' }, command: 'cms-hf-delete' }
            ]);
        }

        function getSelectedToolbarTarget() {
            return findToolbarTarget(editor?.getSelected?.());
        }

        function findToolbarTarget(component) {
            let current = component;

            while (current && !current.is?.('wrapper')) {
                if (isToolbarEditableComponent(current)) {
                    return current;
                }
                current = typeof current.parent === 'function' ? current.parent() : null;
            }

            return null;
        }

        function isToolbarEditableComponent(component) {
            if (!component || component.is?.('wrapper')) return false;

            const type = component.get ? component.get('type') : '';
            const tagName = String(component.get ? component.get('tagName') : '').toLowerCase();
            const blockedTypes = ['textnode', 'text', 'link', 'image', 'video', 'input', 'textarea', 'select', 'button', 'label'];
            const allowedTags = ['header', 'footer', 'section', 'div', 'article', 'main', 'nav', 'aside'];

            return !blockedTypes.includes(type) && allowedTags.includes(tagName || 'div');
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
            applyGjsToolbarOptions(component);
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
            const backgroundImageUrl = extractCssUrl(style['background-image'] || style.background || '');
            const backgroundVideo = findSectionBackgroundVideo(component);
            const backgroundVideoUrl = getSectionBackgroundVideoUrl(backgroundVideo);
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
                                <small>Modifiez dimensions, espacements et media de fond sans quitter l'editeur.</small>
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
                                        <input id="swal-section-background-image" type="url" placeholder="https://.../image.jpg ou /storage/..." value="${escapeHtml(values.backgroundImageUrl)}">
                                    </span>
                                </label>
                                <label class="cms-section-option-field is-wide cms-media-video-field">
                                    <span class="cms-section-option-label">URL video de fond</span>
                                    <span class="cms-section-input-wrap">
                                        <input id="swal-section-background-video" type="url" placeholder="https://.../video.mp4 ou /storage/..." value="${escapeHtml(values.backgroundVideoUrl)}">
                                    </span>
                                </label>
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
                                    Pour une video de fond, le contenu reste au-dessus automatiquement.
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
                },
                preConfirm: () => ({
                    minHeight: document.getElementById('swal-section-min-height')?.value || '',
                    padding: document.getElementById('swal-section-padding')?.value || '',
                    margin: document.getElementById('swal-section-margin')?.value || '',
                    radius: document.getElementById('swal-section-radius')?.value || '',
                    background: document.getElementById('swal-section-background')?.value || '',
                    mediaMode: document.querySelector('input[name="swal-section-media-mode"]:checked')?.value || 'color',
                    backgroundImageUrl: document.getElementById('swal-section-background-image')?.value?.trim() || '',
                    backgroundVideoUrl: document.getElementById('swal-section-background-video')?.value?.trim() || '',
                    backgroundSize: document.getElementById('swal-section-background-size')?.value || 'cover',
                    backgroundPosition: document.getElementById('swal-section-background-position')?.value || 'center center'
                })
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
                setEditorStatus('Options appliquees');
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

        function escapeCssUrl(value) {
            return String(value || '').replace(/\\/g, '\\\\').replace(/"/g, '\\"');
        }

        function findSectionBackgroundVideo(component) {
            if (!component || typeof component.components !== 'function') return null;

            let found = null;
            component.components().forEach(child => {
                if (found) return;
                const attributes = child.getAttributes ? child.getAttributes() : {};
                const tagName = child.get ? child.get('tagName') : '';
                const classes = typeof child.getClasses === 'function' ? child.getClasses() : [];

                if (attributes['data-cms-bg-video'] === 'true' || classes.includes('cms-section-bg-video') || tagName === 'video') {
                    found = child;
                }
            });

            return found;
        }

        function getSectionBackgroundVideoUrl(videoComponent) {
            if (!videoComponent) return '';

            const attributes = videoComponent.getAttributes ? videoComponent.getAttributes() : {};
            return attributes.src || '';
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

                const style = child.getStyle ? child.getStyle() : {};
                child.setStyle({
                    ...style,
                    position: style.position || 'relative',
                    'z-index': style['z-index'] || '1'
                });
            });
        }

        function upsertSectionBackgroundVideo(component, url, fit = 'cover', position = 'center center') {
            if (!component || !url) return;

            const style = component.getStyle ? component.getStyle() : {};
            component.setStyle({
                ...style,
                position: style.position || 'relative',
                overflow: 'hidden'
            });

            let videoComponent = findSectionBackgroundVideo(component);
            const videoAttributes = {
                'data-cms-bg-video': 'true',
                class: 'cms-section-bg-video',
                src: url,
                autoplay: 'autoplay',
                muted: 'muted',
                loop: 'loop',
                playsinline: 'playsinline'
            };
            const videoStyle = {
                height: '100%',
                left: '0',
                'object-fit': fit || 'cover',
                'object-position': position || 'center center',
                'pointer-events': 'none',
                position: 'absolute',
                top: '0',
                width: '100%',
                'z-index': '0'
            };

            if (videoComponent) {
                videoComponent.setAttributes(videoAttributes);
                videoComponent.setStyle(videoStyle);
            } else {
                videoComponent = component.components().add({
                    tagName: 'video',
                    attributes: videoAttributes,
                    style: videoStyle
                }, { at: 0 });
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
                        options.backgroundPosition || 'center center'
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
            setEditorStatus('Styles reinitialises');
        }

        function insertToolbarSectionAfter(component) {
            const collection = component.collection;
            if (!collection) return;

            const tagName = String(component.get ? component.get('tagName') : '').toLowerCase() || (zoneType === 'header' ? 'header' : 'footer');
            const index = collection.indexOf(component);
            const added = collection.add({
                tagName: ['header', 'footer', 'section', 'div', 'nav'].includes(tagName) ? tagName : 'section',
                style: {
                    padding: '36px 24px',
                    'min-height': '120px',
                    background: '#ffffff'
                },
                components: '<div style="max-width:1140px;margin:0 auto;min-height:48px;"></div>'
            }, { at: index + 1 });

            enableToolbarForTree([added]);
            editor.select(added);
            setEditorStatus('Section ajoutee');
        }

        function duplicateToolbarComponent(component) {
            const collection = component.collection;
            if (!collection) return;

            const index = collection.indexOf(component);
            const added = collection.add(component.clone(), { at: index + 1 });
            enableToolbarForTree([added]);
            editor.select(added);
            setEditorStatus('Element duplique');
        }

        function moveToolbarComponent(component, direction) {
            const collection = component.collection;
            if (!collection) return;

            const index = collection.indexOf(component);
            const nextIndex = index + direction;
            if (nextIndex < 0 || nextIndex >= collection.length) return;

            const componentData = component.toJSON();
            component.remove();
            const movedComponent = collection.add(componentData, { at: nextIndex });
            enableToolbarForTree([movedComponent]);
            editor.select(movedComponent);
            setEditorStatus('Element deplace');
        }

        function initBlocksSidebar() {
            document.getElementById('hfBlockSearch').addEventListener('input', renderCmsBlocks);
            document.getElementById('hfBlockCategory').addEventListener('change', event => {
                activeBlockCategory = event.target.value;
                renderCmsBlocks();
            });
            document.getElementById('refreshBlocksBtn').addEventListener('click', loadCmsBlocks);
        }

        function initDeviceSwitcher() {
            document.querySelectorAll('.hf-device-btn').forEach(button => {
                button.addEventListener('click', () => {
                    document.querySelectorAll('.hf-device-btn').forEach(item => item.classList.remove('active'));
                    button.classList.add('active');
                    editor.setDevice(button.dataset.device);
                    setEditorStatus(`Vue ${button.dataset.device.toLowerCase()} active`);
                });
            });
        }

        function initBlocksSidebarVisibility() {
            document.getElementById('toggleBlocksBtn')?.addEventListener('click', () => {
                toggleBlocksSidebar();
            });
            document.getElementById('closeBlocksSidebarBtn')?.addEventListener('click', () => {
                hideBlocksSidebar();
            });
        }

        function showBlocksSidebar() {
            shell.classList.add('blocks-visible');
            document.getElementById('hfBlocksSidebar')?.setAttribute('aria-hidden', 'false');
            document.getElementById('toggleBlocksBtn')?.classList.add('is-active');
        }

        function hideBlocksSidebar() {
            shell.classList.remove('blocks-visible');
            document.getElementById('hfBlocksSidebar')?.setAttribute('aria-hidden', 'true');
            document.getElementById('toggleBlocksBtn')?.classList.remove('is-active');
        }

        function toggleBlocksSidebar() {
            if (shell.classList.contains('blocks-visible')) {
                hideBlocksSidebar();
                return;
            }

            showBlocksSidebar();
        }

        function initEditorDropZone() {
            const editorMain = document.getElementById('hfEditorMain');

            editorMain.addEventListener('dragover', event => {
                if (!hasCmsBlockTransfer(event)) return;
                event.preventDefault();
                editorMain.classList.add('is-drag-over');
            });

            editorMain.addEventListener('dragleave', event => {
                if (!editorMain.contains(event.relatedTarget)) {
                    editorMain.classList.remove('is-drag-over');
                }
            });

            editorMain.addEventListener('drop', event => {
                handleCmsBlockDrop(event);
            });

            editor.on('load', bindCanvasDropZone);
            window.setTimeout(bindCanvasDropZone, 400);
        }

        function bindCanvasDropZone() {
            const canvasDocument = editor.Canvas.getDocument();
            if (!canvasDocument || canvasDocument.__hfCmsDropReady) return;

            canvasDocument.__hfCmsDropReady = true;
            canvasDocument.addEventListener('dragover', event => {
                if (!hasCmsBlockTransfer(event)) return;
                event.preventDefault();
                document.getElementById('hfEditorMain').classList.add('is-drag-over');
            });
            canvasDocument.addEventListener('dragleave', () => {
                document.getElementById('hfEditorMain').classList.remove('is-drag-over');
            });
            canvasDocument.addEventListener('drop', handleCmsBlockDrop);
        }

        function handleCmsBlockDrop(event) {
            if (!hasCmsBlockTransfer(event)) return;

            event.preventDefault();
            document.getElementById('hfEditorMain').classList.remove('is-drag-over');
            addCmsBlockToEditor(event.dataTransfer.getData('application/x-cms-block'));
        }

        async function loadCmsBlocks() {
            cmsBlocks = [];
            renderCmsBlocks();
            setEditorStatus('0 bloc disponible');
        }

        function renderBlockCategories(categories) {
            const select = document.getElementById('hfBlockCategory');
            const categoryNames = new Set(cmsBlocks.map(block => block.category || 'Autres'));

            categories.forEach(category => {
                if (category && allowedBlockCategories.includes(String(category.name || '').trim().toLowerCase())) {
                    categoryNames.add(category.name);
                }
            });

            select.innerHTML = '<option value="all">Toutes les catégories</option>' + [...categoryNames]
                .sort((a, b) => String(a).localeCompare(String(b)))
                .map(category => `<option value="${escapeHtml(category)}">${escapeHtml(category)}</option>`)
                .join('');
            select.value = categoryNames.has(activeBlockCategory) ? activeBlockCategory : 'all';
            activeBlockCategory = select.value;
        }

        function renderCmsBlocks() {
            const list = document.getElementById('hfBlocksList');
            const count = document.getElementById('hfBlocksCount');
            const search = document.getElementById('hfBlockSearch').value.trim().toLowerCase();
            const filteredBlocks = cmsBlocks.filter(block => {
                const label = String(block.label || '').toLowerCase();
                const category = String(block.category || 'Autres');
                const matchesSearch = !search || label.includes(search) || category.toLowerCase().includes(search);
                const matchesCategory = activeBlockCategory === 'all' || category === activeBlockCategory;
                return matchesSearch && matchesCategory;
            });

            count.textContent = filteredBlocks.length;

            if (!filteredBlocks.length) {
                list.innerHTML = '<div class="hf-block-state">Aucun bloc Header ou Footer trouvé. Vérifiez la catégorie du bloc dans /admin/cms/blocks.</div>';
                return;
            }

            list.innerHTML = filteredBlocks.map(block => createBlockCard(block)).join('');
            list.querySelectorAll('.hf-block-card').forEach(card => {
                card.addEventListener('click', event => {
                    if (event.target.closest('[data-action="preview"]')) {
                        event.preventDefault();
                        event.stopPropagation();
                        previewCmsBlock(card.dataset.blockId);
                        return;
                    }

                    addCmsBlockToEditor(card.dataset.blockId);
                });
                card.addEventListener('dragstart', event => {
                    card.classList.add('is-dragging');
                    event.dataTransfer.effectAllowed = 'copy';
                    event.dataTransfer.setData('application/x-cms-block', card.dataset.blockId);
                });
                card.addEventListener('dragend', () => card.classList.remove('is-dragging'));
            });
        }

        function isHeaderFooterBlock(block) {
            return allowedBlockCategories.includes(String(block?.category || '').trim().toLowerCase());
        }

        function createBlockCard(block) {
            const label = escapeHtml(block.label || `Bloc #${block.id}`);
            const category = escapeHtml(block.category || 'Autres');
            const preview = block.content
                ? `<iframe srcdoc="${escapeHtml(blockPreviewDocument(block))}" title="Preview ${label}" loading="lazy" sandbox></iframe>`
                : '<div class="hf-block-preview-empty"><i class="fas fa-eye-slash"></i><span>Aucun aperçu</span></div>';

            return `
                <article class="hf-block-card" draggable="true" data-block-id="${escapeHtml(block.id)}" title="Ajouter ${label}">
                    <div class="hf-block-preview">${preview}</div>
                    <div class="hf-block-meta">
                        <h3 class="hf-block-title">${label}</h3>
                        <div class="hf-block-category">${category}</div>
                        <div class="hf-block-actions">
                            <button type="button" class="hf-block-action" data-action="add">
                                <i class="fas fa-plus-circle"></i>Ajouter
                            </button>
                            <button type="button" class="hf-block-preview-button" data-action="preview">
                                <i class="fas fa-eye"></i>Preview
                            </button>
                        </div>
                    </div>
                </article>
            `;
        }

        function initBlockPreviewModal() {
            document.getElementById('closeBlockPreviewModal')?.addEventListener('click', closeBlockPreviewModal);
            document.getElementById('blockPreviewModal')?.addEventListener('click', event => {
                if (event.target.id === 'blockPreviewModal') {
                    closeBlockPreviewModal();
                }
            });
            document.addEventListener('keydown', event => {
                if (event.key === 'Escape') {
                    closeBlockPreviewModal();
                }
            });
        }

        function previewCmsBlock(blockId) {
            const block = cmsBlocks.find(item => String(item.id) === String(blockId));
            if (!block) {
                showToast('Bloc introuvable', 'error');
                return;
            }

            document.getElementById('blockPreviewTitle').textContent = block.label || `Bloc #${block.id}`;
            document.getElementById('blockPreviewCategory').textContent = block.category || 'Header/Footer';
            document.getElementById('blockPreviewFrame').srcdoc = blockPreviewDocument(block);
            document.getElementById('blockPreviewModal').classList.add('is-visible');
            document.getElementById('blockPreviewModal').setAttribute('aria-hidden', 'false');
        }

        function closeBlockPreviewModal() {
            const modal = document.getElementById('blockPreviewModal');
            const frame = document.getElementById('blockPreviewFrame');
            modal?.classList.remove('is-visible');
            modal?.setAttribute('aria-hidden', 'true');
            if (frame) {
                frame.srcdoc = '';
            }
        }

        function blockPreviewDocument(block) {
            const content = cleanHeaderFooterHtml(block.content || '');
            const fallback = '<div class="preview-empty">Aucun contenu disponible</div>';

            return `<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #fff; color: #111827; font-family: Arial, sans-serif; }
        header, footer, section, nav { max-width: 100%; }
        a { cursor: default; }
        .preview-empty { align-items: center; color: #64748b; display: flex; min-height: 120px; justify-content: center; }
    </style>
</head>
<body>${content || fallback}</body>
</html>`;
        }

        function registerCmsBlocksInGrapes() {
            const bm = editor.BlockManager;

            cmsBlocks.forEach(block => {
                const blockId = `cms-db-block-${block.id}`;
                if (bm.get(blockId)) return;

                bm.add(blockId, {
                    label: block.label || `Bloc #${block.id}`,
                    category: block.category || 'Blocs CMS',
                    media: block.media ? `<img src="${escapeHtml(block.media)}" style="max-width:100%;height:36px;object-fit:cover;">` : '<i class="fas fa-layer-group"></i>',
                    content: block.content || ''
                });
            });
        }

        function addCmsBlockToEditor(blockId) {
            const block = cmsBlocks.find(item => String(item.id) === String(blockId));
            if (!block || !block.content) {
                showToast('Bloc introuvable', 'error');
                return;
            }

            editor.addComponents(cleanHeaderFooterHtml(block.content));
            enableToolbarForTree(editor.DomComponents?.getComponents?.());
            setEditorStatus(`Bloc ajouté : ${block.label || `#${block.id}`}`);
            showToast('Bloc ajouté', 'success');
        }

        function hasCmsBlockTransfer(event) {
            return Array.from(event.dataTransfer?.types || []).includes('application/x-cms-block');
        }

        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, char => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            })[char]);
        }

        async function loadHeaderFooter() {
            try {
                const response = await fetch(shell.dataset.loadUrl, { headers: { Accept: 'application/json' } });
                const payload = await response.json();
                if (!payload.success) throw new Error(payload.message || 'Chargement impossible');

                editor.setComponents(cleanHeaderFooterHtml(payload.data.html_content || payload.data.content || ''));
                editor.setStyle(payload.data.css_content || extractStyle(payload.data.content || ''));
                enableToolbarForTree(editor.DomComponents?.getComponents?.());
                setEditorStatus(`${zoneType === 'header' ? 'Header' : 'Footer'} chargé`);
                showToast(`${zoneType === 'header' ? 'Header' : 'Footer'} chargé`, 'success');
            } catch (error) {
                showToast(error.message || 'Erreur de chargement', 'error');
                setEditorStatus('Erreur pendant le chargement');
            }
        }

        async function saveHeaderFooter() {
            const html = cleanHeaderFooterHtml(editor.getHtml());
            const css = editor.getCss();
            const content = css.trim() ? `<style>${css}</style>${html}` : html;
            const saveBtn = document.getElementById('saveBtn');

            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>Sauvegarde...';
            setEditorStatus('Sauvegarde en cours...');

            try {
                const response = await fetch(shell.dataset.saveUrl, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        name: zoneType === 'header' ? 'Header du site' : 'Footer du site',
                        content: content,
                        html_content: html,
                        css_content: css
                    })
                });
                const payload = await response.json();
                if (!payload.success) throw new Error(payload.message || 'Sauvegarde impossible');
                lastSavedAt = new Date();
                setEditorStatus(`Dernière sauvegarde : ${lastSavedAt.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })}`);
                showToast(payload.message || 'Sauvegardé', 'success');
            } catch (error) {
                showToast(error.message || 'Erreur de sauvegarde', 'error');
                setEditorStatus('Erreur pendant la sauvegarde');
            } finally {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="fas fa-save"></i>Sauvegarder';
            }
        }

        function extractStyle(content) {
            const match = String(content || '').match(/<style>([\s\S]*?)<\/style>/i);
            return match ? match[1] : '';
        }

        function cleanHeaderFooterHtml(content) {
            return String(content || '')
                .replace(/^(\s*(?:<style[\s\S]*?<\/style>\s*)*)\d+\s*(?=<)/i, '$1')
                .replace(/^\s*\d+\s*(?=<)/, '');
        }

        function setEditorStatus(message) {
            const status = document.getElementById('hfEditorStatus');
            if (status) {
                status.textContent = message;
            }
        }

        function showToast(message, type = 'success') {
            document.querySelectorAll('.hf-toast').forEach(toast => toast.remove());
            const toast = document.createElement('div');
            toast.className = `hf-toast ${type === 'error' ? 'error' : 'success'}`;
            toast.textContent = message;
            document.body.appendChild(toast);
            requestAnimationFrame(() => toast.classList.add('show'));
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 240);
            }, 3200);
        }
    </script>
</body>
</html>
