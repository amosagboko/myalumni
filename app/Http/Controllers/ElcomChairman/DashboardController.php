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
            ->count();
        
        // Total EOI: Count paid EOI transactions across all elections
        $eoiFeeTypeIds = \App\Models\FeeType::where('code', 'like', 'eoi-%')->pluck('id');
        $totalCandidates = 0;
        if ($eoiFeeTypeIds->isNotEmpty()) {
            $totalCandidates = \App\Models\Transaction::whereHas('feeTemplate', function ($query) use ($eoiFeeTypeIds) {
                $query->whereIn('fee_type_id', $eoiFeeTypeIds);
            })->where('status', 'paid')->count();
        }
        
        // Total Votes Cast: Count accredited voters who have voted
        $totalVotes = AccreditedVoter::where('has_voted', true)->count();
        
        // Alumni who have paid annual or subscription dues (includes 2024 graduates)
        $duesFeeTypeIds = \App\Models\FeeType::whereIn('code', ['subscription', \App\Models\FeeType::ANNUAL_DUE_CODE])->pluck('id');
        $specialExemption = 0;
        $paidDuesAlumni = $duesFeeTypeIds->isEmpty()
            ? 0
            : \App\Models\Transaction::where('status', 'paid')
                ->whereHas('feeTemplate', fn ($query) => $query->whereIn('fee_type_id', $duesFeeTypeIds))
                ->distinct('alumni_id')
                ->count('alumni_id');
        
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
            'specialExemption',
            'recentElections',
            'totalElections',
            'completedElections',
            'pendingElections',
            'totalAccreditedVoters'
        ));
    }
} 