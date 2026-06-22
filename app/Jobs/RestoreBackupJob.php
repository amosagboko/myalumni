<?php

namespace App\Jobs;

use App\Services\BackupRestoreService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RestoreBackupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 7200;

    public function __construct(
        public string $operationId,
        public string $sourcePath,
        public bool $restoreEnv = false,
    ) {}

    public function handle(BackupRestoreService $backupRestoreService): void
    {
        $backupRestoreService->restoreFromPath(
            $this->sourcePath,
            $this->operationId,
            $this->restoreEnv
        );
    }
}
