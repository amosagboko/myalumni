<?php

namespace App\Services;

use App\Exceptions\ElectionImmutableException;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\ElectionOffice;
use App\Models\ElectionResult;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ElectionByElectionService
{
    public const MODE_RUNOFF = 'runoff';
    public const MODE_EOI = 'eoi';

    public function __construct(
        private ElectionCycleService $cycleService,
        private ElectionResultService $resultService
    ) {}

    /**
     * Offices on the parent election eligible for a new by-election.
     */
    public function schedulableOffices(Election $parent): Collection
    {
        return $parent->offices()
            ->whereIn('resolution_status', [
                ElectionResultService::RESOLUTION_TIED,
                ElectionResultService::RESOLUTION_UNCONTESTED,
            ])
            ->whereNull('by_election_id')
            ->orderBy('title')
            ->get();
    }

    public function hasActiveByElection(Election $parent): bool
    {
        return Election::query()
            ->where('parent_election_id', $parent->id)
            ->whereNotIn('status', ['completed', 'archived'])
            ->exists();
    }

    /**
     * @param  array<int>  $officeIds
     */
    public function schedule(Election $parent, array $officeIds, array $dates): Election
    {
        if (!$parent->isIncomplete()) {
            throw new \InvalidArgumentException('By-elections can only be scheduled for incomplete elections.');
        }

        if ($this->hasActiveByElection($parent)) {
            throw ElectionImmutableException::concurrentOperationalElection();
        }

        $offices = $this->schedulableOffices($parent)
            ->whereIn('id', $officeIds);

        if ($offices->isEmpty()) {
            throw new \InvalidArgumentException('Select at least one pending office for the by-election.');
        }

        $hasEoiOffices = $offices->contains(fn (ElectionOffice $o) => $o->isUncontested());
        $hasRunoffOffices = $offices->contains(fn (ElectionOffice $o) => $o->isTied());

        if ($hasEoiOffices && (empty($dates['eoi_start']) || empty($dates['eoi_end']))) {
            throw new \InvalidArgumentException('EOI dates are required when uncontested offices are included.');
        }

        return DB::transaction(function () use ($parent, $offices, $dates, $hasEoiOffices, $hasRunoffOffices) {
            $initialStatus = $hasEoiOffices ? 'draft' : 'eoi_closed';

            $byElection = Election::create([
                'title' => $dates['title'],
                'description' => $dates['description'] ?? "By-election for {$parent->title}",
                'eligibility_criteria' => $parent->eligibility_criteria,
                'election_year' => $parent->election_year,
                'cycle_label' => $dates['cycle_label'] ?? "{$parent->election_year} By-Election",
                'eoi_start' => $hasEoiOffices ? $dates['eoi_start'] : null,
                'eoi_end' => $hasEoiOffices ? $dates['eoi_end'] : null,
                'accreditation_start' => $dates['accreditation_start'],
                'accreditation_end' => $dates['accreditation_end'],
                'voting_start' => $dates['voting_start'],
                'voting_end' => $dates['voting_end'],
                'screening_fee' => $parent->screening_fee,
                'status' => $initialStatus,
                'election_type' => 'by_election',
                'parent_election_id' => $parent->id,
                'is_active' => true,
            ]);

            foreach ($offices as $parentOffice) {
                $mode = $parentOffice->isTied() ? self::MODE_RUNOFF : self::MODE_EOI;

                $childOffice = $byElection->offices()->create([
                    'title' => $parentOffice->title,
                    'description' => $parentOffice->description,
                    'max_candidates' => $parentOffice->max_candidates,
                    'max_terms' => $parentOffice->max_terms,
                    'term_duration' => $parentOffice->term_duration,
                    'fee_type_id' => $parentOffice->fee_type_id,
                    'is_active' => true,
                    'parent_office_id' => $parentOffice->id,
                    'by_election_mode' => $mode,
                ]);

                $parentOffice->update(['by_election_id' => $byElection->id]);

                if ($mode === self::MODE_RUNOFF) {
                    $this->carryForwardRunoffCandidates($parentOffice, $childOffice, $byElection);
                }
            }

            $this->cycleService->activateByElection($byElection);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($byElection)
                ->withProperties([
                    'parent_election_id' => $parent->id,
                    'office_ids' => $offices->pluck('id')->all(),
                    'has_eoi' => $hasEoiOffices,
                    'has_runoff' => $hasRunoffOffices,
                ])
                ->log('election.by_election_scheduled');

            return $byElection;
        });
    }

    protected function carryForwardRunoffCandidates(
        ElectionOffice $parentOffice,
        ElectionOffice $childOffice,
        Election $byElection
    ): void {
        $tiedResults = ElectionResult::query()
            ->where('election_id', $parentOffice->election_id)
            ->where('election_office_id', $parentOffice->id)
            ->where('is_tied', true)
            ->with('candidate')
            ->get();

        $parentCandidates = $tiedResults->map(fn ($r) => $r->candidate)->filter();

        if ($parentCandidates->isEmpty()) {
            $classification = $this->resultService->classifyOffice($parentOffice);
            $parentCandidates = $classification['tied_candidates'];
        }

        foreach ($parentCandidates as $parentCandidate) {
            Candidate::create([
                'election_id' => $byElection->id,
                'election_office_id' => $childOffice->id,
                'parent_candidate_id' => $parentCandidate->id,
                'alumni_id' => $parentCandidate->alumni_id,
                'status' => 'approved',
                'has_paid_screening_fee' => true,
                'passport' => $parentCandidate->passport,
                'manifesto' => $parentCandidate->manifesto,
                'documents' => $parentCandidate->documents,
                'approved_agent_id' => $parentCandidate->approved_agent_id,
                'screened_at' => now(),
                'screened_by' => $parentCandidate->screened_by,
            ]);
        }
    }

    /**
     * After a by-election ends, merge outcomes back to the parent election.
     */
    public function syncResultsToParent(Election $byElection): array
    {
        if (!$byElection->isByElection()) {
            throw new \InvalidArgumentException('This is not a by-election.');
        }

        $parent = $byElection->parentElection()->with('offices')->firstOrFail();

        return DB::transaction(function () use ($byElection, $parent) {
            foreach ($byElection->offices as $childOffice) {
                $parentOffice = ElectionOffice::find($childOffice->parent_office_id);
                if (!$parentOffice) {
                    continue;
                }

                $parentOffice->update(['by_election_id' => null]);

                if ($childOffice->resolution_status === ElectionResultService::RESOLUTION_DECIDED) {
                    $this->syncDecidedOffice($parent, $parentOffice, $childOffice);
                } else {
                    $parentOffice->update([
                        'resolution_status' => $childOffice->resolution_status,
                        'winner_candidate_id' => null,
                    ]);
                }
            }

            return $this->resultService->finalizeParentElection($parent->fresh());
        });
    }

    protected function syncDecidedOffice(
        Election $parent,
        ElectionOffice $parentOffice,
        ElectionOffice $childOffice
    ): void {
        $childWinner = Candidate::with('alumni')->find($childOffice->winner_candidate_id);
        if (!$childWinner) {
            return;
        }

        $parentCandidate = $this->resolveParentCandidate($parentOffice, $childWinner);

        $parentOffice->update([
            'resolution_status' => ElectionResultService::RESOLUTION_DECIDED,
            'winner_candidate_id' => $parentCandidate->id,
        ]);

        $voteCount = ElectionResult::query()
            ->where('election_id', $childOffice->election_id)
            ->where('election_office_id', $childOffice->id)
            ->where('candidate_id', $childWinner->id)
            ->value('total_votes') ?? 0;

        $approvedCandidates = $parentOffice->candidates()->where('status', 'approved')->get();

        if ($approvedCandidates->isEmpty()) {
            foreach ($childOffice->candidates()->where('status', 'approved')->get() as $childCandidate) {
                $pc = $this->resolveParentCandidate($parentOffice, $childCandidate);
                $cv = ElectionResult::query()
                    ->where('election_id', $childOffice->election_id)
                    ->where('candidate_id', $childCandidate->id)
                    ->value('total_votes') ?? 0;

                ElectionResult::updateOrCreate(
                    [
                        'election_id' => $parent->id,
                        'election_office_id' => $parentOffice->id,
                        'candidate_id' => $pc->id,
                    ],
                    [
                        'total_votes' => $cv,
                        'is_winner' => $pc->id === $parentCandidate->id,
                        'is_tied' => false,
                        'declared_at' => now(),
                    ]
                );
            }
        } else {
            ElectionResult::updateOrCreate(
                [
                    'election_id' => $parent->id,
                    'election_office_id' => $parentOffice->id,
                    'candidate_id' => $parentCandidate->id,
                ],
                [
                    'total_votes' => $voteCount,
                    'is_winner' => true,
                    'is_tied' => false,
                    'declared_at' => now(),
                ]
            );

            ElectionResult::query()
                ->where('election_id', $parent->id)
                ->where('election_office_id', $parentOffice->id)
                ->where('candidate_id', '!=', $parentCandidate->id)
                ->update(['is_winner' => false, 'is_tied' => false]);
        }
    }

    protected function resolveParentCandidate(ElectionOffice $parentOffice, Candidate $childCandidate): Candidate
    {
        if ($childCandidate->parent_candidate_id) {
            return Candidate::findOrFail($childCandidate->parent_candidate_id);
        }

        return Candidate::firstOrCreate(
            [
                'election_id' => $parentOffice->election_id,
                'election_office_id' => $parentOffice->id,
                'alumni_id' => $childCandidate->alumni_id,
            ],
            [
                'status' => 'approved',
                'has_paid_screening_fee' => $childCandidate->has_paid_screening_fee,
                'passport' => $childCandidate->passport,
                'manifesto' => $childCandidate->manifesto,
                'documents' => $childCandidate->documents,
                'screened_at' => now(),
            ]
        );
    }
}
