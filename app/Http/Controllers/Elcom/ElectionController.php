<?php

namespace App\Http\Controllers\Elcom;

use App\Http\Controllers\Controller;
use App\Models\Election;
use App\Models\ElectionOffice;
use App\Models\Candidate;
use App\Models\ElectionResult;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Facades\LogBatch;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Carbon\Carbon;

class ElectionController extends Controller
{
    /**
     * Display a listing of the elections.
     */
    public function index()
    {
        $elections = Election::with(['offices', 'candidates'])
            ->latest()
            ->paginate(10);

        return view('elcom.elections.index', compact('elections'));
    }

    /**
     * Show the form for creating a new election.
     */
    public function create()
    {
        $feeTypes = \App\Models\FeeType::where('is_active', true)->get();
        return view('elcom.elections.create', compact('feeTypes'));
    }

    /**
     * Store a newly created election in storage.
     */
    public function store(Request $request)
    {
        try {
            \Illuminate\Support\Facades\Log::info('Starting election creation with data:', [
                'request_data' => $request->except(['_token']),
            ]);

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'eligibility_criteria' => 'required|string',
                'eoi_start' => 'required|date',
                'eoi_end' => 'required|date|after:eoi_start',
                'accreditation_start' => 'required|date|after:eoi_end',
                'accreditation_end' => 'required|date|after:accreditation_start',
                'voting_start' => 'required|date|after:accreditation_end',
                'voting_end' => [
                    'required',
                    'date',
                    'after:voting_start',
                    function ($attribute, $value, $fail) use ($request) {
                        $start = Carbon::parse($request->voting_start);
                        $end = Carbon::parse($value);
                        
                        if (!$start->isSameDay($end)) {
                            $fail('Voting must start and end on the same day.');
                        }
                        
                        if ($start->isSameDay($end) && $end->lte($start)) {
                            $fail('Voting end time must be after start time.');
                        }
                    },
                ],
                'offices' => 'required|array|min:1',
                'offices.*.title' => 'required|string|max:255',
                'offices.*.description' => 'required|string',
                'offices.*.max_candidates' => 'required|integer|min:1',
                'offices.*.term_duration' => 'required|integer|min:1',
                'offices.*.fee_type_id' => 'required|exists:fee_types,id',
            ]);

            \Illuminate\Support\Facades\Log::info('Validation passed, validated data:', [
                'validated_data' => $validated,
            ]);

            DB::beginTransaction();

