<x-alumniadmin-dashboard>
<div class="container mt-5 pt-5" style="margin-left: 150px;">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0 h4 h-md-3">Backup &amp; Restore</h3>
                    <button type="button" id="run-backup-btn" class="btn btn-primary btn-sm">
                        <i class="bi bi-cloud-upload me-1"></i> Run Backup Now
                    </button>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div id="operation-alert" class="alert d-none" role="alert"></div>
                    <div id="operation-progress" class="progress mb-4 d-none" style="height: 24px;">
                        <div id="operation-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%">0%</div>
                    </div>

                    <div class="row mb-4">
                        @foreach($health as $status)
                            <div class="col-md-6 mb-3">
                                <div class="card border-0 bg-light h-100">
                                    <div class="card-body">
                                        <h5 class="card-title">Backup Health</h5>
                                        <p class="mb-1">
                                            <strong>Status:</strong>
                                            @if($status['healthy'])
                                                <span class="text-success">Healthy</span>
                                            @else
                                                <span class="text-danger">Needs attention</span>
                                            @endif
                                        </p>
                                        <p class="mb-1"><strong>Disk:</strong> {{ $status['disk'] }}</p>
                                        <p class="mb-1"><strong>Reachable:</strong> {{ $status['reachable'] ? 'Yes' : 'No' }}</p>
                                        <p class="mb-1"><strong>Latest backup:</strong> {{ $status['newest_backup'] ?? 'None' }}</p>
                                        <p class="mb-0"><strong>Storage used:</strong> {{ $status['used_storage_mb'] }} MB</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <div class="col-md-6 mb-3">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body">
                                    <h5 class="card-title">Retention Policy</h5>
                                    <ul class="mb-0 small">
                                        <li>Keep all backups: {{ $retention['keep_all_backups_for_days'] ?? 7 }} days</li>
                                        <li>Keep daily backups: {{ $retention['keep_daily_backups_for_days'] ?? 16 }} days</li>
                                        <li>Keep weekly backups: {{ $retention['keep_weekly_backups_for_weeks'] ?? 8 }} weeks</li>
                                        <li>Keep monthly backups: {{ $retention['keep_monthly_backups_for_months'] ?? 4 }} months</li>
                                        <li>Max storage: {{ $retention['delete_oldest_backups_when_using_more_megabytes_than'] ?? 5000 }} MB</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Available Backups</h5>
                        </div>
                        <div class="card-body p-0">
                            @if(count($backups) === 0)
                                <div class="p-4 text-muted">No backups found yet. Run a backup to get started.</div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Filename</th>
                                                <th>Date</th>
                                                <th>Size</th>
                                                <th>Disk</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($backups as $backup)
                                                <tr>
                                                    <td>{{ $backup['filename'] }}</td>
                                                    <td>{{ $backup['date'] }}</td>
                                                    <td>{{ $backup['size_human'] }}</td>
                                                    <td>{{ $backup['disk'] }}</td>
                                                    <td class="text-end">
                                                        <a href="{{ route('admin.backups.download', ['disk' => $backup['disk'], 'path' => $backup['path']]) }}"
                                                           class="btn btn-sm btn-outline-primary">Download</a>
                                                        <button type="button"
                                                                class="btn btn-sm btn-outline-danger"
                                                                onclick="confirmDeleteBackup('{{ $backup['disk'] }}', '{{ $backup['path'] }}', '{{ $backup['filename'] }}')">
                                                            Delete
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="card border-danger">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0">Restore from Backup</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning">
                                <strong>Warning:</strong> Restore will overwrite the current database and uploaded files.
                                The site will enter maintenance mode during restore. Payment records may desync from the payment gateway.
                            </div>

                            <form id="restore-form" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label for="backup_file" class="form-label">Backup file (.zip or .sql)</label>
                                    <input type="file" class="form-control" id="backup_file" name="backup_file" accept=".zip,.sql" required>
                                    <div class="form-text">Upload a Spatie .zip backup from production, or a .sql database dump.</div>
                                </div>

                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="restore_env" name="restore_env" value="1">
                                    <label class="form-check-label" for="restore_env">Also restore .env from zip backup (not recommended for offline dev)</label>
                                </div>

                                <div class="mb-3">
                                    <label for="confirmation" class="form-label">Type <strong>{{ config('app.name') }}</strong> to confirm</label>
                                    <input type="text" class="form-control" id="confirmation" name="confirmation" required>
                                </div>

                                <button type="submit" id="restore-btn" class="btn btn-danger">
                                    Restore Backup
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="alert alert-info mt-4 mb-0">
                        <h6>Notes</h6>
                        <ul class="mb-2">
                            <li>For local/offline restores, set <code>QUEUE_CONNECTION=sync</code> in <code>.env</code> unless a queue worker is running.</li>
                            <li>On production, ensure <code>php artisan queue:work</code> is running when using the database queue driver.</li>
                            <li>Large backups may require increasing PHP upload limits for restore.</li>
                        </ul>
                        <h6>CLI alternative</h6>
                        <p class="mb-1"><code>php artisan backup:run</code> — create a backup from the command line</p>
                        <p class="mb-0"><code>php artisan db:import /path/to/backup.zip</code> — restore from the command line</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="delete-backup-form" method="POST" action="{{ route('admin.backups.destroy') }}" class="d-none">
    @csrf
    @method('DELETE')
    <input type="hidden" name="disk" id="delete-disk">
    <input type="hidden" name="path" id="delete-path">
    <input type="hidden" name="confirmation" id="delete-confirmation">
