<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EnsureClearanceComplete
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Allow admins by default
        if ($user->hasRole('administrator')) {
            return $next($request);
        }

        $alumni = $user->alumni;
        if (!$alumni) {
            return redirect()->route('upload.alumni')->with('error', 'Your alumni profile is not set up yet.');
        }

        // Require both divisions cleared
        if (!($alumni->student_affairs_cleared && $alumni->academic_affairs_cleared)) {
            Log::info('Access blocked by EnsureClearanceComplete', [
                'user_id' => $user->id,
                'student_affairs_cleared' => (bool) $alumni->student_affairs_cleared,
                'academic_affairs_cleared' => (bool) $alumni->academic_affairs_cleared,
                'path' => $request->path(),
            ]);
            return redirect()->route('alumni.home')->with('error', 'You must be cleared by Student Affairs and Academic Affairs to access this page.');
        }

        return $next($request);
    }
}
