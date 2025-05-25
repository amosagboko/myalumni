<x-layouts.alumni-relations-officer>
    <div class="container mt-3 pt-7" style="margin-left: 0px;">
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

                        <div class="mt-4">
                            <h6>Search Alumni</h6>
                            <form action="{{ route('upload.alumni.search') }}" method="GET" class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Programme</label>
                                    <select name="programme" class="form-select">
                                        <option value="">Select Programme</option>
                                        @foreach($programmes as $programme)
                                            <option value="{{ $programme }}" {{ request('programme') == $programme ? 'selected' : '' }}>
                                                {{ $programme }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Department</label>
                                    <select name="department" class="form-select">
                                        <option value="">Select Department</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department }}" {{ request('department') == $department ? 'selected' : '' }}>
                                                {{ $department }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Faculty</label>
                                    <select name="faculty" class="form-select">
                                        <option value="">Select Faculty</option>
                                        @foreach($faculties as $faculty)
                                            <option value="{{ $faculty }}" {{ request('faculty') == $faculty ? 'selected' : '' }}>
                                                {{ $faculty }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Year of Graduation</label>
                                    <select name="year_of_graduation" class="form-select">
                                        <option value="">Select Year</option>
                                        @foreach($years as $year)
                                            <option value="{{ $year }}" {{ request('year_of_graduation') == $year ? 'selected' : '' }}>
                                                {{ $year }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 mt-3">
                                    <button type="submit" class="btn btn-primary">Search</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    function showProgress() {
        document.getElementById('import-progress').style.display = 'block';
        document.getElementById('upload-button').disabled = true;
        
        // Poll for progress
        const progressBar = document.querySelector('.progress-bar');
        const progressText = document.getElementById('progress-text');
        
        const pollInterval = setInterval(() => {
            fetch('{{ route("upload.alumni.progress") }}')
                .then(response => response.json())
                .then(data => {
                    if (data.progress) {
                        progressBar.style.width = data.progress + '%';
                        progressText.textContent = data.message || 'Processing...';
                        
                        if (data.progress >= 100) {
                            clearInterval(pollInterval);
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        }
                    }
                })
                .catch(error => {
                    console.error('Error polling progress:', error);
                    clearInterval(pollInterval);
                });
        }, 1000);
    }
    </script>
    @endpush
</x-layouts.alumni-relations-officer> 