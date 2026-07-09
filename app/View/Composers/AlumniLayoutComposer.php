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

        $needsPayments = false;

        if ($alumni) {
            try {
                $activeFees = $alumni->getActiveFees();
                $unpaidFees = $activeFees->filter(fn ($fee) => !$fee->isPaid());
                $needsPayments = $activeFees->isNotEmpty() && $unpaidFees->isNotEmpty();
            } catch (\Throwable $e) {
                report($e);
                $needsPayments = false;
            }
        }

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
