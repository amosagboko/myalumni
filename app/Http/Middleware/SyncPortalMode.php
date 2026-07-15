<?php

namespace App\Http\Middleware;

use App\Services\PortalModeService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SyncPortalMode
{
    public function __construct(
        private readonly PortalModeService $portalMode
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            $this->portalMode->syncModeFromRequest($request->user(), $request);
        }

        return $next($request);
    }
}
