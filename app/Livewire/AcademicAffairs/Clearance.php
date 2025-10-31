<?php

namespace App\Livewire\AcademicAffairs;

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
    public $department = '';
    public $year = '';
    public $perPage = 20;
    public $selectedAlumni = [];

    protected $paginationTheme = 'bootstrap';

    protected $queryString = [
        'search' => ['except' => ''],
        'faculty' => ['except' => ''],
        'department' => ['except' => ''],
        'year' => ['except' => ''],
        'perPage' => ['except' => 20],
    ];

    public function mount($faculty = null, $year = null, $department = null)
    {
        if ($faculty !== null) { $this->faculty = $faculty; }
        if ($year !== null) { $this->year = $year; }
        if ($department !== null) { $this->department = $department; }
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFaculty() { $this->resetPage(); }
    public function updatingDepartment() { $this->resetPage(); }
    public function updatingYear() { $this->resetPage(); }
    public function updatingPerPage() { $this->resetPage(); }

    public function toggleSelectAll()
    {
        $currentPageAlumni = $this->getQuery()->paginate($this->perPage)->pluck('id')->toArray();
        $allSelected = !empty($currentPageAlumni) && count(array_intersect($this->selectedAlumni, $currentPageAlumni)) === count($currentPageAlumni);
        
        if ($allSelected) {
            $this->selectedAlumni = array_diff($this->selectedAlumni, $currentPageAlumni);
        } else {
            $this->selectedAlumni = array_unique(array_merge($this->selectedAlumni, $currentPageAlumni));
        }
    }

    public function bulkClear()
    {
        if (empty($this->selectedAlumni)) {
            return session()->flash('error', 'Please select at least one alumni.');
        }

        $user = Auth::user();
        if (!$user || !$user->can('toggle academic affairs clearance')) {
            return session()->flash('error', 'Unauthorized.');
        }

        try {
            DB::beginTransaction();
            $alumniRecords = Alumni::with('user')->whereIn('id', $this->selectedAlumni)->get();
            $successCount = 0;
            $skippedCount = 0;

            foreach ($alumniRecords as $alumni) {
                $onboardingComplete = $alumni->biodata_completed ?? true;
                $paymentsComplete = method_exists($alumni, 'hasCompletedRequiredPayments') ? $alumni->hasCompletedRequiredPayments() : true;
                
                if (!$onboardingComplete || !$paymentsComplete) {
                    $skippedCount++;
                    continue;
                }

                $old = (bool) $alumni->academic_affairs_cleared;
                $alumni->academic_affairs_cleared = true;
                $alumni->save();

                DB::table('clearance_logs')->insert([
                    'alumni_id' => $alumni->id,
                    'division' => 'academic_affairs',
                    'actor_user_id' => $user->id,
                    'actor_role' => $user->getRoleNames()->first(),
                    'old_value' => $old,
                    'new_value' => true,
                    'reason' => 'Bulk cleared',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $successCount++;
            }

            DB::commit();
            $this->selectedAlumni = [];
            $message = "Bulk cleared: {$successCount} alumni";
            if ($skippedCount > 0) {
                $message .= ", {$skippedCount} skipped (onboarding/payments incomplete)";
            }
            session()->flash('success', $message);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Academic Affairs bulk clear failed', ['error' => $e->getMessage()]);
            session()->flash('error', 'Failed to bulk clear.');
        }
    }

    public function bulkUnclear()
    {
        if (empty($this->selectedAlumni)) {
            return session()->flash('error', 'Please select at least one alumni.');
        }

        $user = Auth::user();
        if (!$user || !$user->can('toggle academic affairs clearance')) {
            return session()->flash('error', 'Unauthorized.');
        }

        try {
            DB::beginTransaction();
            $alumniRecords = Alumni::with('user')->whereIn('id', $this->selectedAlumni)->get();

            foreach ($alumniRecords as $alumni) {
                $old = (bool) $alumni->academic_affairs_cleared;
                $alumni->academic_affairs_cleared = false;
                $alumni->save();

                DB::table('clearance_logs')->insert([
                    'alumni_id' => $alumni->id,
                    'division' => 'academic_affairs',
                    'actor_user_id' => $user->id,
                    'actor_role' => $user->getRoleNames()->first(),
                    'old_value' => $old,
                    'new_value' => false,
                    'reason' => 'Bulk uncleared',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
            $this->selectedAlumni = [];
            session()->flash('success', 'Bulk uncleared: ' . count($alumniRecords) . ' alumni');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Academic Affairs bulk unclear failed', ['error' => $e->getMessage()]);
            session()->flash('error', 'Failed to bulk unclear.');
        }
    }

    public function toggleClearance($alumniId, $newValue, $reason = null)
    {
        $user = Auth::user();
        if (!$user || !$user->can('toggle academic affairs clearance')) {
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

            $old = (bool) $alumni->academic_affairs_cleared;
            $alumni->academic_affairs_cleared = (bool) $newValue;
            $alumni->save();

            DB::table('clearance_logs')->insert([
                'alumni_id' => $alumni->id,
                'division' => 'academic_affairs',
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
            Log::error('Academic Affairs clearance toggle failed', ['error' => $e->getMessage()]);
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
        if ($this->department) { $q->where('department', $this->department); }
        if ($this->year) { $q->where('year_of_graduation', $this->year); }
        return $q;
    }

    public function export(): StreamedResponse
    {
        $filename = 'academic_affairs_clearance_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $query = $this->getQuery();

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Name', 'Matric', 'Faculty', 'Department', 'Year', 'Onboarding', 'Payments', 'Academic Affairs']);

            $query->chunk(500, function ($chunk) use ($handle) {
                foreach ($chunk as $a) {
                    $onboard = $a->biodata_completed ?? true;
                    $paid = method_exists($a, 'hasCompletedRequiredPayments') ? $a->hasCompletedRequiredPayments() : true;
                    fputcsv($handle, [
                        $a->user->name ?? 'N/A',
                        $a->matric_number ?? 'N/A',
                        $a->faculty ?? 'N/A',
                        $a->department ?? 'N/A',
                        $a->year_of_graduation ?? 'N/A',
                        $onboard ? 'YES' : 'NO',
                        $paid ? 'YES' : 'NO',
                        $a->academic_affairs_cleared ? 'CLEARED' : 'NOT CLEARED',
                    ]);
                }
            });

            fclose($handle);
        }, $filename, $headers);
    }

    public function render()
    {
        $alumni = $this->getQuery()->paginate($this->perPage);
        $faculties = Alumni::distinct()->pluck('faculty')->filter()->sort()->values();
        $departments = Alumni::distinct()->pluck('department')->filter()->sort()->values();
        $years = Alumni::distinct()->pluck('year_of_graduation')->filter()->sort()->reverse()->values();

        return view('livewire.academic-affairs.clearance', [
            'alumni' => $alumni,
            'faculties' => $faculties,
            'departments' => $departments,
            'years' => $years,
        ]);
    }
}
