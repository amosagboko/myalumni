<?php

namespace App\Services;

use App\Models\Candidate;
use App\Models\ElectionOffice;
use App\Models\FeeType;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentCompletionService
{
    public function isEoiTransaction(Transaction $transaction): bool
    {
        $transaction->loadMissing('feeTemplate.feeType');
        $code = $transaction->feeTemplate?->feeType?->code;

        return $code && FeeType::isEoiFeeCode($code);
    }

    /**
     * Resolve EOI linkage from metadata and payment_details.eoi.
     */
    public function resolveEoiMetadata(Transaction $transaction): array
    {
        $meta = $transaction->metadata;
        if (is_string($meta)) {
            $meta = json_decode($meta, true) ?? [];
        }
        if (!is_array($meta)) {
            $meta = [];
        }

        $details = $transaction->payment_details;
        if (is_string($details)) {
            $details = json_decode($details, true) ?? [];
        }
        if (!is_array($details)) {
            $details = [];
        }

        if (!empty($details['eoi']) && is_array($details['eoi'])) {
            $meta = array_merge($meta, $details['eoi']);
        }

        return $meta;
    }

    /**
     * Whether transaction metadata targets a specific EOI election/office (and optionally candidate).
     */
    public function eoiScopeMatches(
        Transaction $transaction,
        int $electionId,
        int $officeId,
        ?int $candidateId = null
    ): bool {
        $meta = $this->resolveEoiMetadata($transaction);

        if ($candidateId && (int) ($meta['candidate_id'] ?? 0) === $candidateId) {
            return true;
        }

        return (int) ($meta['election_id'] ?? 0) === $electionId
            && (int) ($meta['office_id'] ?? 0) === $officeId;
    }

    /**
     * Find a pending EOI payment scoped to election + office (not fee type alone).
     */
    public function findPendingEoiTransaction(
        int $alumniId,
        int $electionId,
        int $officeId,
        ?int $feeTypeId = null
    ): ?Transaction {
        $query = Transaction::where('alumni_id', $alumniId)
            ->where('status', 'pending')
            ->whereHas('feeTemplate.feeType', function ($q) {
                $q->where('code', 'like', 'eoi-%')
                    ->orWhere('code', 'screening_fee');
            });

        if ($feeTypeId) {
            $query->whereHas('feeTemplate', fn ($q) => $q->where('fee_type_id', $feeTypeId));
        }

        return $query->orderByDesc('id')
            ->get()
            ->first(fn (Transaction $tx) => $this->eoiScopeMatches($tx, $electionId, $officeId));
    }

    /**
     * Mark a transaction paid (idempotent) and run post-payment actions.
     */
    public function complete(Transaction $transaction, ?string $paidAt = null, array $detailExtras = []): Transaction
    {
        return DB::transaction(function () use ($transaction, $paidAt, $detailExtras) {
            $locked = Transaction::whereKey($transaction->id)->lockForUpdate()->firstOrFail();
            $locked->loadMissing('feeTemplate.feeType');

            $existingDetails = $locked->payment_details;
            if (!is_array($existingDetails)) {
                $existingDetails = [];
            }

            $updates = [];
            if (!$locked->isPaid()) {
                $updates['status'] = 'paid';
                $updates['paid_at'] = $paidAt ?? now();
            }

            if (!empty($detailExtras)) {
                $updates['payment_details'] = array_merge($existingDetails, $detailExtras);
            }

            if (!empty($updates)) {
                $locked->update($updates);
                $locked->refresh();
            }

            if ($this->isEoiTransaction($locked)) {
                $this->syncEoiCandidate($locked);
            }

            Log::info('Payment completion processed', [
                'transaction_id' => $locked->id,
                'status' => $locked->status,
                'is_eoi' => $this->isEoiTransaction($locked),
            ]);

            return $locked;
        });
    }

    /**
     * Mark the linked EOI candidate as having paid the screening fee.
     */
    public function syncEoiCandidate(Transaction $transaction): ?Candidate
    {
        $meta = $this->resolveEoiMetadata($transaction);
        $candidateId = $meta['candidate_id'] ?? null;
        $electionId = $meta['election_id'] ?? null;
        $officeId = $meta['office_id'] ?? null;

        $candidate = null;
        if ($candidateId) {
            $candidate = Candidate::find($candidateId);
        }

        if (!$candidate && $electionId && $officeId) {
            $candidate = Candidate::where('alumni_id', $transaction->alumni_id)
                ->where('election_id', $electionId)
                ->where('election_office_id', $officeId)
                ->where('status', '!=', 'rejected')
                ->first();
        }

        if ($candidate) {
            $candidateUpdates = [];

            if (!$candidate->has_paid_screening_fee) {
                $candidateUpdates['has_paid_screening_fee'] = true;
            }

            if ($candidate->status === Candidate::STATUS_PENDING) {
                $candidateUpdates['status'] = Candidate::STATUS_PAID_AWAITING_SCREENING;
            }

            if (!empty($candidateUpdates)) {
                $candidate->update($candidateUpdates);
                Log::info('EOI candidate screening fee marked paid', [
                    'transaction_id' => $transaction->id,
                    'candidate_id' => $candidate->id,
                    'status' => $candidateUpdates['status'] ?? $candidate->status,
                ]);
            }

            return $candidate->fresh();
        }

        if ($electionId && $officeId && !empty($meta['is_eoi'])) {
            $office = ElectionOffice::find($officeId);
            if ($office && $office->hasAvailableApplicantSlots()) {
                $candidate = Candidate::create([
                    'election_id' => $electionId,
                    'election_office_id' => $officeId,
                    'alumni_id' => $transaction->alumni_id,
                    'has_paid_screening_fee' => true,
                    'manifesto' => $meta['manifesto'] ?? null,
                    'passport' => $meta['passport'] ?? null,
                    'documents' => $meta['documents'] ?? [],
                    'status' => Candidate::STATUS_PAID_AWAITING_SCREENING,
                ]);

                Log::info('EOI candidate created from payment fallback', [
                    'transaction_id' => $transaction->id,
                    'candidate_id' => $candidate->id,
                ]);

                return $candidate;
            }

            Log::warning('Skipped EOI candidate fallback create — office applicant slots full', [
                'transaction_id' => $transaction->id,
                'office_id' => $officeId,
            ]);
        }

        return null;
    }
}
