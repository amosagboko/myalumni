<div class="clearance-form-page w-100 pe-lg-2">
    <div class="card w-100 border-0 bg-white shadow-xs p-0 mb-4">
        <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h4 class="fw-600 mb-1">Clearance Form</h4>
                <p class="text-grey-500 font-xssss mb-0">
                    Review your alumni registration details, then print or download the official form.
                </p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('reports.print') }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm">
                    <i class="feather-printer me-1"></i> Print Form
                </a>
                <a href="{{ route('reports.download-pdf') }}" class="btn btn-primary btn-sm">
                    <i class="feather-download me-1"></i> Download Form
                </a>
            </div>
        </div>

        <div class="card-body p-4 w-100 border-0">
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="d-flex justify-content-between align-items-center mb-4 clearance-form-header">
                <img src="{{ asset('images/fulafia-logo.jpg') }}" alt="FULAFIA Logo" class="img-fluid" style="width: 60px; height: 60px;">
                <div class="text-center px-2 clearance-form-header__text">
                    <h2 class="mb-1 fw-bold">Federal University of Lafia</h2>
                    <h3 class="mb-0 fw-bold text-grey-700">Alumni Personal Data Registration Form</h3>
                </div>
                <img src="{{ asset('images/alumni-logo.jpg') }}" alt="ALUMNI Logo" class="img-fluid" style="width: 60px; height: 60px;">
            </div>

            @include('alumni.clearance.partials.form-screen')

            <div class="d-flex flex-column flex-sm-row justify-content-center gap-2 mt-4 pt-2 border-top">
                <a href="{{ route('reports.print') }}" target="_blank" rel="noopener" class="btn btn-outline-primary">
                    <i class="feather-printer me-1"></i> Print Form
                </a>
                <a href="{{ route('reports.download-pdf') }}" class="btn btn-primary">
                    <i class="feather-download me-1"></i> Download Form
                </a>
            </div>
        </div>
    </div>
</div>

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/alumni-clearance-form.css') }}">
@endpush
