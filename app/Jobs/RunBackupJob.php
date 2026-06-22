<?php

namespace App\Jobs;

use App\Services\BackupRestoreService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RunBackupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;

    public function __construct(public string $operationId) {}

    public function handle(BackupRestoreService $backupRestoreService): void
    {
        $backupRestoreService->runBackup($this->operationId);
    }
}
