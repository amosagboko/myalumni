<?php

namespace App\Console\Commands;

use App\Services\PaymentObligationService;
use Illuminate\Console\Command;

class ReconcilePaymentObligations extends Command
{
    protected $signature = 'payments:link-duplicate-obligations';

    protected $description = 'Link extra dues transactions to one obligation without deleting them';

    public function handle(PaymentObligationService $obligations): int
    {
        $linked = $obligations->linkDuplicateObligations();
        $this->info("Linked {$linked} extra payment row(s) to their obligation. No rows were deleted.");

        return self::SUCCESS;
    }
}
