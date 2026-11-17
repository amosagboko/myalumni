@props([
    'programmes',
    'departments',
    'faculties',
    'years',
    'categories',
    'embedded' => false,
    'showAdminActions' => null,
    'dashboardRoute' => null,
    'dashboardLabel' => 'Dashboard',
])

@php
    $user = auth()->user();
    $showAdminActions = $showAdminActions ?? $user?->hasRole('administrator');
    $isAro = $user?->hasRole('alumni-relations-officer') && ! $user?->hasRole('administrator');
    $dashboardRoute = $dashboardRoute ?? ($isAro
        ? route('alumni-relations-officer.home')
        : route('admin.dashboard'));
@endphp

<x-admin.surface-styles />

<div class="main-content right-chat-active admin-surface">
    <div class="middle-sidebar-bottom">
        <div class="middle-sidebar-left pe-0">
            <div class="row">
                <div class="col-12">

                    <div class="ads-page-header">
                        <div>
                            <h1 class="ads-page-title">Upload Alumni</h1>
                            <p class="ads-page-subtitle">Import alumni records from CSV or Excel, then search imported data.</p>
                        </div>
                        <div class="ads-page-actions">
                            <a href="{{ $dashboardRoute }}" class="btn btn-sm btn-outline-secondary">
                                <i data-feather="arrow-left" style="width: 14px; height: 14px;"></i>
                                {{ $dashboardLabel }}
                            </a>
                            <a href="{{ route('retrieve.credentials') }}" class="btn btn-sm ads-btn-primary text-white">
                                <i data-feather="key" style="width: 14px; height: 14px;"></i>
                                Retrieve credentials
                            </a>
                        </div>
                    </div>

                    @if (session()->has('success'))
                        <div class="ads-alert ads-alert-success">{{ session('success') }}</div>
                    @endif

                    @if (session()->has('warning'))
                        <div class="ads-alert ads-alert-warning" style="white-space: pre-line;">{{ session('warning') }}</div>
                    @endif

                    @if (session()->has('error'))
                        <div class="ads-alert ads-alert-error">{{ session('error') }}</div>
                    @endif

                    <div class="ads-section">
                        <div class="ads-section-card">
                            <h2 class="ads-section-title">Full records (all columns)</h2>

                            <div id="import-progress" class="ads-progress-wrap" style="display: none;">
                                <div class="progress ads-progress">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                                </div>
                                <small class="ads-progress-text" id="progress-text">Processing…</small>
                            </div>

                            <form action="{{ route('upload.alumni.store') }}" method="POST" enctype="multipart/form-data" id="upload-form">
                                @csrf
                                <div class="mb-3">
                                    <label for="file" class="form-label">Upload file</label>
                                    <input type="file" class="form-control form-control-sm" id="file" name="file" accept=".csv,.xlsx,.xls" required>
                                    <div class="form-text mt-2">
                                        <strong>CSV columns:</strong> firstname, surname, matriculation_id, programme, department, faculty, year_of_graduation, <strong>category</strong>, date_of_birth (YYYY-MM-DD), state, lga, year_of_entry, gender
                                        <br>
                                        <small class="text-muted">
                                            <strong>Valid categories:</strong> Postgraduate, Undergraduate (Full-time), Undergraduate (Part-time), Diploma
                                        </small>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-sm ads-btn-primary" id="upload-button">
                                    <i data-feather="upload" style="width: 14px; height: 14px;"></i>
                                    Upload
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="ads-section">
                        <div class="ads-section-card">
                            <h2 class="ads-section-title">Limited records (matric, first name, surname)</h2>
                            <p class="text-muted small mb-3">
                                Use this for batches that only have identity fields. Graduates will complete faculty, department, programme, category, date of birth, state, LGA, year of entry, and gender on the public form before credentials.
                            </p>

                            <div id="limited-import-progress" class="ads-progress-wrap" style="display: none;">
                                <div class="progress ads-progress">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                                </div>
                                <small class="ads-progress-text" id="limited-progress-text">Processing…</small>
                            </div>

                            <form action="{{ route('upload.alumni.limited') }}" method="POST" enctype="multipart/form-data" id="limited-upload-form">
                                @csrf
                                <div class="mb-3">
                                    <label for="limited-file" class="form-label">Upload file</label>
                                    <input type="file" class="form-control form-control-sm" id="limited-file" name="file" accept=".csv,.xlsx,.xls" required>
                                    <div class="form-text mt-2">
                                        <strong>CSV columns:</strong> firstname, surname, matriculation_id
                                        <br>
                                        <small class="text-muted">
                                            Aliases accepted: first_name, matric_number. Year of graduation is set to 2026. Duplicate matrics already in alumni or this list are skipped.
                                        </small>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-sm ads-btn-primary" id="limited-upload-button">
                                    <i data-feather="upload" style="width: 14px; height: 14px;"></i>
                                    Upload limited records
                                </button>
                            </form>
                        </div>
                    </div>

                    @php
                        $limitedSkipped = session('limited_import_skipped', []);
                        $limitedImportId = session('limited_import_id');
                    @endphp
                    @if (is_array($limitedSkipped) && count($limitedSkipped) > 0)
                        <div class="ads-section">
                            <div class="ads-section-card">
                                <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                                    <h2 class="ads-section-title mb-0">Records not uploaded ({{ count($limitedSkipped) }})</h2>
                                    <a href="{{ route('upload.alumni.limited.skipped', ['importId' => $limitedImportId]) }}" class="btn btn-sm btn-outline-secondary">
                                        <i data-feather="download" style="width: 14px; height: 14px;"></i>
                                        Download CSV
                                    </a>
                                </div>
                                <p class="text-muted small">These rows were not imported. Fix them in the file and upload again; already-imported matrics will be skipped.</p>
                                <div class="table-responsive" style="max-height: 420px; overflow: auto;">
                                    <table class="table table-sm table-striped mb-0">
                                        <thead class="table-light" style="position: sticky; top: 0;">
                                            <tr>
                                                <th>Row</th>
                                                <th>First name</th>
                                                <th>Surname</th>
                                                <th>Matriculation number</th>
                                                <th>Reason</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($limitedSkipped as $skipped)
                                                <tr>
                                                    <td>{{ $skipped['row'] ?? '' }}</td>
                                                    <td>{{ ($skipped['firstname'] ?? '') !== '' ? $skipped['firstname'] : '—' }}</td>
                                                    <td>{{ ($skipped['surname'] ?? '') !== '' ? $skipped['surname'] : '—' }}</td>
                                                    <td>{{ ($skipped['matric_number'] ?? '') !== '' ? $skipped['matric_number'] : '—' }}</td>
                                                    <td>{{ $skipped['reason'] ?? '' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="ads-section">
                        <div class="ads-section-card">
                            <h2 class="ads-section-title">Search alumni</h2>
                            <form action="{{ route('upload.alumni.search') }}" method="GET" class="ads-filter-form">
                                <div class="ads-filter-field">
                                    <label for="programme">Programme</label>
                                    <select name="programme" id="programme" class="form-select form-select-sm ads-select">
                                        <option value="">All programmes</option>
                                        @foreach ($programmes as $programme)
                                            <option value="{{ $programme }}" @selected(request('programme') == $programme)>{{ $programme }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="ads-filter-field">
                                    <label for="department">Department</label>
                                    <select name="department" id="department" class="form-select form-select-sm ads-select">
                                        <option value="">All departments</option>
                                        @foreach ($departments as $department)
                                            <option value="{{ $department }}" @selected(request('department') == $department)>{{ $department }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="ads-filter-field">
                                    <label for="faculty">Faculty</label>
                                    <select name="faculty" id="faculty" class="form-select form-select-sm ads-select">
                                        <option value="">All faculties</option>
                                        @foreach ($faculties as $faculty)
                                            <option value="{{ $faculty }}" @selected(request('faculty') == $faculty)>{{ $faculty }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="ads-filter-field">
                                    <label for="year_of_graduation">Year of graduation</label>
                                    <select name="year_of_graduation" id="year_of_graduation" class="form-select form-select-sm ads-select ads-select-narrow">
                                        <option value="">All years</option>
                                        @foreach ($years as $year)
                                            <option value="{{ $year }}" @selected(request('year_of_graduation') == $year)>{{ $year }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="ads-filter-field">
                                    <label for="category_id">Category</label>
                                    <select name="category_id" id="category_id" class="form-select form-select-sm ads-select">
                                        <option value="">All categories</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="ads-filter-field" style="flex: 0 0 auto; min-width: auto;">
                                    <label aria-hidden="true">&nbsp;</label>
                                    <button type="submit" class="btn btn-sm ads-btn-primary">
                                        <i data-feather="search" style="width: 14px; height: 14px;"></i>
                                        Search
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="ads-section">
                        <div class="ads-section-card">
                            <h2 class="ads-section-title">Quick actions</h2>
                            <div class="ads-quick-actions">
                                @if ($showAdminActions)
                                    <a href="{{ route('admin.alumni-categories.index') }}" class="ads-quick-action">
                                        <span class="ads-quick-action-icon"><i data-feather="tag"></i></span>
                                        <span>Manage categories</span>
                                    </a>
                                    <a href="{{ route('admin.alumni-categories.assign') }}" class="ads-quick-action">
                                        <span class="ads-quick-action-icon"><i data-feather="user-check"></i></span>
                                        <span>Assign categories</span>
                                    </a>
                                    <a href="{{ route('admin.fee-templates.index') }}" class="ads-quick-action">
                                        <span class="ads-quick-action-icon"><i data-feather="file-text"></i></span>
                                        <span>Fee templates</span>
                                    </a>
                                    <a href="{{ route('admin.transactions.index') }}" class="ads-quick-action">
                                        <span class="ads-quick-action-icon"><i data-feather="credit-card"></i></span>
                                        <span>Transactions</span>
                                    </a>
                                @else
                                    <a href="{{ route('alumni-relations-officer.users') }}" class="ads-quick-action">
                                        <span class="ads-quick-action-icon"><i data-feather="users"></i></span>
                                        <span>Manage alumni</span>
                                    </a>
                                    <a href="{{ route('retrieve.credentials') }}" class="ads-quick-action">
                                        <span class="ads-quick-action-icon"><i data-feather="key"></i></span>
                                        <span>Retrieve credentials</span>
                                    </a>
                                    <a href="{{ route('create.event.index') }}" class="ads-quick-action">
                                        <span class="ads-quick-action-icon"><i data-feather="calendar"></i></span>
                                        <span>Homepage content</span>
                                    </a>
                                    <a href="{{ route('alumni-relations-officer.home') }}" class="ads-quick-action">
                                        <span class="ads-quick-action-icon"><i data-feather="home"></i></span>
                                        <span>Dashboard</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const uploadForm = document.getElementById('upload-form');
    const importProgress = document.getElementById('import-progress');
    const progressBar = document.querySelector('#import-progress .progress-bar');
    const progressText = document.getElementById('progress-text');
    const uploadButton = document.getElementById('upload-button');
    const limitedForm = document.getElementById('limited-upload-form');
    const limitedProgress = document.getElementById('limited-import-progress');
    const limitedProgressBar = document.querySelector('#limited-import-progress .progress-bar');
    const limitedProgressText = document.getElementById('limited-progress-text');
    const limitedUploadButton = document.getElementById('limited-upload-button');
    let importId = null;

    if (typeof feather !== 'undefined') {
        feather.replace();
    }

    uploadForm?.addEventListener('submit', function () {
        importProgress.style.display = 'block';
        uploadButton.disabled = true;
        progressBar.style.width = '0%';
        progressText.textContent = 'Starting import…';
    });

    limitedForm?.addEventListener('submit', function () {
        limitedProgress.style.display = 'block';
        limitedUploadButton.disabled = true;
        limitedProgressBar.style.width = '0%';
        limitedProgressText.textContent = 'Starting import…';
    });

    @if (session()->has('importId'))
        importId = @json(session('importId'));
        const isLimitedImport = String(importId).indexOf('limited_import_') === 0;
        const activeProgress = isLimitedImport ? limitedProgress : importProgress;
        const activeBar = isLimitedImport ? limitedProgressBar : progressBar;
        const activeText = isLimitedImport ? limitedProgressText : progressText;
        const activeButton = isLimitedImport ? limitedUploadButton : uploadButton;
        if (activeProgress) {
            activeProgress.style.display = 'block';
        }
        if (activeButton) {
            activeButton.disabled = true;
        }
        checkProgress(activeBar, activeText, activeButton);
    @endif

    function checkProgress(bar, text, button) {
        if (!importId) return;

        fetch(@json(route('upload.alumni.progress')) + '?importId=' + encodeURIComponent(importId))
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    text.textContent = 'Unable to load progress.';
                    return;
                }

                bar.style.width = `${data.progress ?? 0}%`;
                text.textContent = data.total
                    ? `Processed ${data.processed ?? 0} of ${data.total} records`
                    : (data.message || 'Processing…');

                if (!data.completed) {
                    setTimeout(function () { checkProgress(bar, text, button); }, 1000);
                } else {
                    text.textContent = 'Import completed.';
                    if (button) {
                        button.disabled = false;
                    }
                }
            })
            .catch(() => {
                text.textContent = 'Error checking progress.';
            });
    }
});
</script>
@endpush
