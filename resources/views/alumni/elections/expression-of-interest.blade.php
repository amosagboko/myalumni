@extends('layouts.alumni')

@section('content')
<div class="container-fluid mt-3 mt-md-5 pt-3 pt-md-7 px-3 px-md-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8 col-xl-6">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header bg-white border-bottom-0 py-3 py-md-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h1 class="h3 h-md-2 fw-bold text-primary mb-0">Expression of Interest – {{ $office->title }}</h1>
                        <a href="{{ route('alumni.elections') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left me-1"></i>
                            <span class="d-none d-sm-inline">Back to Elections</span>
                            <span class="d-inline d-sm-none">Back</span>
                        </a>
                    </div>
                </div>
                <div class="card-body p-3 p-md-4 p-lg-5">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="bg-light rounded-3 p-3 p-md-4 mb-3 mb-md-4">
                        <h2 class="h5 h-md-4 fw-semibold text-primary mb-2 mb-md-3">Position Details</h2>
                        <p class="text-muted mb-0 small">{{ $office->description }}</p>
                    </div>

                    <div class="bg-light rounded-3 p-3 p-md-4 mb-3 mb-md-4">
                        <h2 class="h5 h-md-4 fw-semibold text-primary mb-2 mb-md-3">Screening Fee</h2>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-primary fs-6 me-2">Amount:</span>
                            <span class="fs-5 fw-medium">{{ $screeningFee->formatted_amount }}</span>
                        </div>
                        <p class="text-muted mt-2 mb-0 small">This fee is required for screening your application.</p>
                    </div>

                    <form action="{{ route('alumni.elections.expression-of-interest.preview', ['election' => $election, 'office' => $office]) }}" 
                          method="POST" 
                          enctype="multipart/form-data"
                          class="needs-validation" novalidate>
                        @csrf

                        <div class="mb-3 mb-md-4">
                            <label for="passport" class="form-label fw-medium small">Passport Photograph</label>
                            <input type="file" 
                                   name="passport" 
                                   id="passport" 
                                   accept="image/jpeg,image/png,image/jpg,image/gif"
                                   class="form-control form-control-lg"
                                   required>
                            <div class="form-text small">Upload a recent passport photograph (max 2MB)</div>
                            @error('passport')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 mb-md-4">
                            <label for="manifesto" class="form-label fw-medium small">Manifesto (Optional)</label>
                            <textarea name="manifesto" 
                                      id="manifesto" 
                                      rows="6" 
                                      class="form-control"
                                      placeholder="Describe your vision and plans for this position..."></textarea>
                            <div class="form-text small">If provided, minimum 100 characters</div>
                            @error('manifesto')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 mb-md-4">
                            <label for="documents" class="form-label fw-medium small">Supporting Documents (Optional)</label>
                            <input type="file" 
                                   name="documents[]" 
                                   id="documents" 
                                   accept=".pdf,.doc,.docx"
                                   multiple
                                   class="form-control form-control-lg">
                            <div class="form-text small">Upload supporting documents if available (PDF, DOC, DOCX, max 5MB each)</div>
                            @error('documents')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="card bg-light border-0 rounded-3 p-3 p-md-4 mb-3 mb-md-4">
                            <h3 class="h6 h-md-5 fw-semibold text-primary mb-2 mb-md-3">Important Notes:</h3>
                            <ul class="list-group list-group-flush bg-transparent">
                                <li class="list-group-item bg-transparent border-0 ps-0 d-flex align-items-center small">
                                    <i class="bi bi-info-circle-fill text-primary me-2"></i>
                                    You can only express interest in one position at a time
                                </li>
                                <li class="list-group-item bg-transparent border-0 ps-0 d-flex align-items-center small">
                                    <i class="bi bi-info-circle-fill text-primary me-2"></i>
                                    All fees must be paid before submitting your expression of interest
                                </li>
                                <li class="list-group-item bg-transparent border-0 ps-0 d-flex align-items-center small">
                                    <i class="bi bi-info-circle-fill text-primary me-2"></i>
                                    Your bio data must be complete
                                </li>
                                <li class="list-group-item bg-transparent border-0 ps-0 d-flex align-items-center small">
                                    <i class="bi bi-info-circle-fill text-primary me-2"></i>
                                    Your application will be screened by the election committee
                                </li>
                                <li class="list-group-item bg-transparent border-0 ps-0 d-flex align-items-center small">
                                    <i class="bi bi-info-circle-fill text-primary me-2"></i>
                                    The screening fee is non-refundable
                                </li>
                            </ul>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" 
                                    class="btn btn-primary btn-lg px-4 px-md-5 py-2 py-md-3 fw-medium">
                                <span class="d-none d-sm-inline">Preview Application</span>
                                <span class="d-inline d-sm-none">Preview</span>
                                <i class="bi bi-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Responsive adjustments */
@media (max-width: 768px) {
    .container-fluid {
        margin-left: 0 !important;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .btn {
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
    }
    
    .form-control-lg {
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
    }
}

@media (max-width: 576px) {
    .container-fluid {
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }
    
    .card-body {
        padding: 0.75rem;
    }
    
    .btn {
        font-size: 0.8rem;
        padding: 0.4rem 0.6rem;
    }
    
    .form-control-lg {
        font-size: 0.8rem;
        padding: 0.4rem 0.6rem;
    }
    
    .card-header {
        padding: 0.75rem;
    }
}

@media (max-width: 480px) {
    .container-fluid {
        padding-left: 0.25rem;
        padding-right: 0.25rem;
    }
    
    .card-body {
        padding: 0.5rem;
    }
    
    .btn {
        font-size: 0.75rem;
        padding: 0.35rem 0.5rem;
    }
    
    .form-control-lg {
        font-size: 0.75rem;
        padding: 0.35rem 0.5rem;
    }
}
</style>

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endpush
@endsection 