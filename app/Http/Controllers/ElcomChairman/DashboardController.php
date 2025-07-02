<?php

namespace App\Http\Controllers\ElcomChairman;

use App\Http\Controllers\Controller;
use App\Models\Election;
use App\Models\Candidate;
use App\Models\AccreditedVoter;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Active Elections: Elections that are in progress (not draft or completed)
        $activeElections = Election::whereIn('status', ['accreditation', 'voting'])
            ->where('is_active', true)
            ->count();
        
        // Total Candidates: Count all candidates across all elections
        $totalCandidates = Candidate::count();
        
        // Total Votes Cast: Count accredited voters who have voted
        $totalVotes = AccreditedVoter::where('has_voted', true)->count();
        
        // Recent Elections: Get latest elections for display
        $recentElections = Election::latest()->take(5)->get();
        
        // Additional useful statistics
        $totalElections = Election::count();
        $completedElections = Election::where('status', 'completed')->count();
        $pendingElections = Election::where('status', 'draft')->count();
        $totalAccreditedVoters = AccreditedVoter::count();

        return view('elcom-chairman.dashboard', compact(
            'activeElections',
            'totalCandidates',
            'totalVotes',
            'recentElections',
            'totalElections',
            'completedElections',
            'pendingElections',
            'totalAccreditedVoters'
        ));
    }
} 