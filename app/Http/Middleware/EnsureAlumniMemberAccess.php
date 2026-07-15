<?php

namespace App\Http\Middleware;

use App\Services\Alumni\AlumniMemberAccessService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAlumniMemberAccess
{
    public function __construct(
        private readonly AlumniMemberAccessService $memberAccess
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $this->memberAccess->shouldEnforcePortalGate($user, $request)) {
            return $next($request);
        }

        $alumni = $user->alumni;
        $status = $this->memberAccess->status($alumni);

        if ($status['isFullMember']) {
            return $next($request);
        }

        if ($this->memberAccess->isAllowedLimitedRoute($request)) {
            return $next($request);
        }

        return redirect()
            ->route($this->memberAccess->redirectRoute($status))
            ->with('warning', $this->memberAccess->restrictionMessage($status));
    }
}
