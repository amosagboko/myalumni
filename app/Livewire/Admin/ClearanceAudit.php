<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ClearanceAuditExport;

class ClearanceAudit extends Component
{
    use WithPagination;

    public $division = '';
    public $alumniName = '';
    public $actorName = '';
    public $dateFrom = '';
    public $dateTo = '';

    protected $paginationTheme = 'bootstrap';

    public function export()
    {
        if (!Auth::user()->can('export clearance audit')) {
            session()->flash('error', 'Unauthorized to export.');
            return;
        }
        return Excel::download(new ClearanceAuditExport($this->division, $this->alumniName, $this->actorName, $this->dateFrom, $this->dateTo), 'clearance_audit.xlsx');
    }

    public function query()
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
            ->orderByDesc('clearance_logs.created_at');

        if ($this->division) { $q->where('clearance_logs.division', $this->division); }
        if ($this->alumniName) { $q->where('alumni_users.name', 'like', "%{$this->alumniName}%"); }
        if ($this->actorName) { $q->where('actors.name', 'like', "%{$this->actorName}%"); }
        if ($this->dateFrom) { $q->whereDate('clearance_logs.created_at', '>=', $this->dateFrom); }
        if ($this->dateTo) { $q->whereDate('clearance_logs.created_at', '<=', $this->dateTo); }

        return $q;
    }

    public function render()
    {
        $logs = $this->query()->paginate(20);
        return view('livewire.admin.clearance-audit', [
            'logs' => $logs,
        ])->layout('components.alumniadmin-dashboard', ['title' => 'Clearance Audit | FuLafia Alumni']);
    }
}
