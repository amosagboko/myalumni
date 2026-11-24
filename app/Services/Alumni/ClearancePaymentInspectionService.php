<?php

namespace App\Services\Alumni;

use App\Models\Alumni;
use App\Models\FeeTemplate;
use App\Models\Transaction;

class ClearancePaymentInspectionService
{
    public const MISSING_FEE_NOTE = 'Required payments are not complete, and no active fee line is currently shown.';

    /**
     * Payment ledger for one alumni, built from stored transactions and current fee lines.
     *
     * @return array{
     *     name: string,
     *     matric_number: string,
     *     payments_complete: bool,
     *     missing_fee_line: bool,
     *     transactions: list<array<string, mixed>>,
     *     outstanding: list<array<string, string>>
     * }
     */
    public function snapshot(Alumni $alumni): array
    {
        $alumni->loadMissing('user');

        $transactions = Transaction::query()
            ->with(['feeTemplate.feeType', 'items.feeTemplate.feeType'])
            ->where('alumni_id', $alumni->id)
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Transaction $transaction) => $this->transactionRow($transaction))
            ->values()
            ->all();

        $outstanding = $this->outstandingFees($alumni);
        $paymentsComplete = $alumni->hasCompletedRequiredPayments();

        return [
            'name' => $alumni->user?->name ?: '—',
            'matric_number' => $alumni->matric_number ?: '—',
            'payments_complete' => $paymentsComplete,
            'missing_fee_line' => ! $paymentsComplete && $outstanding === [],
            'transactions' => $transactions,
            'outstanding' => $outstanding,
        ];
    }

    /**
     * Names of active fees that still have no paid transaction for this alumnus.
     *
     * @return list<string>
     */
    public function outstandingFeeLabels(Alumni $alumni): array
    {
        return array_column($this->outstandingFees($alumni), 'description');
    }

    /**
     * @return list<array{description: string, amount_expected: string, amount_outstanding: string, status: string}>
     */
    public function outstandingFees(Alumni $alumni): array
    {
        return $alumni->getActiveFees()
            ->filter(fn (FeeTemplate $fee) => ! $fee->isPaidByAlumni($alumni))
            ->map(fn (FeeTemplate $fee) => [
                'description' => $this->feeLabel($fee),
                'amount_expected' => $this->money($fee->amount),
                'amount_outstanding' => $this->money($fee->amount),
                'status' => 'Unpaid',
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function transactionRow(Transaction $transaction): array
    {
        $description = $transaction->feeTemplate?->description
            ?: $transaction->feeTemplate?->feeType?->name
            ?: ($transaction->isCombined() ? 'Combined payment' : 'Payment');

        $items = $transaction->items
            ->map(fn ($item) => [
                'description' => $item->description
                    ?: $item->feeTemplate?->description
                    ?: $item->feeTemplate?->feeType?->name
                    ?: 'Fee item',
                'amount' => $this->money($item->amount),
            ])
            ->values()
            ->all();

        return [
            'description' => $description,
            'amount' => $this->money($transaction->amount),
            'reference' => $transaction->payment_reference ?: '—',
            'provider_reference' => $transaction->payment_provider_reference,
            'paid_at' => $transaction->paid_at?->format('d M Y, H:i'),
            'status' => ucfirst((string) $transaction->status),
            'items' => $items,
        ];
    }

    private function feeLabel(FeeTemplate $fee): string
    {
        return $fee->description ?: $fee->feeType?->name ?: 'Fee';
    }

    private function money(mixed $amount): string
    {
        return number_format((float) $amount, 2);
    }
}
