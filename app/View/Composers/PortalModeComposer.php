<?php

namespace App\View\Composers;

use App\Services\PortalModeService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PortalModeComposer
{
    public function __construct(
        private readonly PortalModeService $portalMode
    ) {}

    public function compose(View $view): void
    {
        $user = Auth::user();

        $view->with([
            'hasDualPortalAccess' => $this->portalMode->hasDualPortalAccess($user),
            'portalMode' => $this->portalMode->getMode($user, request()),
            'portalSwitchOperationalLabel' => $this->portalMode->operationalSwitchLabel($user),
            'portalSwitchMemberLabel' => 'Member Portal',
        ]);
    }
}
