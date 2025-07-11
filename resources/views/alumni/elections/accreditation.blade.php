@extends('layouts.alumni')

@section('content')
<div class="container-fluid mt-5 pt-7 px-3 px-md-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10 col-xl-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h3 class="card-title mb-0">Accreditation - {{ $election->title }}</h3>
                </div>
                <div class="card-body">
                    @php
                        $alumni = auth()->user()->alumni;
                        $isEligible = $election->isAlumniEligibleToVote($alumni);
                        $isAccredited = $election->accreditedVoters()->where('alumni_id', $alumni->id)->exists();
                        $accreditationPeriodActive = $election->status === 'accreditation' && 
                            now()->between($election->accreditation_start, $election->accreditation_end);
                        $accreditationEnded = $election->status === 'accreditation' && 
                            now()->greaterThan($election->accreditation_end);
                        $accreditationNotStarted = $election->status === 'accreditation' && 
                            now()->lessThan($election->accreditation_start);
                    @endphp

                    <!-- Accreditation Period Status -->
                    <div class="alert {{ $accreditationPeriodActive ? 'alert-info' : 
                        ($accreditationEnded ? 'alert-danger' : 
                        ($accreditationNotStarted ? 'alert-warning' : 'alert-secondary')) }} mb-4">
                        <h5 class="alert-heading">
                            <i class="bi {{ $accreditationPeriodActive ? 'bi-info-circle' : 
                                ($accreditationEnded ? 'bi-x-circle' : 
                                ($accreditationNotStarted ? 'bi-clock' : 'bi-dash-circle')) }} me-2"></i>
                            Accreditation Period Status
                        </h5>
                        <p class="mb-0">
                            @if($accreditationPeriodActive)
                                Accreditation is currently active. You can submit your accreditation request now.
                            @elseif($accreditationNotStarted)
                                Accreditation period will start on {{ $election->accreditation_start->format('M d, Y h:i A') }}.
                                Please check back later to submit your accreditation request.
                            @elseif($accreditationEnded)
                                Accreditation period has ended on {{ $election->accreditation_end->format('M d, Y h:i A') }}.
                                You can no longer submit accreditation requests for this election.
                            @elseif($election->status === 'voting')
                                Accreditation period has ended. The election is now in the voting phase.
                            @elseif($election->status === 'completed')
                                This election has been completed. Accreditation is no longer available.
                            @else
                                Accreditation period has not been scheduled for this election yet.
                            @endif
                        </p>
                        <small class="d-block mt-2">
                            Period: {{ $election->accreditation_start->format('M d, Y h:i A') }} - 
                            {{ $election->accreditation_end->format('M d, Y h:i A') }}
                        </small>
                    </div>

                    <!-- Accreditation Status -->
                    @if($isAccredited)
                        <div class="alert alert-success mb-4">
                            <h5 class="alert-heading">
                                <i class="bi bi-check-circle me-2"></i>
                                Accreditation Status
                            </h5>
                            <p class="mb-0">You have been successfully accredited for this election.</p>
                            @php
                                $accreditation = $election->accreditedVoters()
                                    ->where('alumni_id', $alumni->id)
                                    ->first();
                            @endphp
                            <small class="d-block mt-2">
                                Accredited on: {{ $accreditation->accredited_at->format('M d, Y h:i A') }}
                            </small>
                        </div>
                    @else
                        <!-- Eligibility Status - Only show if not accredited -->
                        <div class="alert {{ $isEligible ? 'alert-success' : 'alert-danger' }} mb-4">
                            <h5 class="alert-heading">
                                <i class="bi {{ $isEligible ? 'bi-check-circle' : 'bi-x-circle' }} me-2"></i>
                                Eligibility Status
                            </h5>
                            @if($isEligible)
                                <p class="mb-0">You are eligible to be accredited for this election.</p>
                            @else
                                <p class="mb-0">You are not eligible for accreditation. Please check the following:</p>
                                <ul class="mb-0 mt-2">
                                    @if(!$alumni->getActiveFees()->every(fn($fee) => $fee->isPaid()))
                                        <li>Complete all pending payments</li>
                                    @endif
                                    @if(!$alumni->contact_address || !$alumni->phone_number || !$alumni->qualification_type)
                                        <li>Complete your bio data profile</li>
                                    @endif
                                </ul>
                            @endif
                        </div>

                        <!-- Accreditation Form -->
                        @if($isEligible && $accreditationPeriodActive)
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">Submit Accreditation</h5>
                                    <p class="card-text">Please confirm your details before submitting for accreditation:</p>
                                    
                                    <div class="mb-4">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="card border">
                                                    <div class="card-body p-0">
                                                        <div class="row g-0">
                                                            <div class="col-12 col-sm-4 col-md-3 bg-light border-end">
                                                                <div class="p-3">
                                                                    <strong>Name</strong>
                                                                </div>
                                                            </div>
                                                            <div class="col-12 col-sm-8 col-md-9">
                                                                <div class="p-3">
                                                                    {{ $alumni->user->name }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row g-0 border-top">
                                                            <div class="col-12 col-sm-4 col-md-3 bg-light border-end">
                                                                <div class="p-3">
                                                                    <strong>Matriculation Number</strong>
                                                                </div>
                                                            </div>
                                                            <div class="col-12 col-sm-8 col-md-9">
                                                                <div class="p-3">
                                                                    {{ $alumni->matriculation_number }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row g-0 border-top">
                                                            <div class="col-12 col-sm-4 col-md-3 bg-light border-end">
                                                                <div class="p-3">
                                                                    <strong>Email</strong>
                                                                </div>
                                                            </div>
                                                            <div class="col-12 col-sm-8 col-md-9">
                                                                <div class="p-3">
                                                                    {{ $alumni->user->email }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row g-0 border-top">
                                                            <div class="col-12 col-sm-4 col-md-3 bg-light border-end">
                                                                <div class="p-3">
                                                                    <strong>Phone Number</strong>
                                                                </div>
                                                            </div>
                                                            <div class="col-12 col-sm-8 col-md-9">
                                                                <div class="p-3">
                                                                    {{ $alumni->phone_number }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <form action="{{ route('alumni.elections.accreditation.submit', $election) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-primary w-100 w-md-auto">
                                            <i class="bi bi-check-circle me-2"></i>
                                            Submit Accreditation Request
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @elseif(!$accreditationPeriodActive)
                            <div class="alert alert-secondary">
                                <i class="bi bi-info-circle me-2"></i>
                                @if($accreditationEnded)
                                    The accreditation period has ended. You can no longer submit accreditation requests.
                                @elseif($accreditationNotStarted)
                                    The accreditation period has not started yet. Please check back later.
                                @else
                                    Accreditation is not available at this time.
                                @endif
                            </div>
                        @endif
                    @endif

                    <!-- Navigation -->
                    <div class="mt-4 d-flex flex-column flex-md-row gap-2">
                        <a href="{{ route('alumni.elections') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>
                            <span class="d-none d-sm-inline">Back to Elections</span>
                            <span class="d-inline d-sm-none">Back</span>
                        </a>
                        @if($isAccredited)
                            <a href="{{ route('alumni.elections.vote', $election) }}" class="btn btn-primary">
                                <i class="bi bi-check-square me-2"></i>
                                <span class="d-none d-sm-inline">Proceed to Vote</span>
                                <span class="d-inline d-sm-none">Vote</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media (max-width: 768px) {
    .container-fluid {
        margin-left: 0 !important;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .alert-heading {
        font-size: 1.1rem;
    }
    
    .card-title {
        font-size: 1.25rem;
    }
}

@media (max-width: 576px) {
    .container-fluid {
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }
    
    .card-title {
        font-size: 1.1rem;
    }
    
    .alert {
        padding: 0.75rem;
    }
    
    .alert-heading {
        font-size: 1rem;
    }
    
    .btn {
        font-size: 0.875rem;
        padding: 0.5rem 1rem;
    }
    
    .card-body {
        padding: 0.75rem;
    }
    
    .p-3 {
        padding: 0.75rem !important;
    }
    
    .bg-light {
        background-color: #f8f9fa !important;
    }
}
</style>
@endsection 