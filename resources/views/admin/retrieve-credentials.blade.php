<x-alumniadmin-dashboard>
    <div class="container mt-5 pt-7" style="margin-left: 100px;">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                        <h6 class="mb-0">Retrieve Alumni Credentials</h6>
                    </div>
                    <div class="card-body p-3">
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

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Bootstrap 5 form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>
</x-alumniadmin-dashboard> 