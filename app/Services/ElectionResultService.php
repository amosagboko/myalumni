<?php

namespace App\Services;

use App\Models\Election;
use App\Models\ElectionResult;
use App\Models\User;

class ElectionResultService
{
    public function declareResults(Election $election): void
    {
        foreach ($election->offices as $office) {
            $candidates = $office->candidates()
                ->where('status', 'approved')
                ->withCount('votes')
                ->orderByDesc('votes_count')
                ->orderBy('id')
                ->get();

            if ($candidates->isEmpty()) {
                continue;
            }

            $winner = $candidates->first();

            foreach ($candidates as $candidate) {
                ElectionResult::updateOrCreate(
                    [
                        'election_id' => $election->id,
                        'election_office_id' => $office->id,
                        'candidate_id' => $candidate->id,
                    ],
                    [
                        'total_votes' => $candidate->votes_count,
                        'is_winner' => $candidate->id === $winner->id,
                        'declared_at' => now(),
                    ]
                );
            }
        }

        $election->update([
            'status' => 'completed',
            'is_active' => false,
        ]);
    }
}
