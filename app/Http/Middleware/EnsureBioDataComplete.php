<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureBioDataComplete
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
        if (Auth::check() && Auth::user()->alumni) {
            $alumni = Auth::user()->alumni;
            
            // Only enforce for 2023 and earlier graduates
            if ($alumni->year_of_graduation <= 2023) {
                $hasCompleteBioData = $alumni->contact_address && 
                    $alumni->phone_number && 
                    $alumni->qualification_type;

                if (!$hasCompleteBioData) {
                    // Exclude the bio data update route and logout route from redirect
                    if (!$request->is('alumni/profile/update') && !$request->is('logout')) {
                        return redirect()->route('alumni.profile.edit')
                            ->with('warning', 'Please complete your bio data to access this feature.');
                    }
                }
            }
        }

        return $next($request);
    }
} 