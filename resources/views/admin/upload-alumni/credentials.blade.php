<x-alumniadmin-dashboard>
    <div class="container mt-5 pt-7" style="margin-left: 10px;">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                        <h6 class="mb-0">Alumni Credentials</h6>
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
                                    <a href="{{ route('admin.home') }}" class="btn btn-secondary">
                                        Back to Admin Dashboard
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
                                    <form method="POST" action="{{ route('upload.alumni.resend-credentials') }}" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="matriculation_id" value="{{ $matriculation_id }}">
                                        <button type="submit" class="btn btn-warning">
                                            Resend Credentials
                                        </button>
                                    </form>

                                    <button type="button" class="btn btn-primary" onclick="openUpdateEmailModal()">
                                        Update Email
                                    </button>
                                </div>
                            </div>

                            <!-- Update Email Popup -->
                            <div id="updateEmailPopup" class="popup-overlay" style="display: none;">
                                <div class="popup-content">
                                    <div class="popup-header">
                                        <h5>Update Alumni Email</h5>
                                        <button type="button" class="close-btn" onclick="closeUpdateEmailPopup()">&times;</button>
                                    </div>
                                    <form method="POST" action="{{ route('upload.alumni.update-email') }}" id="updateEmailForm">
                                        @csrf
                                        <div class="popup-body">
                                            <input type="hidden" name="matriculation_id" value="{{ $matriculation_id }}">
                                            <div class="mb-3">
                                                <label for="new_email" class="form-label">New Email Address</label>
                                                <input type="email" class="form-control" id="new_email" name="new_email" required>
                                                <div class="invalid-feedback" id="emailError"></div>
                                            </div>
                                        </div>
                                        <div class="popup-footer">
                                            <button type="button" class="btn btn-secondary" onclick="closeUpdateEmailPopup()">Close</button>
                                            <button type="submit" class="btn btn-primary" id="updateEmailBtn">Update Email</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            @push('scripts')
                            <script>
                                function openUpdateEmailModal() {
                                    document.getElementById('updateEmailPopup').style.display = 'flex';
                                }

                                function closeUpdateEmailPopup() {
                                    document.getElementById('updateEmailPopup').style.display = 'none';
                                }

                                document.addEventListener('DOMContentLoaded', function() {
                                    const form = document.getElementById('updateEmailForm');
                                    const submitBtn = document.getElementById('updateEmailBtn');
                                    
                                    form.addEventListener('submit', function(e) {
                                        e.preventDefault();
                                        
                                        // Disable submit button and show loading state
                                        submitBtn.disabled = true;
                                        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...';
                                        
                                        // Submit form using fetch
                                        fetch(this.action, {
                                            method: 'POST',
                                            body: new FormData(this),
                                            headers: {
                                                'X-Requested-With': 'XMLHttpRequest'
                                            }
                                        })
                                        .then(response => response.json())
                                        .then(data => {
                                            if (data.success) {
                                                // Show success message and reload page
                                                alert(data.message);
                                                window.location.reload();
                                            } else {
                                                // Show error message
                                                const emailError = document.getElementById('emailError');
                                                emailError.textContent = data.message;
                                                emailError.style.display = 'block';
                                                submitBtn.disabled = false;
                                                submitBtn.innerHTML = 'Update Email';
                                            }
                                        })
                                        .catch(error => {
                                            console.error('Error:', error);
                                            alert('An error occurred while updating the email. Please try again.');
                                            submitBtn.disabled = false;
                                            submitBtn.innerHTML = 'Update Email';
                                        });
                                    });
                                });
                            </script>
                            @endpush

                            <style>
                                .popup-overlay {
                                    position: fixed;
                                    top: 0;
                                    left: 0;
                                    width: 100%;
                                    height: 100%;
                                    background: rgba(0, 0, 0, 0.5);
                                    display: flex;
                                    justify-content: center;
                                    align-items: center;
                                    z-index: 1000;
                                }
                                .popup-content {
                                    background: white;
                                    padding: 20px;
                                    border-radius: 5px;
                                    width: 90%;
                                    max-width: 500px;
                                    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                                }
                                .popup-header {
                                    display: flex;
                                    justify-content: space-between;
                                    align-items: center;
                                    margin-bottom: 20px;
                                }
                                .popup-header h5 {
                                    margin: 0;
                                }
                                .close-btn {
                                    background: none;
                                    border: none;
                                    font-size: 24px;
                                    cursor: pointer;
                                    padding: 0;
                                    color: #666;
                                }
                                .popup-body {
                                    margin-bottom: 20px;
                                }
                                .popup-footer {
                                    display: flex;
                                    justify-content: flex-end;
                                    gap: 10px;
                                }
                            </style>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-alumniadmin-dashboard>

<style>
    @media print {
        .btn, .card-header, form {
            display: none !important;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
        }
        .container {
            margin-left: 0 !important;
            padding: 0 !important;
        }
        .table {
            border: 1px solid #dee2e6 !important;
        }
        .table th, .table td {
            border: 1px solid #dee2e6 !important;
        }
    }
</style> 