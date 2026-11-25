<?php

namespace App\Livewire\StudentAffairs;

use App\Exports\NyscOnboardingExport;
use App\Models\Alumni;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class NyscOnboarding extends Component
{
    use WithPagination;

    public string $year = '';

    public string $faculty = '';

    public string $department = '';

    public string $programme = '';

    public int $perPage = 20;

    public ?string $exportMessage = null;

    protected $paginationTheme = 'bootstrap';

    protected $queryString = [
        'year' => ['except' => ''],
        'faculty' => ['except' => ''],
        'department' => ['except' => ''],
        'programme' => ['except' => ''],
    ];

    public function updatingYear(): void
    {
        $this->resetPage();
    }

    public function updatingFaculty(): void
    {
        $this->department = '';
        $this->programme = '';
        $this->resetPage();
    }

    public function updatingDepartment(): void
    {
        $this->programme = '';
        $this->resetPage();
    }

    public function updatingProgramme(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->year = '';
        $this->faculty = '';
        $this->department = '';
        $this->programme = '';
        $this->exportMessage = null;
        $this->resetPage();
    }

    public function exportExcel()
    {
        $this->exportMessage = null;

        if (! $this->filteredQuery()->exists()) {
            $this->exportMessage = 'No records match the current filters. Adjust your filters and try again.';

            return null;
        }

        $generatedAt = now();
        $filename = 'nysc-onboarding-'.$generatedAt->format('Ymd-His').'.xlsx';

        return Excel::download(
            new NyscOnboardingExport(
                $this->filteredQuery(),
                $this->exportFilterCriteria(),
                $generatedAt
            ),
            $filename
        );
    }

    public function render()
    {
        return view('livewire.student-affairs.nysc-onboarding', [
            'alumni' => $this->filteredQuery()->paginate($this->perPage)->withQueryString(),
            'years' => $this->optionValues('year_of_graduation'),
            'faculties' => $this->optionValues('faculty'),
            'departments' => $this->optionValues('department', faculty: true),
            'programmes' => $this->optionValues('programme', faculty: true, department: true),
        ])->layout('layouts.student-affairs', ['title' => 'NYSC Onboarding']);
    }

    protected function filteredQuery(): Builder
    {
        return $this->onboardedQuery()
            ->with('user')
            ->when($this->year !== '', fn (Builder $query) => $query->where('year_of_graduation', $this->year))
            ->when($this->faculty !== '', fn (Builder $query) => $query->where('faculty', $this->faculty))
            ->when($this->department !== '', fn (Builder $query) => $query->where('department', $this->department))
            ->when($this->programme !== '', fn (Builder $query) => $query->where('programme', $this->programme))
            ->orderByDesc('year_of_graduation')
            ->orderBy('matric_number');
    }

    protected function onboardedQuery(): Builder
    {
        return Alumni::query()->where('student_affairs_cleared', true);
    }

    /**
     * @return \Illuminate\Support\Collection<int, mixed>
     */
    protected function optionValues(string $column, bool $faculty = false, bool $department = false)
    {
        return $this->onboardedQuery()
            ->when($faculty && $this->faculty !== '', fn (Builder $query) => $query->where('faculty', $this->faculty))
            ->when($department && $this->department !== '', fn (Builder $query) => $query->where('department', $this->department))
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->distinct()
            ->orderBy($column, $column === 'year_of_graduation' ? 'desc' : 'asc')
            ->pluck($column)
            ->filter()
            ->values();
    }

    /**
     * @return array<string, string>
     */
    protected function exportFilterCriteria(): array
    {
        return [
            'Year' => $this->year !== '' ? $this->year : 'All years',
            'Faculty' => $this->faculty !== '' ? $this->faculty : 'All faculties',
            'Department' => $this->department !== '' ? $this->department : 'All departments',
            'Programme' => $this->programme !== '' ? $this->programme : 'All programmes',
        ];
    }
}
