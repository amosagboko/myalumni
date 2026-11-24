@if ($paymentInspection)
    <div
        class="ads-modal-overlay"
        wire:click.self="closePaymentDetails"
        role="dialog"
        aria-modal="true"
        aria-labelledby="clearancePaymentModalTitle"
    >
        <div class="ads-modal-dialog ads-modal-dialog-lg">
            <div class="ads-modal-card">
                <div class="ads-modal-header">
                    <h6 class="ads-modal-title" id="clearancePaymentModalTitle">Payment details</h6>
                    <button type="button" class="btn-close" wire:click="closePaymentDetails" aria-label="Close"></button>
                </div>
                <div class="ads-modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <div class="ads-modal-detail-label">Full name</div>
                            <div class="ads-modal-detail-value mb-0">{{ $paymentInspection['name'] }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="ads-modal-detail-label">Matriculation number</div>
                            <div class="ads-modal-detail-value mb-0">{{ $paymentInspection['matric_number'] }}</div>
                        </div>
                    </div>

                    <div class="ads-modal-detail-label">Payment status</div>
                    <div class="ads-modal-detail-value">{{ $paymentInspection['payments_complete'] ? 'Paid' : 'Unpaid' }}</div>

                    <h6 class="fw-600 mb-2">Recorded transactions</h6>
                    @forelse ($paymentInspection['transactions'] as $transaction)
                        <div class="border rounded-3 p-3 mb-2">
                            <div class="d-flex flex-wrap justify-content-between gap-2">
                                <div class="fw-medium">{{ $transaction['description'] }}</div>
                                <span class="adt-status {{ $transaction['status'] === 'Paid' ? 'adt-status-active' : 'adt-status-suspended' }}">
                                    <span class="adt-status-dot"></span>
                                    {{ $transaction['status'] }}
                                </span>
                            </div>
                            <div class="small text-muted mt-2">Amount: &#8358;{{ $transaction['amount'] }}</div>
                            <div class="small text-muted">Reference: {{ $transaction['reference'] }}</div>
                            @if (! empty($transaction['provider_reference']))
                                <div class="small text-muted">Provider reference: {{ $transaction['provider_reference'] }}</div>
                            @endif
                            <div class="small text-muted">Payment date: {{ $transaction['paid_at'] ?: '—' }}</div>
                            @if (! empty($transaction['items']))
                                <ul class="small mb-0 mt-2 ps-3">
                                    @foreach ($transaction['items'] as $item)
                                        <li>{{ $item['description'] }} — &#8358;{{ $item['amount'] }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @empty
                        <p class="small text-muted">No payment transactions recorded.</p>
                    @endforelse

                    <h6 class="fw-600 mt-3 mb-2">Outstanding fees</h6>
                    @if ($paymentInspection['missing_fee_line'])
                        <p class="small text-muted">{{ \App\Services\Alumni\ClearancePaymentInspectionService::MISSING_FEE_NOTE }}</p>
                    @endif
                    @forelse ($paymentInspection['outstanding'] as $fee)
                        <div class="border rounded-3 p-3 mb-2">
                            <div class="d-flex flex-wrap justify-content-between gap-2">
                                <div class="fw-medium">{{ $fee['description'] }}</div>
                                <span class="adt-status adt-status-suspended">
                                    <span class="adt-status-dot"></span>
                                    {{ $fee['status'] }}
                                </span>
                            </div>
                            <div class="small text-muted mt-2">Amount expected: &#8358;{{ $fee['amount_expected'] }}</div>
                            <div class="small text-muted">Amount outstanding: &#8358;{{ $fee['amount_outstanding'] }}</div>
                        </div>
                    @empty
                        @unless ($paymentInspection['missing_fee_line'])
                            <p class="small text-muted mb-0">No outstanding fees.</p>
                        @endunless
                    @endforelse
                </div>
                <div class="ads-modal-footer">
                    <button type="button" class="btn btn-light btn-sm" wire:click="closePaymentDetails">Close</button>
                </div>
            </div>
        </div>
    </div>
@endif
