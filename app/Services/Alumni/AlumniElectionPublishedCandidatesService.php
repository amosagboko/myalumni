<?php

namespace App\Services\Alumni;

use App\Models\Candidate;
use App\Models\Election;
use App\Models\ElectionOffice;

class AlumniElectionPublishedCandidatesService
{
    public function __construct(
        private readonly AlumniElectionResultsService $resultsService,
    ) {}

    public function pageData(Election $election, ElectionOffice $office): array
    {
        $candidates = $office->candidates()
            ->where('status', Candidate::STATUS_APPROVED)
            ->with('alumni.user')
            ->orderBy('id')
            ->get();

        return [
            'election' => $election,
            'office' => $office,
            'candidates' => $candidates,
            'candidateCount' => $candidates->count(),
        ];
    }

    public function candidateName(?Candidate $candidate): string
    {
        return $this->resultsService->candidateName($candidate);
    }

    public function candidateMatric(?Candidate $candidate): string
    {
        return $this->resultsService->candidateMatric($candidate?->alumni);
    }

    public function storageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return asset('storage/'.$path);
    }
}
