@extends('layouts.alumni')

@section('content')
<div class="container-fluid mt-3 mt-md-5 pt-3 pt-md-7 px-3 px-md-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10 col-xl-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="card-title mb-0 h4 h-md-3">Published Candidates - {{ $office->title }}</h3>
                            <p class="text-muted mb-0 small">{{ $election->title }}</p>
                        </div>
                        <a href="{{ route('alumni.elections') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left me-1"></i>
                            <span class="d-none d-sm-inline">Back to Elections</span>
                            <span class="d-inline d-sm-none">Back</span>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($candidates->isEmpty())
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            No candidates have been published for this office yet.
                        </div>
                    @else
                        <div class="row g-3 g-md-4">
                            @foreach($candidates as $candidate)
                                <div class="col-12 col-md-6 col-lg-4">
                                    <div class="candidate-card h-100 border-0 shadow-sm">
                                        <div class="card-body p-3 p-md-4">
                                            <div class="d-flex align-items-center mb-3">
                                                @if($candidate->passport)
                                                    <img src="{{ Storage::url($candidate->passport) }}" 
                                                         alt="Passport" 
                                                         class="rounded-circle me-2 me-md-3"
                                                         style="width: 60px; height: 60px; object-fit: cover;">
                                                @endif
                                                <div class="flex-grow-1">
                                                    <h5 class="card-title mb-1 h6 h-md-5">{{ $candidate->alumni->user->name }}</h5>
                                                    <p class="text-muted small mb-0">{{ $candidate->alumni->matriculation_number }}</p>
                                                </div>
                                            </div>
                                            @if($candidate->manifesto)
                                                <div class="mt-3">
                                                    <h6 class="text-primary small">Manifesto</h6>
                                                    <p class="card-text small mb-3">{{ Str::limit($candidate->manifesto, 120) }}</p>
                                                    <button type="button" 
                                                        class="btn btn-sm btn-outline-primary w-100"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#manifestoModal{{ $candidate->id }}">
                                                        <i class="bi bi-file-text me-1"></i>
                                                        <span class="d-none d-sm-inline">View Full Manifesto</span>
                                                        <span class="d-inline d-sm-none">Full Manifesto</span>
                                                    </button>
                                                </div>
                                            @endif
                                            @if($candidate->documents)
                                                <div class="mt-3">
                                                    <h6 class="text-secondary small">Supporting Documents</h6>
                                                    <div class="d-flex flex-wrap gap-1">
                                                        @foreach($candidate->documents as $index => $document)
                                                            @if($index < 2)
                                                                <a href="{{ asset('storage/' . $document) }}" 
                                                                    target="_blank" 
                                                                    class="btn btn-sm btn-outline-secondary">
                                                                    <i class="bi bi-file-earmark me-1"></i>
                                                                    <span class="d-none d-sm-inline">Doc {{ $index + 1 }}</span>
                                                                    <span class="d-inline d-sm-none">{{ $index + 1 }}</span>
                                                                </a>
                                                            @endif
                                                        @endforeach
                                                        @if(count($candidate->documents) > 2)
                                                            <button type="button" 
                                                                class="btn btn-sm btn-outline-info"
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#documentsModal{{ $candidate->id }}">
                                                                <i class="bi bi-three-dots"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Manifesto Modal -->
                                    @if($candidate->manifesto)
                                        <div class="modal fade" id="manifestoModal{{ $candidate->id }}" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title h6 h-md-5">
                                                            Manifesto - {{ $candidate->alumni->user->name }}
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="manifesto-content">
                                                            {!! nl2br(e($candidate->manifesto)) !!}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Documents Modal -->
                                    @if($candidate->documents && count($candidate->documents) > 2)
                                        <div class="modal fade" id="documentsModal{{ $candidate->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title h6 h-md-5">
                                                            Supporting Documents - {{ $candidate->alumni->user->name }}
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="list-group">
                                                            @foreach($candidate->documents as $index => $document)
                                                                <a href="{{ asset('storage/' . $document) }}" 
                                                                    target="_blank" 
                                                                    class="list-group-item list-group-item-action d-flex align-items-center">
                                                                    <i class="bi bi-file-earmark me-2"></i>
                                                                    <span>Document {{ $index + 1 }}</span>
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.candidate-card {
    background: white;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.candidate-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15) !important;
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
    
    .candidate-card .card-body {
        padding: 1rem !important;
    }
    
    .card-title {
        font-size: 1.1rem;
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
    
    .candidate-card .card-body {
        padding: 0.75rem !important;
    }
    
    .card-title {
        font-size: 1rem;
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
    
    .candidate-card .card-body {
        padding: 0.5rem !important;
    }
    
    .btn {
        font-size: 0.75rem;
        padding: 0.35rem 0.5rem;
    }
}
</style>
@endsection 