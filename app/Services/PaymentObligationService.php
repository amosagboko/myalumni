<?php

namespace App\Services;

use App\Models\Alumni;
use App\Models\FeeTemplate;
use App\Models\PaymentAttempt;
use App\Models\PaymentStructure;
use App\Models\Transaction;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PaymentObligationService
{
    /**
     * The open dues obligation for this alumnus and fee, creating one only when none exists.
     *
     * @param  array<string, mixed>  $createAttributes
     */
    public function claimFee(Alumni $alumni, FeeTemplate $fee, array $createAttributes): Transaction
    {
        return DB::transaction(function () use ($alumni, $fee, $createAttributes) {
            if ($this->feeIsPaid($alumni->id, $fee->id)) {
                throw new \RuntimeException('This fee has already been paid.');
            }

            $open = $this->lockOpenFee($alumni->id, $fee->id);
            if ($open) {
                $this->prepareForGateway($open);
                $open->amount = $createAttributes['amount'] ?? $open->amount;
                $open->status = 'pending';
                $open->save();

                return $open->fresh();
            }

            try {
                return Transaction::create(array_merge($createAttributes, [
                    'alumni_id' => $alumni->id,
                    'fee_template_id' => $fee->id,
                    'status' => 'pending',
                    'is_eoi' => false,
                    'obligation_id' => null,
                ]));
            } catch (UniqueConstraintViolationException $e) {
                $open = $this->lockOpenFee($alumni->id, $fee->id);
                if (! $open) {
                    throw $e;
                }

                return $open;
            }
        });
    }

    /**
     * The open combined obligation for this structure, creating one only when none exists.
     *
     * @param  array<string, mixed>  $createAttributes
     */
    public function claimCombined(Alumni $alumni, PaymentStructure $structure, array $createAttributes): Transaction
    {
        return DB::transaction(function () use ($alumni, $structure, $createAttributes) {
            $open = $this->lockOpenCombined($alumni->id, $structure->id);
            if ($open) {
                $this->prepareForGateway($open);
                $open->amount = $createAttributes['amount'] ?? $open->amount;
                $open->status = 'pending';
                $open->save();

                return $open->fresh();
            }

            try {
                return Transaction::create(array_merge($createAttributes, [
                    'alumni_id' => $alumni->id,
                    'fee_template_id' => null,
                    'payment_structure_id' => $structure->id,
                    'status' => 'pending',
                    'is_eoi' => false,
                    'obligation_id' => null,
                ]));
            } catch (UniqueConstraintViolationException $e) {
                $open = $this->lockOpenCombined($alumni->id, $structure->id);
                if (! $open) {
                    throw $e;
                }

                return $open;
            }
        });
    }

    public function firstOpenFee(int $alumniId, int $feeId): ?Transaction
    {
        return Transaction::query()
            ->where('alumni_id', $alumniId)
            ->where('fee_template_id', $feeId)
            ->whereNull('obligation_id')
            ->where('is_eoi', false)
            ->whereIn('status', ['pending', 'failed'])
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Start another Credo checkout on the same obligation.
     */
    public function prepareForGateway(Transaction $transaction): Transaction
    {
        $transaction = $transaction->canonicalObligation();

        if ($transaction->isPaid()) {
            return $transaction;
        }

        $started = $transaction->payment_link || $transaction->payment_provider_reference;
        if ($transaction->status === 'failed' || $started) {
            $this->archiveCurrentAttempt(
                $transaction,
                $transaction->status === 'failed' ? 'failed' : 'abandoned'
            );
            $transaction->payment_reference = $this->freshReference($transaction);
            $transaction->payment_link = null;
            $transaction->payment_provider_reference = null;
        }

        $transaction->status = 'pending';
        $transaction->save();

        return $transaction;
    }

    /**
     * Link extra dues rows to one obligation. Rows are not deleted.
     */
    public function linkDuplicateObligations(): int
    {
        $this->markEoiTransactions();

        $linked = $this->linkUnpaidFeeGroups();
        $linked += $this->linkPaidFeeGroups();
        $linked += $this->linkUnpaidCombinedGroups();
        $linked += $this->linkPaidCombinedGroups();
        if (Schema::hasTable('payment_attempts')) {
            $this->recordAttemptsForLinkedRows();
        }

        return $linked;
    }

    public function recordGatewayAttempt(Transaction $transaction, ?string $providerReference, ?string $paymentLink): void
    {
        $obligation = $transaction->canonicalObligation();

        PaymentAttempt::updateOrCreate(
            ['payment_reference' => $transaction->payment_reference],
            [
                'transaction_id' => $obligation->id,
                'payment_provider_reference' => $providerReference,
                'payment_link' => $paymentLink,
                'status' => 'pending',
                'amount' => $transaction->amount,
            ]
        );
    }

    /**
     * A failed provider notice for an older attempt must not reopen or replace the current obligation.
     */
    public function recordFailure(Transaction $matched, array $detailExtras = []): Transaction
    {
        $obligation = $matched->canonicalObligation();
        $targetsCurrent = $matched->id === $obligation->id
            || $matched->payment_reference === $obligation->payment_reference;

        PaymentAttempt::updateOrCreate(
            ['payment_reference' => $matched->payment_reference],
            [
                'transaction_id' => $obligation->id,
                'payment_provider_reference' => $matched->payment_provider_reference,
                'payment_link' => $matched->payment_link,
                'status' => 'failed',
                'amount' => $matched->amount,
                'payload' => $detailExtras,
            ]
        );

        if ($matched->id !== $obligation->id && ! $matched->isPaid()) {
            $matched->update(['status' => 'failed']);
        }

        if ($targetsCurrent && ! $obligation->isPaid()) {
            $details = is_array($obligation->payment_details) ? $obligation->payment_details : [];
            $obligation->update([
                'status' => 'failed',
                'payment_details' => array_merge($details, $detailExtras),
            ]);
        }

        return $obligation->fresh();
    }

    protected function feeIsPaid(int $alumniId, int $feeId): bool
    {
        return Transaction::query()
            ->where('alumni_id', $alumniId)
            ->where('fee_template_id', $feeId)
            ->whereNull('obligation_id')
            ->where('is_eoi', false)
            ->where('status', 'paid')
            ->lockForUpdate()
            ->exists();
    }

    protected function lockOpenFee(int $alumniId, int $feeId): ?Transaction
    {
        return Transaction::query()
            ->where('alumni_id', $alumniId)
            ->where('fee_template_id', $feeId)
            ->whereNull('obligation_id')
            ->where('is_eoi', false)
            ->whereIn('status', ['pending', 'failed'])
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();
    }

    protected function lockOpenCombined(int $alumniId, int $structureId): ?Transaction
    {
        return Transaction::query()
            ->where('alumni_id', $alumniId)
            ->where('payment_structure_id', $structureId)
            ->whereNull('fee_template_id')
            ->whereNull('obligation_id')
            ->where('is_eoi', false)
            ->whereIn('status', ['pending', 'failed'])
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();
    }

    protected function archiveCurrentAttempt(Transaction $transaction, string $status): void
    {
        if (! $transaction->payment_reference) {
            return;
        }

        PaymentAttempt::updateOrCreate(
            ['payment_reference' => $transaction->payment_reference],
            [
                'transaction_id' => $transaction->id,
                'payment_provider_reference' => $transaction->payment_provider_reference,
                'payment_link' => $transaction->payment_link,
                'status' => $status,
                'amount' => $transaction->amount,
            ]
        );
    }

    protected function freshReference(Transaction $transaction): string
    {
        $year = $transaction->metadata['payment_year'] ?? null;
        if ($year) {
            return 'DUE-'.$year.'-'.strtoupper(Str::random(8));
        }

        return 'ALUMNI-'.strtoupper(Str::random(10));
    }

    protected function markEoiTransactions(): void
    {
        DB::statement("
            UPDATE transactions t
            INNER JOIN fee_templates ft ON ft.id = t.fee_template_id
            INNER JOIN fee_types fty ON fty.id = ft.fee_type_id
            SET t.is_eoi = 1
            WHERE fty.code = 'screening_fee' OR fty.code LIKE 'eoi-%'
        ");
    }

    protected function linkUnpaidFeeGroups(): int
    {
        return DB::update("
            UPDATE transactions t
            INNER JOIN (
                SELECT alumni_id, fee_template_id, MAX(id) AS keeper_id
                FROM transactions
                WHERE deleted_at IS NULL
                  AND is_eoi = 0
                  AND obligation_id IS NULL
                  AND fee_template_id IS NOT NULL
                GROUP BY alumni_id, fee_template_id
                HAVING COUNT(*) > 1 AND SUM(status = 'paid') = 0
            ) g ON g.alumni_id = t.alumni_id AND g.fee_template_id = t.fee_template_id
            SET t.obligation_id = g.keeper_id
            WHERE t.id <> g.keeper_id
              AND t.obligation_id IS NULL
              AND t.deleted_at IS NULL
        ");
    }

    protected function linkPaidFeeGroups(): int
    {
        return DB::update("
            UPDATE transactions t
            INNER JOIN (
                SELECT alumni_id, fee_template_id, MAX(CASE WHEN status = 'paid' THEN id END) AS keeper_id
                FROM transactions
                WHERE deleted_at IS NULL
                  AND is_eoi = 0
                  AND obligation_id IS NULL
                  AND fee_template_id IS NOT NULL
                GROUP BY alumni_id, fee_template_id
                HAVING SUM(status = 'paid') > 0 AND COUNT(*) > 1
            ) g ON g.alumni_id = t.alumni_id AND g.fee_template_id = t.fee_template_id
            SET t.obligation_id = g.keeper_id
            WHERE t.id <> g.keeper_id
              AND t.status <> 'paid'
              AND t.obligation_id IS NULL
              AND t.deleted_at IS NULL
        ");
    }

    protected function linkUnpaidCombinedGroups(): int
    {
        return DB::update("
            UPDATE transactions t
            INNER JOIN (
                SELECT alumni_id, payment_structure_id, MAX(id) AS keeper_id
                FROM transactions
                WHERE deleted_at IS NULL
                  AND is_eoi = 0
                  AND obligation_id IS NULL
                  AND payment_structure_id IS NOT NULL
                  AND fee_template_id IS NULL
                GROUP BY alumni_id, payment_structure_id
                HAVING COUNT(*) > 1 AND SUM(status = 'paid') = 0
            ) g ON g.alumni_id = t.alumni_id AND g.payment_structure_id = t.payment_structure_id
            SET t.obligation_id = g.keeper_id
            WHERE t.id <> g.keeper_id
              AND t.fee_template_id IS NULL
              AND t.obligation_id IS NULL
              AND t.deleted_at IS NULL
        ");
    }

    protected function linkPaidCombinedGroups(): int
    {
        return DB::update("
            UPDATE transactions t
            INNER JOIN (
                SELECT alumni_id, payment_structure_id, MAX(CASE WHEN status = 'paid' THEN id END) AS keeper_id
                FROM transactions
                WHERE deleted_at IS NULL
                  AND is_eoi = 0
                  AND obligation_id IS NULL
                  AND payment_structure_id IS NOT NULL
                  AND fee_template_id IS NULL
                GROUP BY alumni_id, payment_structure_id
                HAVING SUM(status = 'paid') > 0 AND COUNT(*) > 1
            ) g ON g.alumni_id = t.alumni_id AND g.payment_structure_id = t.payment_structure_id
            SET t.obligation_id = g.keeper_id
            WHERE t.id <> g.keeper_id
              AND t.fee_template_id IS NULL
              AND t.status <> 'paid'
              AND t.obligation_id IS NULL
              AND t.deleted_at IS NULL
        ");
    }

    public function recordAttemptsForLinkedRows(): void
    {
        DB::statement("
            INSERT INTO payment_attempts (
                transaction_id, payment_reference, payment_provider_reference, payment_link, status, amount, created_at, updated_at
            )
            SELECT t.obligation_id, t.payment_reference, t.payment_provider_reference, t.payment_link, t.status, t.amount, NOW(), NOW()
            FROM transactions t
            WHERE t.obligation_id IS NOT NULL
              AND NOT EXISTS (
                  SELECT 1 FROM payment_attempts pa WHERE pa.payment_reference = t.payment_reference
              )
        ");
    }
}
