<?php

namespace App\Http\Controllers\StudentAffairs;

use App\Http\Controllers\Controller;
use App\Models\Alumni;
use App\Models\ClearanceLog;
use App\Models\OnboardingSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClearanceController extends Controller
{
    public function toggle(Request $request, $alumniId)
    {
        $user = Auth::user();

        if (! $user || ! $user->hasRole('student-affairs') || ! $user->can('toggle student affairs clearance')) {
            return redirect()->route('student-affairs.clearance')
                ->with('error', 'Unauthorized.');
        }

        if (! OnboardingSetting::isStudentAffairsClearanceEnabled()) {
            return redirect()->route('student-affairs.clearance')
                ->with('error', 'Clearance for this office is currently disabled by admin.');
        }

        $alumni = Alumni::findOrFail($alumniId);
        $newValue = $request->input('value', '1') === '1' || $request->input('value', '1') === 1;

        if ($newValue) {
            if ($alumni->student_affairs_cleared) {
                return redirect()->route('student-affairs.clearance')
                    ->with('error', 'This alumnus has already been onboarded.');
            }

            if (! $alumni->academic_affairs_cleared || ! $alumni->hasCompletedRequiredPayments()) {
                return redirect()->route('student-affairs.clearance')
                    ->with('error', 'Academic Affairs clearance and full payment are required before NYSC onboarding.');
            }

            ClearanceLog::recordStudentAffairs($alumni, $user, true, 'NYSC onboarding');

            return redirect()->route('student-affairs.clearance')
                ->with('success', 'NYSC onboarding completed.');
        }

        ClearanceLog::recordStudentAffairs($alumni, $user, false, 'To collect result statement');

        return redirect()->route('student-affairs.clearance')
            ->with('success', 'Alumni marked as uncleared.');
    }
}
