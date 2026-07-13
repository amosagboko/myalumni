<?php

namespace App\Services\Alumni;

use App\Models\Alumni;
use App\Models\Election;
use App\Models\User;

class AlumniElectionAccreditationService
{
    public function pageData(Election $election, ?User $user): array
    {
        $alumni = $user?->alumni;
        $accreditation = $alumni
            ? $election->accreditedVoters()->where('alumni_id', $alumni->id)->first()
            : null;

        $accreditationPeriodActive = $election->canAcceptAccreditationSubmissions();
        $accreditationEnded = $election->status === 'accreditation' && $election->hasAccreditationEnded();
        $accreditationNotStarted = $election->status === 'accreditation' && ! $election->hasAccreditationStarted();

        return [
            'election' => $election,
            'alumni' => $alumni,
            'user' => $user,
            'isEligible' => $alumni ? $election->isAlumniEligibleToVote($alumni) : false,
            'isAccredited' => (bool) $accreditation,
            'accreditation' => $accreditation,
            'accreditationPeriodActive' => $accreditationPeriodActive,
            'accreditationEnded' => $accreditationEnded,
            'accreditationNotStarted' => $accreditationNotStarted,
            'periodAlertClass' => $this->periodAlertClass($accreditationPeriodActive, $accreditationEnded, $accreditationNotStarted),
            'periodMessage' => $this->periodMessage($election, $accreditationPeriodActive, $accreditationEnded, $accreditationNotStarted),
            'ineligibilityReasons' => $this->ineligibilityReasons($alumni),
        ];
    }

    public function matricNumber(?Alumni $alumni): string
    {
        return (string) ($alumni?->matric_number ?? 'N/A');
    }

    private function periodAlertClass(bool $active, bool $ended, bool $notStarted): string
    {
        if ($active) {
            return 'alert-info';
        }

        if ($ended) {
            return 'alert-danger';
        }

        if ($notStarted) {
            return 'alert-warning';
        }

        return 'alert-secondary';
    }

    private function periodMessage(
        Election $election,
        bool $active,
        bool $ended,
        bool $notStarted
    ): string {
        if ($active) {
            return 'Accreditation is currently active. You can submit your accreditation request now.';
        }

        if ($notStarted && $election->accreditation_start) {
            return 'Accreditation period will start on '.$election->accreditation_start->format('M d, Y h:i A').'. Please check back later.';
        }

        if ($ended && $election->accreditation_end) {
            return 'Accreditation period ended on '.$election->accreditation_end->format('M d, Y h:i A').'.';
        }

        if ($election->status === 'voting') {
            return 'Accreditation period has ended. The election is now in the voting phase.';
        }

        if ($election->isIncomplete()) {
            return 'This election is incomplete. Accreditation is closed while pending offices await a by-election.';
        }

        if ($election->status === 'completed') {
            return 'This election has been completed. Accreditation is no longer available.';
        }

        return 'Accreditation period has not been scheduled for this election yet.';
    }

    private function ineligibilityReasons(?Alumni $alumni): array
    {
        if (! $alumni) {
            return ['Complete your alumni profile'];
        }

        $reasons = [];

        if (! $alumni->hasPaidAllActiveFees()) {
            $reasons[] = 'Complete all pending payments';
        }

        if (! $alumni->contact_address || ! $alumni->phone_number || ! $alumni->qualification_type) {
            $reasons[] = 'Complete your bio-data profile';
        }

        if ($alumni->hasPaidAllActiveFees() && $alumni->contact_address && $alumni->phone_number && $alumni->qualification_type) {
            $reasons[] = 'You may already be accredited or are otherwise ineligible';
        }

        return $reasons;
    }
}
