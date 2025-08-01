<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OnboardingSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OnboardingSettingsController extends Controller
{
    public function index()
    {
        $setting = OnboardingSetting::getCurrent();
        
        return view('admin.onboarding-settings.index', compact('setting'));
    }

    public function close(Request $request)
    {
        $request->validate([
            'closure_reason' => 'required|string|max:500'
        ]);

        try {
            OnboardingSetting::close($request->closure_reason, Auth::id());
            
            Log::info('Onboarding closed by admin', [
                'admin_id' => Auth::id(),
                'admin_name' => Auth::user()->name,
                'reason' => $request->closure_reason
            ]);

            return redirect()->route('admin.onboarding-settings.index')
                ->with('success', 'Onboarding has been closed successfully.');

        } catch (\Exception $e) {
            Log::error('Failed to close onboarding', [
                'error' => $e->getMessage(),
                'admin_id' => Auth::id()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to close onboarding. Please try again.')
                ->withInput();
        }
    }

    public function reopen()
    {
        try {
            OnboardingSetting::reopen(Auth::id());
            
            Log::info('Onboarding reopened by admin', [
                'admin_id' => Auth::id(),
                'admin_name' => Auth::user()->name
            ]);

            return redirect()->route('admin.onboarding-settings.index')
                ->with('success', 'Onboarding has been reopened successfully.');

        } catch (\Exception $e) {
            Log::error('Failed to reopen onboarding', [
                'error' => $e->getMessage(),
                'admin_id' => Auth::id()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to reopen onboarding. Please try again.');
        }
    }
} 