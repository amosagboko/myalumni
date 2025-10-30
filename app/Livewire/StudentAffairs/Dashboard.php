<?php

namespace App\Livewire\StudentAffairs;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Models\Alumni;

class Dashboard extends Component
{
    public function render()
    {
        $today = now()->startOfDay();
        
        $pendingClearances = Alumni::where('student_affairs_cleared', false)->count();
        $clearedToday = Alumni::where('student_affairs_cleared', true)
            ->whereDate('updated_at', '>=', $today)
            ->count();
        $clearedThisWeek = Alumni::where('student_affairs_cleared', true)
            ->whereDate('updated_at', '>=', now()->startOfWeek())
            ->count();
        $overallCleared = Alumni::where('student_affairs_cleared', true)->count();

        $recentActivity = DB::table('clearance_logs')
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
            ->orderByDesc('clearance_logs.created_at')
            ->limit(10)
            ->get();

        return view('livewire.student-affairs.dashboard', [
            'kpis' => [
                'pending' => $pendingClearances,
                'today' => $clearedToday,
                'week' => $clearedThisWeek,
                'overall' => $overallCleared,
            ],
            'recentActivity' => $recentActivity,
        ]);
    }
}
