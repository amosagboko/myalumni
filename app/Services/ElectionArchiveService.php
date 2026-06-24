<?php

namespace App\Services;

use App\Exceptions\ElectionImmutableException;
use App\Models\Election;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ElectionArchiveService
{
    public function archive(Election $election, User $user): Election
    {
        if (!$election->canArchive()) {
            throw new \RuntimeException('Only completed elections can be archived.');
        }

        return DB::transaction(function () use ($election, $user) {
            $snapshotPath = $this->generateSnapshot($election);

            $election->update([
                'status' => 'archived',
                'is_active' => false,
                'archived_at' => now(),
                'archived_by' => $user->id,
            ]);

            activity()
                ->causedBy($user)
                ->performedOn($election)
                ->withProperties([
                    'election_year' => $election->election_year,
                    'snapshot_path' => $snapshotPath,
                ])
                ->log('election.archived');

            return $election->fresh();
        });
    }

    private function generateSnapshot(Election $election): ?string
    {
        $election->load([
            'offices.candidates.alumni.user',
            'accreditedVoters.alumni.user',
            'results.candidate.alumni.user',
            'results.office',
        ]);

        $payload = [
            'archived_at' => now()->toIso8601String(),
            'election' => $election->only([
                'id', 'title', 'election_year', 'cycle_label', 'description',
                'eligibility_criteria', 'status', 'eoi_start', 'eoi_end',
                'accreditation_start', 'accreditation_end', 'voting_start', 'voting_end',
            ]),
            'ballots_cast' => $election->getTotalVotes(),
            'accredited_voters' => $election->getTotalAccreditedVoters(),
            'offices' => $election->offices->map(fn ($office) => [
                'title' => $office->title,
                'candidates' => $office->candidates->map(fn ($c) => [
                    'name' => $c->alumni?->user?->name,
                    'status' => $c->status,
                    'votes' => $c->votes()->count(),
                    'rejection_reason' => $c->rejection_reason,
                ]),
            ]),
            'results' => $election->results->map(fn ($r) => [
                'office' => $r->office?->title,
                'candidate' => $r->candidate?->alumni?->user?->name,
                'total_votes' => $r->total_votes,
                'is_winner' => $r->is_winner,
            ]),
        ];

        $year = $election->election_year ?? now()->year;
        $path = "election-archives/{$year}/election-{$election->id}.json";
        Storage::disk('local')->put($path, json_encode($payload, JSON_PRETTY_PRINT));

        return $path;
    }
}
