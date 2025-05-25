@extends('layouts.alumni')

@section('content')
<div class="container-fluid bg-light min-vh-100 py-5 pt-7">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header bg-white border-bottom-0 py-4">
                    <h1 class="h2 fw-bold text-primary mb-0">Preview Application</h1>
                </div>
                <div class="card-body p-4 p-md-5">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="bg-light rounded-3 p-4 mb-4">
                        <h2 class="h5 fw-semibold text-primary mb-3">Application Summary</h2>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Position:</strong></p>
                                <p class="text-muted">{{ $office->title }}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Screening Fee:</strong></p>
                                <p class="text-muted">{{ $screeningFee->formatted_amount }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-light rounded-3 p-4 mb-4">
                        <h2 class="h5 fw-semibold text-primary mb-3">Passport Photograph</h2>
                        <div class="text-center">
                            <img src="{{ Storage::url($passport) }}" 
                                 alt="Passport Photograph" 
                                 class="img-fluid rounded" 
                                 style="width: 150px; height: 150px; object-fit: cover;">
                        </div>
                    </div>

                    <div class="bg-light rounded-3 p-4 mb-4">
                        <h2 class="h5 fw-semibold text-primary mb-3">Uploaded Documents</h2>
                        <ul class="list-group list-group-flush bg-transparent">
                            <li class="list-group-item bg-transparent border-0 ps-0 d-flex align-items-center">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                Passport Photograph
                            </li>
                            <li class="list-group-item bg-transparent border-0 ps-0 d-flex align-items-center">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                Supporting Documents ({{ count($documents) }} files)
                            </li>
                        </ul>
                    </div>

                    <div class="bg-light rounded-3 p-4 mb-4">
                        <h2 class="h5 fw-semibold text-primary mb-3">Manifesto Preview</h2>
                        <div class="p-3 bg-white rounded border">
                            {{ $manifesto }}
                        </div>
                    </div>

                    <div class="alert alert-info mb-4">
                        <h6 class="alert-heading">Payment Required</h6>
                        <p class="mb-0">A screening fee of {{ $screeningFee->formatted_amount }} is required to process your application.</p>
                    </div>

                    <div class="card bg-light border-0 rounded-3 p-4 mb-4">
                        <h3 class="h6 fw-semibold text-primary mb-3">Important Notes:</h3>
                        <ul class="list-group list-group-flush bg-transparent">
                            <li class="list-group-item bg-transparent border-0 ps-0 d-flex align-items-center">
                                <i class="bi bi-info-circle-fill text-primary me-2"></i>
                                The screening fee is non-refundable
                            </li>
                            <li class="list-group-item bg-transparent border-0 ps-0 d-flex align-items-center">
                                <i class="bi bi-info-circle-fill text-primary me-2"></i>
                                Your application will be reviewed by the election committee
                            </li>
                            <li class="list-group-item bg-transparent border-0 ps-0 d-flex align-items-center">
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
                                    class="btn btn-secondary btn-lg px-5 py-3 fw-medium">
                                <i class="bi bi-arrow-left me-2"></i>
                                Back to Edit
                            </button>
                        </form>

                        <form action="{{ route('alumni.elections.expression-of-interest.submit', ['election' => $election, 'office' => $office]) }}" 
                              method="POST" 
                              class="d-inline">
                            @csrf
                            <input type="hidden" name="preview_token" value="{{ $previewToken }}">
                            <button type="submit" 
                                    class="btn btn-primary btn-lg px-5 py-3 fw-medium">
                                Proceed to Payment
                                <i class="bi bi-arrow-right ms-2"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endpush
@endsection 