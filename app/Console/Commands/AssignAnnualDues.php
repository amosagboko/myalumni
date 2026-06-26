<?php

namespace App\Console\Commands;

use App\Models\AlumniYear;
use App\Services\AlumniDuesService;
use Illuminate\Console\Command;

class AssignAnnualDues extends Command
{
    protected $signature = 'dues:assign-annual {--year= : Payment year (defaults to active year)}';

    protected $description = 'Create pending annual-due transactions for eligible alumni';

    public function handle(AlumniDuesService $duesService): int
    {
        $year = null;
        if ($this->option('year')) {
            $requestedYear = (int) $this->option('year');
            $year = AlumniYear::where('year', $requestedYear)->first();
            if (!$year) {
                $available = AlumniYear::orderBy('year')->pluck('year')->implode(', ');
                $this->error("Payment year {$requestedYear} not found.");
                if ($available !== '') {
                    $this->line("Available payment years: {$available}");
                } else {
                    $this->line('Create a payment year in Dues Config first.');
                }

                return self::FAILURE;
            }
        } else {
            $year = AlumniYear::where('is_active', true)->first();
        }

        if (!$year) {
            $this->error('No active payment year. Activate one in Dues Config first.');

            return self::FAILURE;
        }

        if (!$year->annualDueTemplate()) {
            $this->error("No annual due template configured for {$year->year}.");

            return self::FAILURE;
        }

        $assigned = $duesService->assignAnnualDuesForPaymentYear($year);
        $this->info("Assigned {$assigned} pending annual due(s) for payment year {$year->year}.");

        return self::SUCCESS;
    }
}
