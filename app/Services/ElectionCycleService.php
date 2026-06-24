<?php

namespace App\Services;

use App\Exceptions\ElectionImmutableException;
use App\Models\Election;
use Illuminate\Support\Facades\DB;

class ElectionCycleService
{
    public function assertMutable(Election $election): void
    {
        if ($election->isArchived()) {
            throw ElectionImmutableException::archived();
        }
    }

    public function assertCanStartNewCycle(): void
    {
        if (Election::completedUnarchived()->exists()) {
            throw ElectionImmutableException::unarchivedCompletedExists();
        }

        if (Election::operational()->where('is_active', true)->exists()) {
            throw ElectionImmutableException::activeElectionExists();
        }
    }

    public function assertSingleOperationalElection(Election $election): void
    {
        $conflicting = Election::operational()
            ->where('id', '!=', $election->id)
            ->whereIn('status', ['eoi', 'eoi_closed', 'accreditation', 'voting'])
            ->exists();

        if ($conflicting) {
            throw ElectionImmutableException::concurrentOperationalElection();
        }
    }

    public function beginOperationalPhase(Election $election): void
    {
        $this->assertMutable($election);
        $this->assertSingleOperationalElection($election);
        $this->activate($election);
    }

    public function activate(Election $election): void
    {
        Election::where('id', '!=', $election->id)->update(['is_active' => false]);
        $election->update(['is_active' => true]);
    }

    public function createFromStructure(?Election $source, array $meta): Election
    {
        $this->assertCanStartNewCycle();

        return DB::transaction(function () use ($source, $meta) {
            $election = Election::create([
                'title' => $meta['title'],
                'description' => $meta['description'],
                'eligibility_criteria' => $meta['eligibility_criteria'],
                'election_year' => $meta['election_year'],
                'cycle_label' => $meta['cycle_label'] ?? null,
                'eoi_start' => $meta['eoi_start'],
                'eoi_end' => $meta['eoi_end'],
                'accreditation_start' => $meta['accreditation_start'],
                'accreditation_end' => $meta['accreditation_end'],
                'voting_start' => $meta['voting_start'],
                'voting_end' => $meta['voting_end'],
                'screening_fee' => $meta['screening_fee'] ?? 0,
                'status' => 'draft',
                'is_active' => true,
                'cloned_from_election_id' => $source?->id,
            ]);

            if ($source) {
                foreach ($source->offices as $office) {
                    $election->offices()->create([
                        'title' => $office->title,
                        'description' => $office->description,
                        'max_candidates' => $office->max_candidates,
                        'max_terms' => $office->max_terms,
                        'term_duration' => $office->term_duration,
                        'fee_type_id' => $office->fee_type_id,
                        'is_active' => true,
                    ]);
                }
            }

            $this->activate($election);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($election)
                ->withProperties([
                    'cloned_from' => $source?->id,
                    'election_year' => $election->election_year,
                ])
                ->log('election.cycle_started');

            return $election;
        });
    }
}
