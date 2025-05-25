<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\Election;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the agent's dashboard with election statistics and candidate information.
     */
    public function index()
    {
        // Get active elections where the agent has candidates
        $activeElections = Election::whereHas('candidates', function ($query) {
            $query->where('approved_agent_id', Auth::id());
        })
        ->whereIn('status', ['draft', 'accreditation', 'voting'])
        ->with(['candidates' => function ($query) {
            $query->where('approved_agent_id', Auth::id())
                  ->with(['office', 'alumni.user']);
        }])
        ->latest()
        ->get();

        // Get statistics for the agent's candidates
        $candidateStats = [
            'total' => Candidate::where('approved_agent_id', Auth::id())->count(),
            'pending' => Candidate::where('approved_agent_id', Auth::id())
                                ->where('status', 'pending')
                                ->count(),
            'approved' => Candidate::where('approved_agent_id', Auth::id())
                                 ->where('status', 'approved')
                                 ->count(),
            'rejected' => Candidate::where('approved_agent_id', Auth::id())
                                 ->where('status', 'rejected')
                                 ->count(),
            'unpaid' => Candidate::where('approved_agent_id', Auth::id())
                               ->where('has_paid_screening_fee', false)
                               ->count(),
        ];

        // Get recent activities (last 5 candidates with status changes)
        $recentActivities = Candidate::where('approved_agent_id', Auth::id())
            ->with(['election', 'office', 'alumni.user'])
            ->whereNotNull('screened_at')
            ->latest('screened_at')
            ->take(5)
            ->get();

        return view('agent.dashboard', compact(
            'activeElections',
            'candidateStats',
            'recentActivities'
        ));
    }

    /**
     * Display election results for the agent's candidates.
     */
    public function electionResults(Election $election)
    {
        // Ensure the election has ended
        if ($election->status !== 'completed') {
            return back()->with('error', 'Election results are not yet available.');
        }

        // Get the agent's candidates in this election with their vote counts
        $candidates = Candidate::where('election_id', $election->id)
            ->where('approved_agent_id', Auth::id())
            ->with(['office', 'alumni.user', 'votes'])
            ->get()
            ->map(function ($candidate) {
                $candidate->vote_count = $candidate->votes->count();
                return $candidate;
            });

        // Get total votes cast in the election
        $totalVotes = $election->accreditedVoters()
            ->where('has_voted', true)
            ->count();

        return view('agent.election-results', compact('election', 'candidates', 'totalVotes'));
    }
} 