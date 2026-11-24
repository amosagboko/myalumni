<?php

namespace App\Services\Alumni;

use App\Models\Alumni;
use App\Models\FeeTemplate;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AccountantLinkagePaymentService
{
    public const MIN_YEAR = 2026;

    /** Statuses that mean the alumnus has completed that payment. */
    private const PAID_STATUSES = ['paid', 'success', 'successful', 'completed'];

    public function query(array $filters = []): Builder
    {
        $year = isset($filters['year']) && (int) $filters['year'] >= self::MIN_YEAR
            ? (int) $filters['year']
            : null;
        $search = trim((string) ($filters['search'] ?? ''));

        $query = Alumni::query()
            ->with('user')
            ->where('year_of_graduation', '>=', self::MIN_YEAR);

        if ($year) {
            $query->where('year_of_graduation', $year);
        }

        if ($search !== '') {
            $query->where(function (Builder $inner) use ($search) {
                $inner->where('matric_number', 'like', '%'.$search.'%')
                    ->orWhere('faculty', 'like', '%'.$search.'%')
                    ->orWhere('department', 'like', '%'.$search.'%')
                    ->orWhereHas('user', function (Builder $user) use ($search) {
                        $user->where('name', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%');
                    });
            });
        }

        return $query->orderByDesc('year_of_graduation')->orderBy('id');
    }

    /**
     * @return Collection<int, int|string>
     */
    public function years(): Collection
    {
        return Alumni::query()
            ->where('year_of_graduation', '>=', self::MIN_YEAR)
            ->distinct()
            ->orderByDesc('year_of_graduation')
            ->pluck('year_of_graduation');
    }

    public function findEligible(int $alumniId): Alumni
    {
        $alumni = Alumni::query()->with('user')->findOrFail($alumniId);

        if ((int) $alumni->year_of_graduation < self::MIN_YEAR) {
            abort(404);
        }

        return $alumni;
    }

    public function statusLabel(Alumni $alumni): string
    {
        return $this->isFullyPaid($alumni) ? 'Paid' : 'Pending';
    }

    public function isFullyPaid(Alumni $alumni): bool
    {
        $rows = $this->ledger($alumni);

        if ($rows === []) {
            return false;
        }

        foreach ($rows as $row) {
            if ($row['status'] !== 'Paid') {
                return false;
            }
        }

        return true;
    }

    public function photoUrl(Alumni $alumni): string
    {
        $avatar = $alumni->user?->avatar;

        return $avatar
            ? asset('storage/'.$avatar)
            : asset('images/default-avatar.png');
    }

    /**
     * Paid and pending payments for fee years from 2026, plus unpaid 2026+ fees with no transaction yet.
     *
     * @return list<array{description: string, amount: string, reference: string, date: string, status: string}>
     */
    public function ledger(Alumni $alumni): array
    {
        $transactions = $this->scopedTransactions($alumni);
        $coveredFeeIds = [];
        $rows = [];

        foreach ($transactions as $transaction) {
            $feeIds = collect([$transaction->fee_template_id])
                ->merge($transaction->items->pluck('fee_template_id'))
                ->filter()
                ->map(fn ($id) => (int) $id);

            foreach ($feeIds as $feeId) {
                $coveredFeeIds[$feeId] = true;
            }

            $rows[] = [
                'description' => $this->transactionDescription($transaction),
                'amount' => $this->money($transaction->amount),
                'reference' => $transaction->payment_reference ?: '—',
                'date' => $transaction->paid_at?->format('d M Y')
                    ?: $transaction->created_at?->format('d M Y')
                    ?: '—',
                'status' => $this->isPaidStatus($transaction->status) ? 'Paid' : 'Pending',
            ];
        }

        $outstanding = $alumni->getActiveFees()
            ->filter(fn (FeeTemplate $fee) => $this->feeIsInScope($fee))
            ->filter(fn (FeeTemplate $fee) => ! $fee->isPaidByAlumni($alumni))
            ->reject(fn (FeeTemplate $fee) => isset($coveredFeeIds[$fee->id]));

        foreach ($outstanding as $fee) {
            $rows[] = [
                'description' => $fee->description ?: $fee->feeType?->name ?: 'Fee',
                'amount' => $this->money($fee->amount),
                'reference' => '—',
                'date' => '—',
                'status' => 'Pending',
            ];
        }

        return $rows;
    }

    /**
     * @return Collection<int, Transaction>
     */
    private function scopedTransactions(Alumni $alumni): Collection
    {
        return Transaction::query()
            ->with(['feeTemplate.feeType', 'items.feeTemplate.feeType'])
            ->where('alumni_id', $alumni->id)
            ->whereIn('status', array_merge(self::PAID_STATUSES, ['pending']))
            ->where(function (Builder $query) {
                $query->whereHas('feeTemplate', fn (Builder $fee) => $this->scopeFeeYear($fee))
                    ->orWhereHas('items.feeTemplate', fn (Builder $fee) => $this->scopeFeeYear($fee));
            })
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->get();
    }

    private function scopeFeeYear(Builder $fee): void
    {
        $fee->where(function (Builder $year) {
            $year->where('graduation_year', '>=', self::MIN_YEAR)
                ->orWhere('graduation_year', FeeTemplate::PAYMENT_YEAR_ALL);
        });
    }

    private function feeIsInScope(FeeTemplate $fee): bool
    {
        $year = $fee->graduation_year;

        if ($year === null) {
            return false;
        }

        return (int) $year === FeeTemplate::PAYMENT_YEAR_ALL
            || (int) $year >= self::MIN_YEAR;
    }

    private function transactionDescription(Transaction $transaction): string
    {
        if ($transaction->items->isNotEmpty()) {
            $labels = $transaction->items
                ->map(fn ($item) => $item->description
                    ?: $item->feeTemplate?->description
                    ?: $item->feeTemplate?->feeType?->name)
                ->filter()
                ->unique()
                ->implode('; ');

            if ($labels !== '') {
                return $labels;
            }
        }

        return $transaction->feeTemplate?->description
            ?: $transaction->feeTemplate?->feeType?->name
            ?: ($transaction->isCombined() ? 'Combined payment' : 'Payment');
    }

    private function isPaidStatus(?string $status): bool
    {
        return in_array(strtolower((string) $status), self::PAID_STATUSES, true);
    }

    private function money(mixed $amount): string
    {
        return number_format((float) $amount, 2);
    }
}
