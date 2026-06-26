<?php

namespace App\Services;

use App\Models\Election;
use App\Models\ElectionOffice;
use App\Models\ElectionResult;
use Illuminate\Support\Collection;

class ElectionResultService
{
    public const RESOLUTION_DECIDED = 'decided';
    public const RESOLUTION_TIED = 'tied';
    public const RESOLUTION_UNCONTESTED = 'uncontested';

    /**
     * Classify a single office after voting ends.
     *
     * @return array{status: string, winner: ?\App\Models\Candidate, tied_candidates: Collection, candidates: Collection}
     */
    public function classifyOffice(ElectionOffice $office): array
    {
        $candidates = $office->candidates()
            ->where('status', 'approved')
            ->withCount('votes')
            ->orderByDesc('votes_count')
            ->orderBy('id')
            ->get();

        if ($candidates->isEmpty()) {
            return [
                'status' => self::RESOLUTION_UNCONTESTED,
                'winner' => null,
                'tied_candidates' => collect(),
                'candidates' => $candidates,
            ];
        }

        $maxVotes = $candidates->first()->votes_count;
        $topCandidates = $candidates->where('votes_count', $maxVotes);

        if ($topCandidates->count() > 1) {
            return [
                'status' => self::RESOLUTION_TIED,
                'winner' => null,
                'tied_candidates' => $topCandidates->values(),
                'candidates' => $candidates,
            ];
        }

        return [
            'status' => self::RESOLUTION_DECIDED,
            'winner' => $topCandidates->first(),
            'tied_candidates' => collect(),
            'candidates' => $candidates,
        ];
    }

    /**
     * Declare results for all offices and set election status.
     *
     * @return array{all_decided: bool, decided: int, tied: int, uncontested: int}
     */
    public function declareResults(Election $election): array
    {
        $counts = ['decided' => 0, 'tied' => 0, 'uncontested' => 0];

        foreach ($election->offices as $office) {
            $classification = $this->classifyOffice($office);
            $counts[$classification['status']]++;

            $office->update([
                'resolution_status' => $classification['status'],
                'winner_candidate_id' => $classification['winner']?->id,
            ]);

            foreach ($classification['candidates'] as $candidate) {
                $isTied = $classification['status'] === self::RESOLUTION_TIED
                    && $classification['tied_candidates']->contains('id', $candidate->id);

                ElectionResult::updateOrCreate(
                    [
                        'election_id' => $election->id,
                        'election_office_id' => $office->id,
                        'candidate_id' => $candidate->id,
                    ],
                    [
                        'total_votes' => $candidate->votes_count,
                        'is_winner' => $classification['winner']?->id === $candidate->id,
                        'is_tied' => $isTied,
                        'declared_at' => now(),
                    ]
                );
            }

            if ($classification['status'] === self::RESOLUTION_UNCONTESTED) {
                ElectionResult::where('election_id', $election->id)
                    ->where('election_office_id', $office->id)
                    ->delete();
            }
        }

        $allDecided = $counts['tied'] === 0 && $counts['uncontested'] === 0;

        $election->update([
            'status' => $allDecided ? 'completed' : 'incomplete',
            'is_active' => !$allDecided,
        ]);

        return array_merge(['all_decided' => $allDecided], $counts);
    }

    /**
     * Summary for ELCOM resolution dashboard.
     */
    public function getResolutionSummary(Election $election): array
    {
        $election->load([
            'offices.winnerCandidate.alumni.user',
            'offices.candidates' => fn ($q) => $q->where('status', 'approved')->withCount('votes'),
            'offices.candidates.alumni.user',
            'offices.results',
        ]);

        $decided = collect();
        $tied = collect();
        $uncontested = collect();

        foreach ($election->offices as $office) {
            if ($office->by_election_id) {
                continue;
            }

            $item = $this->buildOfficeResolutionItem($office);

            match ($office->resolution_status) {
                self::RESOLUTION_TIED => $tied->push($item),
                self::RESOLUTION_UNCONTESTED => $uncontested->push($item),
                default => $decided->push($item),
            };
        }

        return [
            'decided' => $decided,
            'tied' => $tied,
            'uncontested' => $uncontested,
            'has_pending' => $tied->isNotEmpty() || $uncontested->isNotEmpty(),
            'pending_count' => $tied->count() + $uncontested->count(),
            'active_by_election' => $election->activeByElection(),
            'offices_in_by_election' => $election->offices()->whereNotNull('by_election_id')->with('byElection')->get(),
        ];
    }

