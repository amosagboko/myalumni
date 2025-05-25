<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Candidate;
use Illuminate\Support\Facades\Auth;

class EnsureAgentOwnsCandidate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $candidate = $request->route('candidate');
        
        if (!$candidate || $candidate->approved_agent_id !== Auth::id()) {
            abort(403, 'You are not authorized to access this candidate.');
        }

        return $next($request);
    }
} 