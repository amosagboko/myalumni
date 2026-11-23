<?php

namespace App\Services\Alumni;

use App\Models\Alumni;
use App\Models\Transaction;
use Illuminate\Support\Collection;

class SkippedPaymentReportService
{
    public const DEFAULT_MIN_YEAR = 2025;

    /**
     * Alumni who have not completed statutory onboarding/cohort fees.
     *
     * @param  array{year?:int|null, source?:string, search?:string, category_id?:int|null, bypassed?:bool}  $filters
     * @return Collection<int, array<string, mixed>>
     */
    public function rows(array $filters = []): Collection
    {
        $year = isset($filters['year']) && $filters['year'] !== '' && $filters['year'] !== null
            ? (int) $filters['year']
            : null;
        $source = $filters['source'] ?? 'all';
        $search = trim((string) ($filters['search'] ?? ''));
        $categoryId = ! empty($filters['category_id']) ? (int) $filters['category_id'] : null;
        $bypassedOnly = (bool) ($filters['bypassed'] ?? false);

        $query = Alumni::query()->with(['user', 'category']);

        if ($year) {
            $query->where('year_of_graduation', $year);
        } else {
            $query->where('year_of_graduation', '>=', self::DEFAULT_MIN_YEAR);
        }

        if ($source === 'online') {
            $query->whereNull('created_by');
        } elseif ($source === 'uploaded') {
            $query->whereNotNull('created_by');
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('matric_number', 'like', '%'.$search.'%')
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%');
                    });
            });
        }

        $rows = collect();

        $query->orderBy('id')->chunkById(100, function ($chunk) use ($rows) {
            $paidCounts = Transaction::query()
                ->whereIn('alumni_id', $chunk->pluck('id'))
                ->where('status', 'paid')
                ->selectRaw('alumni_id, count(*) as paid_count')
                ->groupBy('alumni_id')
                ->pluck('paid_count', 'alumni_id');

            foreach ($chunk as $alumni) {
                if ($alumni->hasCompletedRequiredPayments()) {
                    continue;
                }

                $activeFees = $alumni->getActiveFees();
                $unpaidActive = $activeFees->filter(fn ($fee) => ! $fee->isPaidByAlumni($alumni));
                $bypassedGate = (bool) $alumni->biodata_completed && $unpaidActive->isEmpty();

                $rows->push([
                    'id' => $alumni->id,
                    'name' => $alumni->user?->name,
                    'email' => $alumni->user?->email,
                    'matric_number' => $alumni->matric_number,
                    'year_of_graduation' => $alumni->year_of_graduation,
                    'category' => $alumni->category?->name ?? $alumni->category?->slug,
                    'faculty' => $alumni->faculty,
                    'department' => $alumni->department,
                    'source' => $alumni->created_by ? 'Uploaded' : 'Online',
                    'biodata_completed' => (bool) $alumni->biodata_completed,
                    'bypassed_payment_gate' => $bypassedGate,
                    'outstanding_fees' => $unpaidActive
                        ->map(fn ($fee) => $fee->description ?: $fee->feeType?->name)
                        ->filter()
                        ->implode('; '),
                    'paid_transaction_count' => (int) ($paidCounts[$alumni->id] ?? 0),
                    'created_at' => optional($alumni->created_at)?->format('Y-m-d H:i:s'),
                ]);
            }
        });

        if ($bypassedOnly) {
            return $rows->where('bypassed_payment_gate', true)->values();
        }

        return $rows->values();
    }

    /**
     * @return list<string>
     */
    public function csvHeaders(): array
    {
        return [
            'Name',
            'Email',
            'Matriculation Number',
            'Year of Graduation',
            'Category',
            'Faculty',
            'Department',
            'Source',
            'Biodata Complete',
            'Bypassed Payment Gate',
            'Outstanding Fees',
            'Paid Transactions',
            'Enrolled At',
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return list<string|int>
     */
    public function csvRow(array $row): array
    {
        return [
            $row['name'] ?? '',
            $row['email'] ?? '',
            $row['matric_number'] ?? '',
            $row['year_of_graduation'] ?? '',
            $row['category'] ?? '',
            $row['faculty'] ?? '',
            $row['department'] ?? '',
            $row['source'] ?? '',
            ! empty($row['biodata_completed']) ? 'Yes' : 'No',
            ! empty($row['bypassed_payment_gate']) ? 'Yes' : 'No',
            $row['outstanding_fees'] ?? '',
            $row['paid_transaction_count'] ?? 0,
            $row['created_at'] ?? '',
        ];
    }
}
