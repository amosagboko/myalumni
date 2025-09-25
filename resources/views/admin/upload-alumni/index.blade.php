<x-alumniadmin-dashboard>
    <div class="container mt-3 pt-7" style="margin-left: 150px;">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                        <h6 class="mb-0">Upload Alumni</h6>
                    </div>
                    <div class="card-body p-3">
                        @if (session()->has('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if (session()->has('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif

                        <div id="import-progress" class="mb-3" style="display: none;">
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                            </div>
                            <small class="text-muted mt-1 d-block" id="progress-text">Processing...</small>
                        </div>

                        <form action="{{ route('upload.alumni.store') }}" method="POST" enctype="multipart/form-data" id="upload-form" onsubmit="showProgress()">
                            @csrf
                            <div class="mb-3">
                                <label for="file" class="form-label">Upload File</label>
                                <input type="file" class="form-control" id="file" name="file" accept=".csv,.xlsx,.xls">
                                <div class="form-text">Please upload a CSV or Excel file with the following columns: firstname, surname, matriculation_id, programme, department, faculty, year_of_graduation, category, date_of_birth (YYYY-MM-DD), state, lga, year_of_entry, gender (enter the specific gender for each alumni)</div>
                            </div>
                            <button type="submit" class="btn btn-primary" id="upload-button">Upload</button>
                        </form>

                        <!-- Quick Actions -->
                        <div class="mt-4">
                            <div class="card">
                                <div class="card-header bg-white">
                                    <h6 class="mb-0">Quick Actions</h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('admin.alumni-categories.index') }}" class="btn btn-primary btn-sm">
                                            <i class="feather-tag me-1"></i> Manage Categories
                                        </a>
                                        <a href="{{ route('admin.alumni-categories.assign') }}" class="btn btn-success btn-sm">
                                            <i class="feather-user-check me-1"></i> Assign Categories
                                        </a>
                                        <a href="{{ route('admin.fee-templates.index') }}" class="btn btn-info btn-sm">
                                            <i class="feather-file-text me-1"></i> Fee Templates
                                        </a>
                                        <a href="{{ route('admin.transactions.index') }}" class="btn btn-warning btn-sm">
                                            <i class="feather-credit-card me-1"></i> Transactions
                                        </a>
                                        <a href="{{ route('retrieve.credentials') }}" class="btn btn-secondary btn-sm">
                                            <i class="feather-key me-1"></i> Retrieve Credentials
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showProgress() {
            const importProgress = document.getElementById('import-progress');
            const uploadButton = document.getElementById('upload-button');
            const progressBar = document.querySelector('.progress-bar');
            const progressText = document.getElementById('progress-text');

            // Show progress bar immediately
            importProgress.style.display = 'block';
            uploadButton.disabled = true;
            progressBar.style.width = '0%';
            progressText.textContent = 'Starting import...';
        }

        document.addEventListener('DOMContentLoaded', function() {
            const uploadForm = document.getElementById('upload-form');
            const importProgress = document.getElementById('import-progress');
            const progressBar = document.querySelector('.progress-bar');
            const progressText = document.getElementById('progress-text');
            const uploadButton = document.getElementById('upload-button');
            let importId = null;

            // Check for existing import ID in session
            @if (session()->has('importId'))
                importId = '{{ session('importId') }}';
                importProgress.style.display = 'block';
                uploadButton.disabled = true;
                checkProgress();
            @endif

            // Function to check progress
            function checkProgress() {
                if (!importId) return;

                fetch(`{{ route('upload.alumni.progress') }}?importId=${importId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            console.error(data.error);
                            return;
                        }

                        progressBar.style.width = `${data.progress}%`;
                        progressText.textContent = `Processed ${data.processed} of ${data.total} records`;

                        if (!data.completed) {
                            setTimeout(checkProgress, 1000);
                        } else {
                            progressText.textContent = 'Import completed!';
                            uploadButton.disabled = false;
                        }
                    })
                    .catch(error => {
                        console.error('Error checking progress:', error);
                    });
            }
        });
    </script>
</x-alumniadmin-dashboard> 