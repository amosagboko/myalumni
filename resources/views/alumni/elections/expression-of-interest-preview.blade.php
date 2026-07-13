@extends('layouts.alumni')

@section('content')
@php
    $eoiFormService = app(\App\Services\Alumni\AlumniElectionEoiFormService::class);
@endphp

<div class="elections-hub-page elections-eoi-preview-page w-100 pe-lg-2">
    <div class="card w-100 border-0 bg-white shadow-xs p-0 mb-4">
        <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex flex-wrap justify-content-between align-items-start gap-2">
            <div>
                <h4 class="fw-600 mb-1">Preview Application</h4>
                <p class="text-grey-500 font-xssss mb-0">{{ $election->title }} — {{ $office->title }}</p>
            </div>
            <a href="{{ route('alumni.elections.expression-of-interest.form', ['election' => $election, 'office' => $office]) }}"
               class="btn btn-outline-secondary btn-sm">
                <i class="feather-arrow-left me-1"></i> Back to form
            </a>
        </div>

        <div class="card-body p-4 w-100 border-0">
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="elections-hub-stat">
                        <div class="elections-hub-stat__label">Position</div>
                        <div class="elections-hub-stat__value fw-600">{{ $office->title }}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="elections-hub-stat">
                        <div class="elections-hub-stat__label">Screening fee</div>
                        <div class="elections-hub-stat__value fw-600 text-primary">
                            {{ $screeningFee->formatted_amount }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="elections-eoi-panel mb-4 text-center">
                <h6 class="fw-600 font-xssss mb-3">Passport photograph</h6>
                <img src="{{ $eoiFormService->storageUrl($passport) }}"
                     alt="Passport photograph"
                     class="rounded elections-eoi-passport elections-eoi-passport--preview">
            </div>

            <div class="elections-eoi-panel mb-4">
                <h6 class="fw-600 font-xssss mb-3">Uploaded files</h6>
                <ul class="list-unstyled mb-0 font-xssss">
                    <li class="mb-2">
                        <i class="feather-check-circle text-success me-1"></i> Passport photograph
                    </li>
                    <li>
                        <i class="feather-check-circle text-success me-1"></i>
                        Supporting documents ({{ $documentCount }} {{ Str::plural('file', $documentCount) }})
                    </li>
                </ul>
            </div>

            @if($manifesto)
                <div class="elections-eoi-panel mb-4">
                    <h6 class="fw-600 font-xssss mb-3">Manifesto</h6>
                    <div class="elections-eoi-manifesto font-xssss">{{ $manifesto }}</div>
                </div>
            @else
                <div class="elections-eoi-panel mb-4">
                    <h6 class="fw-600 font-xssss mb-2">Manifesto</h6>
                    <p class="text-grey-500 font-xssss mb-0">No manifesto provided.</p>
                </div>
            @endif

            <div class="alert alert-warning font-xssss mb-4">
                <div class="fw-600 mb-1"><i class="feather-credit-card me-1"></i> Payment required</div>
                <p class="mb-0">
                    A screening fee of {{ $screeningFee->formatted_amount }} is required to finalize your application.
                </p>
            </div>

            <div class="alert alert-info font-xssss mb-4">
                <ul class="mb-0 ps-3">
                    <li>The screening fee is non-refundable.</li>
                    <li>Your application will be reviewed by ELCOM after payment.</li>
                    <li>You can only express interest in one position at a time.</li>
                </ul>
            </div>

            <div class="d-flex flex-wrap justify-content-end gap-2">
                <a href="{{ route('alumni.elections.expression-of-interest.form', ['election' => $election, 'office' => $office]) }}"
                   class="btn btn-outline-secondary">
                    <i class="feather-edit me-1"></i> Edit application
                </a>

                <form action="{{ route('alumni.elections.expression-of-interest.submit', ['election' => $election, 'office' => $office]) }}"
                      method="POST"
                      class="d-inline">
                    @csrf
                    <input type="hidden" name="preview_token" value="{{ $previewToken }}">
                    <button type="submit" class="btn btn-primary">
                        Proceed to payment <i class="feather-arrow-right ms-1"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/alumni-elections-hub.css') }}">
@endpush
