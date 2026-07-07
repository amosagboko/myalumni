{{-- Shared design tokens for admin UI (tables + surfaces). --}}
@once
@push('styles')
<style>
    .admin-ui,
    .admin-surface,
    .admin-data-table {
        --adt-border: #e8ecf1;
        --adt-bg: #ffffff;
        --adt-bg-subtle: #f7f8fa;
        --adt-text: #1a1d26;
        --adt-text-muted: #6b7280;
        --adt-primary: #132977;
        --adt-radius: 10px;
        --ads-shadow-sm: 0 1px 2px rgba(15, 23, 42, 0.05), 0 1px 3px rgba(15, 23, 42, 0.04);
        --ads-shadow-md: 0 4px 14px rgba(15, 23, 42, 0.07);
        --ads-accent-soft: rgba(19, 41, 119, 0.07);
        --ads-accent-border: rgba(19, 41, 119, 0.14);
        --ads-hero-gradient: linear-gradient(135deg, #fafbfd 0%, #f3f6fb 100%);
    }
</style>
@endpush
@endonce
