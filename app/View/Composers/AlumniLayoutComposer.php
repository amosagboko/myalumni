<?php

namespace App\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class AlumniLayoutComposer
{
    public function compose(View $view): void
    {
        $user = Auth::user();
        $alumni = $user?->alumni;

        $needsBioData = !$alumni
            || !$alumni->contact_address
            || !$alumni->phone_number
            || !$alumni->qualification_type;

        $activeFees = $alumni ? $alumni->getActiveFees() : collect();
        $unpaidFees = $activeFees->filter(fn ($fee) => !$fee->isPaid());
        $needsPayments = $alumni && $activeFees->isNotEmpty() && $unpaidFees->isNotEmpty();

        $clearanceDisabled = $needsBioData || $needsPayments;
        $electionLinksDisabled = $needsBioData || $needsPayments;

        $view->with([
            'alumniNavUser' => $user,
            'alumniNavAlumni' => $alumni,
            'alumniNeedsBioData' => $needsBioData,
            'alumniNeedsPayments' => $needsPayments,
            'alumniClearanceDisabled' => $clearanceDisabled,
            'alumniElectionLinksDisabled' => $electionLinksDisabled,
        ]);
    }
}