    /**
     * After by-election results are merged, complete the parent if all offices are decided.
     *
     * @return array{all_decided: bool, parent_completed: bool, pending?: int}
     */
    public function finalizeParentElection(Election $parent): array
    {
        $pending = $parent->offices()
            ->whereIn('resolution_status', [self::RESOLUTION_TIED, self::RESOLUTION_UNCONTESTED])
            ->count();

        if ($pending === 0) {
            $parent->update([
                'status' => 'completed',
                'is_active' => false,
            ]);

            return ['all_decided' => true, 'parent_completed' => true];
        }

        $parent->update([
            'status' => 'incomplete',
            'is_active' => true,
        ]);

        return [
            'all_decided' => false,
            'parent_completed' => false,
            'pending' => $pending,
        ];
    }

    protected function buildOfficeResolutionItem(ElectionOffice $office): array
    {
        $approved = $office->candidates->sortByDesc('votes_count')->values();
        $maxVotes = $approved->max('votes_count') ?? 0;
        $tiedCandidates = $approved->where('votes_count', $maxVotes);

        return [
            'office' => $office,
            'winner' => $office->winnerCandidate,
            'candidates' => $approved,
            'tied_candidates' => $office->resolution_status === self::RESOLUTION_TIED
                ? $tiedCandidates
                : collect(),
            'top_vote_count' => $maxVotes,
        ];
    }

    /**
     * Whether an office has an officially declared winner (not vote-count inference).
     */
    public function officeHasDeclaredWinner(ElectionOffice $office): bool
    {
        if ($office->resolution_status === self::RESOLUTION_DECIDED && $office->winner_candidate_id) {
            return true;
        }

        return ElectionResult::query()
            ->where('election_id', $office->election_id)
            ->where('election_office_id', $office->id)
            ->where('is_winner', true)
            ->exists();
    }

    /**
     * Declared winners only — for certificates, winner lists, and verification.
     */
    public function getDeclaredWinners(Election $election): Collection
    {
        $election->load([
            'offices.winnerCandidate.alumni.user',
            'offices.results.candidate.alumni.user',
        ]);

        return $election->offices->map(function (ElectionOffice $office) {
            if (!$this->officeHasDeclaredWinner($office)) {
                return null;
            }

            $result = $office->results->firstWhere('is_winner', true)
                ?? ElectionResult::query()
                    ->where('election_id', $office->election_id)
                    ->where('election_office_id', $office->id)
                    ->where('is_winner', true)
                    ->first();

            $candidate = $office->winnerCandidate ?? $result?->candidate;
            if (!$candidate) {
                return null;
            }

            $candidate->loadMissing('alumni.user');

            $officeTotal = $office->results->sum('total_votes');
            if ($officeTotal === 0 && $result) {
                $officeTotal = ElectionResult::query()
                    ->where('election_id', $office->election_id)
                    ->where('election_office_id', $office->id)
                    ->sum('total_votes');
            }

            $votes = $result?->total_votes ?? 0;

            return [
                'office' => $office,
                'candidate' => $candidate,
                'votes' => $votes,
                'percentage' => $officeTotal > 0 ? round(($votes / $officeTotal) * 100, 1) : 0.0,
                'declared_at' => $result?->declared_at,
            ];
        })->filter()->values();
    }
}
