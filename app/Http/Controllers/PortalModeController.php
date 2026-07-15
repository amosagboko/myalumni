<?php

namespace App\Http\Controllers;

use App\Services\PortalModeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PortalModeController extends Controller
{
    public function switch(Request $request, PortalModeService $portalMode): RedirectResponse
    {
        $validated = $request->validate([
            'mode' => 'required|in:operational,member',
        ]);

        $user = $request->user();

        if (! $portalMode->hasDualPortalAccess($user)) {
            abort(403);
        }

        $mode = $validated['mode'];

        if ($mode === PortalModeService::MODE_OPERATIONAL && ! $portalMode->hasOperationalAccess($user)) {
            abort(403);
        }

        if ($mode === PortalModeService::MODE_MEMBER && ! $portalMode->hasMemberAccess($user)) {
            abort(403);
        }

        $portalMode->setMode($mode);

        return redirect()->route(
            $mode === PortalModeService::MODE_OPERATIONAL
                ? $portalMode->getOperationalHomeRoute($user)
                : $portalMode->getMemberHomeRoute($user)
        );
    }
}
