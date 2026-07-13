<?php

namespace App\Services\Alumni;

use App\Models\Alumni;
use App\Models\Candidate;
use App\Models\Election;

class AlumniElectionVoteService
{
    public function __construct(
        private readonly AlumniElectionResultsService $resultsService
    ) {}

    public function votePageData(Election $election, ?Alumni $alumni): array
    {
        $offices = $election->offices()->with([
            'candidates' => function ($query) {
                $query->where('status', 'approved');
            },
            'candidates.alumni.user',
        ])->get();

        $voter = $alumni
            ? $election->accreditedVoters()->where('alumni_id', $alumni->id)->first()
            : null;

        $votingPeriodActive = $election->canAcceptVoteSubmissions();

        return [
            'election' => $election,
            'offices' => $offices,
            'totalAccredited' => $election->getTotalAccreditedVoters(),
            'totalSubscribed' => $election->getTotalSubscribedUsers(),
            'totalExempted' => $election->getTotalExemptedUsers(),
            'timeRemaining' => $votingPeriodActive
                ? $election->voting_end->diffForHumans(['parts' => 2])
                : null,
            'isAccredited' => (bool) $voter,
            'hasVoted' => (bool) $voter?->has_voted,
            'votedAt' => $voter?->voted_at,
            'votingPeriodActive' => $votingPeriodActive,
            'votingStatusAlert' => $votingPeriodActive ? 'alert-info' : 'alert-warning',
            'votingStatusMessage' => $this->votingStatusMessage($election, $votingPeriodActive),
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

    private function votingStatusMessage(Election $election, bool $active): string
    {
        if ($active) {
            return 'Voting is currently active. You can cast your vote now.';
        }

        if ($election->isIncomplete()) {
            return 'This election is incomplete. Voting is closed while pending offices await a by-election.';
        }

        if ($election->status === 'completed') {
            return 'This election has been completed. Voting is no longer available.';
        }

        if ($election->status === 'accreditation') {
            return 'Voting has not started yet. The election is still in the accreditation phase.';
        }

        return 'Voting period has not been scheduled for this election yet.';
    }
}