</form>

<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let pollTimer = null;

    function showProgress(message, percent, type = 'info') {
        const alert = document.getElementById('operation-alert');
        const progress = document.getElementById('operation-progress');
        const bar = document.getElementById('operation-progress-bar');

        alert.className = `alert alert-${type}`;
        alert.textContent = message;
        alert.classList.remove('d-none');
        progress.classList.remove('d-none');
        bar.style.width = `${percent}%`;
        bar.textContent = `${percent}%`;
    }

    function pollProgress(operationId, type) {
        if (pollTimer) {
            clearInterval(pollTimer);
        }

        pollTimer = setInterval(async () => {
            const response = await fetch(`{{ route('admin.backups.progress') }}?operation_id=${operationId}&type=${type}`, {
                headers: { 'Accept': 'application/json' }
            });

            if (!response.ok) {
                return;
            }

            const data = await response.json();
            const alertType = data.status === 'failed' ? 'danger' : (data.status === 'completed' ? 'success' : 'info');
            showProgress(data.message, data.percent ?? 0, alertType);

            if (data.status === 'completed' || data.status === 'failed') {
                clearInterval(pollTimer);
                if (data.status === 'completed') {
                    setTimeout(() => window.location.reload(), 1500);
                }
            }
        }, 2000);
    }

    document.getElementById('run-backup-btn').addEventListener('click', async () => {
        if (!confirm('Start a new backup now?')) {
            return;
        }

        const button = document.getElementById('run-backup-btn');
        button.disabled = true;

        try {
            const response = await fetch('{{ route('admin.backups.run') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Failed to start backup.');
            }

            showProgress(data.message, 0, 'info');
            pollProgress(data.operation_id, 'backup');
        } catch (error) {
            showProgress(error.message, 0, 'danger');
        } finally {
            button.disabled = false;
        }
    });

    document.getElementById('restore-form').addEventListener('submit', async (event) => {
        event.preventDefault();

        if (!confirm('This will overwrite all current data. Are you absolutely sure?')) {
            return;
        }

        const form = event.target;
        const button = document.getElementById('restore-btn');
        const formData = new FormData(form);
        button.disabled = true;

        try {
            const response = await fetch('{{ route('admin.backups.restore') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: formData,
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || Object.values(data.errors || {}).flat().join(' ') || 'Restore failed to start.');
            }

            showProgress(data.message, 0, 'warning');
            pollProgress(data.operation_id, 'restore');
        } catch (error) {
            showProgress(error.message, 0, 'danger');
        } finally {
            button.disabled = false;
        }
    });

    function confirmDeleteBackup(disk, path, filename) {
        const confirmation = prompt(`Type DELETE to permanently remove backup:\n${filename}`);
        if (confirmation !== 'DELETE') {
            return;
        }

        document.getElementById('delete-disk').value = disk;
        document.getElementById('delete-path').value = path;
        document.getElementById('delete-confirmation').value = 'DELETE';
        document.getElementById('delete-backup-form').submit();
    }
</script>
</x-alumniadmin-dashboard>
