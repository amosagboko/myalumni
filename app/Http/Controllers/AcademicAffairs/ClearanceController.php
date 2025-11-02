<?php

namespace App\Http\Controllers\AcademicAffairs;

use App\Http\Controllers\Controller;
use App\Models\Alumni;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClearanceController extends Controller
{
    public function toggle(Request $request, $alumniId)
    {
        $user = Auth::user();
        
        if (!$user || !$user->can('toggle academic affairs clearance')) {
            return redirect()->route('academic-affairs.clearance')
                ->with('error', 'Unauthorized.');
        }

        $alumni = Alumni::findOrFail($alumniId);
        
        $onboard = $alumni->biodata_completed ?? true;
        $paid = method_exists($alumni, 'hasCompletedRequiredPayments') ? $alumni->hasCompletedRequiredPayments() : true;
        
        if (!$onboard || !$paid) {
            return redirect()->route('academic-affairs.clearance')
                ->with('error', 'Alumni must complete onboarding and payments first.');
        }

        $newValue = $request->input('value', '1') === '1' || $request->input('value', '1') === 1;
        $old = (bool) $alumni->academic_affairs_cleared;
        
        $alumni->academic_affairs_cleared = $newValue;
        $alumni->save();

        DB::table('clearance_logs')->insert([
            'alumni_id' => $alumni->id,
            'division' => 'academic_affairs',
            'actor_user_id' => $user->id,
            'actor_role' => $user->getRoleNames()->first() ?? 'academic-affairs',
            'old_value' => $old,
            'new_value' => $newValue,
            'reason' => 'Manual toggle',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $message = $newValue ? 'Alumni marked as cleared.' : 'Alumni marked as uncleared.';
        return redirect()->route('academic-affairs.clearance')
            ->with('success', $message);
    }
}

