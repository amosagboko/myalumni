{{--
    Admin data-table styles (lists / tables only).
    Also include <x-admin.surface-styles /> for page headers, stats, and modals.
    Wrap content in <div class="admin-data-table"> and use adt-* classes.
--}}
@include('components.admin.ui-tokens')
@once('admin-data-table-styles')
<style>
    .admin-data-table .adt-panel {
        background: var(--adt-bg);
        border: 1px solid var(--adt-border);
        border-radius: var(--adt-radius);
        overflow: hidden;
    }

    .admin-data-table .adt-toolbar {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--adt-border);
        background: var(--adt-bg-subtle);
    }

    .admin-data-table .adt-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
    }

    .admin-data-table .adt-search {
        position: relative;
        flex: 1;
        min-width: 200px;
        max-width: 280px;
    }

    .admin-data-table .adt-search .form-control {
        padding-left: 2.1rem;
        border-color: var(--adt-border);
        background: var(--adt-bg);
    }

    .admin-data-table .adt-search .form-control:focus {
        border-color: var(--adt-primary);
        box-shadow: 0 0 0 3px rgba(19, 41, 119, 0.08);
    }

    .admin-data-table .adt-search-icon {
        position: absolute;
        left: 0.65rem;
        top: 50%;
        transform: translateY(-50%);
        width: 15px;
        height: 15px;
        color: var(--adt-text-muted);
        pointer-events: none;
    }

    .admin-data-table .adt-select {
        width: auto;
        min-width: 130px;
        border-color: var(--adt-border);
        background: var(--adt-bg);
        font-size: 0.8125rem;
    }

    .admin-data-table .adt-select-narrow {
        min-width: 95px;
    }

    .admin-data-table .adt-alert {
        margin: 1rem 1.25rem 0;
        padding: 0.65rem 1rem;
        border-radius: 6px;
        font-size: 0.875rem;
    }

    .admin-data-table .adt-alert-success {
        background: #f0fdf4;
        color: #166534;
        border: 1px solid #bbf7d0;
    }

    .admin-data-table .adt-alert-error {
        background: #fef2f2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }

    .admin-data-table .adt-table-wrap {
        overflow-x: auto;
    }

    .admin-data-table .adt-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.875rem;
    }

    .admin-data-table .adt-table thead {
        background: var(--adt-bg-subtle);
        border-bottom: 1px solid var(--adt-border);
    }

    .admin-data-table .adt-table th {
        padding: 0.7rem 1.25rem;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--adt-text-muted);
        white-space: nowrap;
        border: none;
    }

    .admin-data-table .adt-th-sortable {
        cursor: pointer;
        user-select: none;
    }

    .admin-data-table .adt-th-sortable:hover {
        color: var(--adt-text);
    }

    .admin-data-table .adt-sort-indicator {
        margin-left: 0.2rem;
        font-size: 0.65rem;
    }

    .admin-data-table .adt-th-actions {
        width: 120px;
        text-align: right;
    }

    .admin-data-table .adt-table tbody tr {
        border-bottom: 1px solid var(--adt-border);
        transition: background 0.12s ease;
    }

    .admin-data-table .adt-table tbody tr:last-child {
        border-bottom: none;
    }

    .admin-data-table .adt-table tbody tr:hover {
        background: #fafbfc;
    }

    .admin-data-table .adt-table td {
        padding: 0.85rem 1.25rem;
        vertical-align: middle;
        border: none;
        color: var(--adt-text);
    }

    .admin-data-table .adt-user-cell {
        display: flex;
        align-items: center;
        gap: 0.65rem;
    }

    .admin-data-table .adt-avatar {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        overflow: hidden;
        flex-shrink: 0;
        background: var(--adt-bg-subtle);
        border: 1px solid var(--adt-border);
    }

    .admin-data-table .adt-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .admin-data-table .adt-user-name {
        font-weight: 500;
        color: var(--adt-text);
    }

    .admin-data-table .adt-email {
        color: var(--adt-text-muted);
        word-break: break-word;
        max-width: 220px;
    }

    .admin-data-table .adt-tag {
        display: inline-block;
        font-size: 0.8125rem;
        color: var(--adt-text);
        background: var(--adt-bg-subtle);
        border: 1px solid var(--adt-border);
        border-radius: 4px;
        padding: 0.15rem 0.5rem;
    }

    .admin-data-table .adt-muted {
        color: var(--adt-text-muted);
        font-size: 0.8125rem;
    }

    .admin-data-table .adt-status {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.8125rem;
        font-weight: 500;
    }

    .admin-data-table .adt-status-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .admin-data-table .adt-status-active {
        color: #15803d;
    }

    .admin-data-table .adt-status-active .adt-status-dot {
        background: #22c55e;
    }

    .admin-data-table .adt-status-suspended {
        color: #b45309;
    }

    .admin-data-table .adt-status-suspended .adt-status-dot {
        background: #f59e0b;
    }

    .admin-data-table .adt-status-pending {
        color: #b45309;
    }

    .admin-data-table .adt-status-pending .adt-status-dot {
        background: #f59e0b;
    }

    .admin-data-table .adt-status-inactive {
        color: var(--adt-text-muted);
    }

    .admin-data-table .adt-status-inactive .adt-status-dot {
        background: #9ca3af;
    }

    .admin-data-table .adt-actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.25rem;
    }

    .admin-data-table .adt-action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        padding: 0;
        border: 1px solid var(--adt-border);
        border-radius: 6px;
        background: var(--adt-bg);
        color: var(--adt-text-muted);
        cursor: pointer;
        transition: background 0.12s, color 0.12s, border-color 0.12s;
    }

    .admin-data-table .adt-action-btn:hover:not(:disabled) {
        background: var(--adt-bg-subtle);
        color: var(--adt-primary);
        border-color: #c5cdd8;
    }

    .admin-data-table .adt-action-btn:disabled {
        opacity: 0.35;
        cursor: not-allowed;
    }

    .admin-data-table .adt-action-btn svg {
        stroke: currentColor;
    }

    .admin-data-table .adt-action-danger {
        color: #dc2626;
        border-color: #fecaca;
        background: #fef2f2;
    }

    .admin-data-table .adt-action-danger:hover:not(:disabled) {
        color: #dc2626;
        border-color: #fecaca;
        background: #fef2f2;
    }

    .admin-data-table .adt-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.75rem;
        padding: 0.85rem 1.25rem;
        border-top: 1px solid var(--adt-border);
        background: var(--adt-bg-subtle);
    }

    .admin-data-table .adt-footer-count {
        font-size: 0.8125rem;
        color: var(--adt-text-muted);
    }

    .admin-data-table .adt-pagination .pagination {
        margin: 0;
    }

    .admin-data-table .adt-pagination .page-link {
        font-size: 0.8125rem;
        padding: 0.3rem 0.65rem;
        color: var(--adt-text-muted);
        border-color: var(--adt-border);
    }

    .admin-data-table .adt-pagination .page-item.active .page-link {
        background: var(--adt-primary);
        border-color: var(--adt-primary);
        color: #ffffff;
    }

    .admin-data-table .adt-empty {
        text-align: center;
        padding: 3.5rem 1.5rem;
    }

    .admin-data-table .adt-empty-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: var(--adt-bg-subtle);
        color: var(--adt-text-muted);
        margin-bottom: 1rem;
    }

    .admin-data-table .adt-empty-title {
        font-size: 1rem;
        font-weight: 600;
        color: var(--adt-text);
        margin: 0 0 0.35rem;
    }

    .admin-data-table .adt-empty-text {
        font-size: 0.875rem;
        color: var(--adt-text-muted);
        margin: 0;
    }
</style>
@endonce
