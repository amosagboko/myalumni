<x-layouts.accountant-linkages title="Payment details | Accountant-Linkages">
    <x-admin.surface-styles />
    <x-admin.data-table-styles />

    <div class="main-content admin-surface admin-data-table" style="padding-right: 1.25rem;">
        <div class="middle-sidebar-bottom">
            <div class="middle-sidebar-left pe-0">
                <div class="ads-page-header">
                    <div>
                        <h1 class="ads-page-title">{{ $alumni->user?->name ?: 'Alumni' }}</h1>
                        <p class="ads-page-subtitle">Payments from 2026 onward. Overall status: {{ $status }}.</p>
                    </div>
                    <div class="ads-page-actions d-flex flex-wrap gap-2">
                        <a href="{{ route('accountant-linkages.payments') }}" class="btn btn-sm btn-outline-secondary">Back</a>
                        <a href="{{ route('accountant-linkages.payments.print', $alumni) }}" class="btn btn-sm ads-btn-primary text-white" target="_blank" rel="noopener">Print</a>
                    </div>
                </div>

                @include('accountant-linkages.payments.partials.ledger')
            </div>
        </div>
    </div>
</x-layouts.accountant-linkages>
