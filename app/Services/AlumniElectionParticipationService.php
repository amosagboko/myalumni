<?php

namespace App\Services;

use App\Models\Alumni;
use App\Models\Election;

class AlumniElectionParticipationService
{
    public function participationFor(Election $election, Alumni $alumni): array
    {
        $candidate = $election->candidates()
            ->with('office')
            ->where('alumni_id', $alumni->id)
            ->first();

        $voter = $election->accreditedVoters()
            ->where('alumni_id', $alumni->id)
            ->first();

        return [
            'eoi' => $candidate ? [
                'status' => $candidate->status,
                'office' => $candidate->office?->title,
            ] : null,
            'is_accredited' => (bool) $voter,
            'accredited_at' => $voter?->accredited_at,
            'has_voted' => (bool) $voter?->has_voted,
            'voted_at' => $voter?->voted_at,
        ];
    }

    public function phaseLabel(Election $election): string
    {
        if ($election->isArchived()) {
            return 'Archived';
        }

        if ($election->status === 'completed') {
            return 'Completed';
        }

        if ($election->isEoiPeriodActive()) {
            return 'Expression of Interest';
        }

        if ($election->status === 'eoi') {
            return 'EOI scheduled';
        }

        if ($election->isAccreditationPeriodActive()) {
            return 'Accreditation';
        }

        if ($election->status === 'accreditation') {
            return 'Accreditation scheduled';
        }

        if ($election->isVotingPeriodActive()) {
            return 'Voting';
        }

        if ($election->status === 'voting') {
            return 'Voting scheduled';
        }

        return ucfirst($election->status);
    }

    public function actionsFor(Election $election, Alumni $alumni, array $participation): array
    {
        if ($election->isHistorical()) {
            return [
                'view_results' => true,
                'view_candidates' => true,
                'express_interest' => false,
                'accredit' => false,
                'vote' => false,
                'live_results' => false,
            ];
        }

        $canAccredit = $election->isAccreditationPeriodActive()
            && !$participation['is_accredited']
            && $election->isAlumniEligibleToVote($alumni);

        $canVote = $election->isVotingPeriodActive()
            && $participation['is_accredited']
            && !$participation['has_voted'];

        return [
            'view_results' => false,
            'view_candidates' => true,
            'express_interest' => $election->isEoiPeriodActive(),
            'accredit' => $canAccredit,
            'vote' => $canVote,
            'live_results' => $election->status === 'voting' && $election->isVotingPeriodActive(),
            'view_accreditation_status' => $election->status === 'accreditation'
                || $participation['is_accredited'],
            'view_vote_page' => $election->status === 'voting'
                && ($participation['is_accredited'] || $participation['has_voted']),
        ];
    }
}
