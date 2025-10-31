<?php

namespace App\Livewire\StudentAffairs;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Audit extends Component
{
    use WithPagination;

    public $alumniName = '';
    public $actorName = '';
    public $dateFrom = '';
    public $dateTo = '';

    protected $paginationTheme = 'bootstrap';

    protected $queryString = [
        'alumniName' => ['except' => ''],
        'actorName' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
    ];

    public function updatingAlumniName() { $this->resetPage(); }
    public function updatingActorName() { $this->resetPage(); }
    public function updatingDateFrom() { $this->resetPage(); }
    public function updatingDateTo() { $this->resetPage(); }

    public function export(): StreamedResponse
    {
        $filename = 'student_affairs_audit_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $query = $this->getQuery();

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Timestamp', 'Who (User + Role)', 'Alumni', 'Division', 'Old → New', 'Reason']);

            $query->chunk(500, function ($chunk) use ($handle) {
                foreach ($chunk as $log) {
                    $oldNew = ($log->old_value ? 'Cleared' : 'Not Cleared') . ' → ' . ($log->new_value ? 'Cleared' : 'Not Cleared');
                    fputcsv($handle, [
                        $log->created_at,
                        $log->actor_name . ' (' . $log->actor_role . ')',
                        $log->alumni_name . ' (' . $log->matric_number . ')',
                        ucfirst(str_replace('_', ' ', $log->division)),
                        $oldNew,
                        $log->reason ?? 'N/A',
                    ]);
                }
            });

            fclose($handle);
        }, $filename, $headers);
    }

    protected function getQuery()
    {
        $q = DB::table('clearance_logs')
            ->join('alumni', 'clearance_logs.alumni_id', '=', 'alumni.id')
            ->join('users as alumni_users', 'alumni.user_id', '=', 'alumni_users.id')
            ->join('users as actors', 'clearance_logs.actor_user_id', '=', 'actors.id')
            ->select(
                'clearance_logs.*',
                'alumni.matric_number',
                'alumni_users.name as alumni_name',
                'actors.name as actor_name'
            )
            ->where('clearance_logs.division', 'student_affairs')
            ->orderByDesc('clearance_logs.created_at');

        if ($this->alumniName) { 
            $q->where('alumni_users.name', 'like', "%{$this->alumniName}%"); 
        }
        if ($this->actorName) { 
            $q->where('actors.name', 'like', "%{$this->actorName}%"); 
        }
        if ($this->dateFrom) { 
            $q->whereDate('clearance_logs.created_at', '>=', $this->dateFrom); 
        }
        if ($this->dateTo) { 
            $q->whereDate('clearance_logs.created_at', '<=', $this->dateTo); 
        }

        return $q;
    }

    public function render()
    {
        $logs = $this->getQuery()->paginate(20);
        return view('livewire.student-affairs.audit', [
            'logs' => $logs,
        ])->layout('layouts.student-affairs', ['title' => 'Student Affairs Audit']);
    }
}

