<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        // If user is an admin, redirect to admin dashboard
        if ($user->hasRole('administrator')) {
            return redirect()->route('admin.dashboard');
        }

        // If user is an ELCOM chairman, redirect to ELCOM chairman dashboard
        if ($user->hasRole('elcom-chairman')) {
            return redirect()->route('elcom-chairman.dashboard');
        }

        // If user is an ARO, redirect to ARO dashboard
        if ($user->hasRole('alumni-relations-officer')) {
            return redirect()->route('alumni-relations-officer.home');
        }

        // For alumni, redirect to their home page
        if ($user->hasRole('alumni')) {
            return redirect()->route('alumni.home');
        }

        return redirect()->intended(route(RouteServiceProvider::getHomeRoute()));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function getOnboardingStatus($user)
    {
        $alumni = $user->alumni;
        
        return [
            'bio_data_completed' => $alumni && 
                $alumni->contact_address && 
                $alumni->phone_number && 
                $alumni->qualification_type,
            'payments_completed' => $alumni && 
                $alumni->getActiveFees()->every(function($fee) {
                    return $fee->isPaid();
                })
        ];
    }
}
