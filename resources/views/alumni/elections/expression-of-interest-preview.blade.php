@extends('layouts.alumni')

@section('content')
<div class="container-fluid mt-3 mt-md-5 pt-3 pt-md-7 px-3 px-md-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8 col-xl-6">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header bg-white border-bottom-0 py-3 py-md-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h1 class="h3 h-md-2 fw-bold text-primary mb-0">Preview Application</h1>
                        <a href="{{ route('alumni.elections.expression-of-interest.form', ['election' => $election, 'office' => $office]) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left me-1"></i>
                            <span class="d-none d-sm-inline">Back to Form</span>
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
                        <h2 class="h5 h-md-4 fw-semibold text-primary mb-2 mb-md-3">Application Summary</h2>
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <p class="mb-2 small"><strong>Position:</strong></p>
                                <p class="text-muted small">{{ $office->title }}</p>
                            </div>
                            <div class="col-12 col-md-6">
                                <p class="mb-2 small"><strong>Screening Fee:</strong></p>
                                <p class="text-muted small">{{ $screeningFee->formatted_amount }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-light rounded-3 p-3 p-md-4 mb-3 mb-md-4">
                        <h2 class="h5 h-md-4 fw-semibold text-primary mb-2 mb-md-3">Passport Photograph</h2>
                        <div class="text-center">
                            <img src="{{ Storage::url($passport) }}" 
                                 alt="Passport Photograph" 
                                 class="img-fluid rounded" 
                                 style="width: 120px; height: 120px; object-fit: cover;">
                        </div>
                    </div>

                    <div class="bg-light rounded-3 p-3 p-md-4 mb-3 mb-md-4">
                        <h2 class="h5 h-md-4 fw-semibold text-primary mb-2 mb-md-3">Uploaded Documents</h2>
                        <ul class="list-group list-group-flush bg-transparent">
                            <li class="list-group-item bg-transparent border-0 ps-0 d-flex align-items-center small">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                Passport Photograph
                            </li>
                            <li class="list-group-item bg-transparent border-0 ps-0 d-flex align-items-center small">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                Supporting Documents ({{ count($documents) }} files)
                            </li>
                        </ul>
                    </div>

                    <div class="bg-light rounded-3 p-3 p-md-4 mb-3 mb-md-4">
                        <h2 class="h5 h-md-4 fw-semibold text-primary mb-2 mb-md-3">Manifesto Preview</h2>
                        <div class="p-3 bg-white rounded border">
                            <p class="small mb-0">{{ $manifesto }}</p>
                        </div>
                    </div>

                    <div class="alert alert-info mb-3 mb-md-4">
                        <h6 class="alert-heading small">Payment Required</h6>
                        <p class="mb-0 small">A screening fee of {{ $screeningFee->formatted_amount }} is required to process your application.</p>
                    </div>

                    <div class="card bg-light border-0 rounded-3 p-3 p-md-4 mb-3 mb-md-4">
                        <h3 class="h6 h-md-5 fw-semibold text-primary mb-2 mb-md-3">Important Notes:</h3>
                        <ul class="list-group list-group-flush bg-transparent">
                            <li class="list-group-item bg-transparent border-0 ps-0 d-flex align-items-center small">
                                <i class="bi bi-info-circle-fill text-primary me-2"></i>
                                The screening fee is non-refundable
                            </li>
                            <li class="list-group-item bg-transparent border-0 ps-0 d-flex align-items-center small">
                                <i class="bi bi-info-circle-fill text-primary me-2"></i>
                                Your application will be reviewed by the election committee
                            </li>
                            <li class="list-group-item bg-transparent border-0 ps-0 d-flex align-items-center small">
                                <i class="bi bi-info-circle-fill text-primary me-2"></i>
                                You can only express interest in one position at a time
                            </li>
                        </ul>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <form action="{{ route('alumni.elections.expression-of-interest.preview', ['election' => $election, 'office' => $office]) }}" 
                              method="POST" 
                              class="d-inline me-2">
                            @csrf
                            <input type="hidden" name="passport" value="{{ $passport }}">
                            <input type="hidden" name="manifesto" value="{{ $manifesto }}">
                            @if(!empty($documents))
                                @foreach($documents as $document)
                                    <input type="hidden" name="documents[]" value="{{ $document }}">
                                @endforeach
                            @endif
                            <button type="submit" 
                                    class="btn btn-secondary btn-lg px-4 px-md-5 py-2 py-md-3 fw-medium w-100 w-md-auto">
                                <i class="bi bi-arrow-left me-2"></i>
                                <span class="d-none d-sm-inline">Back to Edit</span>
                                <span class="d-inline d-sm-none">Edit</span>
                            </button>
                        </form>

                        <form action="{{ route('alumni.elections.expression-of-interest.submit', ['election' => $election, 'office' => $office]) }}" 
                              method="POST" 
                              class="d-inline">
                            @csrf
                            <input type="hidden" name="preview_token" value="{{ $previewToken }}">
                            <button type="submit" 
                                    class="btn btn-primary btn-lg px-4 px-md-5 py-2 py-md-3 fw-medium w-100 w-md-auto">
                                <span class="d-none d-sm-inline">Proceed to Payment</span>
                                <span class="d-inline d-sm-none">Payment</span>
                                <i class="bi bi-arrow-right ms-2"></i>
                            </button>
                        </form>
                    </div>
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
}
</style>

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endpush
@endsection 