<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Backup\BackupDestination\Backup;
use Spatie\Backup\BackupDestination\BackupDestination;
use Spatie\Backup\Config\Config as BackupConfig;
use Spatie\Backup\Config\MonitoredBackupsConfig;
use Spatie\Backup\Tasks\Monitor\BackupDestinationStatusFactory;
use Symfony\Component\Process\Process;
use ZipArchive;

class BackupRestoreService
{
    public const PROGRESS_TTL_SECONDS = 3600;

    public function listBackups(): array
    {
        $backups = [];

        try {
            foreach ($this->backupDisks() as $diskName) {
                $destination = BackupDestination::create($diskName, config('backup.backup.name'));

                if (! $destination->isReachable()) {
                    continue;
                }

                foreach ($destination->backups() as $backup) {
                    $backups[] = $this->formatBackup($backup, $diskName);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Failed to list backups', ['error' => $e->getMessage()]);

            return [];
        }

        usort($backups, fn (array $a, array $b) => $b['timestamp'] <=> $a['timestamp']);

        return $backups;
    }

    public function getHealthStatus(): array
    {
        $statuses = [];

        try {
            $monitorConfig = app(BackupConfig::class)->monitoredBackups;
        } catch (\Throwable $e) {
            Log::warning('Falling back to raw backup monitor config', ['error' => $e->getMessage()]);
            $monitorConfig = MonitoredBackupsConfig::fromArray(config('backup.monitor_backups', []));
        }

        foreach (BackupDestinationStatusFactory::createForMonitorConfig($monitorConfig) as $status) {
            try {
                $statuses[] = [
                    'name' => $status->backupDestination()->backupName(),
                    'disk' => $status->backupDestination()->diskName(),
                    'reachable' => $status->backupDestination()->isReachable(),
                    'healthy' => $status->isHealthy(),
                    'newest_backup' => $status->backupDestination()->newestBackup()?->date()?->toDateTimeString(),
                    'used_storage_mb' => round($status->backupDestination()->usedStorage() / 1024 / 1024, 2),
                ];
            } catch (\Throwable $e) {
                Log::warning('Backup health check failed for destination', [
                    'disk' => $status->backupDestination()->diskName(),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $statuses;
    }

    public function getRetentionPolicy(): array
    {
        return config('backup.cleanup.default_strategy', []);
    }

    public function runBackup(string $operationId): void
    {
        $this->updateProgress('backup', $operationId, [
            'status' => 'running',
            'message' => 'Starting backup...',
            'percent' => 5,
        ]);

        try {
            $exitCode = Artisan::call('backup:run', ['--only-db' => false]);

            if ($exitCode !== 0) {
                throw new \RuntimeException(trim(Artisan::output()) ?: 'Backup command failed.');
            }

            $this->updateProgress('backup', $operationId, [
                'status' => 'completed',
                'message' => 'Backup completed successfully.',
                'percent' => 100,
            ]);
        } catch (\Throwable $e) {
            Log::error('Backup failed', ['error' => $e->getMessage(), 'operation_id' => $operationId]);

            $this->updateProgress('backup', $operationId, [
                'status' => 'failed',
                'message' => $e->getMessage(),
                'percent' => 100,
            ]);

            throw $e;
        }
    }

    public function deleteBackup(string $disk, string $path): void
    {
        $backup = $this->resolveBackup($disk, $path);
        $backup->delete();
    }

    public function getBackupStream(string $disk, string $path)
    {
        $backup = $this->resolveBackup($disk, $path);

        return $backup->stream();
    }

    public function getBackupFilename(string $path): string
    {
        return basename($path);
    }

    public function restoreFromPath(string $sourcePath, string $operationId, bool $restoreEnv = false): void
    {
        $this->updateProgress('restore', $operationId, [
            'status' => 'running',
            'message' => 'Enabling maintenance mode...',
            'percent' => 5,
        ]);

        $maintenanceEnabled = false;

        try {
            Artisan::call('down', ['--retry' => 60]);
            $maintenanceEnabled = true;

            $extension = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));

            if ($extension === 'zip') {
                $this->restoreFromZip($sourcePath, $operationId, $restoreEnv);
            } elseif ($extension === 'sql') {
                $this->updateProgress('restore', $operationId, [
                    'status' => 'running',
                    'message' => 'Importing database...',
                    'percent' => 40,
                ]);
                $this->importSqlFile($sourcePath);
            } else {
                throw new \InvalidArgumentException('Unsupported backup file type. Upload a .zip or .sql file.');
            }

            $this->updateProgress('restore', $operationId, [
                'status' => 'running',
                'message' => 'Clearing caches and restarting workers...',
                'percent' => 90,
            ]);

            $this->runPostRestoreTasks();

            $this->updateProgress('restore', $operationId, [
                'status' => 'completed',
                'message' => 'Restore completed successfully.',
                'percent' => 100,
            ]);
        } catch (\Throwable $e) {
            Log::error('Restore failed', [
                'error' => $e->getMessage(),
                'operation_id' => $operationId,
                'source' => $sourcePath,
            ]);

            $this->updateProgress('restore', $operationId, [
                'status' => 'failed',
                'message' => $e->getMessage(),
                'percent' => 100,
            ]);

            throw $e;
        } finally {
            if ($maintenanceEnabled) {
                Artisan::call('up');
            }

            if (File::exists($sourcePath) && str_contains($sourcePath, 'backup-restore-uploads')) {
                File::delete($sourcePath);
            }
        }
    }

    public function importSqlFile(string $sqlPath): void
    {
        if (! File::exists($sqlPath)) {
            throw new \RuntimeException("SQL file not found: {$sqlPath}");
        }

        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        if (($config['driver'] ?? '') === 'mysql' && $this->mysqlCliIsAvailable()) {
            $this->importSqlViaMysqlCli($sqlPath, $config);

            return;
        }

        $this->importSqlViaPhp($sqlPath);
    }

    public function getProgress(string $type, string $operationId): ?array
    {
        return Cache::get("{$type}_progress_{$operationId}");
    }

    public function updateProgress(string $type, string $operationId, array $data): void
    {
        Cache::put(
            "{$type}_progress_{$operationId}",
            array_merge(['updated_at' => now()->toDateTimeString()], $data),
            self::PROGRESS_TTL_SECONDS
        );
    }

    protected function restoreFromZip(string $zipPath, string $operationId, bool $restoreEnv): void
    {
        $extractPath = storage_path('app/backup-temp/restore-'.Str::uuid());

        if (! File::isDirectory($extractPath)) {
            File::makeDirectory($extractPath, 0755, true);
        }

        $zip = new ZipArchive;
        if ($zip->open($zipPath) !== true) {
            throw new \RuntimeException('Unable to open backup archive.');
        }

        $zip->extractTo($extractPath);
        $zip->close();

        $this->updateProgress('restore', $operationId, [
            'status' => 'running',
            'message' => 'Locating database dump...',
            'percent' => 20,
        ]);

        $sqlFiles = $this->findSqlFiles($extractPath);

        if (empty($sqlFiles)) {
            throw new \RuntimeException('No database dump found in backup archive.');
        }

        $this->updateProgress('restore', $operationId, [
            'status' => 'running',
            'message' => 'Importing database...',
            'percent' => 40,
        ]);

        $this->importSqlFile($sqlFiles[0]);

        $this->updateProgress('restore', $operationId, [
            'status' => 'running',
            'message' => 'Restoring uploaded files...',
            'percent' => 65,
        ]);

        $this->restoreFilesFromExtractedArchive($extractPath, $restoreEnv);

        File::deleteDirectory($extractPath);
    }

    protected function restoreFilesFromExtractedArchive(string $extractPath, bool $restoreEnv): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($extractPath, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                continue;
            }

            $relativePath = $this->mapArchivePathToLocal($file->getPathname(), $extractPath);

            if ($relativePath === null) {
                continue;
            }

            if (! $restoreEnv && basename($relativePath) === '.env') {
                continue;
            }

            $directory = dirname($relativePath);
            if (! File::isDirectory($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            File::copy($file->getPathname(), $relativePath);
        }
    }

    protected function mapArchivePathToLocal(string $absolutePath, string $extractPath): ?string
    {
        $normalized = str_replace('\\', '/', $absolutePath);
        $extractPrefix = str_replace('\\', '/', $extractPath);
        $relative = Str::after($normalized, $extractPrefix.'/');

        if (preg_match('#^db-dumps/.+\.sql$#i', $relative)) {
            return null;
        }

        if (preg_match('#(^|/)storage/app/public/(.+)$#i', $relative, $matches)) {
            return storage_path('app/public/'.$matches[2]);
        }

        if (preg_match('#(^|/)storage/app/private/(.+)$#i', $relative, $matches)) {
            $privatePath = storage_path('app/private/'.$matches[2]);
            $backupName = config('backup.backup.name');

            if (str_starts_with($matches[2], $backupName.'/')) {
                return null;
            }

            return $privatePath;
        }

        if (preg_match('#(^|/)\.env$#', $relative)) {
            return base_path('.env');
        }

        return null;
    }

    protected function findSqlFiles(string $directory): array
    {
        $sqlFiles = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && str_ends_with(strtolower($file->getFilename()), '.sql')) {
                $sqlFiles[] = $file->getPathname();
            }
        }

        usort($sqlFiles, function (string $a, string $b) {
            $score = fn (string $path) => (str_contains($path, 'db-dumps') ? 0 : 1)
                + (str_contains(basename($path), 'mysql') ? 0 : 1);

            return $score($a) <=> $score($b);
        });

        return $sqlFiles;
    }

    protected function importSqlViaMysqlCli(string $sqlPath, array $config): void
    {
        $command = [
            'mysql',
            '-h', $config['host'],
            '-P', (string) ($config['port'] ?? 3306),
            '-u', $config['username'],
            $config['database'],
        ];

        $process = new Process($command);
        $process->setTimeout(3600);

        if (! empty($config['password'])) {
            $process->setEnv(array_merge($_ENV, ['MYSQL_PWD' => $config['password']]));
        }

        $process->setInput(file_get_contents($sqlPath));
        $process->run();

        if (! $process->isSuccessful()) {
            throw new \RuntimeException('MySQL import failed: '.$process->getErrorOutput());
        }
    }

    protected function importSqlViaPhp(string $sqlPath): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $handle = fopen($sqlPath, 'r');
        if ($handle === false) {
            throw new \RuntimeException('Unable to read SQL file.');
        }

        $statement = '';

        while (($line = fgets($handle)) !== false) {
            $trimmed = trim($line);

            if ($trimmed === '' || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '/*')) {
                continue;
            }

            $statement .= $line;

            if (str_ends_with(rtrim($trimmed), ';')) {
                DB::unprepared($statement);
                $statement = '';
            }
        }

        fclose($handle);

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    protected function runPostRestoreTasks(): void
    {
        Artisan::call('optimize:clear');

        if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        }

        if (File::exists(base_path('artisan'))) {
            Artisan::call('storage:link');
        }

        Artisan::call('queue:restart');
    }

