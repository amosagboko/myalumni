<?php

namespace App\Http\Controllers;

use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AlumniOnboardingController extends Controller
{
    /**
     * Check if onboarding is currently allowed based on admin settings
     */
    private function isOnboardingAllowed()
    {
        $isEnabled = \App\Models\OnboardingSetting::isEnabled();
        
        // Log for debugging
        \Illuminate\Support\Facades\Log::info('Onboarding status check', [
            'current_time' => now()->format('Y-m-d H:i:s'),
            'is_enabled' => $isEnabled
        ]);
        
        return $isEnabled;
    }

    public function showOnboarding()
    {
        try {
            // Check if onboarding deadline has passed
            if (!$this->isOnboardingAllowed()) {
                return redirect()->route('home')
                    ->with('error', 'Onboarding has ended for all alumni categories. The registration period has closed. Thank you.');
            }

            $user = Auth::user();
            
            // Check if user is an alumni
            if (!$user->hasRole('alumni') && !$user->alumni) {
                return RouteServiceProvider::redirectToHome();
            }

            // Check if password needs to be updated (first login)
            if ($user->is_first_login) {
                return view('alumni.onboarding.password-update');
            }

            // If email is not verified, show verification notice
            if (!$user->email_verified_at) {
                return view('alumni.onboarding.email-verification');
            }

            // Check if bio data needs to be completed
            if (!$user->alumni || !$user->alumni->contact_address || !$user->alumni->phone_number || !$user->alumni->qualification_type) {
                return redirect()->route('alumni.bio-data')
                    ->with('warning', 'Please complete your bio data to continue.');
            }

            // Check if payments need to be completed
            $alumni = $user->alumni;
            $hasUnpaidFees = $alumni->getActiveFees()->contains(function ($fee) {
                return !$fee->isPaid();
            });

            if ($hasUnpaidFees) {
                return redirect()->route('alumni.payments.index')
                    ->with('warning', 'Please complete your payments to continue.');
            }

            // All checks passed, redirect to alumni home
            return redirect()->route('alumni.home');
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in AlumniOnboardingController@showOnboarding', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);
            
            return redirect()->route('alumni.home')
                ->with('error', 'There was an issue with the onboarding process. Please try again.');
        }
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
        // Check if onboarding deadline has passed
        if (!$this->isOnboardingAllowed()) {
            return redirect()->route('home')
                ->with('error', 'Onboarding has ended for all alumni categories. The registration period has closed. Thank you.');
        }

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
        // Check if onboarding deadline has passed
        if (!$this->isOnboardingAllowed()) {
            return redirect()->route('home')
                ->with('error', 'Onboarding has ended for all alumni categories. The registration period has closed. Thank you.');
        }

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
        // Check if onboarding deadline has passed
        if (!$this->isOnboardingAllowed()) {
            return redirect()->route('home')
                ->with('error', 'Onboarding has ended for all alumni categories. The registration period has closed. Thank you.');
        }

        return view('auth.verify-email');
    }
}
