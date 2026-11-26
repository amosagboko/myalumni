<?php

namespace App\Services\Alumni;

use App\Models\Alumni;
use App\Models\FeeTemplate;
use App\Models\FeeType;
use App\Models\PaymentStructureItem;
use App\Models\Transaction;
use App\Services\AlumniDuesService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ClearancePaymentInspectionService
{
    /** @var array<int, ?Carbon> */
    private array $cohortCompletedAt = [];

    /**
     * Payment ledger for one alumni, built from stored transactions and current fee lines.
     *
     * @return array{
     *     name: string,
     *     matric_number: string,
     *     payments_complete: bool,
     *     missing_fee_line: bool,
     *     transactions: list<array<string, mixed>>,
     *     outstanding: list<array<string, string>>,
     *     pending_note: ?string
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
            ->reject(fn (Transaction $transaction) => $this->isAnnualDueTransaction($transaction))
            ->map(fn (Transaction $transaction) => $this->transactionRow($transaction))
            ->values()
            ->all();

        $outstanding = $this->outstandingFees($alumni);
        $paymentsComplete = $this->paymentsComplete($alumni);

        return [
            'name' => $alumni->user?->name ?: '—',
            'matric_number' => $alumni->matric_number ?: '—',
            'payments_complete' => $paymentsComplete,
            'missing_fee_line' => ! $paymentsComplete && $outstanding === [],
            'transactions' => $transactions,
            'outstanding' => $outstanding,
            'pending_note' => $paymentsComplete ? null : $this->pendingPaymentNote($outstanding),
        ];
    }

    /**
     * Clearance payment is complete when the cohort fees are paid.
     * A combined-structure fee attached after that payment does not reopen it.
     */
    public function paymentsComplete(Alumni $alumni): bool
    {
        return $alumni->hasCompletedRequiredPayments() && $this->outstandingFees($alumni) === [];
    }

    /**
     * @return array{paid: bool, note: ?string}
     */
    public function paymentColumn(Alumni $alumni): array
    {
        $outstanding = $this->outstandingFees($alumni);
        $paid = $this->paymentsComplete($alumni);

        return [
            'paid' => $paid,
            'note' => $paid ? null : $this->pendingPaymentNote($outstanding),
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
        $seen = [];

        return $this->candidateFees($alumni)
            ->filter(function (FeeTemplate $fee) use ($alumni, &$seen) {
                if (isset($seen[$fee->id]) || $fee->isPaidByAlumni($alumni) || $this->addedAfterCohortPayment($alumni, $fee)) {
                    return false;
                }

                $seen[$fee->id] = true;

                return true;
            })
            ->map(fn (FeeTemplate $fee) => [
                'description' => $this->feeLabel($fee),
                'amount_expected' => $this->money($fee->amount),
                'amount_outstanding' => $this->money($fee->amount),
                'status' => 'Pending',
            ])
            ->values()
            ->all();
    }

    /**
     * @param  list<array{description: string, amount_expected: string, amount_outstanding: string, status: string}>  $outstanding
     */
    private function pendingPaymentNote(array $outstanding): string
    {
        $names = array_column($outstanding, 'description');

        if ($names === []) {
            return 'Required payments are not complete; a required fee is pending.';
        }

        if (count($names) === 1) {
            return 'Required payments are not complete; '.$names[0].' is pending.';
        }

        $last = array_pop($names);

        return 'Required payments are not complete; '.implode(', ', $names).' and '.$last.' are pending.';
    }

    /**
     * @return Collection<int, FeeTemplate>
     */
    private function candidateFees(Alumni $alumni): Collection
    {
        $categoryId = app(AlumniDuesService::class)->resolveEffectiveCategoryId($alumni);

        $cohort = FeeTemplate::query()
            ->with('feeType')
            ->where('is_active', true)
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->where(function ($query) use ($alumni) {
                $query->where('graduation_year', $alumni->year_of_graduation)
                    ->orWhere('graduation_year', FeeTemplate::PAYMENT_YEAR_ALL);
            })
            ->where(function ($query) {
                $query->whereNull('fee_purpose')
                    ->orWhere('fee_purpose', '')
                    ->orWhere('fee_purpose', FeeTemplate::PURPOSE_ONBOARDING);
            })
            ->get()
            ->filter(fn (FeeTemplate $fee) => $fee->isValid() && ! $fee->feeType?->isEoiFee());

        return $alumni->getActiveFees()
            ->concat($cohort)
            ->unique('id')
            ->reject(fn (FeeTemplate $fee) => $fee->feeType?->code === FeeType::ANNUAL_DUE_CODE)
            ->values();
    }

    /**
     * A non-onboarding fee that was attached to a combined payment only after
     * this alumnus had already paid the cohort fees.
     */
    private function addedAfterCohortPayment(Alumni $alumni, FeeTemplate $fee): bool
    {
        $fee->loadMissing('feeType');

        if ($fee->isOnboarding() || $fee->feeType?->isOnboardingFee() || $fee->feeType?->code === 'subscription') {
            return false;
        }

        $completedAt = $this->cohortPaymentCompletedAt($alumni);
        if (! $completedAt) {
            return false;
        }

        $attachedAt = PaymentStructureItem::query()
            ->where('fee_template_id', $fee->id)
            ->whereHas('structure', fn ($query) => $query->combined()->active())
            ->orderBy('created_at')
            ->value('created_at');

        return $attachedAt !== null && Carbon::parse($attachedAt)->gt($completedAt);
    }

    private function cohortPaymentCompletedAt(Alumni $alumni): ?Carbon
    {
        if (array_key_exists($alumni->id, $this->cohortCompletedAt)) {
            return $this->cohortCompletedAt[$alumni->id];
        }

        if (! $alumni->hasCompletedRequiredPayments()) {
            return $this->cohortCompletedAt[$alumni->id] = null;
        }

        $categoryId = app(AlumniDuesService::class)->resolveEffectiveCategoryId($alumni);
        $templateIds = FeeTemplate::query()
            ->where(function ($query) use ($alumni, $categoryId) {
                $query->where(function ($cohort) use ($alumni, $categoryId) {
                    $cohort->when($categoryId, fn ($inner) => $inner->where('category_id', $categoryId))
                        ->where('graduation_year', $alumni->year_of_graduation)
                        ->where(function ($purpose) {
                            $purpose->where('fee_purpose', FeeTemplate::PURPOSE_ONBOARDING)
                                ->orWhereHas('feeType', fn ($type) => $type->whereIn('code', FeeType::ONBOARDING_FEE_CODES));
                        });
                })->orWhere(function ($subscription) use ($alumni) {
                    $subscription->where(function ($year) use ($alumni) {
                        $year->where('graduation_year', $alumni->year_of_graduation)
                            ->orWhere('graduation_year', FeeTemplate::PAYMENT_YEAR_ALL);
                    })->whereHas('feeType', fn ($type) => $type->where('code', 'subscription')->where('is_active', true));
                });
            })
            ->pluck('id');

        $latest = null;
        if ($templateIds->isNotEmpty()) {
            $transactions = Transaction::query()
                ->where('alumni_id', $alumni->id)
                ->where('status', 'paid')
                ->where(function ($query) use ($templateIds) {
                    $query->whereIn('fee_template_id', $templateIds)
                        ->orWhereHas('items', fn ($items) => $items->whereIn('fee_template_id', $templateIds));
                })
                ->get(['paid_at', 'created_at']);

            foreach ($transactions as $transaction) {
                $at = $transaction->paid_at ?? $transaction->created_at;
                if ($at && ($latest === null || $at->gt($latest))) {
                    $latest = $at;
                }
            }
        }

        return $this->cohortCompletedAt[$alumni->id] = $latest;
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
            ->reject(fn ($item) => $this->isAnnualDueFee($item->feeTemplate))
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

    private function isAnnualDueTransaction(Transaction $transaction): bool
    {
        $transaction->loadMissing(['feeTemplate.feeType', 'items.feeTemplate.feeType']);

        if ($transaction->items->isEmpty()) {
            return $this->isAnnualDueFee($transaction->feeTemplate);
        }

        return $transaction->items->every(fn ($item) => $this->isAnnualDueFee($item->feeTemplate));
    }

    private function isAnnualDueFee(?FeeTemplate $fee): bool
    {
        return $fee?->feeType?->code === FeeType::ANNUAL_DUE_CODE;
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
