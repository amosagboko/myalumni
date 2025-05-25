@extends('layouts.alumni')

@section('content')
<div class="container mt-5 pt-7" style="margin-left: 300px; max-width: 900px;">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h3 class="card-title">Published Candidates - {{ $office->title }}</h3>
                    <p class="text-muted mb-0">{{ $election->title }}</p>
                </div>
                <div class="card-body">
                    @if($candidates->isEmpty())
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            No candidates have been published for this office yet.
                        </div>
                    @else
                        <div class="row row-cols-1 row-cols-md-2 g-4">
                            @foreach($candidates as $candidate)
                                <div class="col">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center mb-3">
                                                @if($candidate->passport)
                                                    <img src="{{ Storage::url($candidate->passport) }}" 
                                                         alt="Passport" 
                                                         class="rounded-circle me-3"
                                                         style="width: 80px; height: 80px; object-fit: cover;">
                                                @endif
                                                <div>
                                                    <h5 class="card-title mb-1">{{ $candidate->alumni->user->name }}</h5>
                                                    <p class="text-muted small mb-0">{{ $candidate->alumni->matriculation_number }}</p>
                                                </div>
                                            </div>
                                            @if($candidate->manifesto)
                                                <div class="mt-3">
                                                    <h6 class="text-primary">Manifesto</h6>
                                                    <p class="card-text small">{{ Str::limit($candidate->manifesto, 150) }}</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 