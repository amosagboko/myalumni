<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Http\Requests\Candidate\SuggestAgentRequest;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\Alumni;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\AgentSuggestion;
use App\Notifications\AgentSuggestionPending;

class AgentController extends Controller
{
    /**
     * Show the form for suggesting an agent.
     */
    public function suggestForm(Election $election, Candidate $candidate)
    {
        // Ensure the candidate belongs to the authenticated user
        $user = auth()->user();
        if ($candidate->alumni_id !== $user->alumni->id) {
            abort(403);
        }

        // Get all eligible alumni (not already agents for this election)
        $eligibleAlumni = Alumni::whereDoesntHave('approvedAgentCandidates', function ($query) use ($election) {
            $query->where('election_id', $election->id);
        })
        ->where('id', '!=', $user->alumni->id) // Exclude the current user
        ->with(['user:id,name,email'])
        ->get();

        return view('candidate.suggest-agent', compact('election', 'candidate', 'eligibleAlumni'));
    }

    /**
     * Handle the agent suggestion submission.
     */
    public function suggest(SuggestAgentRequest $request, Election $election, Candidate $candidate)
    {
        $validated = $request->validated();

        // Find the alumni by email
        $suggestedAgent = Alumni::whereHas('user', function ($query) use ($validated) {
            $query->where('email', $validated['agent_email'])
                  ->whereRaw("email NOT LIKE '%@alumni.fulafia.edu.ng'"); // Exclude system-generated emails
        })
        ->with('user') // Eager load the user relationship
        ->first();

        if (!$suggestedAgent) {
            return back()
                ->withInput()
                ->withErrors(['agent_email' => 'No alumni found with this email address.']);
        }

        // Check if the suggested agent is already an agent for another candidate in this election
        $isAgent = $election->candidates()
            ->where('approved_agent_id', $suggestedAgent->id)
            ->exists();
        
        if ($isAgent) {
            return back()
                ->withInput()
                ->withErrors(['agent_email' => 'This alumni is already an approved agent for another candidate in this election.']);
        }

        // Update the candidate with the suggested agent
        $candidate->update([
            'suggested_agent_id' => $suggestedAgent->id,
            'agent_status' => 'pending'
        ]);
        
        // Notify the suggested agent
        if ($suggestedAgent->user) {
            $suggestedAgent->user->notify(new AgentSuggestion([
                    'candidate_name' => Auth::user()->name,
                    'election_title' => $election->title,
                    'office_title' => $candidate->office->title,
                    'candidate_id' => $candidate->id,
                    'election_id' => $election->id
            ]));
        }

        // Notify ELCOM members
        $elcomUsers = \App\Models\User::role('elcom')->get();
        foreach ($elcomUsers as $elcomUser) {
            $elcomUser->notify(new AgentSuggestionPending([
                    'candidate_name' => Auth::user()->name,
                    'suggested_agent_name' => $suggestedAgent->user->name,
                    'election_title' => $election->title,
                    'office_title' => $candidate->office->title,
                    'candidate_id' => $candidate->id,
                    'election_id' => $election->id
            ]));
        }

        return redirect()
            ->route('candidate.elections.candidates.suggest-agent-form', [$election, $candidate])
            ->with('success', 'Agent suggestion submitted successfully. Waiting for ELCOM approval.');
    }

    /**
     * Cancel a pending agent suggestion.
     */
    public function cancelSuggestion(Election $election, Candidate $candidate)
    {
        // Ensure the candidate belongs to the authenticated user
        if ($candidate->alumni_id !== Auth::user()->alumni->id) {
            abort(403, 'You are not authorized to cancel this agent suggestion.');
        }

        // Only allow cancellation if the suggestion is still pending
        if ($candidate->agent_status !== 'pending') {
            return back()->with('error', 'Cannot cancel agent suggestion that is not pending.');
        }

        $candidate->update([
            'suggested_agent_id' => null,
            'agent_status' => null
        ]);

        return back()->with('success', 'Agent suggestion cancelled successfully.');
    }

    /**
     * Search for alumni by name or email.
     */
    public function searchAlumni(Request $request, Election $election)
    {
        $search = $request->input('search');
        $candidate = $election->candidates()
            ->where('alumni_id', Auth::user()->alumni->id)
            ->firstOrFail();
        
        // Get alumni who are not already agents for this election
        $alumni = Alumni::whereDoesntHave('approvedAgentCandidates', function ($query) use ($election) {
            $query->where('election_id', $election->id);
        })
        ->where('id', '!=', Auth::user()->alumni->id) // Exclude the current user
        ->whereHas('user', function($query) use ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere(function($q) use ($search) {
                      // Only search in non-system generated emails
                      $q->where('email', 'like', "%{$search}%")
                        ->whereRaw("email NOT LIKE '%@alumni.fulafia.edu.ng'");
                  });
            });
        })
        ->with('user:id,name,email') // Ensure user relationship is loaded
        ->take(10)
        ->get();

        if ($alumni->isNotEmpty()) {
            $firstAlumni = $alumni->first();
            if ($firstAlumni->user) { // Check if user relationship exists
                return redirect()
                    ->route('candidate.elections.candidates.suggest-agent-form', [$election, $candidate])
                    ->withInput([
                        'agent_email' => $firstAlumni->user->email,
                        'suggested_agent_id' => $firstAlumni->id
                    ]);
            }
        }

        return redirect()
            ->route('candidate.elections.candidates.suggest-agent-form', [$election, $candidate])
            ->withInput(['agent_email' => $search])
            ->withErrors(['agent_email' => 'No alumni found with this email address.']);
    }
} 