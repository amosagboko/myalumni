@extends('layouts.alumni')

@section('content')
<div class="elections-hub-page elections-accreditation-page w-100 pe-lg-2">
    <div class="card w-100 border-0 bg-white shadow-xs p-0 mb-4">
        <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex flex-wrap justify-content-between align-items-start gap-2">
            <div>
                <h4 class="fw-600 mb-1">Accreditation</h4>
                <p class="text-grey-500 font-xssss mb-0">{{ $election->title }}</p>
            </div>
            <a href="{{ route('alumni.elections') }}" class="btn btn-outline-secondary btn-sm">
                <i class="feather-arrow-left me-1"></i> Back to Elections
            </a>
        </div>

        <div class="card-body p-4 w-100 border-0">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="alert {{ $periodAlertClass }} font-xssss mb-4">
                <div class="fw-600 mb-1"><i class="feather-info me-1"></i> Accreditation period</div>
                <p class="mb-0">{{ $periodMessage }}</p>
                @if($election->accreditation_start && $election->accreditation_end)
                    <p class="mb-0 mt-2 text-muted">
                        {{ $election->accreditation_start->format('M d, Y h:i A') }}
                        –
                        {{ $election->accreditation_end->format('M d, Y h:i A') }}
                    </p>
                @endif
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="elections-hub-stat text-center">
                        <div class="elections-hub-stat__label">Your status</div>
                        <div class="elections-hub-stat__value">
                            @if($isAccredited)
                                <span class="badge bg-success">Accredited</span>
                            @else
                                <span class="badge bg-secondary">Not accredited</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="elections-hub-stat text-center">
                        <div class="elections-hub-stat__label">Eligibility</div>
                        <div class="elections-hub-stat__value">
                            @if($isEligible || $isAccredited)
                                <span class="badge bg-success">Eligible</span>
                            @else
                                <span class="badge bg-danger">Not eligible</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="elections-hub-stat text-center">
                        <div class="elections-hub-stat__label">Window</div>
                        <div class="elections-hub-stat__value">
                            @if($accreditationPeriodActive)
                                <span class="badge bg-info">Open now</span>
                            @elseif($accreditationNotStarted)
                                <span class="badge bg-warning text-dark">Not started</span>
                            @elseif($accreditationEnded)
                                <span class="badge bg-danger">Closed</span>
                            @else
                                <span class="badge bg-secondary">Unavailable</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @if($isAccredited)
                <div class="alert alert-success font-xssss mb-4">
                    <i class="feather-check-circle me-1"></i>
                    You have been successfully accredited for this election.
                    @if($accreditation?->accredited_at)
                        <div class="mt-2 text-muted">Accredited on {{ $accreditation->accredited_at->format('M d, Y h:i A') }}</div>
                    @endif
                </div>
            @else
                <div class="alert {{ $isEligible ? 'alert-success' : 'alert-danger' }} font-xssss mb-4">
                    @if($isEligible)
                        <i class="feather-check-circle me-1"></i>
                        You are eligible to be accredited for this election.
                    @else
                        <i class="feather-x-circle me-1"></i>
                        You are not eligible for accreditation yet. Please resolve the following:
                        <ul class="mb-0 mt-2">
                            @foreach($ineligibilityReasons as $reason)
                                <li>{{ $reason }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                @if($isEligible && $accreditationPeriodActive)
                    <h6 class="fw-600 font-xssss text-grey-900 text-uppercase mb-3">Confirm your details</h6>
                    <div class="border rounded-3 p-3 mb-4">
                        <div class="row g-2 font-xssss">
                            <div class="col-sm-4 text-grey-500">Name</div>
                            <div class="col-sm-8 fw-600">{{ $user?->name ?? 'N/A' }}</div>
                            <div class="col-sm-4 text-grey-500">Matriculation number</div>
                            <div class="col-sm-8">{{ app(\App\Services\Alumni\AlumniElectionAccreditationService::class)->matricNumber($alumni) }}</div>
                            <div class="col-sm-4 text-grey-500">Email</div>
                            <div class="col-sm-8">{{ $user?->email ?? 'N/A' }}</div>
                            <div class="col-sm-4 text-grey-500">Phone</div>
                            <div class="col-sm-8">{{ $alumni?->phone_number ?? 'N/A' }}</div>
                        </div>
                    </div>

                    <form action="{{ route('alumni.elections.accreditation.submit', $election) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i class="feather-check-circle me-1"></i> Submit accreditation request
                        </button>
                    </form>
                @elseif(! $accreditationPeriodActive)
                    <div class="alert alert-secondary font-xssss mb-0">
                        <i class="feather-clock me-1"></i>
                        Accreditation submissions are not open right now.
                    </div>
                @endif
            @endif

            <div class="d-flex flex-wrap gap-2 mt-4 pt-3 border-top">
                <a href="{{ route('alumni.elections') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="feather-arrow-left me-1"></i> Back to Elections
                </a>
                @if($isAccredited)
                    <a href="{{ route('alumni.elections.vote', $election) }}" class="btn btn-primary btn-sm">
                        <i class="feather-check-square me-1"></i> Proceed to vote
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/alumni-elections-hub.css') }}">
@endpush
@endsection
