<?php

namespace App\Console\Commands;

use App\Models\Candidate;
use App\Models\Transaction;
use App\Services\PaymentCompletionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanupAbandonedEoiApplications extends Command
{
    protected $signature = 'eoi:cleanup-abandoned {--dry-run : List candidates that would be rejected without making changes}';

    protected $description = 'Reject unpaid EOI applications past the payment grace period and fail their pending transactions';

    public function __construct(
        protected PaymentCompletionService $paymentCompletion
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $graceHours = (int) config('election.eoi_payment_grace_hours', 48);
        $dryRun = (bool) $this->option('dry-run');

        $candidates = Candidate::abandonedUnpaid()
            ->with(['office', 'election'])
            ->orderBy('id')
            ->get();

        if ($candidates->isEmpty()) {
            $this->info('No abandoned unpaid EOI applications found.');

            return self::SUCCESS;
        }

        $rejected = 0;
        $transactionsFailed = 0;
        $reason = "Screening fee payment was not completed within {$graceHours} hours. "
            . 'You may submit a new expression of interest if slots are still available.';

        foreach ($candidates as $candidate) {
            if ($dryRun) {
                $this->line("Would reject candidate #{$candidate->id} (alumni {$candidate->alumni_id}, office {$candidate->election_office_id})");
                continue;
            }

            DB::transaction(function () use ($candidate, $reason, &$rejected, &$transactionsFailed) {
                $candidate->update([
                    'status' => Candidate::STATUS_REJECTED,
                    'rejection_reason' => $reason,
                ]);

                $failedForCandidate = $this->failPendingEoiTransactions($candidate);
                $transactionsFailed += $failedForCandidate;
                $rejected++;

                Log::info('Abandoned EOI application rejected', [
                    'candidate_id' => $candidate->id,
                    'alumni_id' => $candidate->alumni_id,
                    'election_id' => $candidate->election_id,
                    'office_id' => $candidate->election_office_id,
                    'pending_transactions_failed' => $failedForCandidate,
                ]);
            });
        }

        if ($dryRun) {
            $this->info("Dry run complete. {$candidates->count()} application(s) would be rejected.");
        } else {
            $this->info("Cleanup complete. Rejected: {$rejected}, pending transactions failed: {$transactionsFailed}.");
        }

        return self::SUCCESS;
    }

    protected function failPendingEoiTransactions(Candidate $candidate): int
    {
        $failed = 0;

        $pending = Transaction::where('alumni_id', $candidate->alumni_id)
            ->where('status', 'pending')
            ->whereHas('feeTemplate', function ($query) use ($candidate) {
                $query->where('fee_type_id', $candidate->office?->fee_type_id);
            })
            ->get();

        foreach ($pending as $transaction) {
            $matchesCandidate = $this->paymentCompletion->eoiScopeMatches(
                $transaction,
                (int) $candidate->election_id,
                (int) $candidate->election_office_id,
                (int) $candidate->id
            );

            if (!$matchesCandidate) {
                continue;
            }

            $transaction->update([
                'status' => 'failed',
                'payment_details' => array_merge(
                    is_array($transaction->payment_details) ? $transaction->payment_details : [],
                    [
                        'failed_at' => now()->toIso8601String(),
                        'failure_reason' => 'eoi_payment_grace_expired',
                    ]
                ),
            ]);

            $failed++;
        }

        return $failed;
    }
}
