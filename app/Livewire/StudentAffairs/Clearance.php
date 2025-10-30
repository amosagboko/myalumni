<?php

namespace App\Livewire\StudentAffairs;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Alumni;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Clearance extends Component
{
    use WithPagination;

    public $search = '';
    public $faculty = '';
    public $year = '';

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFaculty() { $this->resetPage(); }
    public function updatingYear() { $this->resetPage(); }

    public function toggleClearance($alumniId, $newValue, $reason = null)
    {
        $user = Auth::user();
        if (!$user || !$user->can('toggle student affairs clearance')) {
            return session()->flash('error', 'Unauthorized.');
        }

        try {
            DB::beginTransaction();
            $alumni = Alumni::with('user')->findOrFail($alumniId);

            // Enforce onboarding & payments completion (read-only flags)
            $onboardingComplete = $alumni->biodata_completed ?? true; // adjust if you have a flag
            $paymentsComplete = method_exists($alumni, 'hasCompletedRequiredPayments') ? $alumni->hasCompletedRequiredPayments() : true;
            if (!$onboardingComplete) {
                DB::rollBack();
                return session()->flash('error', 'Complete onboarding first.');
            }
            if (!$paymentsComplete) {
                DB::rollBack();
                return session()->flash('error', 'Complete required payments first.');
            }

            $old = (bool) $alumni->student_affairs_cleared;
            $alumni->student_affairs_cleared = (bool) $newValue;
            $alumni->save();

            // Audit log
            DB::table('clearance_logs')->insert([
                'alumni_id' => $alumni->id,
                'division' => 'student_affairs',
                'actor_user_id' => $user->id,
                'actor_role' => $user->getRoleNames()->first(),
                'old_value' => $old,
                'new_value' => (bool) $newValue,
                'reason' => $reason,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();
            session()->flash('success', 'Clearance updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Student Affairs clearance toggle failed', ['error' => $e->getMessage()]);
            session()->flash('error', 'Failed to update clearance.');
        }
    }

    public function getQuery()
    {
        $q = Alumni::with(['user', 'category'])->orderBy('created_at', 'desc');
        if ($this->search) {
            $q->where(function($sub){
                $sub->whereHas('user', function($uq){
                    $uq->where('name', 'like', "%{$this->search}%");
                })->orWhere('matric_number', 'like', "%{$this->search}%");
            });
        }
        if ($this->faculty) { $q->where('faculty', $this->faculty); }
        if ($this->year) { $q->where('year_of_graduation', $this->year); }
        return $q;
    }

    public function render()
    {
        $alumni = $this->getQuery()->paginate(20);
        $faculties = Alumni::distinct()->pluck('faculty')->filter()->sort()->values();
        $years = Alumni::distinct()->pluck('year_of_graduation')->filter()->sort()->reverse()->values();

        return view('livewire.student-affairs.clearance', [
            'alumni' => $alumni,
            'faculties' => $faculties,
            'years' => $years,
        ]);
    }
}
