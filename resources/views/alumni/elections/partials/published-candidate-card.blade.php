@php
    $candidatesService = app(\App\Services\Alumni\AlumniElectionPublishedCandidatesService::class);
@endphp

<div class="elections-published-candidate h-100">
    <div class="d-flex align-items-start gap-3 mb-3">
        @if($candidate->passport)
            <img src="{{ $candidatesService->storageUrl($candidate->passport) }}"
                 alt="Candidate photo"
                 class="rounded-circle elections-published-candidate__avatar">
        @else
            <div class="elections-published-candidate__avatar elections-published-candidate__avatar--placeholder rounded-circle d-flex align-items-center justify-content-center">
                <i class="feather-user text-grey-500"></i>
            </div>
        @endif
        <div class="min-w-0 flex-grow-1">
            <div class="fw-600 font-xssss text-grey-900">{{ $candidatesService->candidateName($candidate) }}</div>
            <div class="text-grey-500 font-xssss">{{ $candidatesService->candidateMatric($candidate) }}</div>
        </div>
    </div>

    @if($candidate->manifesto)
        <p class="font-xssss text-grey-500 mb-3">{{ Str::limit($candidate->manifesto, 140) }}</p>
        <button type="button"
                class="btn btn-sm btn-outline-primary w-100"
                data-bs-toggle="modal"
                data-bs-target="#manifestoModal{{ $candidate->id }}">
            <i class="feather-file-text me-1"></i> View manifesto
        </button>
    @endif

    @if($candidate->documents)
        <div class="mt-3">
            <div class="font-xssss fw-600 mb-2">Supporting documents</div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($candidate->documents as $index => $document)
                    <a href="{{ $candidatesService->storageUrl($document) }}"
                       target="_blank"
                       rel="noopener"
                       class="btn btn-sm btn-outline-secondary">
                        <i class="feather-file me-1"></i> Doc {{ $index + 1 }}
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>

@if($candidate->manifesto)
    <div class="modal fade" id="manifestoModal{{ $candidate->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title h6">Manifesto — {{ $candidatesService->candidateName($candidate) }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="elections-manifesto-content font-xssss">{!! nl2br(e($candidate->manifesto)) !!}</div>
                    @if($candidate->documents)
                        <div class="mt-4">
                            <h6 class="font-xssss">Supporting documents</h6>
                            <ul class="list-unstyled mb-0">
                                @foreach($candidate->documents as $index => $document)
                                    <li class="mb-2">
                                        <a href="{{ $candidatesService->storageUrl($document) }}"
                                           target="_blank"
                                           rel="noopener"
                                           class="btn btn-sm btn-outline-secondary">
                                            <i class="feather-file me-1"></i> Document {{ $index + 1 }}
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
