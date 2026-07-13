@php
    $voteService = app(\App\Services\Alumni\AlumniElectionVoteService::class);
@endphp

<label class="elections-ballot-option list-group-item list-group-item-action border rounded-3 mb-2">
    <div class="form-check d-flex align-items-start gap-2 m-0">
        <input class="form-check-input mt-1"
               type="radio"
               name="votes[{{ $office->id }}]"
               value="{{ $candidate->id }}"
               required>
        <div class="d-flex align-items-start gap-2 flex-grow-1 min-w-0">
            @if($candidate->passport)
                <img src="{{ asset('storage/' . $candidate->passport) }}"
                     alt="Candidate photo"
                     class="rounded-circle elections-ballot-option__avatar">
            @else
                <div class="elections-ballot-option__avatar elections-ballot-option__avatar--placeholder rounded-circle d-flex align-items-center justify-content-center">
                    <i class="feather-user text-grey-500"></i>
                </div>
            @endif
            <div class="min-w-0 flex-grow-1">
                <div class="fw-600 font-xssss text-grey-900">{{ $voteService->candidateName($candidate) }}</div>
                <div class="text-grey-500 font-xssss">{{ $voteService->candidateMatric($candidate) }}</div>
                @if($candidate->manifesto)
                    <button type="button"
                            class="btn btn-sm btn-outline-primary mt-2"
                            data-bs-toggle="modal"
                            data-bs-target="#manifestoModal{{ $candidate->id }}">
                        <i class="feather-file-text me-1"></i> View manifesto
                    </button>
                @endif
            </div>
        </div>
    </div>
</label>

@if($candidate->manifesto)
    <div class="modal fade" id="manifestoModal{{ $candidate->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title h6">Manifesto — {{ $voteService->candidateName($candidate) }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="elections-manifesto-content font-xssss">{!! nl2br(e($candidate->manifesto)) !!}</div>
                    @if($candidate->documents)
                        <div class="mt-4">
                            <h6 class="font-xssss">Supporting documents</h6>
                            <ul class="list-unstyled mb-0">
                                @foreach($candidate->documents as $document)
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
