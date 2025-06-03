<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Candidate;
use App\Models\Transaction;
use App\Models\FeeTemplate;
use Illuminate\Support\Facades\Log;

class SyncEoiCandidatePayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'eoi:sync-candidate-payments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync EOI candidate payment status with transactions table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $candidates = Candidate::where('status', 'pending')
            ->orWhere('has_paid_screening_fee', false)
            ->get();

        $updated = 0;

        foreach ($candidates as $candidate) {
            // Find the active fee template for this office
            $feeTemplate = FeeTemplate::where('fee_type_id', $candidate->office->fee_type_id)
                ->where('is_active', true)
                ->first();
            if (!$feeTemplate) {
                continue;
            }
            // Find a paid transaction for this candidate's alumni and fee template
            $transaction = Transaction::where('alumni_id', $candidate->alumni_id)
                ->where('fee_template_id', $feeTemplate->id)
                ->where('status', 'paid')
                ->first();
            if ($transaction) {
                $candidate->update([
                    'has_paid_screening_fee' => true,
                    'status' => 'paid',
                ]);
                $updated++;
                Log::info('EOI candidate payment synced', [
                    'candidate_id' => $candidate->id,
                    'alumni_id' => $candidate->alumni_id,
                    'election_id' => $candidate->election_id,
                    'office_id' => $candidate->election_office_id,
                    'transaction_id' => $transaction->id,
                ]);
            }
        }

        $this->info("EOI candidate payment sync complete. Updated: {$updated}");
    }
} 