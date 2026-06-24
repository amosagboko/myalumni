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

        return RouteServiceProvider::redirectToHome();
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
