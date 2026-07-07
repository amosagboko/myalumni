<x-alumniadmin-dashboard>
    <x-admin.surface-styles />
    <x-admin.data-table-styles />

    <div class="main-content right-chat-active admin-data-table">
        <div class="middle-sidebar-bottom">
            <div class="middle-sidebar-left pe-0">
                <div class="row">
                    <div class="col-12">

                        @php
                            $healthItem = $health[0] ?? null;
                            $backupCount = count($backups);
                        @endphp

                        <div class="ads-page-header">
                            <div>
                                <h1 class="ads-page-title">Backup &amp; restore</h1>
                                <p class="ads-page-subtitle">Create backups, download archives, and restore the system when needed.</p>
                            </div>
                            <button type="button" id="run-backup-btn" class="btn btn-sm ads-btn-primary text-white">
                                <i data-feather="hard-drive" style="width: 14px; height: 14px;"></i>
                                Run backup now
                            </button>
                        </div>

                        @if (session('success'))
                            <div class="ads-alert ads-alert-success">{{ session('success') }}</div>
                        @endif

                        @if (session('error'))
                            <div class="ads-alert ads-alert-error">{{ session('error') }}</div>
                        @endif

                        @if (!empty($loadError))
                            <div class="ads-alert ads-alert-warning">
                                Some backup details could not be loaded: {{ $loadError }}
                            </div>
                        @endif

                        <div id="operation-alert" class="ads-alert d-none" role="alert"></div>
                        <div id="operation-progress" class="ads-progress-wrap d-none">
                            <div class="progress ads-progress">
                                <div id="operation-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%">0%</div>
                            </div>
                        </div>

                        <div class="ads-stats">
                            <div class="ads-stat">
                                <span class="ads-stat-label">Available backups</span>
                                <span class="ads-stat-value">{{ number_format($backupCount) }}</span>
                            </div>
                            <div class="ads-stat">
                                <span class="ads-stat-label">Health</span>
                                <span class="ads-stat-value ads-stat-value-sm">
                                    @if ($healthItem && $healthItem['healthy'])
                                        Healthy
                                    @elseif ($healthItem)
                                        Needs attention
                                    @else
                                        Unknown
                                    @endif
                                </span>
                            </div>
                            <div class="ads-stat">
                                <span class="ads-stat-label">Latest backup</span>
                                <span class="ads-stat-value ads-stat-value-sm">{{ $healthItem['newest_backup'] ?? 'None' }}</span>
                            </div>
                            <div class="ads-stat">
                                <span class="ads-stat-label">Storage used</span>
                                <span class="ads-stat-value ads-stat-value-sm">{{ $healthItem['used_storage_mb'] ?? 0 }} MB</span>
                            </div>
                        </div>

                        @if ($healthItem || !empty($retention))
                            <div class="admin-surface" style="padding-bottom: 0;">
                                <div class="ads-section">
                                    <div class="row g-3">
                                        @if ($healthItem)
                                            <div class="col-lg-6">
                                                <div class="ads-section-card h-100">
                                                    <h2 class="ads-section-title">Backup health</h2>
                                                    <dl class="row small mb-0">
                                                        <dt class="col-sm-5 text-muted">Status</dt>
                                                        <dd class="col-sm-7 mb-2">
                                                            @if ($healthItem['healthy'])
                                                                <span class="adt-status adt-status-active">
                                                                    <span class="adt-status-dot"></span>
                                                                    Healthy
                                                                </span>
                                                            @else
                                                                <span class="adt-status adt-status-inactive">
                                                                    <span class="adt-status-dot"></span>
                                                                    Needs attention
                                                                </span>
                                                            @endif
                                                        </dd>
                                                        <dt class="col-sm-5 text-muted">Disk</dt>
                                                        <dd class="col-sm-7 mb-2">{{ $healthItem['disk'] }}</dd>
                                                        <dt class="col-sm-5 text-muted">Reachable</dt>
                                                        <dd class="col-sm-7 mb-2">{{ $healthItem['reachable'] ? 'Yes' : 'No' }}</dd>
                                                        <dt class="col-sm-5 text-muted">Latest backup</dt>
                                                        <dd class="col-sm-7 mb-2">{{ $healthItem['newest_backup'] ?? 'None' }}</dd>
                                                        <dt class="col-sm-5 text-muted mb-0">Storage used</dt>
                                                        <dd class="col-sm-7 mb-0">{{ $healthItem['used_storage_mb'] }} MB</dd>
                                                    </dl>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="col-lg-6">
                                            <div class="ads-section-card h-100">
                                                <h2 class="ads-section-title">Retention policy</h2>
                                                <ul class="small text-muted mb-0 ps-3">
                                                    <li class="mb-1">Keep all backups: {{ $retention['keep_all_backups_for_days'] ?? 7 }} days</li>
                                                    <li class="mb-1">Keep daily backups: {{ $retention['keep_daily_backups_for_days'] ?? 16 }} days</li>
                                                    <li class="mb-1">Keep weekly backups: {{ $retention['keep_weekly_backups_for_weeks'] ?? 8 }} weeks</li>
                                                    <li class="mb-1">Keep monthly backups: {{ $retention['keep_monthly_backups_for_months'] ?? 4 }} months</li>
                                                    <li>Max storage: {{ $retention['delete_oldest_backups_when_using_more_megabytes_than'] ?? 5000 }} MB</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="adt-panel mt-3">
                            <div class="px-3 pt-3 pb-2 border-bottom">
                                <h2 class="ads-section-title mb-0" style="border: none; padding: 0;">Available backups</h2>
                            </div>

                            @if ($backupCount === 0)
                                <div class="p-4">
                                    <div class="adt-empty py-4">
                                        <div class="adt-empty-icon">
                                            <i data-feather="archive" style="width: 28px; height: 28px;"></i>
                                        </div>
                                        <h3 class="adt-empty-title">No backups listed</h3>
                                        <p class="adt-empty-text">Run a backup to get started.</p>
                                    </div>
                                    @if (($storageDiagnostics['zip_count_on_disk'] ?? 0) > 0)
                                        <div class="ads-alert ads-alert-warning mt-3 mb-0">
                                            <strong>{{ $storageDiagnostics['zip_count_on_disk'] }} backup zip file(s) exist on disk</strong> but the web server cannot list them.
                                            This usually happens when <code>php artisan backup:run</code> was run as <code>root</code> while the site runs as <code>www-data</code>.
                                            <hr class="my-2">
                                            <p class="mb-1"><strong>Path:</strong> <code>{{ $storageDiagnostics['storage_path'] }}</code></p>
                                            <p class="mb-0">On the server, run:</p>
                                            <pre class="mb-0 mt-2 small"><code>chown -R www-data:www-data storage/app/private
