@props([
    'programmes',
    'departments',
    'faculties',
    'years',
    'categories',
    'embedded' => false,
    'showAdminActions' => null,
])

@php
    $showAdminActions = $showAdminActions ?? auth()->user()->hasRole('administrator');
@endphp

<x-admin.surface-styles />

@if ($embedded)
    <div class="admin-surface">
@else
    <div class="main-content right-chat-active admin-surface">
        <div class="middle-sidebar-bottom">
            <div class="middle-sidebar-left pe-0">
                <div class="row">
                    <div class="col-12">
@endif

                        <div class="ads-page-header">
                            <div>
                                <h1 class="ads-page-title">Upload Alumni</h1>
                                <p class="ads-page-subtitle">Import alumni records from CSV or Excel, then search imported data.</p>
                            </div>
                            <div class="ads-page-actions">
                                <a href="{{ route('retrieve.credentials') }}" class="btn btn-sm btn-outline-secondary">
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
                                <h2 class="ads-section-title">Import file</h2>

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

                        @if ($showAdminActions)
                            <div class="ads-section">
                                <div class="ads-section-card">
                                    <h2 class="ads-section-title">Quick actions</h2>
                                    <div class="ads-quick-actions">
                                        <a href="{{ route('admin.alumni-categories.index') }}" class="ads-quick-action">
                                            <i data-feather="tag"></i>
                                            Manage categories
                                        </a>
                                        <a href="{{ route('admin.alumni-categories.assign') }}" class="ads-quick-action">
                                            <i data-feather="user-check"></i>
                                            Assign categories
                                        </a>
                                        <a href="{{ route('admin.fee-templates.index') }}" class="ads-quick-action">
                                            <i data-feather="file-text"></i>
                                            Fee templates
                                        </a>
                                        <a href="{{ route('admin.transactions.index') }}" class="ads-quick-action">
                                            <i data-feather="credit-card"></i>
                                            Transactions
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endif

@if ($embedded)
    </div>
@else
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const uploadForm = document.getElementById('upload-form');
    const importProgress = document.getElementById('import-progress');
    const progressBar = document.querySelector('#import-progress .progress-bar');
    const progressText = document.getElementById('progress-text');
    const uploadButton = document.getElementById('upload-button');
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

    @if (session()->has('importId'))
        importId = @json(session('importId'));
        importProgress.style.display = 'block';
        uploadButton.disabled = true;
        checkProgress();
    @endif

    function checkProgress() {
        if (!importId) return;

        fetch(@json(route('upload.alumni.progress')) + '?importId=' + encodeURIComponent(importId))
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    progressText.textContent = 'Unable to load progress.';
                    return;
                }

                progressBar.style.width = `${data.progress ?? 0}%`;
                progressText.textContent = data.total
                    ? `Processed ${data.processed ?? 0} of ${data.total} records`
                    : (data.message || 'Processing…');

                if (!data.completed) {
                    setTimeout(checkProgress, 1000);
                } else {
                    progressText.textContent = 'Import completed.';
                    uploadButton.disabled = false;
                }
            })
            .catch(() => {
                progressText.textContent = 'Error checking progress.';
            });
    }
});
</script>
@endpush
