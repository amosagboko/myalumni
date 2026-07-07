{{--
    Admin surface styles (non-table): stats, sections, modals, quick actions.
    Wrap content in <div class="admin-surface"> or include on table pages too.
    Include once per page: <x-admin.surface-styles />
--}}
@include('components.admin.ui-tokens')
@once
@push('styles')
<style>
    /* Page header */
    .admin-ui .ads-page-header,
    .admin-surface .ads-page-header,
    .admin-data-table .ads-page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 1.25rem;
        flex-wrap: wrap;
    }

    .admin-ui .ads-page-title,
    .admin-surface .ads-page-title,
    .admin-data-table .ads-page-title {
        font-size: 1.35rem;
        font-weight: 600;
        color: var(--adt-text);
        margin: 0 0 0.2rem;
    }

    .admin-ui .ads-page-subtitle,
    .admin-surface .ads-page-subtitle,
    .admin-data-table .ads-page-subtitle {
        font-size: 0.875rem;
        color: var(--adt-text-muted);
        margin: 0;
    }

    .admin-ui .ads-page-actions,
    .admin-surface .ads-page-actions,
    .admin-data-table .ads-page-actions,
    .admin-ui .ads-filters,
    .admin-surface .ads-filters,
    .admin-data-table .ads-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
    }

    /* Primary button */
    .admin-ui .ads-btn-primary,
    .admin-surface .ads-btn-primary,
    .admin-data-table .ads-btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: var(--adt-primary) !important;
        border-color: var(--adt-primary) !important;
        color: #ffffff !important;
    }

    .admin-ui .ads-btn-primary:hover,
    .admin-ui .ads-btn-primary:focus,
    .admin-surface .ads-btn-primary:hover,
    .admin-surface .ads-btn-primary:focus,
    .admin-data-table .ads-btn-primary:hover,
    .admin-data-table .ads-btn-primary:focus {
        color: #ffffff !important;
        background: #0f2060 !important;
        border-color: #0f2060 !important;
    }

    /* Form controls (filters) */
    .admin-ui .ads-select,
    .admin-surface .ads-select,
    .admin-data-table .ads-select {
        width: auto;
        min-width: 130px;
        border-color: var(--adt-border);
        background: var(--adt-bg);
        font-size: 0.8125rem;
    }

    .admin-ui .ads-select-narrow,
    .admin-surface .ads-select-narrow,
    .admin-data-table .ads-select-narrow {
        min-width: 95px;
    }

    /* Sections */
    .admin-ui .ads-section,
    .admin-surface .ads-section,
    .admin-data-table .ads-section {
        margin-bottom: 1.25rem;
    }

    .admin-ui .ads-section-title,
    .admin-surface .ads-section-title,
    .admin-data-table .ads-section-title {
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--adt-text-muted);
        margin: 0 0 0.65rem;
    }

    .admin-ui .ads-section-meta,
    .admin-surface .ads-section-meta,
    .admin-data-table .ads-section-meta {
        font-weight: 500;
        text-transform: none;
        letter-spacing: normal;
        color: var(--adt-text);
    }

    /* Stat grids */
    .admin-ui .ads-stats,
    .admin-surface .ads-stats,
    .admin-data-table .ads-stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 0.75rem;
        margin-bottom: 0;
    }

    .admin-ui .ads-stats-5,
    .admin-surface .ads-stats-5,
    .admin-data-table .ads-stats-5 {
        grid-template-columns: repeat(5, 1fr);
    }

    .admin-ui .ads-stats-3,
    .admin-surface .ads-stats-3,
    .admin-data-table .ads-stats-3 {
        grid-template-columns: repeat(3, 1fr);
    }

    @media (max-width: 992px) {
        .admin-ui .ads-stats-5,
        .admin-surface .ads-stats-5,
        .admin-data-table .ads-stats-5 {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .admin-ui .ads-stats,
        .admin-surface .ads-stats,
        .admin-data-table .ads-stats {
            grid-template-columns: repeat(2, 1fr);
        }

        .admin-ui .ads-stats-3,
        .admin-surface .ads-stats-3,
        .admin-data-table .ads-stats-3 {
            grid-template-columns: 1fr;
        }
    }

    .admin-ui .ads-stat,
    .admin-surface .ads-stat,
    .admin-data-table .ads-stat {
        background: var(--adt-bg);
        border: 1px solid var(--adt-border);
        border-radius: var(--adt-radius);
        padding: 0.85rem 1rem;
    }

    .admin-ui .ads-stat-label,
    .admin-surface .ads-stat-label,
    .admin-data-table .ads-stat-label {
        display: block;
        font-size: 0.75rem;
        font-weight: 500;
        color: var(--adt-text-muted);
        text-transform: uppercase;
        letter-spacing: 0.03em;
        margin-bottom: 0.25rem;
    }

    .admin-ui .ads-stat-value,
    .admin-surface .ads-stat-value,
    .admin-data-table .ads-stat-value {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--adt-text);
        line-height: 1.2;
    }

    .admin-ui .ads-stat-value-sm,
    .admin-surface .ads-stat-value-sm,
    .admin-data-table .ads-stat-value-sm {
        font-size: 1.15rem;
    }

    .admin-ui .ads-stat-highlight,
    .admin-surface .ads-stat-highlight,
    .admin-data-table .ads-stat-highlight {
        border-color: #c5d4f0;
        background: #f8faff;
    }

    .admin-ui .ads-stat-link,
    .admin-surface .ads-stat-link,
    .admin-data-table .ads-stat-link {
        display: inline-block;
        margin-top: 0.35rem;
        font-size: 0.75rem;
        font-weight: 500;
        color: var(--adt-primary);
        text-decoration: none;
    }

    .admin-ui .ads-stat-link:hover,
    .admin-surface .ads-stat-link:hover,
    .admin-data-table .ads-stat-link:hover {
        text-decoration: underline;
    }

    /* Content panel (non-table summary blocks) */
    .admin-ui .ads-panel,
    .admin-surface .ads-panel,
    .admin-data-table .ads-panel {
        background: var(--adt-bg);
        border: 1px solid var(--adt-border);
        border-radius: var(--adt-radius);
        overflow: hidden;
    }

    .admin-ui .ads-panel-header,
    .admin-surface .ads-panel-header,
    .admin-data-table .ads-panel-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--adt-border);
        background: var(--adt-bg-subtle);
    }

    .admin-ui .ads-panel-title,
    .admin-surface .ads-panel-title,
    .admin-data-table .ads-panel-title {
        margin: 0;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--adt-text);
    }

    .admin-ui .ads-panel-body,
    .admin-surface .ads-panel-body,
    .admin-data-table .ads-panel-body {
        padding: 1rem 1.25rem;
    }

    /* Alerts */
    .admin-ui .ads-alert,
    .admin-surface .ads-alert,
    .admin-data-table .ads-alert {
        padding: 0.65rem 1rem;
        border-radius: 6px;
        font-size: 0.875rem;
        margin-bottom: 1rem;
    }

    .admin-ui .ads-alert-success,
    .admin-surface .ads-alert-success,
    .admin-data-table .ads-alert-success {
        background: #f0fdf4;
        color: #166534;
        border: 1px solid #bbf7d0;
    }

    .admin-ui .ads-alert-error,
    .admin-surface .ads-alert-error,
    .admin-data-table .ads-alert-error {
        background: #fef2f2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }

    .admin-ui .ads-alert-warning,
    .admin-surface .ads-alert-warning,
    .admin-data-table .ads-alert-warning {
        background: #fffbeb;
        color: #92400e;
        border: 1px solid #fde68a;
    }

    .admin-ui .ads-progress-wrap,
    .admin-surface .ads-progress-wrap,
    .admin-data-table .ads-progress-wrap {
        margin-bottom: 1rem;
    }

    .admin-ui .ads-progress,
    .admin-surface .ads-progress,
    .admin-data-table .ads-progress {
        height: 0.5rem;
        border-radius: 4px;
        background: var(--adt-bg-subtle);
    }

    .admin-ui .ads-progress-text,
    .admin-surface .ads-progress-text,
    .admin-data-table .ads-progress-text {
        display: block;
        margin-top: 0.35rem;
        font-size: 0.8125rem;
        color: var(--adt-text-muted);
    }

    /* Quick actions */
    .admin-ui .ads-quick-actions,
    .admin-surface .ads-quick-actions,
    .admin-data-table .ads-quick-actions {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 0.65rem;
    }

    .admin-ui .ads-quick-action,
    .admin-surface .ads-quick-action,
    .admin-data-table .ads-quick-action {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 1rem 0.75rem;
        background: var(--adt-bg);
        border: 1px solid var(--adt-border);
        border-radius: var(--adt-radius);
        color: var(--adt-text);
        text-decoration: none;
        font-size: 0.8125rem;
        font-weight: 500;
        transition: border-color 0.12s, background 0.12s;
    }

    .admin-ui .ads-quick-action:hover,
    .admin-surface .ads-quick-action:hover,
    .admin-data-table .ads-quick-action:hover {
        border-color: #c5cdd8;
        background: var(--adt-bg-subtle);
        color: var(--adt-primary);
    }

    .admin-ui .ads-quick-action svg,
    .admin-surface .ads-quick-action svg,
    .admin-data-table .ads-quick-action svg {
        width: 20px;
        height: 20px;
        color: var(--adt-text-muted);
    }

    .admin-ui .ads-quick-action:hover svg,
    .admin-surface .ads-quick-action:hover svg,
    .admin-data-table .ads-quick-action:hover svg {
        color: var(--adt-primary);
    }

    /* Livewire modal (no Bootstrap) */
    .ads-modal-overlay {
        position: fixed !important;
        inset: 0 !important;
        z-index: 99999 !important;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(2px);
    }

    .admin-data-table .ads-modal-overlay .ads-modal-card {
        background: #fff;
    }

    .ads-modal-dialog {
        width: 100%;
        max-width: 400px;
        max-height: calc(100vh - 2rem);
        display: flex;
        flex-direction: column;
    }

    .ads-modal-dialog-lg {
        max-width: 640px;
    }

    .ads-modal-card {
        background: #fff;
        border: 1px solid var(--adt-border);
        border-radius: calc(var(--adt-radius) + 2px);
        box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.35);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        max-height: calc(100vh - 2rem);
    }

    .ads-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--adt-border);
        background: var(--adt-bg-subtle);
        flex-shrink: 0;
    }

    .ads-modal-title {
        margin: 0;
        font-size: 1rem;
        font-weight: 600;
        color: var(--adt-text);
    }

    .ads-modal-body {
        padding: 1rem 1.25rem;
        overflow-y: auto;
        flex: 1 1 auto;
    }

    .ads-modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 0.5rem;
        padding: 0.85rem 1.25rem;
        border-top: 1px solid var(--adt-border);
        background: var(--adt-bg-subtle);
        flex-shrink: 0;
    }

    body.ads-modal-open {
        overflow: hidden;
    }

    .ads-modal-detail-label {
        font-size: 0.75rem;
        color: var(--adt-text-muted);
        margin-bottom: 0.15rem;
    }

    .ads-modal-detail-value {
        font-size: 0.875rem;
        color: var(--adt-text);
        margin-bottom: 0.75rem;
    }

    .ads-modal-overlay .adt-tag {
        display: inline-block;
        padding: 0.15rem 0.5rem;
        font-size: 0.75rem;
        font-weight: 500;
        border-radius: 4px;
        background: var(--adt-bg-subtle);
        color: var(--adt-text-muted);
        border: 1px solid var(--adt-border);
    }

    .ads-modal-body .ads-section-card {
        padding: 1rem 1.1rem;
        background: var(--adt-bg-subtle);
        border: 1px solid var(--adt-border);
        border-radius: var(--adt-radius);
    }

    .ads-modal-body .ads-section-title {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.72rem;
        margin-bottom: 0.85rem;
        padding-bottom: 0.65rem;
        border-bottom: 1px solid var(--adt-border);
    }

    .ads-modal-body .ads-section-title::before {
        content: '';
        width: 3px;
        height: 0.85rem;
        border-radius: 2px;
        background: var(--adt-primary);
        flex-shrink: 0;
    }

    /* ── Surface face-lift (dashboard / overview pages only) ── */
    .admin-surface {
        padding-bottom: 1.5rem;
    }

    .admin-surface .ads-page-header {
        margin-bottom: 1.5rem;
        padding: 1.25rem 1.35rem;
        background: var(--ads-hero-gradient);
        border: 1px solid var(--adt-border);
        border-radius: calc(var(--adt-radius) + 2px);
        box-shadow: var(--ads-shadow-sm);
    }

    .admin-surface .ads-page-title {
        font-size: 1.5rem;
        letter-spacing: -0.02em;
    }

    .admin-surface .ads-filters .ads-select {
        box-shadow: var(--ads-shadow-sm);
        border-color: #d8dee8;
    }

    .admin-surface .ads-section {
        margin-bottom: 1.5rem;
    }

    .admin-surface .ads-section-card {
        padding: 1.15rem 1.25rem 1.25rem;
        background: var(--adt-bg);
        border: 1px solid var(--adt-border);
        border-radius: calc(var(--adt-radius) + 2px);
        box-shadow: var(--ads-shadow-sm);
    }

    .admin-surface .ads-section-title {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.72rem;
        margin-bottom: 0.85rem;
        padding-bottom: 0.65rem;
        border-bottom: 1px solid var(--adt-border);
    }

    .admin-surface .ads-section-title::before {
        content: '';
        width: 3px;
        height: 14px;
        border-radius: 2px;
        background: var(--adt-primary);
        flex-shrink: 0;
    }

    .admin-surface .ads-stat {
        position: relative;
        padding: 1rem 1rem 1rem 1.1rem;
        border-color: #e2e8f0;
        box-shadow: var(--ads-shadow-sm);
        transition: box-shadow 0.18s ease, transform 0.18s ease, border-color 0.18s ease;
        overflow: hidden;
    }

    .admin-surface .ads-stat::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 3px;
        background: var(--adt-primary);
        opacity: 0.55;
    }

    .admin-surface .ads-stat:hover {
        box-shadow: var(--ads-shadow-md);
        transform: translateY(-1px);
        border-color: #d0d9e6;
    }

    .admin-surface .ads-stat-inner {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.65rem;
    }

    .admin-surface .ads-stat-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 8px;
        background: var(--ads-accent-soft);
        color: var(--adt-primary);
        flex-shrink: 0;
    }

    .admin-surface .ads-stat-icon svg {
        width: 16px;
        height: 16px;
    }

    .admin-surface .ads-stat-value {
        font-size: 1.65rem;
        letter-spacing: -0.02em;
    }

    .admin-surface .ads-stat-highlight {
        background: linear-gradient(145deg, #f8faff 0%, #f0f4fc 100%);
        border-color: var(--ads-accent-border);
    }

    .admin-surface .ads-stat-highlight::before {
        opacity: 1;
    }

    .admin-surface .ads-quick-action {
        padding: 1.1rem 0.85rem;
        box-shadow: var(--ads-shadow-sm);
        transition: box-shadow 0.18s ease, transform 0.18s ease, border-color 0.18s ease, color 0.18s ease;
    }

    .admin-surface .ads-quick-action:hover {
        transform: translateY(-2px);
        box-shadow: var(--ads-shadow-md);
    }

    .admin-surface .ads-quick-action-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: var(--ads-accent-soft);
        color: var(--adt-primary);
    }

    .admin-surface .ads-quick-action svg {
        width: 18px;
        height: 18px;
        color: var(--adt-primary);
    }

    .admin-surface .ads-quick-action:hover .ads-quick-action-icon {
        background: rgba(19, 41, 119, 0.11);
    }

    /* Compact table inside surface panels (dashboard activity feeds) */
    .admin-surface .ads-compact-table-wrap {
        overflow-x: auto;
    }

    .admin-surface .ads-compact-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.8125rem;
        margin: 0;
    }

    .admin-surface .ads-compact-table thead {
        background: var(--adt-bg-subtle);
        border-bottom: 1px solid var(--adt-border);
    }

    .admin-surface .ads-compact-table th {
        padding: 0.55rem 1rem;
        font-size: 0.68rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--adt-text-muted);
        border: none;
        white-space: nowrap;
    }

    .admin-surface .ads-compact-table td {
        padding: 0.65rem 1rem;
        border-bottom: 1px solid var(--adt-border);
        color: var(--adt-text);
        vertical-align: middle;
    }

    .admin-surface .ads-compact-table tbody tr:last-child td {
        border-bottom: none;
    }

    .admin-surface .ads-compact-table tbody tr:hover {
        background: #fafbfc;
    }

    .admin-surface .ads-empty-inline {
        text-align: center;
        padding: 2rem 1rem;
        color: var(--adt-text-muted);
        font-size: 0.875rem;
    }

    .admin-surface .ads-filter-form {
        display: flex;
        flex-wrap: wrap;
        gap: 0.65rem;
        align-items: flex-end;
    }

    .admin-surface .ads-filter-field {
        flex: 1;
        min-width: 140px;
    }

    .admin-surface .ads-filter-field label {
        display: block;
        font-size: 0.75rem;
        color: var(--adt-text-muted);
        margin-bottom: 0.25rem;
    }
</style>
@endpush
@endonce
