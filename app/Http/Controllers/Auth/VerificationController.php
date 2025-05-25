<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }

    public function verify(EmailVerificationRequest $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            if ($request->user()->hasRole('alumni')) {
                return redirect()->route('alumni.home');
            }
            return redirect()->route('admin.dashboard');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        if ($request->user()->hasRole('alumni')) {
            return redirect()->route('alumni.home')
                ->with('verified', true);
        }

        return redirect()->route('admin.dashboard')
            ->with('verified', true);
    }

    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            if ($request->user()->hasRole('alumni')) {
                return redirect()->route('alumni.home');
            }
            return redirect()->route('admin.dashboard');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
} 