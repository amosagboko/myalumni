<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Candidate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdatePendingCandidatesPaymentStatus extends Command
{
    protected $signature = 'candidates:update-payment-status';
    protected $description = 'Update all pending candidates payment status to paid';

    public function handle()
    {
        $this->info('Starting to update pending candidates payment status...');

        try {
            DB::beginTransaction();

            $updatedCount = Candidate::where('status', 'pending')
                ->where('has_paid_screening_fee', false)
                ->update(['has_paid_screening_fee' => true]);

            DB::commit();

            $this->info("Successfully updated {$updatedCount} candidates' payment status to paid.");
            Log::info("Updated {$updatedCount} candidates' payment status to paid via command.");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Failed to update candidates payment status: ' . $e->getMessage());
            Log::error('Failed to update candidates payment status', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
} 