chmod -R 775 storage/app/private</code></pre>
                                        </div>
                                    @else
                                        <p class="text-muted small text-center mb-0">
                                            Check that files exist under <code>{{ $storageDiagnostics['storage_path'] ?? 'storage/app/private/{APP_NAME}' }}</code>.
                                        </p>
                                    @endif
                                </div>
                            @else
                                <div class="adt-table-wrap">
                                    <table class="adt-table">
                                        <thead>
                                            <tr>
                                                <th>Filename</th>
                                                <th>Date</th>
                                                <th>Size</th>
                                                <th>Disk</th>
                                                <th class="adt-th-actions">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($backups as $backup)
                                                <tr>
                                                    <td class="fw-medium">{{ $backup['filename'] }}</td>
                                                    <td class="adt-muted">{{ $backup['date'] }}</td>
                                                    <td class="adt-muted">{{ $backup['size_human'] }}</td>
                                                    <td><span class="adt-tag">{{ $backup['disk'] }}</span></td>
                                                    <td>
                                                        <div class="adt-actions">
                                                            <a
                                                                href="{{ route('admin.backups.download', ['disk' => $backup['disk'], 'path' => $backup['path']]) }}"
                                                                class="adt-action-btn"
                                                                title="Download"
                                                            >
                                                                <i data-feather="download" style="width: 14px; height: 14px;"></i>
                                                            </a>
                                                            <button
                                                                type="button"
                                                                class="adt-action-btn adt-action-danger"
                                                                title="Delete"
                                                                onclick="confirmDeleteBackup(@json($backup['disk']), @json($backup['path']), @json($backup['filename']))"
                                                            >
                                                                <i data-feather="trash-2" style="width: 14px; height: 14px;"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>

                        <div class="admin-surface mt-3">
                            <div class="ads-section">
                                <div class="ads-section-card">
                                    <h2 class="ads-section-title">Restore from backup</h2>
                                    <div class="ads-alert ads-alert-warning mb-3">
                                        <strong>Warning:</strong> Restore will overwrite the current database and uploaded files.
                                        The site will enter maintenance mode during restore. Payment records may desync from the payment gateway.
                                    </div>

                                    <form id="restore-form" enctype="multipart/form-data">
                                        @csrf
                                        <div class="mb-3" style="max-width: 520px;">
                                            <label for="backup_file" class="form-label">Backup file (.zip or .sql)</label>
                                            <input type="file" class="form-control form-control-sm" id="backup_file" name="backup_file" accept=".zip,.sql" required>
                                            <div class="form-text">Upload a Spatie .zip backup from production, or a .sql database dump.</div>
                                        </div>

                                        <div class="mb-3 form-check" style="max-width: 520px;">
                                            <input type="checkbox" class="form-check-input" id="restore_env" name="restore_env" value="1">
                                            <label class="form-check-label" for="restore_env">Also restore .env from zip backup (not recommended for offline dev)</label>
                                        </div>

                                        <div class="mb-4" style="max-width: 520px;">
                                            <label for="confirmation" class="form-label">Type <strong>{{ config('app.name') }}</strong> to confirm</label>
                                            <input type="text" class="form-control form-control-sm" id="confirmation" name="confirmation" required>
                                        </div>

                                        <button type="submit" id="restore-btn" class="btn btn-sm btn-outline-secondary">
                                            <i data-feather="rotate-ccw" style="width: 14px; height: 14px;"></i>
                                            Restore backup
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <div class="ads-section">
                                <div class="ads-section-card">
                                    <h2 class="ads-section-title">Notes</h2>
                                    <ul class="small text-muted mb-3 ps-3">
                                        <li class="mb-1">For local/offline restores, set <code>QUEUE_CONNECTION=sync</code> in <code>.env</code> unless a queue worker is running.</li>
                                        <li class="mb-1">On production, ensure <code>php artisan queue:work</code> is running when using the database queue driver.</li>
                                        <li class="mb-1">Large backups may require increasing PHP upload limits for restore.</li>
                                    </ul>
                                    <h3 class="small fw-semibold mb-2">CLI alternative</h3>
                                    <p class="small text-muted mb-1"><code>php artisan backup:run</code> — create a backup from the command line</p>
                                    <p class="small text-muted mb-0"><code>php artisan db:import /path/to/backup.zip</code> — restore from the command line</p>
                                </div>
                            </div>
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

    @push('scripts')
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        let pollTimer = null;

        function alertClassForType(type) {
            if (type === 'danger') return 'ads-alert ads-alert-error';
            if (type === 'success') return 'ads-alert ads-alert-success';
            if (type === 'warning') return 'ads-alert ads-alert-warning';
            return 'ads-alert';
        }

        function showProgress(message, percent, type = 'info') {
            const alert = document.getElementById('operation-alert');
            const progress = document.getElementById('operation-progress');
            const bar = document.getElementById('operation-progress-bar');

            alert.className = alertClassForType(type);
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
                const response = await fetch(@json(route('admin.backups.run')), {
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
                const response = await fetch(@json(route('admin.backups.restore')), {
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

        document.addEventListener('DOMContentLoaded', function () {
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        });
    </script>
    @endpush
</x-alumniadmin-dashboard>
