<x-layouts.alumni-relations-officer>
    <div class="container mt-5 pt-5" style="margin-left: 10px;">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                        <h6 class="mb-0">Retrieve Alumni Credentials</h6>
                    </div>
                    <div class="card-body p-3">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="mb-4">
                            <h5 class="card-title mb-3">Search Alumni Credentials</h5>
                            
                            <form method="GET" action="{{ route('upload.alumni.credentials') }}" class="needs-validation" novalidate>
                                <div class="mb-3">
                                    <label for="matriculation_id" class="form-label">Matriculation Number</label>
                                    <input type="text" class="form-control" id="matriculation_id" name="matriculation_id" required>
                                    <div class="form-text">Enter the matriculation number to retrieve temporary login credentials</div>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">Search</button>
                                    <a href="{{ route('alumni-relations-officer.home') }}" class="btn btn-secondary">
                                        Back to ARO Dashboard
                                    </a>
                                </div>
                            </form>
                        </div>

                        @if(isset($alumni))
                            <div class="mt-4">
                                <h5 class="card-title mb-3">Alumni Credentials</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <tbody>
                                            <tr>
                                                <th style="width: 200px;">Name</th>
                                                <td>{{ $name }}</td>
                                            </tr>
                                            <tr>
                                                <th>Matriculation Number</th>
                                                <td>{{ $matriculation_id }}</td>
                                            </tr>
                                            <tr>
                                                <th>Temporary Email</th>
                                                <td>{{ $tempEmail }}</td>
                                            </tr>
                                            <tr>
                                                <th>Category</th>
                                                <td>{{ $category ? $category->name : 'Not Set' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="mt-3">
                                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#updateEmailModal">
                                        Update Email
                                    </button>
                                    <form method="POST" action="{{ route('upload.alumni.resend-credentials') }}" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="matriculation_id" value="{{ $matriculation_id }}">
                                        <button type="submit" class="btn btn-info">Resend Credentials</button>
                                    </form>
                                </div>
                            </div>

                            <!-- Update Email Modal -->
                            <div class="modal fade" id="updateEmailModal" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Update Alumni Email</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form id="updateEmailForm">
                                                <input type="hidden" name="matriculation_id" value="{{ $matriculation_id }}">
                                                <div class="mb-3">
                                                    <label for="new_email" class="form-label">New Email Address</label>
                                                    <input type="email" class="form-control" id="new_email" name="new_email" required>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="button" class="btn btn-primary" onclick="updateEmail()">Update Email</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.alumni-relations-officer>

@push('scripts')
<script>
function updateEmail() {
    const form = document.getElementById('updateEmailForm');
    const formData = new FormData(form);

    fetch('{{ route("upload.alumni.update-email") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(Object.fromEntries(formData))
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the email.');
    });
}
</script>
@endpush 