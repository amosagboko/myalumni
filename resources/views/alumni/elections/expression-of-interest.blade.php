@extends('layouts.alumni')

@section('content')
<div class="container-fluid bg-light min-vh-100 py-5 pt-7">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header bg-white border-bottom-0 py-4">
                    <h1 class="h2 fw-bold text-primary mb-0">Expression of Interest – {{ $office->title }}</h1>
                </div>
                <div class="card-body p-4 p-md-5">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="bg-light rounded-3 p-4 mb-4">
                        <h2 class="h5 fw-semibold text-primary mb-3">Position Details</h2>
                        <p class="text-muted mb-0">{{ $office->description }}</p>
                    </div>

                    <div class="bg-light rounded-3 p-4 mb-4">
                        <h2 class="h5 fw-semibold text-primary mb-3">Screening Fee</h2>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-primary fs-6 me-2">Amount:</span>
                            <span class="fs-5 fw-medium">{{ $screeningFee->formatted_amount }}</span>
                        </div>
                        <p class="text-muted mt-2 mb-0">This fee is required for screening your application.</p>
                    </div>

                    <form action="{{ route('alumni.elections.expression-of-interest.preview', ['election' => $election, 'office' => $office]) }}" 
                          method="POST" 
                          enctype="multipart/form-data"
                          class="needs-validation" novalidate>
                        @csrf

                        <div class="mb-4">
                            <label for="passport" class="form-label fw-medium">Passport Photograph</label>
                            <input type="file" 
                                   name="passport" 
                                   id="passport" 
                                   accept="image/jpeg,image/png,image/jpg,image/gif"
                                   class="form-control form-control-lg"
                                   required>
                            <div class="form-text">Upload a recent passport photograph (max 2MB)</div>
                            @error('passport')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="manifesto" class="form-label fw-medium">Manifesto (Optional)</label>
                            <textarea name="manifesto" 
                                      id="manifesto" 
                                      rows="6" 
                                      class="form-control"
                                      placeholder="Describe your vision and plans for this position..."></textarea>
                            <div class="form-text">If provided, minimum 100 characters</div>
                            @error('manifesto')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="documents" class="form-label fw-medium">Supporting Documents (Optional)</label>
                            <input type="file" 
                                   name="documents[]" 
                                   id="documents" 
                                   accept=".pdf,.doc,.docx"
                                   multiple
                                   class="form-control form-control-lg">
                            <div class="form-text">Upload supporting documents if available (PDF, DOC, DOCX, max 5MB each)</div>
                            @error('documents')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="card bg-light border-0 rounded-3 p-4 mb-4">
                            <h3 class="h6 fw-semibold text-primary mb-3">Important Notes:</h3>
                            <ul class="list-group list-group-flush bg-transparent">
                                <li class="list-group-item bg-transparent border-0 ps-0 d-flex align-items-center">
                                    <i class="bi bi-info-circle-fill text-primary me-2"></i>
                                    You can only express interest in one position at a time
                                </li>
                                <li class="list-group-item bg-transparent border-0 ps-0 d-flex align-items-center">
                                    <i class="bi bi-info-circle-fill text-primary me-2"></i>
                                    All fees must be paid before submitting your expression of interest
                                </li>
                                <li class="list-group-item bg-transparent border-0 ps-0 d-flex align-items-center">
                                    <i class="bi bi-info-circle-fill text-primary me-2"></i>
                                    Your bio data must be complete
                                </li>
                                <li class="list-group-item bg-transparent border-0 ps-0 d-flex align-items-center">
                                    <i class="bi bi-info-circle-fill text-primary me-2"></i>
                                    Your application will be screened by the election committee
                                </li>
                                <li class="list-group-item bg-transparent border-0 ps-0 d-flex align-items-center">
                                    <i class="bi bi-info-circle-fill text-primary me-2"></i>
                                    The screening fee is non-refundable
                                </li>
                            </ul>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" 
                                    class="btn btn-primary btn-lg px-5 py-3 fw-medium">
                                Preview Application
                                <i class="bi bi-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endpush
@endsection 