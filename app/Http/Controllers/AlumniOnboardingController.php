<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AlumniOnboardingController extends Controller
{
    public function showOnboarding()
    {
        $user = Auth::user();
        
        // Check if user is an alumni
        if (!$user->hasRole('alumni')) {
            return redirect()->route('dashboard');
        }

        // Check if password needs to be updated (first login)
        if ($user->is_first_login) {
            return view('alumni.onboarding.password-update');
        }

        // If email is not verified, show verification notice
        if (!$user->email_verified_at) {
            return view('alumni.onboarding.email-verification');
        }

        // All checks passed, redirect to alumni home
        return redirect()->route('alumni.home');
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

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
            'current_password' => ['required', 'current_password'],
        ]);

        try {
            $user = Auth::user();
            
            // Update password
            $user->password = Hash::make($request->password);
            $user->is_first_login = false;
            $user->save();

            // Regenerate session to prevent session fixation
            $request->session()->regenerate();

            // Redirect to alumni home
            return redirect()->route('alumni.home')
                ->with('success', 'Password updated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update password. Please try again.')
                ->withInput();
        }
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . Auth::id()],
            'password' => ['required', 'confirmed', Password::defaults()],
            'current_password' => ['required', 'current_password'],
        ]);

        $user = Auth::user();
        
        // Update email and password
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();

        // Send verification email
        $user->sendEmailVerificationNotification();

        return redirect()->route('verification.notice')
            ->with('status', 'profile-updated');
    }

    public function showVerificationNotice()
    {
        return view('auth.verify-email');
    }
}
