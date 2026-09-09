<?php

namespace App\Livewire\Concerns;

use App\Models\Alumni;
use App\Models\OnboardingSetting;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;

trait HasClearanceReport
{
    use WithPagination;

    public string $search = '';

    public string $year = '';

    public string $status = '';

    public string $actorName = '';

    public string $sortField = 'when';

    public string $sortDirection = 'desc';

    public int $perPage = 20;

    public string $paginationTheme = 'bootstrap';

    protected $queryString = [
        'search' => ['except' => ''],
        'year' => ['except' => ''],
        'status' => ['except' => ''],
        'actorName' => ['except' => ''],
        'sortField' => ['except' => 'when'],
        'sortDirection' => ['except' => 'desc'],
    ];

    abstract protected function division(): string;

    abstract protected function clearedColumn(): string;

    abstract protected function layoutName(): string;

    abstract protected function pageTitle(): string;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingYear(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingActorName(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = in_array($field, ['alumni', 'matric'], true) ? 'asc' : 'desc';
        }

        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->year = '';
        $this->status = '';
        $this->actorName = '';
        $this->sortField = 'when';
        $this->sortDirection = 'desc';
        $this->resetPage();
    }

    protected function getQuery()
    {
        $division = $this->division();
        $clearedColumn = $this->clearedColumn();

        $latestLogSub = DB::table('clearance_logs as cl1')
            ->select('cl1.alumni_id', DB::raw('MAX(cl1.id) as latest_id'))
            ->where('cl1.division', $division)
            ->groupBy('cl1.alumni_id');

        $q = Alumni::query()
            ->join('users as alumni_users', 'alumni.user_id', '=', 'alumni_users.id')
            ->leftJoinSub($latestLogSub, 'latest_logs', function ($join) {
                $join->on('alumni.id', '=', 'latest_logs.alumni_id');
            })
            ->leftJoin('clearance_logs as logs', 'logs.id', '=', 'latest_logs.latest_id')
            ->leftJoin('users as actors', 'logs.actor_user_id', '=', 'actors.id')
            ->select([
                'alumni.id',
                'alumni.matric_number',
                'alumni.year_of_graduation',
                "alumni.{$clearedColumn} as is_cleared",
                'alumni_users.name as alumni_name',
                'logs.created_at as cleared_at',
                'actors.name as actor_name',
            ]);

        if ($this->search !== '') {
            $term = '%'.$this->search.'%';
            $q->where(function ($sub) use ($term) {
                $sub->where('alumni_users.name', 'like', $term)
                    ->orWhere('alumni.matric_number', 'like', $term);
            });
        }

        if ($this->year !== '') {
            $q->where('alumni.year_of_graduation', $this->year);
        }

        if ($this->status === 'cleared') {
            $q->where("alumni.{$clearedColumn}", true);
        } elseif ($this->status === 'not_cleared') {
            $q->where("alumni.{$clearedColumn}", false);
        }

        if ($this->actorName !== '') {
            $q->where('actors.name', 'like', '%'.$this->actorName.'%');
        }

        $direction = $this->sortDirection === 'asc' ? 'asc' : 'desc';

        return match ($this->sortField) {
            'alumni' => $q->orderBy('alumni_users.name', $direction),
            'matric' => $q->orderBy('alumni.matric_number', $direction),
            'status' => $q->orderBy("alumni.{$clearedColumn}", $direction),
            'actor' => $q->orderBy('actors.name', $direction),
            'year' => $q->orderBy('alumni.year_of_graduation', $direction),
            default => $q->orderByRaw('logs.created_at IS NULL')
                ->orderBy('logs.created_at', $direction)
                ->orderBy('alumni_users.name'),
        };
    }

    public function render()
    {
        $rows = $this->getQuery()->paginate($this->perPage)->withQueryString();
        $years = Alumni::query()
            ->distinct()
            ->pluck('year_of_graduation')
            ->filter()
            ->sort()
            ->reverse()
            ->values();

        $officeEnabled = OnboardingSetting::isDivisionClearanceEnabled($this->division());
        $yearStats = $this->yearStatistics();

        $chartData = $yearStats->map(fn ($stat) => [
            'year' => (int) $stat->year,
            'cleared' => (int) $stat->cleared,
            'notCleared' => (int) $stat->not_cleared,
        ])->values()->all();

        return view('livewire.division.clearance-report', [
            'rows' => $rows,
            'years' => $years,
            'chartData' => $chartData,
            'officeEnabled' => $officeEnabled,
            'fixedReason' => 'To collect result statement',
            'pageTitle' => $this->pageTitle(),
        ])->layout($this->layoutName(), ['title' => $this->pageTitle()]);
    }

    /**
     * Cleared / not-cleared totals for each graduation year (newest first).
     */
    protected function yearStatistics()
    {
        $clearedColumn = $this->clearedColumn();

        return Alumni::query()
            ->selectRaw("
                year_of_graduation as year,
                COUNT(*) as total,
                SUM(CASE WHEN {$clearedColumn} = 1 THEN 1 ELSE 0 END) as cleared,
                SUM(CASE WHEN {$clearedColumn} = 0 THEN 1 ELSE 0 END) as not_cleared
            ")
            ->whereNotNull('year_of_graduation')
            ->groupBy('year_of_graduation')
            ->orderByDesc('year_of_graduation')
            ->get();
    }
}