    protected function mysqlCliIsAvailable(): bool
    {
        $process = new Process(['mysql', '--version']);
        $process->run();

        return $process->isSuccessful();
    }

    protected function resolveBackup(string $disk, string $path): Backup
    {
        if (! in_array($disk, $this->backupDisks(), true)) {
            throw new \InvalidArgumentException('Invalid backup disk.');
        }

        $backupName = config('backup.backup.name');
        $expectedPrefix = $backupName.'/';

        if (! str_starts_with($path, $expectedPrefix) || str_contains($path, '..')) {
            throw new \InvalidArgumentException('Invalid backup path.');
        }

        $destination = BackupDestination::create($disk, $backupName);
        $backup = $destination->backups()->first(fn (Backup $item) => $item->path() === $path);

        if (! $backup || ! $backup->exists()) {
            throw new \InvalidArgumentException('Backup file not found.');
        }

        return $backup;
    }

    protected function formatBackup(Backup $backup, string $diskName): array
    {
        return [
            'disk' => $diskName,
            'path' => $backup->path(),
            'filename' => basename($backup->path()),
            'size_bytes' => $backup->sizeInBytes(),
            'size_human' => $this->formatBytes($backup->sizeInBytes()),
            'date' => $backup->date()->toDateTimeString(),
            'timestamp' => $backup->date()->timestamp,
        ];
    }

    protected function backupDisks(): array
    {
        return config('backup.backup.destination.disks', ['local']);
    }

    protected function formatBytes(float $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;

        return number_format($bytes / (1024 ** $power), 2).' '.$units[$power];
    }
}
