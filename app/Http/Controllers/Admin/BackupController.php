<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\RestoreBackupJob;
use App\Jobs\RunBackupJob;
use App\Services\BackupRestoreService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    public function __construct(protected BackupRestoreService $backupService)
    {
        $this->middleware(function ($request, $next) {
            $user = $request->user();

            if (! $user || ! $user->can('manage backups')) {
                abort(403, 'You do not have permission to manage backups.');
            }

            return $next($request);
        });
    }

    public function index()
    {
        return view('admin.backups.index', [
            'backups' => $this->backupService->listBackups(),
            'health' => $this->backupService->getHealthStatus(),
            'retention' => $this->backupService->getRetentionPolicy(),
        ]);
    }

    public function run(Request $request)
    {
        $operationId = (string) Str::uuid();

        $this->backupService->updateProgress('backup', $operationId, [
            'status' => 'queued',
            'message' => 'Backup queued...',
            'percent' => 0,
        ]);

        if (config('queue.default') === 'sync') {
            RunBackupJob::dispatchSync($operationId);
        } else {
            RunBackupJob::dispatch($operationId);
        }

        activity()
            ->causedBy(Auth::user())
            ->withProperties(['operation_id' => $operationId])
            ->log('Manual backup started');

        Log::info('Manual backup queued', [
            'admin_id' => Auth::id(),
            'operation_id' => $operationId,
        ]);

        return response()->json([
            'operation_id' => $operationId,
            'message' => 'Backup started. This may take several minutes.',
        ]);
    }

    public function progress(Request $request)
    {
        $request->validate([
            'operation_id' => 'required|string',
            'type' => 'required|in:backup,restore',
        ]);

        $progress = $this->backupService->getProgress(
            $request->input('type'),
            $request->input('operation_id')
        );

        if (! $progress) {
            return response()->json(['error' => 'Progress not found'], 404);
        }

        return response()->json($progress);
    }

    public function download(Request $request): StreamedResponse
    {
        $request->validate([
            'disk' => 'required|string',
            'path' => 'required|string',
        ]);

        $stream = $this->backupService->getBackupStream(
            $request->input('disk'),
            $request->input('path')
        );

        $filename = $this->backupService->getBackupFilename($request->input('path'));

        activity()
            ->causedBy(Auth::user())
            ->withProperties([
                'disk' => $request->input('disk'),
                'path' => $request->input('path'),
            ])
            ->log('Backup downloaded');

        return response()->streamDownload(function () use ($stream) {
            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }, $filename, [
            'Content-Type' => 'application/zip',
        ]);
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'disk' => 'required|string',
            'path' => 'required|string',
            'confirmation' => 'required|string|in:DELETE',
        ]);

        $this->backupService->deleteBackup(
            $request->input('disk'),
            $request->input('path')
        );

        activity()
            ->causedBy(Auth::user())
            ->withProperties([
                'disk' => $request->input('disk'),
                'path' => $request->input('path'),
            ])
            ->log('Backup deleted');

        return redirect()
            ->route('admin.backups.index')
            ->with('success', 'Backup deleted successfully.');
    }

    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => [
                'required',
                'file',
                function (string $attribute, $value, \Closure $fail) {
                    $extension = strtolower($value->getClientOriginalExtension());
                    if (! in_array($extension, ['zip', 'sql'], true)) {
                        $fail('The backup file must be a .zip or .sql file.');
                    }
                },
                'max:512000',
            ],
            'confirmation' => 'required|string|in:'.config('app.name'),
            'restore_env' => 'nullable|boolean',
        ]);

        $operationId = (string) Str::uuid();
        $uploadDir = storage_path('app/backup-restore-uploads');

        if (! is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $extension = $request->file('backup_file')->getClientOriginalExtension();
        $storedPath = $uploadDir.'/restore_'.$operationId.'.'.$extension;
        $request->file('backup_file')->move($uploadDir, basename($storedPath));

        $this->backupService->updateProgress('restore', $operationId, [
            'status' => 'queued',
            'message' => 'Restore queued...',
            'percent' => 0,
        ]);

        if (config('queue.default') === 'sync') {
            RestoreBackupJob::dispatchSync(
                $operationId,
                $storedPath,
                $request->boolean('restore_env')
            );
        } else {
            RestoreBackupJob::dispatch(
                $operationId,
                $storedPath,
                $request->boolean('restore_env')
            );
        }

        activity()
            ->causedBy(Auth::user())
            ->withProperties([
                'operation_id' => $operationId,
                'filename' => $request->file('backup_file')->getClientOriginalName(),
                'restore_env' => $request->boolean('restore_env'),
            ])
            ->log('System restore started');

        Log::warning('System restore queued', [
            'admin_id' => Auth::id(),
            'operation_id' => $operationId,
            'filename' => $request->file('backup_file')->getClientOriginalName(),
        ]);

        return response()->json([
            'operation_id' => $operationId,
            'message' => 'Restore started. The application may be briefly unavailable.',
        ]);
    }
}
