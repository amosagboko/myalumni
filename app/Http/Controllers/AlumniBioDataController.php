<?php

namespace App\Http\Controllers;

use App\Models\Alumni;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AlumniBioDataController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        
        // If user is an admin, redirect to admin dashboard
        if ($user->hasRole('administrator')) {
            return redirect()->route('admin.dashboard');
        }

        $alumni = $user->alumni;

        // If alumni record doesn't exist, redirect to admin for proper setup
        if (!$alumni) {
            return redirect()->route('upload.alumni')
                ->with('error', 'Your alumni record needs to be set up by an administrator first.');
        }

        $titles = ['Prof', 'Dr', 'Mr', 'Mrs', 'Miss', 'Alh', 'Hajj', 'Chief', 'Mal'];
        $qualificationTypes = ['Degree', 'Diploma', 'Certificate'];
        
        return view('alumni.bio-data', compact('alumni', 'titles', 'qualificationTypes'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        
        // If user is an admin, redirect to admin dashboard
        if ($user->hasRole('administrator')) {
            return redirect()->route('admin.dashboard');
        }

        $request->validate([
            'title' => 'required|string|in:Prof,Dr,Mr,Mrs,Miss,Alh,Hajj,Chief,Mal',
            'nationality' => 'required|string|max:255',
            'contact_address' => 'required|string',
            'phone_number' => 'required|string|max:20',
            'qualification_type' => 'required|string|in:Degree,Diploma,Certificate',
            'qualification_details' => 'required|string',
            'present_employer' => 'required|string|max:255',
            'present_designation' => 'required|string|max:255',
            'professional_bodies' => 'nullable|string',
            'student_responsibilities' => 'nullable|string',
            'hobbies' => 'nullable|string',
            'other_information' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $alumni = $user->alumni;

            if (!$alumni) {
                Log::error('Alumni record not found for user: ' . $user->id);
                return redirect()->route('upload.alumni')
                    ->with('error', 'Your alumni record needs to be set up by an administrator first.');
            }

            // Log the incoming data
            Log::info('Updating bio data for alumni: ' . $alumni->id, [
                'data' => $request->except(['_token', '_method']),
                'alumni_id' => $alumni->id,
                'user_id' => $user->id,
                'fillable_fields' => $alumni->getFillable()
            ]);

            // Update the alumni record
            $updated = $alumni->update($request->except(['_token', '_method']));

            if (!$updated) {
                Log::error('Failed to update alumni record', [
                    'alumni_id' => $alumni->id,
                    'data' => $request->except(['_token', '_method'])
                ]);
                throw new \Exception('Failed to update alumni record');
            }

            DB::commit();

            Log::info('Bio data updated successfully for alumni: ' . $alumni->id);

            return redirect()->route('alumni.payments.index')
                ->with('success', 'Bio data updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Log the error
            Log::error('Failed to update bio data for alumni: ' . ($alumni->id ?? 'unknown'), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to update bio data. Please try again.')
                ->withInput();
        }
    }
}
