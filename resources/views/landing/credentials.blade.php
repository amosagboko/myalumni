<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Alumni Credentials - FuLafia Alumni Portal</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        body {
            background-color: #f8f9fa;
        }
        .credentials-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-top: 50px;
        }
        .credentials-header {
            border-bottom: 2px solid #f8f9fa;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .credentials-table th {
            width: 200px;
            background-color: #f8f9fa;
        }
        .modal-header {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }
        .modal-footer {
            background-color: #f8f9fa;
            border-top: 2px solid #dee2e6;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('landing') }}">FuLafia Alumni Portal</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="credentials-card">
                    <div class="credentials-header">
                        <h2 class="text-center mb-0">Your Alumni Credentials</h2>
                    </div>

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('info'))
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            {{ session('info') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table credentials-table">
                            <tbody>
                                @if($message)
                                <tr class="table-info">
                                    <td colspan="2" class="text-center">
                                        <strong>{{ $message }}</strong>
                                    </td>
                                </tr>
                                @endif
                                <tr>
                                    <th>Name</th>
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

                    <div class="mt-4">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#updateEmailModal">
                            Update Email
                        </button>
                        <button type="button" class="btn btn-warning" id="resendCredentialsBtn">
                            Resend Credentials
                        </button>
                        <a href="{{ route('landing') }}" class="btn btn-secondary">Back to Home</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Email Modal -->
    <div class="modal fade" id="updateEmailModal" tabindex="-1" aria-labelledby="updateEmailModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateEmailModalLabel">Update Your Email</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="updateEmailForm" method="POST" action="{{ route('landing.update-email') }}">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="matriculation_id" value="{{ $matriculation_id }}">
                        <div class="mb-3">
                            <label for="new_email" class="form-label">New Email Address</label>
                            <input type="email" class="form-control" id="new_email" name="new_email" required>
                            <div class="invalid-feedback" id="emailError"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="updateEmailBtn">Update Email</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('updateEmailForm');
            const submitBtn = document.getElementById('updateEmailBtn');
            const emailError = document.getElementById('emailError');
            
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

            // Reset form and error message when modal is closed
            const modal = document.getElementById('updateEmailModal');
            modal.addEventListener('hidden.bs.modal', function () {
                form.reset();
                emailError.style.display = 'none';
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Update Email';
            });
        });

        document.getElementById('resendCredentialsBtn').addEventListener('click', function() {
            if (confirm('Are you sure you want to resend the credentials? A new password reset link will be sent to your email.')) {
                fetch('{{ route("landing.resend-credentials") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        matriculation_id: '{{ $matriculation_id }}'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                    } else {
                        alert(data.message || 'An error occurred while resending the credentials.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while resending the credentials. Please try again.');
                });
            }
        });
    </script>
</body>
</html> 