<?php

namespace App\View\Composers;

use App\Services\Alumni\AlumniMemberAccessService;
use App\Services\Alumni\ClearanceFormService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AlumniLayoutComposer
{
    public function __construct(
        private readonly ClearanceFormService $clearanceFormService,
        private readonly AlumniMemberAccessService $memberAccess
    ) {}

    public function compose(View $view): void
    {
        $user = Auth::user();
        $alumni = $user?->alumni;
        $status = $this->clearanceFormService->accessStatus($alumni);
        $restricted = $this->memberAccess->shouldEnforcePortalGate($user)
            && ! ($status['isFullMember'] ?? false);

        $view->with([
            'alumniNavUser' => $user,
            'alumniNavAlumni' => $alumni,
            'alumniNeedsBioData' => $status['needsBioData'],
            'alumniNeedsPayments' => $status['needsPayments'],
            'alumniMemberRestricted' => $restricted,
            'alumniMemberRestrictionMessage' => $this->memberAccess->restrictionMessage($status),
            'alumniClearanceDisabled' => $restricted,
            'alumniElectionLinksDisabled' => $restricted,
        ]);
    }
}
