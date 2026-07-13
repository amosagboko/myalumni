@extends('layouts.alumni')

@section('content')
@php
    $eoi = $expressionOfInterest;
    $eoiService = app(\App\Services\Alumni\AlumniElectionEoiStatusService::class);
@endphp

<div class="elections-hub-page elections-eoi-status-page w-100 pe-lg-2">
    <div class="card w-100 border-0 bg-white shadow-xs p-0 mb-4">
        <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex flex-wrap justify-content-between align-items-start gap-2">
            <div>
                <h4 class="fw-600 mb-1">EOI Status</h4>
                <p class="text-grey-500 font-xssss mb-0">{{ $eoi->election->title }}</p>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="badge {{ $statusBadgeClass }}">{{ $statusLabel }}</span>
                <a href="{{ route('alumni.elections') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="feather-arrow-left me-1"></i> Back to Elections
                </a>
            </div>
        </div>

        <div class="card-body p-4 w-100 border-0">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if(session('info'))
                <div class="alert alert-info">{{ session('info') }}</div>
            @endif

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="elections-hub-stat">
                        <div class="elections-hub-stat__label">Position</div>
                        <div class="elections-hub-stat__value fw-600">{{ $eoi->office->title }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="elections-hub-stat">
                        <div class="elections-hub-stat__label">Application</div>
                        <div class="elections-hub-stat__value">
                            <span class="badge {{ $statusBadgeClass }}">{{ $statusLabel }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="elections-hub-stat">
                        <div class="elections-hub-stat__label">Agent</div>
                        <div class="elections-hub-stat__value">
                            @if($eoi->suggested_agent_id)
                                <span class="badge {{ $agentStatusBadgeClass }}">{{ ucfirst($eoi->agent_status ?? 'pending') }}</span>
                            @else
                                <span class="text-grey-500 font-xssss">Not suggested</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @if($pendingPaymentUrl)
                <div class="alert alert-warning font-xssss mb-4">
                    <i class="feather-credit-card me-1"></i>
                    Your application is awaiting screening fee payment.
                    <a href="{{ $pendingPaymentUrl }}" class="alert-link">Complete payment</a>
                </div>
            @endif

            @if($eoi->status === 'rejected' && $eoi->rejection_reason)
                <div class="alert alert-danger font-xssss mb-4">
                    <i class="feather-x-circle me-1"></i>
                    <strong>Screening remarks:</strong> {{ $eoi->rejection_reason }}
                </div>
            @endif

            <h6 class="fw-600 font-xssss text-grey-900 text-uppercase mb-3">Agent</h6>
            <div class="elections-eoi-panel mb-4">
                @if($eoi->suggested_agent_id)
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                        <div>
                            <div class="fw-600 font-xssss text-grey-900">{{ $agentName ?? 'Unknown agent' }}</div>
                            <span class="badge {{ $agentStatusBadgeClass }}">{{ ucfirst($eoi->agent_status ?? 'pending') }}</span>
                        </div>
                    </div>
                    @if($eoi->agent_status === 'rejected' && $eoi->agent_rejection_reason)
                        <div class="alert alert-danger font-xssss py-2 mb-2">{{ $eoi->agent_rejection_reason }}</div>
                    @endif
                @else
                    <p class="text-grey-500 font-xssss mb-2">No agent has been suggested yet.</p>
                @endif

                @if($canManageAgent)
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('candidate.elections.candidates.suggest-agent-form', [$eoi->election, $eoi->id]) }}"
                           class="btn btn-outline-primary btn-sm">
                            <i class="feather-user-plus me-1"></i>
                            {{ $eoi->suggested_agent_id ? 'Change agent' : 'Suggest agent' }}
                        </a>
                        @if($eoi->suggested_agent_id && in_array($eoi->agent_status, ['pending', 'rejected']))
                            <form action="{{ route('candidate.elections.candidates.cancel-suggestion', [$eoi->election, $eoi->id]) }}"
                                  method="POST"
                                  onsubmit="return confirm('Are you sure you want to cancel your agent suggestion?');">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                    <i class="feather-x-circle me-1"></i> Cancel suggestion
                                </button>
                            </form>
                        @endif
                    </div>
                @endif
            </div>

            @if($eoi->screened_at)
                <h6 class="fw-600 font-xssss text-grey-900 text-uppercase mb-3">Screening</h6>
                <div class="elections-eoi-panel mb-4">
                    <div class="row g-3 font-xssss">
                        <div class="col-md-4">
                            <div class="text-grey-500">Screened by</div>
                            <div class="fw-600">{{ $eoi->screener_name ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-grey-500">Screened at</div>
                            <div class="fw-600">{{ $eoi->formatted_screened_at ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            @endif

            <h6 class="fw-600 font-xssss text-grey-900 text-uppercase mb-3">Submitted materials</h6>
            <div class="elections-eoi-panel mb-4">
                <div class="row g-3">
                    @if($eoi->passport)
                        <div class="col-md-3">
                            <div class="text-grey-500 font-xsssss text-uppercase fw-600 mb-2">Passport photo</div>
                            <img src="{{ $eoiService->storageUrl($eoi->passport) }}"
                                 alt="Passport photograph"
                                 class="elections-eoi-passport rounded-3 w-100">
                        </div>
                    @endif
                    <div class="col-md-{{ $eoi->passport ? '5' : '8' }}">
                        <div class="text-grey-500 font-xsssss text-uppercase fw-600 mb-2">Manifesto</div>
                        <div class="elections-eoi-manifesto font-xssss">
                            {{ $eoi->manifesto ?: 'No manifesto submitted.' }}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-grey-500 font-xsssss text-uppercase fw-600 mb-2">Supporting documents</div>
                        @if($eoi->documents)
                            <div class="d-flex flex-column gap-2">
                                @foreach($eoi->documents as $document)
                                    <a href="{{ $eoiService->storageUrl($document) }}"
                                       target="_blank"
                                       rel="noopener"
                                       class="btn btn-sm btn-outline-secondary text-start">
                                        <i class="feather-file me-1"></i> View document
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <p class="text-grey-500 font-xssss mb-0">No supporting documents submitted.</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2 pt-3 border-top">
                <a href="{{ route('alumni.elections') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="feather-arrow-left me-1"></i> Back to Elections
                </a>
                @if($pendingPaymentUrl)
                    <a href="{{ $pendingPaymentUrl }}" class="btn btn-warning btn-sm">
                        <i class="feather-credit-card me-1"></i> Complete payment
                    </a>
                @endif
                @if($eoi->status === 'approved')
                    <a href="{{ route('alumni.elections.published-candidates', ['election' => $eoi->election, 'office' => $eoi->office]) }}"
                       class="btn btn-primary btn-sm">
                        <i class="feather-users me-1"></i> View published candidates
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
