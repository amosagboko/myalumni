@extends('layouts.alumni')

@section('content')
<div class="container-fluid mt-3 mt-md-5 pt-3 pt-md-7 px-3 px-md-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10 col-xl-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0 h4 h-md-3">Preview Your Votes - {{ $election->title }}</h3>
                        <a href="{{ route('alumni.elections.vote', $election) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left me-1"></i>
                            <span class="d-none d-sm-inline">Back to Voting</span>
                            <span class="d-inline d-sm-none">Back</span>
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Please review your selections carefully. Once confirmed, your votes cannot be changed.
                    </div>

                    <!-- Mobile View - Cards -->
                    <div class="d-md-none">
                        @foreach($selectedCandidates as $selection)
                            <div class="vote-preview-card mb-3 p-3 border rounded">
                                <div class="d-flex align-items-center mb-2">
                                    @if($selection['candidate']->passport)
                                        <img src="{{ Storage::url($selection['candidate']->passport) }}" 
                                             alt="Candidate Photo" 
                                             class="rounded-circle me-2"
                                             style="width: 50px; height: 50px; object-fit: cover;">
                                    @endif
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">{{ $selection['candidate']->alumni->user->name }}</h6>
                                        <small class="text-muted">{{ $selection['candidate']->alumni->matriculation_number }}</small>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <strong class="text-primary">{{ $selection['office']->title }}</strong>
                                    @if($selection['office']->description)
                                        <br>
                                        <small class="text-muted">{{ $selection['office']->description }}</small>
                                    @endif
                                </div>
                                @if($selection['candidate']->manifesto)
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-primary w-100"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#manifestoModal{{ $selection['candidate']->id }}">
                                        <i class="bi bi-file-text me-1"></i>
                                        <span class="d-none d-sm-inline">View Manifesto</span>
                                        <span class="d-inline d-sm-none">Manifesto</span>
                                    </button>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <!-- Desktop View - Table -->
                    <div class="d-none d-md-block">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Office</th>
                                        <th>Selected Candidate</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($selectedCandidates as $selection)
                                    <tr>
                                        <td>
                                            <strong>{{ $selection['office']->title }}</strong>
                                            @if($selection['office']->description)
                                                <br>
                                                <small class="text-muted">{{ $selection['office']->description }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($selection['candidate']->passport)
                                                    <img src="{{ Storage::url($selection['candidate']->passport) }}" 
                                                         alt="Candidate Photo" 
                                                         class="rounded-circle me-2"
                                                         style="width: 40px; height: 40px; object-fit: cover;">
                                                @endif
                                                <div>
                                                    <strong>{{ $selection['candidate']->alumni->user->name }}</strong>
                                                    @if($selection['candidate']->manifesto)
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-primary ms-2"
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#manifestoModal{{ $selection['candidate']->id }}">
                                                            View Manifesto
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="alert alert-warning mt-3">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Important:</strong> Your votes will be recorded after confirmation. This action cannot be undone.
                    </div>

                    <div class="card-footer">
                        <div class="d-flex flex-column flex-md-row justify-content-between gap-2">
                            <a href="{{ route('alumni.elections.vote', $election) }}" class="btn btn-secondary">
                                <i class="bi bi-pencil me-2"></i>
                                <span class="d-none d-sm-inline">Modify Votes</span>
                                <span class="d-inline d-sm-none">Modify</span>
                            </a>
                            <form action="{{ route('alumni.elections.submit-vote', $election) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success w-100 w-md-auto" onclick="return confirm('Are you sure you want to submit these votes? This action cannot be undone.')">
                                    <i class="bi bi-check-circle me-2"></i>
                                    <span class="d-none d-sm-inline">Confirm & Submit Votes</span>
                                    <span class="d-inline d-sm-none">Confirm & Submit</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Manifesto Modals -->
@foreach($selectedCandidates as $selection)
    @if($selection['candidate']->manifesto)
        <div class="modal fade" id="manifestoModal{{ $selection['candidate']->id }}" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title h6 h-md-5">
                            Manifesto - {{ $selection['candidate']->alumni->user->name }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="manifesto-content">
                            {!! nl2br(e($selection['candidate']->manifesto)) !!}
                        </div>
                        @if($selection['candidate']->documents)
                            <div class="mt-4">
                                <h6>Supporting Documents:</h6>
                                <ul class="list-unstyled">
                                    @foreach($selection['candidate']->documents as $document)
                                        <li class="mb-2">
                                            <a href="{{ asset('storage/' . $document) }}" 
                                                target="_blank" 
                                                class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-file-earmark me-2"></i>
                                                <span class="d-none d-sm-inline">View Document</span>
                                                <span class="d-inline d-sm-none">Document</span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
@endforeach

<style>
.vote-preview-card {
    background: white;
    transition: all 0.3s ease;
}

.vote-preview-card:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.manifesto-content {
    line-height: 1.6;
    white-space: pre-line;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .container-fluid {
        margin-left: 0 !important;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .vote-preview-card {
        margin-bottom: 1rem;
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
    
    .vote-preview-card {
        padding: 0.75rem !important;
        margin-bottom: 0.75rem;
    }
    
    .btn {
        font-size: 0.8rem;
        padding: 0.4rem 0.6rem;
    }
    
    .modal-dialog {
        margin: 0.5rem;
    }
    
    .modal-body {
        padding: 1rem;
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
    
    .vote-preview-card {
        padding: 0.5rem !important;
        margin-bottom: 0.5rem;
    }
    
    .btn {
        font-size: 0.75rem;
        padding: 0.35rem 0.5rem;
    }
}
</style>
@endsection 