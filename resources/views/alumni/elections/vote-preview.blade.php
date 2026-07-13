@extends('layouts.alumni')

@section('content')
@php
    $voteService = app(\App\Services\Alumni\AlumniElectionVoteService::class);
@endphp

<div class="elections-hub-page elections-vote-page w-100 pe-lg-2">
    <div class="card w-100 border-0 bg-white shadow-xs p-0 mb-4">
        <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex flex-wrap justify-content-between align-items-start gap-2">
            <div>
                <h4 class="fw-600 mb-1">Preview Your Votes</h4>
                <p class="text-grey-500 font-xssss mb-0">{{ $election->title }}</p>
            </div>
            <a href="{{ route('alumni.elections.vote', $election) }}" class="btn btn-outline-secondary btn-sm">
                <i class="feather-arrow-left me-1"></i> Back to voting
            </a>
        </div>

        <div class="card-body p-4 w-100 border-0">
            <div class="alert alert-info font-xssss mb-4">
                <i class="feather-info me-1"></i>
                Review your selections carefully. Once confirmed, your votes cannot be changed.
            </div>

            <div class="d-flex flex-column gap-3 mb-4">
                @foreach($selectedCandidates as $selection)
                    <div class="elections-vote-preview-card">
                        <div class="text-grey-500 font-xsssss text-uppercase fw-600 mb-1">{{ $selection['office']->title }}</div>
                        @if($selection['office']->description)
                            <p class="text-grey-500 font-xssss mb-3">{{ $selection['office']->description }}</p>
                        @endif
                        <div class="d-flex align-items-start gap-3">
                            @if($selection['candidate']->passport)
                                <img src="{{ asset('storage/' . $selection['candidate']->passport) }}"
                                     alt="Candidate photo"
                                     class="rounded-circle elections-ballot-option__avatar">
                            @else
                                <div class="elections-ballot-option__avatar elections-ballot-option__avatar--placeholder rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="feather-user text-grey-500"></i>
                                </div>
                            @endif
                            <div class="min-w-0 flex-grow-1">
                                <div class="fw-600 font-xssss text-grey-900">{{ $voteService->candidateName($selection['candidate']) }}</div>
                                <div class="text-grey-500 font-xssss">{{ $voteService->candidateMatric($selection['candidate']) }}</div>
                                @if($selection['candidate']->manifesto)
                                    <button type="button"
                                            class="btn btn-sm btn-outline-primary mt-2"
                                            data-bs-toggle="modal"
                                            data-bs-target="#previewManifestoModal{{ $selection['candidate']->id }}">
                                        <i class="feather-file-text me-1"></i> View manifesto
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="alert alert-warning font-xssss mb-4">
                <i class="feather-alert-triangle me-1"></i>
                <strong>Important:</strong> Your votes will be recorded after confirmation. This action cannot be undone.
            </div>

            <div class="d-flex flex-wrap gap-2 pt-2 border-top">
                <a href="{{ route('alumni.elections.vote', $election) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="feather-edit-2 me-1"></i> Modify votes
                </a>
                <form action="{{ route('alumni.elections.submit-vote', $election) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit"
                            class="btn btn-success btn-sm"
                            onclick="return confirm('Are you sure you want to submit these votes? This action cannot be undone.')">
                        <i class="feather-check-circle me-1"></i> Confirm &amp; submit votes
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@foreach($selectedCandidates as $selection)
    @if($selection['candidate']->manifesto)
        <div class="modal fade" id="previewManifestoModal{{ $selection['candidate']->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title h6">Manifesto — {{ $voteService->candidateName($selection['candidate']) }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="elections-manifesto-content font-xssss">{!! nl2br(e($selection['candidate']->manifesto)) !!}</div>
                        @if($selection['candidate']->documents)
                            <div class="mt-4">
                                <h6 class="font-xssss">Supporting documents</h6>
                                <ul class="list-unstyled mb-0">
                                    @foreach($selection['candidate']->documents as $document)
                                        <li class="mb-2">
                                            <a href="{{ asset('storage/' . $document) }}"
                                               target="_blank"
                                               rel="noopener"
                                               class="btn btn-sm btn-outline-secondary">
                                                <i class="feather-file me-1"></i> View document
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

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/alumni-elections-hub.css') }}">
@endpush
@endsection
