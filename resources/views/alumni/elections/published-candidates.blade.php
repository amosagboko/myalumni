@extends('layouts.alumni')

@section('content')
<div class="elections-hub-page elections-published-candidates-page w-100 pe-lg-2">
    <div class="card w-100 border-0 bg-white shadow-xs p-0 mb-4">
        <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex flex-wrap justify-content-between align-items-start gap-2">
            <div>
                <h4 class="fw-600 mb-1">Published Candidates</h4>
                <p class="text-grey-500 font-xssss mb-0">{{ $election->title }} — {{ $office->title }}</p>
            </div>
            <a href="{{ route('alumni.elections') }}" class="btn btn-outline-secondary btn-sm">
                <i class="feather-arrow-left me-1"></i> Back to Elections
            </a>
        </div>

        <div class="card-body p-4 w-100 border-0">
            <div class="elections-hub-stat mb-4 d-inline-block">
                <div class="elections-hub-stat__label">Approved candidates</div>
                <div class="elections-hub-stat__value fw-600">{{ $candidateCount }}</div>
            </div>

            @if($candidates->isEmpty())
                <div class="alert alert-info font-xssss mb-0">
                    <i class="feather-info me-1"></i>
                    No candidates have been published for this office yet.
                </div>
            @else
                <div class="row g-3">
                    @foreach($candidates as $candidate)
                        <div class="col-12 col-md-6 col-lg-4">
                            @include('alumni.elections.partials.published-candidate-card', ['candidate' => $candidate])
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/alumni-elections-hub.css') }}">
@endpush
