@php
    $resultsService = app(\App\Services\Alumni\AlumniElectionResultsService::class);
@endphp

<div class="elections-results-office mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h6 class="fw-600 font-xssss text-grey-900 mb-1">{{ $officeResult['office']->title }}</h6>
            <span class="text-grey-500 font-xssss">{{ number_format($officeResult['total_votes']) }} votes cast</span>
        </div>
    </div>

    @if($officeResult['candidates']->isEmpty())
        <p class="text-grey-500 font-xssss mb-0">No approved candidates for this office.</p>
    @else
        <div class="d-flex flex-column gap-2">
            @foreach($officeResult['candidates'] as $index => $candidate)
                <div class="elections-results-candidate {{ $candidate['is_winner'] ? 'elections-results-candidate--winner' : '' }}">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        @if($candidate['candidate']->passport)
                            <img src="{{ asset('storage/' . $candidate['candidate']->passport) }}"
                                 alt="Candidate photo"
                                 class="rounded-circle elections-results-candidate__avatar">
                        @else
                            <div class="elections-results-candidate__avatar elections-results-candidate__avatar--placeholder rounded-circle d-flex align-items-center justify-content-center">
                                <i class="feather-user text-grey-500"></i>
                            </div>
                        @endif
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-600 font-xssss text-grey-900 text-truncate">
                                {{ $resultsService->candidateName($candidate['candidate']) }}
                            </div>
                            <div class="text-grey-500 font-xssss">
                                {{ $resultsService->candidateMatric($candidate['candidate']->alumni) }}
                            </div>
                        </div>
                        <span class="badge {{ $index === 0 ? 'bg-success' : 'bg-secondary' }}">{{ $index + 1 }}</span>
                    </div>
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                        <strong class="font-xssss">{{ number_format($candidate['votes']) }} votes</strong>
                        @if($candidate['is_winner'])
                            <span class="badge bg-success"><i class="feather-award me-1"></i> Winner</span>
                        @else
                            <span class="badge bg-secondary">Not elected</span>
                        @endif
                    </div>
                    <div class="progress elections-results-progress">
                        <div class="progress-bar {{ $candidate['is_winner'] ? 'bg-success' : 'bg-primary' }}"
                             role="progressbar"
                             style="width: {{ $candidate['percentage'] }}%"
                             aria-valuenow="{{ $candidate['percentage'] }}"
                             aria-valuemin="0"
                             aria-valuemax="100">
                            <span class="font-xssss">{{ $candidate['percentage'] }}%</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
