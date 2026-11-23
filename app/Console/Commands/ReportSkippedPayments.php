<?php

namespace App\Console\Commands;

use App\Services\Alumni\SkippedPaymentReportService;
use Illuminate\Console\Command;

class ReportSkippedPayments extends Command
{
    protected $signature = 'alumni:skipped-payments
                            {--year= : Graduation year (defaults to 2025 and later)}
                            {--source=all : all, online, or uploaded}
                            {--bypassed : Only alumni the portal treated as paid without fees}
                            {--csv= : Write the list to this CSV path}';

    protected $description = 'List alumni who enrolled but have not completed statutory onboarding/cohort payments';

    public function handle(SkippedPaymentReportService $report): int
    {
        $yearOption = $this->option('year');
        $rows = $report->rows([
            'year' => $yearOption !== null && $yearOption !== '' ? (int) $yearOption : null,
            'source' => (string) $this->option('source'),
            'bypassed' => (bool) $this->option('bypassed'),
        ]);

        $this->info($rows->count().' alumni have not completed required payments.');
        $this->line('Bypassed payment gate: '.$rows->where('bypassed_payment_gate', true)->count());

        if ($rows->isEmpty()) {
            return self::SUCCESS;
        }

        $this->table(
            ['Matric', 'Name', 'Year', 'Source', 'Bypassed', 'Outstanding'],
            $rows->map(fn (array $row) => [
                $row['matric_number'],
                $row['name'],
                $row['year_of_graduation'],
                $row['source'],
                $row['bypassed_payment_gate'] ? 'Yes' : 'No',
                $row['outstanding_fees'] ?: '—',
            ])->all()
        );

        $csvPath = $this->option('csv');
        if ($csvPath) {
            $handle = fopen($csvPath, 'w');
            if ($handle === false) {
                $this->error('Could not write CSV to '.$csvPath);

                return self::FAILURE;
            }

            fputcsv($handle, $report->csvHeaders());
            foreach ($rows as $row) {
                fputcsv($handle, $report->csvRow($row));
            }
            fclose($handle);

            $this->info('CSV written to '.$csvPath);
        }

        return self::SUCCESS;
    }
}
