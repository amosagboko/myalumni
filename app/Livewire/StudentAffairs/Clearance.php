<?php

namespace App\Livewire\StudentAffairs;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Alumni;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Clearance extends Component
{
    use WithPagination;

    public $search = '';
    public $faculty = '';
    public $year = '';

    protected $paginationTheme = 'bootstrap';

    protected $queryString = [
        'search' => ['except' => ''],
        'faculty' => ['except' => ''],
        'year' => ['except' => ''],
    ];

    public function mount($faculty = null, $year = null)
    {
        if ($faculty !== null) { $this->faculty = $faculty; }
        if ($year !== null) { $this->year = $year; }
    }

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

            $onboardingComplete = $alumni->biodata_completed ?? true;
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

    public function export(): StreamedResponse
    {
        $filename = 'student_affairs_clearance_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $query = $this->getQuery();

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Name', 'Matric', 'Faculty', 'Year', 'Onboarding', 'Payments', 'Student Affairs']);

            $query->chunk(500, function ($chunk) use ($handle) {
                foreach ($chunk as $a) {
                    $onboard = $a->biodata_completed ?? true;
                    $paid = method_exists($a, 'hasCompletedRequiredPayments') ? $a->hasCompletedRequiredPayments() : true;
                    fputcsv($handle, [
                        $a->user->name ?? 'N/A',
                        $a->matric_number ?? 'N/A',
                        $a->faculty ?? 'N/A',
                        $a->year_of_graduation ?? 'N/A',
                        $onboard ? 'YES' : 'NO',
                        $paid ? 'YES' : 'NO',
                        $a->student_affairs_cleared ? 'CLEARED' : 'NOT CLEARED',
                    ]);
                }
            });

            fclose($handle);
        }, $filename, $headers);
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
