<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ClearanceAuditExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        protected ?string $division,
        protected ?string $alumniName,
        protected ?string $actorName,
        protected ?string $dateFrom,
        protected ?string $dateTo
    ) {}

    public function collection()
    {
        $q = DB::table('clearance_logs')
            ->join('alumni', 'clearance_logs.alumni_id', '=', 'alumni.id')
            ->join('users as alumni_users', 'alumni.user_id', '=', 'alumni_users.id')
            ->join('users as actors', 'clearance_logs.actor_user_id', '=', 'actors.id')
            ->select(
                'clearance_logs.created_at',
                'alumni_users.name as alumni_name',
                'alumni.matric_number',
                'clearance_logs.division',
                'clearance_logs.old_value',
                'clearance_logs.new_value',
                'actors.name as actor_name',
                'clearance_logs.actor_role',
                'clearance_logs.reason'
            )
            ->orderByDesc('clearance_logs.created_at');

        if ($this->division) { $q->where('clearance_logs.division', $this->division); }
        if ($this->alumniName) { $q->where('alumni_users.name', 'like', "%{$this->alumniName}%"); }
        if ($this->actorName) { $q->where('actors.name', 'like', "%{$this->actorName}%"); }
        if ($this->dateFrom) { $q->whereDate('clearance_logs.created_at', '>=', $this->dateFrom); }
        if ($this->dateTo) { $q->whereDate('clearance_logs.created_at', '<=', $this->dateTo); }

        return $q->get();
    }

    public function headings(): array
    {
        return [
            'Timestamp', 'Alumni Name', 'Matric No', 'Division', 'Old', 'New', 'Actor', 'Role', 'Reason'
        ];
    }

    public function map($row): array
    {
        return [
            (string) $row->created_at,
            $row->alumni_name,
            $row->matric_number,
            $row->division === 'student_affairs' ? 'Student Affairs' : 'Academic Affairs',
            $row->old_value ? 'Cleared' : 'Not Cleared',
            $row->new_value ? 'Cleared' : 'Not Cleared',
            $row->actor_name,
            $row->actor_role,
            $row->reason,
        ];
    }
}