            \Illuminate\Support\Facades\Log::info('Creating election record...');
            $election = Election::create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'eligibility_criteria' => $validated['eligibility_criteria'],
                'eoi_start' => $validated['eoi_start'],
                'eoi_end' => $validated['eoi_end'],
                'accreditation_start' => $validated['accreditation_start'],
                'accreditation_end' => $validated['accreditation_end'],
                'voting_start' => $validated['voting_start'],
                'voting_end' => $validated['voting_end'],
                'status' => 'draft',
            ]);

            \Illuminate\Support\Facades\Log::info('Election created, creating offices...', [
                'election_id' => $election->id,
            ]);

            foreach ($validated['offices'] as $officeData) {
                \Illuminate\Support\Facades\Log::info('Creating office:', [
                    'office_data' => $officeData,
                ]);
                
                $election->offices()->create([
                    'title' => $officeData['title'],
                    'description' => $officeData['description'],
                    'max_candidates' => $officeData['max_candidates'],
                    'term_duration' => $officeData['term_duration'],
                    'max_terms' => 1, // Default value
                    'fee_type_id' => $officeData['fee_type_id'],
                    'is_active' => true
                ]);
            }

            DB::commit();
            \Illuminate\Support\Facades\Log::info('Election creation completed successfully');

            return redirect()
                ->route('elcom.elections.show', $election)
                ->with('success', 'Election created successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Validation failed:', [
                'errors' => $e->errors(),
                'request_data' => $request->except(['_token'])
            ]);
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Election creation failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['_token'])
            ]);
            return back()
                ->withInput()
                ->with('error', 'Failed to create election. Please try again.');
        }
    }

    /**
     * Display the specified election.
     */
    public function show(Election $election)
    {
        $election->load(['offices.candidates.alumni', 'offices.candidates.votes', 'expressionsOfInterest']);
        return view('elcom.elections.show', compact('election'));
    }

    /**
     * Show the form for editing the specified election.
     */
    public function edit(Election $election)
    {
        if ($election->status !== 'draft') {
            return redirect()
                ->route('elcom.elections.show', $election)
                ->with('error', 'Only draft elections can be edited.');
        }

        return view('elcom.elections.edit', compact('election'));
    }

    /**
     * Update the specified election in storage.
     */
    public function update(Request $request, Election $election)
    {
        try {
            if ($election->status !== 'draft') {
                return redirect()
                    ->route('elcom.elections.show', $election)
                    ->with('error', 'Only draft elections can be edited.');
            }

            // Log the incoming request data
            \Illuminate\Support\Facades\Log::info('Election update request data:', [
                'election_id' => $election->id,
                'request_data' => $request->except(['_token', '_method']),
            ]);

            // Base validation rules for election fields
            $electionRules = [
                'title' => 'sometimes|required|string|max:255',
                'description' => 'sometimes|required|string',
                'eligibility_criteria' => 'sometimes|required|string',
                'eoi_start' => 'sometimes|required|date',
                'eoi_end' => 'sometimes|required|date|after:eoi_start',
                'accreditation_start' => 'sometimes|required|date|after:eoi_end',
                'accreditation_end' => 'sometimes|required|date|after:accreditation_start',
                'voting_start' => 'sometimes|required|date|after:accreditation_end',
                'voting_end' => [
                    'sometimes',
                    'required',
                    'date',
                    'after:voting_start',
                    function ($attribute, $value, $fail) use ($request) {
                        $start = Carbon::parse($request->voting_start);
                        $end = Carbon::parse($value);
                        
                        if (!$start->isSameDay($end)) {
                            $fail('Voting must start and end on the same day.');
                        }
                        
                        if ($start->isSameDay($end) && $end->lte($start)) {
                            $fail('Voting end time must be after start time.');
                        }
                    },
                ],
            ];

            // Only validate office rules if offices data is present
            $rules = $electionRules;
            if ($request->has('offices')) {
                $rules['offices'] = 'array';
                $rules['offices.*.id'] = 'required|exists:election_offices,id';
                $rules['offices.*.title'] = 'sometimes|required|string|max:255';
                $rules['offices.*.description'] = 'sometimes|required|string';
                $rules['offices.*.max_candidates'] = 'sometimes|required|integer|min:1';
                $rules['offices.*.term_duration'] = 'sometimes|required|integer|min:1';
                $rules['offices.*.fee_type_id'] = 'sometimes|required|exists:fee_types,id';
            }

            // Log the validation rules being used
            \Illuminate\Support\Facades\Log::info('Validation rules:', ['rules' => $rules]);

            // Validate only the rules we need
            $validated = $request->validate($rules);

            // Log the validated data
            \Illuminate\Support\Facades\Log::info('Validated data:', ['validated' => $validated]);

            DB::beginTransaction();

            // Update only the election fields that were provided
            $electionData = array_intersect_key($validated, $electionRules);
            if (!empty($electionData)) {
                \Illuminate\Support\Facades\Log::info('Updating election with data:', ['election_data' => $electionData]);
                $election->update($electionData);
            }

            // Update office data only if it was provided
            if (isset($validated['offices'])) {
                foreach ($validated['offices'] as $officeData) {
                    $office = $election->offices()->findOrFail($officeData['id']);
                    
                    // Only update fields that were provided
                    $officeData = array_filter($officeData, function($value, $key) {
                        return $key !== 'id' && $value !== null;
                    }, ARRAY_FILTER_USE_BOTH);

                    if (!empty($officeData)) {
                        \Illuminate\Support\Facades\Log::info('Updating office with data:', [
                            'office_id' => $office->id,
                            'office_data' => $officeData
                        ]);
                        $office->update($officeData);
                    }
                }
            }

            DB::commit();
            \Illuminate\Support\Facades\Log::info('Election update completed successfully');

            return redirect()
                ->route('elcom.elections.show', $election)
                ->with('success', 'Election updated successfully.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Validation failed:', [
                'errors' => $e->errors(),
                'request_data' => $request->except(['_token', '_method'])
            ]);
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Election update failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['_token', '_method'])
            ]);
            return back()
                ->withInput()
                ->with('error', 'Failed to update election: ' . $e->getMessage());
        }
    }

    /**
     * Start the accreditation period for the election.
     */
    public function startAccreditation(Election $election)
    {
        if (!$election->canStartAccreditation()) {
            return back()->with('error', 'Cannot start accreditation at this time.');
        }

        $election->update(['status' => 'accreditation']);
        return back()->with('success', 'Accreditation period has started.');
    }

    /**
     * Start the voting period for the election.
     */
    public function startVoting(Election $election)
    {
        if (!$election->canStartVoting()) {
            return back()->with('error', 'Cannot start voting at this time.');
        }

        $election->update(['status' => 'voting']);
        return back()->with('success', 'Voting period has started.');
    }

    /**
     * End the voting period and declare results.
     */
    public function endVoting(Election $election)
    {
        if (!$election->canEndVoting()) {
            return back()->with('error', 'Cannot end voting at this time.');
        }

        try {
            DB::beginTransaction();

            // Calculate results for each office
            foreach ($election->offices as $office) {
                $candidates = $office->candidates()
                    ->withCount('votes')
                    ->orderByDesc('votes_count')
                    ->get();

                if ($candidates->isNotEmpty()) {
                    $winner = $candidates->first();
                    $totalVotes = $candidates->sum('votes_count');

                    // Create or update election result
                    ElectionResult::updateOrCreate(
                        [
                            'election_id' => $election->id,
                            'election_office_id' => $office->id,
                            'candidate_id' => $winner->id,
                        ],
                        [
                            'total_votes' => $totalVotes,
                            'is_winner' => true,
                            'declared_at' => now(),
                        ]
                    );
                }
            }

            // Remove ELCOM chairman role after election completion
            $elcomChairman = User::role('elcom-chairman')->first();
            if ($elcomChairman) {
                // Keep the alumni role but remove elcom-chairman role
                $elcomChairman->removeRole('elcom-chairman');
                
                // Log the role removal using the activity log facade
                activity()
                    ->causedBy(Auth::user())
                    ->performedOn($elcomChairman)
                    ->withProperties([
                        'election_id' => $election->id,
                        'election_title' => $election->title,
                        'action' => 'removed_elcom_chairman_role'
                    ])
                    ->log('ELCOM chairman role removed after election completion');
            }

            $election->update(['status' => 'completed']);
            DB::commit();

            return redirect()
                ->route('elcom.elections.show', $election)
                ->with('success', 'Voting has ended, results have been declared, and ELCOM chairman role has been removed.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to end voting. Please try again.');
        }
    }

    /**
     * Display real-time election results.
     */
    public function realTimeResults(Election $election)
    {
        if (!in_array($election->status, ['voting', 'completed'])) {
            return back()->with('error', 'Results are only available during voting or after completion.');
        }

        return view('elcom.elections.real-time-results', compact('election'));
    }

    /**
     * Generate a PDF of the full election results with signatures.
     */
    public function printFullResults(Election $election)
    {
        if (!in_array($election->status, ['voting', 'completed'])) {
            return back()->with('error', 'Results are only available during voting or after completion.');
        }

        $election->load(['offices.candidates.alumni.user', 'offices.candidates.votes']);
        return view('elcom.elections.print-full-results', compact('election'));
    }

    /**
     * Generate a PDF of the election winners list with signatures.
     */
    public function printWinners(Election $election)
    {
        if (!in_array($election->status, ['voting', 'completed'])) {
            return back()->with('error', 'Results are only available during voting or after completion.');
        }

        $election->load(['offices.candidates.alumni.user', 'offices.candidates.votes']);
        return view('elcom.elections.print-winners', compact('election'));
    }

    /**
     * Display certificates for election winners.
     */
    public function printCertificates(Election $election)
    {
        if ($election->status !== 'completed') {
            return back()->with('error', 'Certificates are only available after election completion.');
        }

        $election->load(['offices.candidates.alumni', 'offices.candidates.electionResults']);
        return view('elcom.elections.print-certificates', compact('election'));
    }

    /**
     * Screen/approve a candidate for an office.
     */
    public function screenCandidate(Request $request, Election $election, ElectionOffice $office, Candidate $candidate)
    {
        if (!in_array($election->status, ['draft', 'accreditation'])) {
            return back()->with('error', 'Candidates can only be screened during the draft or accreditation phase.');
        }

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'remarks' => 'nullable|string',
        ]);

        $candidate->update([
            'status' => $validated['status'],
            'remarks' => $validated['remarks'],
            'screened_at' => now(),
            'screened_by' => auth()->id(),
        ]);

        return back()->with('success', 'Candidate has been ' . $validated['status']);
    }

    /**
     * Show all candidates for screening for a specific office in an election.
     */
    public function screenCandidates(Election $election, ElectionOffice $office)
    {
        $candidates = $office->candidates()->with('alumni.user')->get();
        return view('elcom.elections.screen-candidates', compact('election', 'office', 'candidates'));
    }

    /**
     * Approve a candidate's expression of interest.
     */
    public function approveCandidate(Election $election, ElectionOffice $office, Candidate $candidate)
    {
        $candidate->status = 'approved';
        $candidate->save();
        return back()->with('success', 'Candidate approved successfully.');
    }

    /**
     * Reject a candidate's expression of interest.
     */
    public function rejectCandidate(Request $request, Election $election, ElectionOffice $office, Candidate $candidate)
    {
        $candidate->status = 'rejected';
        $candidate->rejection_reason = $request->input('rejection_reason');
        $candidate->save();
        return back()->with('success', 'Candidate rejected successfully.');
    }

    /**
     * Show the form for creating a new office for an election.
     */
    public function createOffice(Election $election)
    {
        if ($election->status !== 'draft') {
            return redirect()
                ->route('elcom.elections.show', $election)
                ->with('error', 'Offices can only be added to draft elections.');
        }

        $feeTypes = \App\Models\FeeType::where('is_active', true)->get();
        return view('elcom.elections.offices.create', compact('election', 'feeTypes'));
    }

    /**
     * Store a newly created office for an election.
     */
    public function storeOffice(Request $request, Election $election)
    {
        if ($election->status !== 'draft') {
            return redirect()
                ->route('elcom.elections.show', $election)
                ->with('error', 'Offices can only be added to draft elections.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'max_candidates' => 'required|integer|min:1',
            'max_terms' => 'required|integer|min:1',
            'fee_type_id' => 'required|exists:fee_types,id'
        ]);

        $election->offices()->create($validated);

        return redirect()
            ->route('elcom.elections.edit', $election)
            ->with('success', 'Office created successfully.');
    }

    /**
     * Show the form for editing an office.
     */
    public function editOffice(Election $election, ElectionOffice $office)
    {
        if ($election->status !== 'draft') {
            return redirect()
                ->route('elcom.elections.show', $election)
                ->with('error', 'Offices can only be edited in draft elections.');
        }

        $feeTypes = \App\Models\FeeType::where('is_active', true)->get();
        return view('elcom.elections.offices.edit', compact('election', 'office', 'feeTypes'));
    }

    /**
     * Update the specified office.
     */
    public function updateOffice(Request $request, Election $election, ElectionOffice $office)
    {
        if ($election->status !== 'draft') {
            return redirect()
                ->route('elcom.elections.show', $election)
                ->with('error', 'Offices can only be edited in draft elections.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'max_candidates' => 'required|integer|min:1',
            'max_terms' => 'required|integer|min:1',
            'fee_type_id' => 'required|exists:fee_types,id'
        ]);

        $office->update($validated);

        return redirect()
            ->route('elcom.elections.edit', $election)
            ->with('success', 'Office updated successfully.');
    }

    /**
     * Delete the specified office.
     */
    public function deleteOffice(Election $election, ElectionOffice $office)
    {
        if ($election->status !== 'draft') {
            return redirect()
                ->route('elcom.elections.show', $election)
                ->with('error', 'Offices can only be deleted from draft elections.');
        }

        $office->delete();

        return redirect()
            ->route('elcom.elections.edit', $election)
            ->with('success', 'Office deleted successfully.');
    }

    /**
     * Show candidates for a specific office.
     */
    public function officeCandidates(Election $election, ElectionOffice $office)
    {
        $candidates = $office->candidates()->with('alumni.user')->get();
        return view('elcom.elections.screen-candidates', compact('election', 'office', 'candidates'));
    }

    public function startEoi(Election $election)
    {
        if (!$election->canStartEoi()) {
            return back()->with('error', 'Cannot start EOI period at this time.');
        }

        $election->startEoi();
        return back()->with('success', 'EOI period has been started.');
    }

    public function endEoi(Election $election)
    {
        if (!$election->canEndEoi()) {
            return back()->with('error', 'Cannot end EOI period at this time.');
        }

        $election->endEoi();
        return back()->with('success', 'EOI period has been ended.');
    }

    /**
     * Extend the EOI period for an election.
     */
    public function extendEoi(Request $request, Election $election)
    {
        if (!$election->canExtendEoiPeriod()) {
            return back()->with('error', 'Cannot extend EOI period at this time.');
        }

        $validated = $request->validate([
            'extension_days' => 'required|integer|min:1|max:30',
        ]);

        $days = $validated['extension_days'];
        
        if ($election->extendEoiPeriod($days)) {
            $extensionReasons = $election->getEoiExtensionReasons();
            $reasonText = implode(', ', $extensionReasons);
            
            return back()->with('success', "EOI period has been extended by {$days} days. Reasons: {$reasonText}");
        } else {
            return back()->with('error', 'Failed to extend EOI period. Please check the dates and ensure the new end date does not conflict with the accreditation period.');
        }
    }

    /**
     * Show EOI payment status and extension options.
     */
    public function eoiPaymentStatus(Election $election)
    {
        $pendingPayments = $election->getPendingEoiPaymentsCount();
        $paidApplications = $election->getPaidEoiApplicationsCount();
        $totalApplications = $election->getTotalEoiApplicationsCount();
        
        return view('elcom.elections.eoi-payment-status', compact(
            'election', 
            'pendingPayments', 
            'paidApplications', 
            'totalApplications'
        ));
    }

    /**
     * Display the list of accredited voters for an election.
     */
    public function accreditedVoters(Election $election)
    {
        $accreditedVoters = $election->accreditedVoters()
            ->with(['alumni.user'])
            ->orderBy('accredited_at', 'desc')
            ->paginate(20);

        return view('elcom.elections.accredited-voters', compact('election', 'accreditedVoters'));
    }

    /**
     * Show the form for assigning an agent to a candidate.
     */
    public function assignAgentForm(Election $election, ElectionOffice $office, Candidate $candidate)
    {
        if (!in_array($election->status, ['draft', 'accreditation'])) {
            return back()->with('error', 'Agents can only be assigned during the draft or accreditation phase.');
        }

        // Get all users with the alumni-agent role who are not already assigned to other candidates in this election
        $availableAgents = \App\Models\User::role('alumni-agent')
            ->whereNotIn('id', function($query) use ($election) {
                $query->select('approved_agent_id')
                    ->from('candidates')
                    ->where('election_id', $election->id)
                    ->whereNotNull('approved_agent_id');
            })
            ->get();

        // If the candidate has a suggested agent, add them to the available agents list
        if ($candidate->suggested_agent_id && $candidate->agent_status === 'pending') {
            $suggestedAgent = \App\Models\Alumni::with('user')->find($candidate->suggested_agent_id);
            if ($suggestedAgent && !$availableAgents->contains('id', $suggestedAgent->user->id)) {
                $availableAgents->push($suggestedAgent->user);
            }
        }

        return view('elcom.elections.assign-agent', compact('election', 'office', 'candidate', 'availableAgents'));
    }

    /**
     * Assign an agent to a candidate.
     */
    public function assignAgent(Request $request, Election $election, ElectionOffice $office, Candidate $candidate)
    {
        if (!in_array($election->status, ['draft', 'accreditation'])) {
            return back()->with('error', 'Agents can only be assigned during the draft or accreditation phase.');
        }

        $validated = $request->validate([
            'agent_id' => 'required|exists:users,id'
        ]);

        // Get the selected agent
        $agent = \App\Models\User::findOrFail($validated['agent_id']);

        // Check if the agent is already assigned to another candidate in this election
        $isAgentAssigned = $election->candidates()
            ->where('approved_agent_id', $validated['agent_id'])
            ->where('id', '!=', $candidate->id)
            ->exists();

        if ($isAgentAssigned) {
            return back()->with('error', 'This agent is already assigned to another candidate in this election.');
        }

        // Assign the alumni-agent role if they don't already have it
        if (!$agent->hasRole('alumni-agent')) {
            $agent->assignRole('alumni-agent');
        }

        $candidate->update([
            'approved_agent_id' => $validated['agent_id'],
            'agent_status' => 'approved',
            'agent_rejection_reason' => null
        ]);

        // Log the activity
        activity()
            ->performedOn($candidate)
            ->causedBy(auth()->user())
            ->withProperties([
                'agent_id' => $validated['agent_id'],
                'election_id' => $election->id,
                'office_id' => $office->id
            ])
            ->log('Agent assigned to candidate');

        // Send notification to the agent using Laravel's notification system
        $agent->notify(new \App\Notifications\AgentRoleAssigned(
            $election->title,
            $candidate->alumni->user->name,
            $office->title,
            $candidate->id,
            $election->id
        ));

        return redirect()
            ->route('elcom.election-offices.candidates.index', [$election, $office])
            ->with('success', 'Agent assigned successfully.');
    }

    /**
     * Remove an agent from a candidate.
     */
    public function removeAgent(Election $election, ElectionOffice $office, Candidate $candidate)
    {
        if (!in_array($election->status, ['draft', 'accreditation'])) {
            return back()->with('error', 'Agents can only be removed during the draft or accreditation phase.');
        }

        $agentId = $candidate->approved_agent_id;
        $candidate->update(['approved_agent_id' => null]);

        // Log the activity
        activity()
            ->performedOn($candidate)
            ->causedBy(auth()->user())
            ->withProperties([
                'previous_agent_id' => $agentId,
                'election_id' => $election->id,
                'office_id' => $office->id
            ])
            ->log('Agent removed from candidate');

        return back()->with('success', 'Agent removed successfully.');
    }

    /**
     * Show the agent suggestions review page for an election.
     */
    public function reviewAgentSuggestions(Election $election)
    {
        // Get all candidates with pending agent suggestions
        $candidates = $election->candidates()
            ->whereNotNull('suggested_agent_id')
            ->where('agent_status', 'pending')
            ->with(['alumni.user', 'suggestedAgent.user', 'office'])
            ->get();

        return view('elcom.elections.review-agent-suggestions', compact('election', 'candidates'));
    }

    /**
     * Approve a candidate's suggested agent.
     */
    public function approveAgentSuggestion(Request $request, Election $election, Candidate $candidate)
    {
        // Validate the request
        $request->validate([
            'reason' => 'nullable|string|max:255'
        ]);

        // Ensure the candidate has a pending agent suggestion
        if (!$candidate->suggested_agent_id || $candidate->agent_status !== 'pending') {
            return back()->with('error', 'This candidate does not have a pending agent suggestion.');
        }

        // Get the suggested agent before updating the candidate
        $suggestedAgent = \App\Models\Alumni::with('user')->find($candidate->suggested_agent_id);
        if (!$suggestedAgent) {
            return back()->with('error', 'Suggested agent not found.');
        }

        // Update the candidate with the approved agent
        $candidate->update([
            'approved_agent_id' => $candidate->suggested_agent_id,
            'agent_status' => 'approved',
            'agent_rejection_reason' => null
        ]);

        // Assign the agent role to the suggested agent if they don't already have it
        if (!$suggestedAgent->user->hasRole('alumni-agent')) {
            $suggestedAgent->user->assignRole('alumni-agent');
        }

        // Notify the candidate
        $candidate->alumni->user->notifications()->create([
            'type' => 'agent_suggestion_approved',
            'data' => [
                'election_title' => $election->title,
                'office_title' => $candidate->office->title,
                'agent_name' => $suggestedAgent->user->name,
                'candidate_id' => $candidate->id,
                'election_id' => $election->id
            ],
            'read_at' => null
        ]);

        // Notify the approved agent
        $suggestedAgent->user->notifications()->create([
            'type' => 'agent_role_assigned',
            'data' => [
                'election_title' => $election->title,
                'candidate_name' => $candidate->alumni->user->name,
                'office_title' => $candidate->office->title,
                'candidate_id' => $candidate->id,
                'election_id' => $election->id
            ],
            'read_at' => null
        ]);

        return back()->with('success', 'Agent suggestion approved successfully.');
    }

    /**
     * Reject a candidate's suggested agent.
     */
    public function rejectAgentSuggestion(Request $request, Election $election, Candidate $candidate)
    {
        // Validate the request
        $request->validate([
            'reason' => 'required|string|max:255'
        ]);

        // Ensure the candidate has a pending agent suggestion
        if (!$candidate->suggested_agent_id || $candidate->agent_status !== 'pending') {
            return back()->with('error', 'This candidate does not have a pending agent suggestion.');
        }

        // Get the suggested agent before updating the candidate
        $suggestedAgent = \App\Models\Alumni::with('user')->find($candidate->suggested_agent_id);
        if (!$suggestedAgent) {
            return back()->with('error', 'Suggested agent not found.');
        }

        // Update the candidate with the rejection
        $candidate->update([
            'agent_status' => 'rejected',
            'agent_rejection_reason' => $request->reason,
            'suggested_agent_id' => null
        ]);

        // Notify the candidate
        $candidate->alumni->user->notifications()->create([
            'type' => 'agent_suggestion_rejected',
            'data' => [
                'election_title' => $election->title,
                'office_title' => $candidate->office->title,
                'reason' => $request->reason,
                'candidate_id' => $candidate->id,
                'election_id' => $election->id
            ],
            'read_at' => null
        ]);

        // Notify the rejected agent
        $suggestedAgent->user->notifications()->create([
            'type' => 'agent_suggestion_rejected',
            'data' => [
                'election_title' => $election->title,
                'candidate_name' => $candidate->alumni->user->name,
                'office_title' => $candidate->office->title,
                'reason' => $request->reason,
                'candidate_id' => $candidate->id,
                'election_id' => $election->id
            ],
            'read_at' => null
        ]);

        return back()->with('success', 'Agent suggestion rejected successfully.');
    }

    /**
     * Stream real-time election results using Server-Sent Events.
     */
    public function streamRealTimeResults(Election $election)
    {
        if (!in_array($election->status, ['voting', 'completed'])) {
            return response('Results are only available during voting or after completion.', 403);
        }

        return response()->stream(function() use ($election) {
            while (true) {
                $election->load(['offices.candidates.alumni.user', 'offices.candidates.votes']);
                
                $data = [
                    'totalAccredited' => $election->getTotalAccreditedVoters(),
                    'totalVotes' => $election->getTotalVotes(),
                    'voterTurnout' => number_format(($election->getTotalVotes() / max($election->getTotalAccreditedVoters(), 1)) * 100, 1),
                    'timeRemaining' => $election->voting_end->diffForHumans(),
                    'offices' => $election->offices->map(function ($office) {
                        $totalVotes = $office->candidates->sum(function ($candidate) {
                            return $candidate->votes->count();
                        });

                        return [
                            'id' => $office->id,
                            'title' => $office->title,
                            'candidates' => $office->candidates->map(function ($candidate) use ($totalVotes) {
                                $votes = $candidate->votes->count();
                                return [
                                    'name' => $candidate->alumni->user->name,
                                    'votes' => $votes,
                                    'percentage' => $totalVotes > 0 ? ($votes / $totalVotes) * 100 : 0,
                                ];
                            })->sortByDesc('votes')->values(),
                        ];
                    }),
                ];

                echo "data: " . json_encode($data) . "\n\n";
                ob_flush();
                flush();
                
                sleep(30); // Update every 30 seconds
            }
        }, 200, [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'text/event-stream',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Show basic election results in a tabular format.
     */
    public function basicResults(Election $election)
    {
        // Only show results if election is in voting or completed state
        if (!in_array($election->status, ['voting', 'completed'])) {
            return back()->with('error', 'Results are not available for this election yet.');
        }

        // Load election data with necessary relationships
        $election->load(['offices.candidates.votes']);

        // Calculate basic statistics
        $totalAccredited = $election->getTotalAccreditedVoters();
        $totalVotes = $election->getTotalVotes();
        $voterTurnout = $totalAccredited > 0 ? round(($totalVotes / $totalAccredited) * 100, 2) : 0;

        // Get time remaining if election is still in voting
        $timeRemaining = null;
        if ($election->status === 'voting' && now()->between($election->voting_start, $election->voting_end)) {
            $timeRemaining = $election->voting_end->diffForHumans(['parts' => 2]);
        }

        return view('elcom.elections.basic-results', compact(
            'election',
            'totalAccredited',
            'totalVotes',
            'voterTurnout',
            'timeRemaining'
        ));
    }

    /**
     * Verify the authenticity of an election certificate.
     */
    public function verifyCertificate(Election $election, ElectionOffice $office, Candidate $winner, string $code)
    {
        // Generate the expected certificate number
        $expectedCode = strtoupper(substr(md5($election->id . $office->id . $winner->id), 0, 8));
        
        // Verify if the winner is actually the winner for this office
        $isWinner = $winner->votes->count() === $office->candidates->max(function ($candidate) {
            return $candidate->votes->count();
        });

        if ($code !== $expectedCode || !$isWinner) {
            return view('elcom.elections.certificate-verification', [
                'isValid' => false,
                'message' => 'This certificate appears to be invalid or has been tampered with.'
            ]);
        }

        return view('elcom.elections.certificate-verification', [
            'isValid' => true,
            'election' => $election,
            'office' => $office,
            'winner' => $winner,
            'certificateNumber' => $code,
            'issueDate' => now()->format('F j, Y')
        ]);
    }
}