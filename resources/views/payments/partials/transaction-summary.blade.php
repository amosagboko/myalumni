@php
    $dateLabel = $dateLabel ?? 'Date';
    $dateValue = $dateValue ?? $transaction->paid_at ?? $transaction->created_at;
    $showStatus = $showStatus ?? false;
    $statusClass = match ($transaction->status) {
        'paid' => 'bg-success',
        'failed' => 'bg-danger',
        default => 'bg-warning text-dark',
    };
@endphp

<div class="table-responsive mb-4 {{ $transaction->isCombined() ? 'text-start' : '' }}">
    <table class="table table-bordered align-middle mb-0">
        <tbody>
            <tr>
                <th class="bg-light text-nowrap" style="width: 40%;">Payment</th>
                <td>{{ $transaction->display_description }}</td>
            </tr>
            <tr>
                <th class="bg-light">Payment reference</th>
                <td><code class="small">{{ $transaction->payment_reference }}</code></td>
            </tr>
            @if ($transaction->payment_provider_reference)
                <tr>
                    <th class="bg-light">Credo reference</th>
                    <td><code class="small">{{ $transaction->payment_provider_reference }}</code></td>
                </tr>
            @endif
            <tr>
                <th class="bg-light">Amount</th>
                <td class="fw-semibold">₦{{ number_format($transaction->amount, 2) }}</td>
            </tr>
            @if ($showStatus)
                <tr>
                    <th class="bg-light">Status</th>
                    <td>
                        <span class="badge {{ $statusClass }}">{{ ucfirst($transaction->status) }}</span>
                    </td>
                </tr>
            @endif
            <tr>
                <th class="bg-light">{{ $dateLabel }}</th>
                <td>{{ $dateValue?->format('M d, Y H:i A') }}</td>
            </tr>
        </tbody>
    </table>
</div>

@if ($transaction->isCombined() && $transaction->items->isNotEmpty())
    <div class="table-responsive mb-4 text-start">
        <table class="table table-sm table-bordered mb-0">
            <thead>
                <tr>
                    <th>Item</th>
                    <th class="text-end">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transaction->items as $item)
                    <tr>
                        <td>{{ $item->description }}</td>
                        <td class="text-end">₦{{ number_format($item->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th>Total</th>
                    <th class="text-end">₦{{ number_format($transaction->amount, 2) }}</th>
                </tr>
            </tfoot>
        </table>
    </div>
@endif
