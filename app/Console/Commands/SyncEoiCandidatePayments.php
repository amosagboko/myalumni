<?php

namespace App\Console\Commands;

use App\Models\Candidate;
use App\Models\FeeType;
use App\Models\Transaction;
use App\Services\PaymentCompletionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncEoiCandidatePayments extends Command
{
    protected $signature = 'eoi:sync-candidate-payments';

    protected $description = 'Sync EOI candidate payment status with paid transactions';

    public function __construct(
        protected PaymentCompletionService $paymentCompletion
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $updated = 0;

        $transactions = Transaction::where('status', 'paid')
            ->whereHas('feeTemplate.feeType', function ($query) {
                $query->where('code', 'like', 'eoi-%')
                    ->orWhere('code', 'screening_fee');
            })
            ->with(['feeTemplate.feeType'])
            ->orderBy('id')
            ->get();

        foreach ($transactions as $transaction) {
            $meta = $this->paymentCompletion->resolveEoiMetadata($transaction);

            if (empty($meta['candidate_id']) && (empty($meta['election_id']) || empty($meta['office_id']))) {
                continue;
            }

            $candidate = null;
            if (!empty($meta['candidate_id'])) {
                $candidate = Candidate::find($meta['candidate_id']);
            }

            if (!$candidate && !empty($meta['election_id']) && !empty($meta['office_id'])) {
                $candidate = Candidate::where('alumni_id', $transaction->alumni_id)
                    ->where('election_id', $meta['election_id'])
                    ->where('election_office_id', $meta['office_id'])
                    ->where('status', '!=', Candidate::STATUS_REJECTED)
                    ->first();
            }

            if (!$candidate || $candidate->has_paid_screening_fee) {
                continue;
            }

            $this->paymentCompletion->syncEoiCandidate($transaction);
            $candidate->refresh();

            if ($candidate->has_paid_screening_fee) {
                $updated++;
                Log::info('EOI candidate payment synced', [
                    'candidate_id' => $candidate->id,
                    'transaction_id' => $transaction->id,
                ]);
            }
        }

        $this->info("EOI candidate payment sync complete. Updated: {$updated}");

        return self::SUCCESS;
    }
}
