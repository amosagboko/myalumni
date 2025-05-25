<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\Election;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CandidateController extends Controller
{
    /**
     * Display a list of candidates managed by the agent.
     */
    public function index()
    {
        $candidates = Candidate::with(['election', 'office', 'alumni.user'])
            ->where('approved_agent_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('agent.candidates.index', compact('candidates'));
    }

    /**
     * Display details of a specific candidate.
     */
    public function show(Election $election, Candidate $candidate)
    {
        // Additional validation is handled by the middleware
        $candidate->load(['election', 'office', 'alumni.user', 'votes']);
        
        return view('agent.candidates.show', compact('election', 'candidate'));
    }

    /**
     * Show the form for editing candidate documents.
     */
    public function editDocuments(Election $election, Candidate $candidate)
    {
        // Additional validation is handled by the middleware
        if (!in_array($election->status, ['draft', 'accreditation'])) {
            return back()->with('error', 'Documents can only be edited during the draft or accreditation phase.');
        }

        return view('agent.candidates.edit-documents', compact('election', 'candidate'));
    }

    /**
     * Update the candidate's documents.
     */
    public function updateDocuments(Request $request, Election $election, Candidate $candidate)
    {
        // Additional validation is handled by the middleware
        if (!in_array($election->status, ['draft', 'accreditation'])) {
            return back()->with('error', 'Documents can only be updated during the draft or accreditation phase.');
        }

        $validated = $request->validate([
            'manifesto' => 'required|string|max:10000',
            'documents.*' => 'nullable|file|mimes:pdf,doc,docx|max:10240' // Max 10MB per file
        ]);

        // Handle document uploads
        $documents = [];
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                $path = $file->store('candidate-documents', 'public');
                $documents[] = $path;
            }
        }

        // Update the candidate
        $candidate->update([
            'manifesto' => $validated['manifesto'],
            'documents' => array_merge($candidate->documents ?? [], $documents)
        ]);

        // Log the activity
        activity()
            ->performedOn($candidate)
            ->causedBy(auth()->user())
            ->withProperties([
                'election_id' => $election->id,
                'documents_count' => count($documents)
            ])
            ->log('Updated candidate documents');

        return redirect()
            ->route('agent.candidates.show', [$election, $candidate])
            ->with('success', 'Documents updated successfully.');
    }
} 