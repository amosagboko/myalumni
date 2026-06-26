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
                'status_label' => $candidate->status_label,
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

        if ($election->isIncomplete()) {
            return 'Incomplete — by-election pending';
        }

        if ($election->isByElection()) {
            return 'By-Election — ' . $this->phaseLabelForOperational($election);
        }

        if ($election->canAcceptEoiSubmissions()) {
            return 'Expression of Interest';
        }

        if ($election->status === 'eoi') {
            return 'EOI scheduled';
        }

        if ($election->status === 'eoi_closed') {
            return 'EOI closed';
        }

        if ($election->canAcceptAccreditationSubmissions()) {
            return 'Accreditation';
        }

        if ($election->status === 'accreditation') {
            return 'Accreditation scheduled';
        }

        if ($election->canAcceptVoteSubmissions()) {
            return 'Voting';
        }

        if ($election->status === 'voting') {
            return 'Voting scheduled';
        }

        return ucfirst(str_replace('_', ' ', $election->status));
    }

    protected function phaseLabelForOperational(Election $election): string
    {
        if ($election->canAcceptEoiSubmissions()) {
            return 'Expression of Interest';
        }

        if ($election->status === 'eoi') {
            return 'EOI scheduled';
        }

        if ($election->status === 'eoi_closed') {
            return 'EOI closed';
        }

        if ($election->canAcceptAccreditationSubmissions()) {
            return 'Accreditation';
        }

        if ($election->status === 'accreditation') {
            return 'Accreditation scheduled';
        }

        if ($election->canAcceptVoteSubmissions()) {
            return 'Voting';
        }

        if ($election->status === 'voting') {
            return 'Voting scheduled';
        }

        return ucfirst(str_replace('_', ' ', $election->status));
    }

    public function actionsFor(Election $election, Alumni $alumni, array $participation): array
    {
        if ($election->isHistorical()) {
            return [
                'view_results' => $election->resultsArePublished(),
                'view_candidates' => true,
                'express_interest' => false,
                'accredit' => false,
                'vote' => false,
                'live_results' => false,
            ];
        }

        $canAccredit = $election->canAcceptAccreditationSubmissions()
            && !$participation['is_accredited']
            && $election->isAlumniEligibleToVote($alumni);

        $canVote = $election->canAcceptVoteSubmissions()
            && $participation['is_accredited']
            && !$participation['has_voted'];

        return [
            'view_results' => false,
            'view_candidates' => true,
            'express_interest' => $election->canAcceptEoiSubmissions(),
            'accredit' => $canAccredit,
            'vote' => $canVote,
            'live_results' => $election->canAcceptVoteSubmissions(),
            'view_accreditation_status' => $election->status === 'accreditation'
                || $participation['is_accredited'],
            'view_vote_page' => $election->status === 'voting'
                && ($participation['is_accredited'] || $participation['has_voted']),
        ];
    }
}
