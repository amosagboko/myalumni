<?php

namespace App\View\Composers;

use App\Services\Alumni\ClearanceFormService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AlumniLayoutComposer
{
    public function __construct(
        private readonly ClearanceFormService $clearanceFormService
    ) {}

    public function compose(View $view): void
    {
        $user = Auth::user();
        $alumni = $user?->alumni;
        $status = $this->clearanceFormService->accessStatus($alumni);

        $view->with([
            'alumniNavUser' => $user,
            'alumniNavAlumni' => $alumni,
            'alumniNeedsBioData' => $status['needsBioData'],
            'alumniNeedsPayments' => $status['needsPayments'],
            'alumniClearanceDisabled' => ! $status['allOk'],
            'alumniElectionLinksDisabled' => ! $status['allOk'],
        ]);
    }
}
