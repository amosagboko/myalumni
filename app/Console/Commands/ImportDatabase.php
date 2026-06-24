<?php

namespace App\Console\Commands;

use App\Services\BackupRestoreService;
use Illuminate\Console\Command;

class ImportDatabase extends Command
{
    protected $signature = 'db:import
                            {file : Path to a .sql file or Spatie .zip backup}
                            {--restore-env : Restore .env from zip backup (use with caution)}
                            {--force : Skip confirmation prompt}';

    protected $description = 'Restore the database (and files from zip backups) from a backup file';

    public function handle(BackupRestoreService $backupRestoreService): int
    {
        $file = $this->argument('file');

        if (! file_exists($file)) {
            $this->error("File not found: {$file}");

            return Command::FAILURE;
        }

        $operationId = 'cli-'.uniqid();

        if (! $this->option('force') && ! $this->confirm('This will overwrite current data. Continue?', false)) {
            $this->warn('Restore cancelled.');

            return Command::SUCCESS;
        }

        try {
            $backupRestoreService->restoreFromPath(
                $file,
                $operationId,
                (bool) $this->option('restore-env')
            );

            $this->info('Restore completed successfully.');

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Restore failed: '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
