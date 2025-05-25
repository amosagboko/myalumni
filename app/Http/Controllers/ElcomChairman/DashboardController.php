<?php

namespace App\Http\Controllers\ElcomChairman;

use App\Http\Controllers\Controller;
use App\Models\Election;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $activeElections = Election::where('status', 'active')->count();
        $totalCandidates = Election::whereHas('candidates')->count();
        $totalVotes = Election::whereHas('votes')->count();
        $recentElections = Election::latest()->take(5)->get();

        return view('elcom-chairman.dashboard', compact(
            'activeElections',
            'totalCandidates',
            'totalVotes',
            'recentElections'
        ));
    }
} 