<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\Election;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ElectionController extends Controller
{
    /**
     * Display the specified election.
     *
     * @param  \App\Models\Election  $election
     * @return \Illuminate\View\View
     */
    public function show(Election $election)
    {
        // Get the authenticated user's candidate record for this election
        $candidate = $election->candidates()
            ->where('alumni_id', Auth::user()->alumni->id)
            ->first();

        // If user is not a candidate in this election, abort
        if (!$candidate) {
            abort(403, 'You are not a candidate in this election.');
        }

        return view('candidate.elections.show', compact('election', 'candidate'));
    }
} 