<?php

namespace App\Services\Alumni;

use App\Models\Election;
use App\Services\ElectionResultService;

class AlumniElectionResultsService
{
    public function __construct(
        private readonly ElectionResultService $resultService
    ) {}

    public function resultsData(Election $election): array
    {
        $election->load([
            'offices.candidates' => function ($query) {
                $query->where('status', 'approved')->with('electionResults');
            },
            'offices.candidates.alumni.user',
            'offices.candidates.votes',
            'results.candidate.alumni.user',
            'results.office',
        ]);

        $totalAccredited = $election->getTotalAccreditedVoters();
        $totalVotes = $election->getTotalVotes();
        $voterTurnout = $totalAccredited > 0
            ? round(($totalVotes / $totalAccredited) * 100, 2)
            : 0;

        $officeResults = $election->offices->map(function ($office) {
            $candidates = $office->candidates->map(function ($candidate) {
                $votes = $candidate->votes->count();

                return [
                    'candidate' => $candidate,
                    'votes' => $votes,
                    'is_winner' => $candidate->electionResults->where('is_winner', true)->isNotEmpty(),
                ];
            })->sortByDesc('votes')->values();

            $totalOfficeVotes = $candidates->sum('votes');

            return [
                'office' => $office,
                'candidates' => $candidates->map(function ($candidate) use ($totalOfficeVotes) {
                    $percentage = $totalOfficeVotes > 0
                        ? round(($candidate['votes'] / $totalOfficeVotes) * 100, 1)
                        : 0;

                    return array_merge($candidate, ['percentage' => $percentage]);
                }),
                'total_votes' => $totalOfficeVotes,
            ];
        });

        $resolution = $this->resultService->getResolutionSummary($election);

        return [
            'election' => $election,
            'officeResults' => $officeResults,
            'totalAccredited' => $totalAccredited,
            'totalVotes' => $totalVotes,
            'voterTurnout' => $voterTurnout,
            'resolution' => $resolution,
            'declaredAt' => $election->results->first()?->declared_at,
            'statusLabel' => $this->statusLabel($election),
            'statusBadgeClass' => $this->statusBadgeClass($election),
        ];
    }

    public function candidateMatric(?object $alumni): string
    {
        if (! $alumni) {
            return 'N/A';
        }

        return (string) ($alumni->matric_number ?? 'N/A');
    }

    public function candidateName(?object $candidate): string
    {
        return $candidate?->alumni?->user?->name ?? 'N/A';
    }

    private function statusLabel(Election $election): string
    {
        if ($election->isArchived()) {
            return 'Archived';
        }

        if ($election->isIncomplete()) {
            return 'Incomplete';
        }

        return 'Completed';
    }

    private function statusBadgeClass(Election $election): string
    {
        if ($election->isArchived()) {
            return 'bg-secondary';
        }

        if ($election->isIncomplete()) {
            return 'bg-warning text-dark';
        }

        return 'bg-success';
    }
}
