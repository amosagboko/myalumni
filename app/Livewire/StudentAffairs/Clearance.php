<?php

namespace App\Livewire\StudentAffairs;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Alumni;
use App\Models\ClearanceLog;
use App\Models\OnboardingSetting;
use App\Services\Alumni\ClearancePaymentInspectionService;
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
    public $clearanceStatus = '';
    public $perPage = 20;
    public $selectedAlumni = [];
    public ?int $paymentAlumniId = null;
    public ?array $paymentInspection = null;

    protected $paginationTheme = 'bootstrap';

    protected $queryString = [
        'search' => ['except' => ''],
        'faculty' => ['except' => ''],
        'department' => ['except' => ''],
        'year' => ['except' => ''],
        'clearanceStatus' => ['except' => ''],
        'perPage' => ['except' => 20],
    ];

    public function mount($faculty = null, $year = null, $department = null, $clearanceStatus = null)
    {
        if ($faculty !== null) {
            $this->faculty = $faculty;
        }
        if ($year !== null) {
            $this->year = $year;
        }
        if ($department !== null) {
            $this->department = $department;
        }
        if ($clearanceStatus !== null) {
            $this->clearanceStatus = $clearanceStatus;
        }
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFaculty() { $this->resetPage(); }
    public function updatingDepartment() { $this->resetPage(); }
    public function updatingYear() { $this->resetPage(); }
    public function updatingClearanceStatus() { $this->resetPage(); }
    public function updatingPerPage() { $this->resetPage(); }

    public function applyFilters()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->faculty = '';
        $this->department = '';
        $this->year = '';
        $this->clearanceStatus = '';
        $this->resetPage();
    }

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
        if (! $this->officeClearanceEnabled()) {
            return session()->flash('error', 'Clearance for this office is currently disabled by admin.');
        }

        if (empty($this->selectedAlumni)) {
            return session()->flash('error', 'Please select at least one alumni.');
        }

        $user = Auth::user();
        if (! $user || ! $user->hasRole('student-affairs') || ! $user->can('toggle student affairs clearance')) {
            return session()->flash('error', 'Unauthorized.');
        }

        try {
            DB::beginTransaction();
            $alumniRecords = Alumni::with('user')->whereIn('id', $this->selectedAlumni)->get();
            $successCount = 0;
            $skippedCount = 0;

            foreach ($alumniRecords as $alumni) {
                if (! $alumni->isEligibleForNyscOnboarding()) {
                    $skippedCount++;
                    continue;
                }

                ClearanceLog::recordStudentAffairs($alumni, $user, true, 'NYSC onboarding');
                $successCount++;
            }

            DB::commit();
            $this->selectedAlumni = [];
            $message = "NYSC onboarded: {$successCount} alumni";
            if ($skippedCount > 0) {
                $message .= ", {$skippedCount} skipped (Academic Affairs clearance or payments incomplete)";
            }
            session()->flash('success', $message);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Student Affairs bulk clear failed', ['error' => $e->getMessage()]);
            session()->flash('error', 'Failed to bulk clear.');
        }
    }

    public function bulkUnclear()
    {
        if (! $this->officeClearanceEnabled()) {
            return session()->flash('error', 'Clearance for this office is currently disabled by admin.');
        }

        if (empty($this->selectedAlumni)) {
            return session()->flash('error', 'Please select at least one alumni.');
        }

        $user = Auth::user();
        if (! $user || ! $user->hasRole('student-affairs') || ! $user->can('toggle student affairs clearance')) {
            return session()->flash('error', 'Unauthorized.');
        }

        try {
            DB::beginTransaction();
            $alumniRecords = Alumni::with('user')->whereIn('id', $this->selectedAlumni)->get();

            foreach ($alumniRecords as $alumni) {
                ClearanceLog::recordStudentAffairs($alumni, $user, false, 'To collect result statement');
            }

            DB::commit();
            $this->selectedAlumni = [];
            session()->flash('success', 'Bulk uncleared: ' . count($alumniRecords) . ' alumni');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Student Affairs bulk unclear failed', ['error' => $e->getMessage()]);
            session()->flash('error', 'Failed to bulk unclear.');
        }
    }

    public $message = '';
    public $messageType = '';
    public $selectedAlumniId = null;
    public $selectedAlumniValue = null;

    public function testMethod()
    {
        Log::info('TEST METHOD CALLED - Livewire is working!');
        $this->message = 'Test successful - Livewire is working!';
        $this->messageType = 'success';
    }

    public function viewPaymentDetails(int $alumniId): void
    {
        $user = Auth::user();

        if (! $user || ! $user->hasRole('student-affairs')) {
            abort(403);
        }

        $alumni = Alumni::query()->findOrFail($alumniId);
        $this->paymentAlumniId = $alumni->id;
        $this->paymentInspection = app(ClearancePaymentInspectionService::class)->snapshot($alumni);
    }

    public function closePaymentDetails(): void
    {
        $this->paymentAlumniId = null;
        $this->paymentInspection = null;
    }

    public function markCleared($alumniId)
    {
        $this->onboard((int) $alumniId);
    }

    public function onboard(int $alumniId): void
    {
        if (! $this->officeClearanceEnabled()) {
            $this->message = 'Clearance for this office is currently disabled by admin.';
            $this->messageType = 'error';

            return;
        }

        $user = Auth::user();
        if (! $user || ! $user->hasRole('student-affairs') || ! $user->can('toggle student affairs clearance')) {
            abort(403);
        }

        $alumni = Alumni::find($alumniId);
        if (! $alumni) {
            $this->message = 'Alumni not found.';
            $this->messageType = 'error';

            return;
        }

        if ($alumni->student_affairs_cleared) {
            $this->message = 'This alumnus has already been onboarded.';
            $this->messageType = 'error';

            return;
        }

        if (! $alumni->academic_affairs_cleared || ! $alumni->hasCompletedRequiredPayments()) {
            $this->message = 'Academic Affairs clearance and full payment are required before NYSC onboarding.';
            $this->messageType = 'error';

            return;
        }

        ClearanceLog::recordStudentAffairs($alumni, $user, true, 'NYSC onboarding');

        $this->message = 'NYSC onboarding completed.';
        $this->messageType = 'success';
    }

    public function markUncleared($alumniId)
    {
        Log::info('markUncleared CALLED', ['id' => $alumniId]);
        $this->selectedAlumniId = (int) $alumniId;
        $this->selectedAlumniValue = false;
        $this->toggleClearance();
    }

    public function toggleClearance()
    {
        if ($this->selectedAlumniId === null) {
            Log::info('toggleClearance CALLED without selectedAlumniId');
            return;
        }

        if (! $this->officeClearanceEnabled()) {
            $this->message = 'Clearance for this office is currently disabled by admin.';
            $this->messageType = 'error';
            $this->selectedAlumniId = null;
            $this->selectedAlumniValue = null;
            return;
        }

        Log::info('toggleClearance CALLED', ['id' => $this->selectedAlumniId, 'value' => $this->selectedAlumniValue]);
        
        $user = Auth::user();
        
        if (! $user || ! $user->hasRole('student-affairs') || ! $user->can('toggle student affairs clearance')) {
            $this->message = 'Unauthorized.';
            $this->messageType = 'error';
            $this->selectedAlumniId = null;
            $this->selectedAlumniValue = null;
            return;
        }

        if ($this->selectedAlumniValue) {
            $alumniId = (int) $this->selectedAlumniId;
            $this->selectedAlumniId = null;
            $this->selectedAlumniValue = null;
            $this->onboard($alumniId);

            return;
        }

        $alumni = Alumni::find($this->selectedAlumniId);
        if (!$alumni) {
            $this->message = 'Alumni not found.';
            $this->messageType = 'error';
            $this->selectedAlumniId = null;
            $this->selectedAlumniValue = null;
            return;
        }

        ClearanceLog::recordStudentAffairs($alumni, $user, false, 'To collect result statement');

        $this->message = 'Clearance updated successfully.';
        $this->messageType = 'success';
        $this->selectedAlumniId = null;
        $this->selectedAlumniValue = null;
    }

    protected function officeClearanceEnabled(): bool
    {
        return OnboardingSetting::isStudentAffairsClearanceEnabled();
    }

    public function clearMessage()
    {
        $this->message = '';
        $this->messageType = '';
    }

    public function getQuery()
    {
        $q = Alumni::with(['user', 'category', 'nyscOnboardingLog.actor'])->orderBy('created_at', 'desc');
        if ($this->search) {
            $q->where(function($sub){
                $sub->whereHas('user', function($uq){
                    $uq->where('name', 'like', "%{$this->search}%");
                })->orWhere('matric_number', 'like', "%{$this->search}%");
            });
        }
        if ($this->faculty) {
            $q->where('faculty', $this->faculty);
        }
        if ($this->department) {
            $q->where('department', $this->department);
        }
        if ($this->year) {
            $q->where('year_of_graduation', $this->year);
        }
        if ($this->clearanceStatus === 'cleared') {
            $q->where('student_affairs_cleared', true);
        } elseif ($this->clearanceStatus === 'pending') {
            $q->where('student_affairs_cleared', false);
        }

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
            fputcsv($handle, ['Name', 'Matric', 'Faculty', 'Department', 'Year', 'AA Status', 'Payments', 'NYSC Onboarding', 'Actor']);

            $query->chunk(500, function ($chunk) use ($handle) {
                foreach ($chunk as $a) {
                    $paid = $a->hasCompletedRequiredPayments();
                    fputcsv($handle, [
                        $a->user->name ?? 'N/A',
                        $a->matric_number ?? 'N/A',
                        $a->faculty ?? 'N/A',
                        $a->department ?? 'N/A',
                        $a->year_of_graduation ?? 'N/A',
                        $a->academic_affairs_cleared ? 'CLEARED' : 'PENDING',
                        $paid ? 'PAID' : 'UNPAID',
                        $a->student_affairs_cleared ? 'ONBOARDED' : 'PENDING',
                        $a->nyscOnboardingActorName(),
                    ]);
                }
            });

            fclose($handle);
        }, $filename, $headers);
    }

    public function render()
    {
        $officeEnabled = $this->officeClearanceEnabled();
        $alumni = $this->getQuery()->paginate($this->perPage)->withQueryString();
        $faculties = Alumni::distinct()->pluck('faculty')->filter()->sort()->values();
        $departments = Alumni::distinct()->pluck('department')->filter()->sort()->values();
        $years = Alumni::distinct()->pluck('year_of_graduation')->filter()->sort()->reverse()->values();

        return view('livewire.student-affairs.clearance', [
            'alumni' => $alumni,
            'faculties' => $faculties,
            'departments' => $departments,
            'years' => $years,
            'officeEnabled' => $officeEnabled,
            'stats' => [
                'total' => Alumni::count(),
                'pending' => Alumni::where('student_affairs_cleared', false)->count(),
                'cleared' => Alumni::where('student_affairs_cleared', true)->count(),
                'filtered' => $alumni->total(),
            ],
        ])->layout('layouts.student-affairs', ['title' => 'Student Affairs Clearance']);
    }
}
