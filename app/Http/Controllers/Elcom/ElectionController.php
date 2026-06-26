<?php

namespace App\Http\Controllers\Elcom;

use App\Http\Controllers\Controller;
use App\Models\Election;
use App\Models\ElectionOffice;
use App\Models\Candidate;
use App\Models\ElectionResult;
use App\Models\User;
use App\Services\ElectionArchiveService;
use App\Services\ElectionCycleService;
use App\Services\ElectionByElectionService;
use App\Services\ElectionResultService;
use App\Exceptions\ElectionImmutableException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Facades\LogBatch;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ElectionController extends Controller
{
    public function __construct(
        private ElectionCycleService $cycleService,
        private ElectionArchiveService $archiveService,
        private ElectionResultService $resultService,
        private ElectionByElectionService $byElectionService
    ) {}
    /**
     * Display a listing of the elections.
     */
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'active');

        $query = Election::with(['offices', 'candidates'])->latest();

        $query = match ($filter) {
            'active' => $query->operational(),
            'completed' => $query->completedUnarchived(),
            'archived' => $query->archived(),
            default => $query,
        };

        $elections = $query->paginate(10)->withQueryString();
        $canStartNewCycle = Election::canStartNewCycle();

        return view('elcom.elections.index', compact('elections', 'filter', 'canStartNewCycle'));
    }

    /**
     * Show the form for creating a new election.
     */
    public function create()
    {
        if (!Election::canStartNewCycle()) {
            return redirect()
                ->route('elcom.elections.index')
                ->with('error', 'Archive the completed election or finish the active cycle before creating a new election.');
        }

        $feeTypes = \App\Models\FeeType::where('is_active', true)->get();
        return view('elcom.elections.create', compact('feeTypes'));
    }

    /**
     * Store a newly created election in storage.
     */
    public function store(Request $request)
    {
        try {
            $this->cycleService->assertCanStartNewCycle();

            \Illuminate\Support\Facades\Log::info('Starting election creation with data:', [
                'request_data' => $request->except(['_token']),
            ]);

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'election_year' => 'required|integer|min:2000|max:2100',
                'cycle_label' => 'nullable|string|max:100',
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
                'election_year' => $validated['election_year'],
                'cycle_label' => $validated['cycle_label'] ?? null,
                'description' => $validated['description'],
                'eligibility_criteria' => $validated['eligibility_criteria'],
                'eoi_start' => $validated['eoi_start'],
                'eoi_end' => $validated['eoi_end'],
                'accreditation_start' => $validated['accreditation_start'],
                'accreditation_end' => $validated['accreditation_end'],
                'voting_start' => $validated['voting_start'],
                'voting_end' => $validated['voting_end'],
                'status' => 'draft',
                'is_active' => true,
            ]);

            $this->cycleService->activate($election);

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
        } catch (ElectionImmutableException $e) {
            DB::rollBack();
            return back()->withInput()->with('error', $e->getMessage());
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
        $this->cycleService->assertMutable($election);

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
            $this->cycleService->assertMutable($election);

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
        try {
            $this->cycleService->beginOperationalPhase($election);
        } catch (ElectionImmutableException $e) {
            return back()->with('error', $e->getMessage());
        }

        if (!$election->canStartAccreditation()) {
            return back()->with('error', 'Cannot start accreditation at this time.');
        }

        $election->update(['status' => 'accreditation']);
        return back()->with('success', 'Accreditation period has started.');
    }

    /**
     * End the accreditation period early.
     */
    public function endAccreditation(Election $election)
    {
        $this->cycleService->assertMutable($election);

        if (!$election->endAccreditation()) {
            return back()->with('error', 'Cannot end accreditation at this time.');
        }

        return back()->with('success', 'Accreditation period has been closed.');
    }

    /**
     * Start the voting period for the election.
     */
    public function startVoting(Election $election)
    {
        try {
            $this->cycleService->beginOperationalPhase($election);
        } catch (ElectionImmutableException $e) {
            return back()->with('error', $e->getMessage());
        }

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
        $this->cycleService->assertMutable($election);

        if (!$election->canEndVoting()) {
            return back()->with('error', 'Cannot end voting at this time.');
        }

        try {
            DB::beginTransaction();

            $summary = $this->resultService->declareResults($election);

            if ($election->isByElection()) {
                $parentOutcome = $this->byElectionService->syncResultsToParent($election);
                $parent = $election->parentElection->fresh();

                DB::commit();

                if ($parentOutcome['parent_completed']) {
                    $this->removeElcomChairmanRole($parent);

                    return redirect()
                        ->route('elcom.elections.resolution', $parent)
                        ->with('success', 'By-election complete. All offices now have winners. ELCOM chairman role has been removed.');
                }

                if (!$summary['all_decided']) {
                    return redirect()
                        ->route('elcom.elections.resolution', $parent)
                        ->with('success', 'By-election ended with unresolved offices. Schedule another by-election if needed.');
                }

                return redirect()
                    ->route('elcom.elections.resolution', $parent)
                    ->with('success', 'By-election results have been merged. Some offices may still require resolution.');
            }

            if ($summary['all_decided']) {
                $this->removeElcomChairmanRole($election);
            }

            DB::commit();

            if ($summary['all_decided']) {
                return redirect()
                    ->route('elcom.elections.show', $election)
                    ->with('success', 'Voting has ended and all offices have declared winners. ELCOM chairman role has been removed.');
            }

            return redirect()
                ->route('elcom.elections.resolution', $election)
                ->with('success', "Voting has ended. {$summary['tied']} tied and {$summary['uncontested']} uncontested office(s) require a by-election. ELCOM chairman role is retained until all seats are filled.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to end voting. Please try again.');
        }
    }

    /**
     * Show election resolution summary (ties, uncontested offices, declared winners).
     */
    public function resolution(Election $election)
    {
        if (!in_array($election->status, ['incomplete', 'completed', 'archived'])) {
            return redirect()
                ->route('elcom.elections.show', $election)
                ->with('error', 'Resolution summary is only available after voting has ended.');
        }

        $resolution = $this->resultService->getResolutionSummary($election);

        return view('elcom.elections.resolution', compact('election', 'resolution'));
    }

    public function scheduleByElection(Election $election)
    {
        if (!$election->isIncomplete()) {
            return redirect()
                ->route('elcom.elections.resolution', $election)
                ->with('error', 'By-elections can only be scheduled for incomplete elections.');
        }

        if ($this->byElectionService->hasActiveByElection($election)) {
            $active = $election->activeByElection();

            return redirect()
                ->route('elcom.elections.show', $active)
                ->with('info', 'A by-election is already in progress for this election.');
        }

        $schedulableOffices = $this->byElectionService->schedulableOffices($election);

        if ($schedulableOffices->isEmpty()) {
            return redirect()
                ->route('elcom.elections.resolution', $election)
                ->with('error', 'No offices are available for a by-election.');
        }

        return view('elcom.elections.schedule-by-election', compact('election', 'schedulableOffices'));
    }

    public function storeByElection(Request $request, Election $election)
    {
        $this->cycleService->assertMutable($election);

        $schedulableIds = $this->byElectionService->schedulableOffices($election)->pluck('id')->all();
        $hasUncontested = $election->offices()
            ->whereIn('id', $request->input('office_ids', []))
            ->where('resolution_status', ElectionResultService::RESOLUTION_UNCONTESTED)
            ->exists();

        $rules = [
            'office_ids' => 'required|array|min:1',
            'office_ids.*' => 'integer|in:' . implode(',', $schedulableIds),
            'title' => 'required|string|max:255',
            'cycle_label' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'accreditation_start' => 'required|date',
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
        ];

        if ($hasUncontested) {
            $rules['eoi_start'] = 'required|date';
            $rules['eoi_end'] = 'required|date|after:eoi_start|before:accreditation_start';
        }

        $validated = $request->validate($rules);

        try {
            $byElection = $this->byElectionService->schedule($election, $validated['office_ids'], $validated);

            return redirect()
                ->route('elcom.elections.show', $byElection)
                ->with('success', 'By-election scheduled. Runoff offices have candidates on the ballot; uncontested offices will accept EOI when you start the EOI period.');
        } catch (ElectionImmutableException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to schedule by-election. Please try again.');
        }
    }

    protected function removeElcomChairmanRole(Election $election): void
    {
        $elcomChairman = User::role('elcom-chairman')->first();
        if ($elcomChairman) {
            $elcomChairman->removeRole('elcom-chairman');

            activity()
                ->causedBy(Auth::user())
                ->performedOn($elcomChairman)
                ->withProperties([
                    'election_id' => $election->id,
                    'election_title' => $election->title,
                    'action' => 'removed_elcom_chairman_role',
                ])
                ->log('ELCOM chairman role removed after election completion');
        }
    }

    /**
     * Display real-time election results.
     */
    public function realTimeResults(Election $election)
    {
        if (!in_array($election->status, ['voting', 'incomplete', 'completed', 'archived'])) {
            return back()->with('error', 'Results are only available during voting or after completion.');
        }

        return view('elcom.elections.real-time-results', compact('election'));
    }

    /**
     * Generate a PDF of the full election results with signatures.
     */
    public function printFullResults(Election $election)
    {
        if (!in_array($election->status, ['voting', 'incomplete', 'completed', 'archived'])) {
            return back()->with('error', 'Results are only available during voting or after completion.');
        }

        // Load ONLY approved candidates for printing
        $election->load(['offices.candidates' => function ($query) {
            $query->where('status', 'approved');
        }, 'offices.candidates.alumni.user', 'offices.candidates.votes', 'offices.results', 'offices.winnerCandidate']);

        $declaredWinnerIds = $this->resultService->getDeclaredWinners($election)
            ->pluck('candidate.id')
            ->flip();

        return view('elcom.elections.print-full-results', compact('election', 'declaredWinnerIds'));
    }

    /**
     * Generate a PDF of the election winners list with signatures.
     */
    public function printWinners(Election $election)
    {
        if (!in_array($election->status, ['incomplete', 'completed', 'archived'])) {
            return back()->with('error', 'Winners can only be printed after results have been declared.');
        }

        $declaredWinners = $this->resultService->getDeclaredWinners($election);
        $pendingOffices = $election->offices()
            ->whereIn('resolution_status', [
                ElectionResultService::RESOLUTION_TIED,
                ElectionResultService::RESOLUTION_UNCONTESTED,
            ])
            ->whereNull('by_election_id')
            ->count();

        return view('elcom.elections.print-winners', compact('election', 'declaredWinners', 'pendingOffices'));
    }

    /**
     * Display certificates for election winners.
     */
    public function printCertificates(Election $election)
    {
        if (!in_array($election->status, ['completed', 'archived'])) {
            return back()->with('error', 'Certificates are only available after the election is fully completed.');
        }

        $declaredWinners = $this->resultService->getDeclaredWinners($election);

        if ($declaredWinners->isEmpty()) {
            return back()->with('error', 'No declared winners are available for certificate printing.');
        }

        return view('elcom.elections.print-certificates', compact('election', 'declaredWinners'));
    }

    /**
     * Screen/approve a candidate for an office.
     */
    public function screenCandidate(Request $request, Election $election, ElectionOffice $office, Candidate $candidate)
    {
        $this->cycleService->assertMutable($election);
        $this->assertOfficeBelongsToElection($election, $office);
        $this->assertCandidateBelongsToOffice($election, $office, $candidate);

        if (!$this->canScreenCandidates($election)) {
            return back()->with('error', 'Candidates can only be screened during draft, EOI, or accreditation.');
        }

        if (!$candidate->canBeScreened()) {
            return back()->with('error', 'Only candidates awaiting screening can be reviewed.');
        }

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'rejection_reason' => 'required_if:status,rejected|nullable|string|max:2000',
        ]);

        if ($validated['status'] === 'approved') {
            if (!$candidate->has_paid_screening_fee) {
                return back()->with('error', 'Cannot approve a candidate who has not paid the screening fee.');
            }

            $candidate->approve();
        } else {
            $reason = trim($validated['rejection_reason'] ?? '');
            if ($reason === '') {
                return back()->with('error', 'A rejection reason is required.');
            }

            $candidate->reject($reason);
        }

        return back()->with('success', 'Candidate has been ' . $validated['status'] . '.');
    }

    public function screenCandidates(Election $election, ElectionOffice $office)
    {
        return $this->officeCandidates($election, $office);
    }

    public function officeCandidates(Election $election, ElectionOffice $office)
    {
        return view('elcom.elections.screen-candidates', $this->officeCandidatesViewData($election, $office));
    }

    /**
     * Show the form for creating a new office for an election.
     */
    public function createOffice(Election $election)
    {
        $this->cycleService->assertMutable($election);

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
        $this->cycleService->assertMutable($election);

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

    public function startEoi(Election $election)
    {
        try {
            $this->cycleService->beginOperationalPhase($election);
        } catch (ElectionImmutableException $e) {
            return back()->with('error', $e->getMessage());
        }

        if (!$election->canStartEoi()) {
            return back()->with('error', 'Cannot start EOI period at this time.');
        }

        $election->startEoi();
        return back()->with('success', 'EOI period has been started.');
    }

    public function endEoi(Election $election)
    {
        $this->cycleService->assertMutable($election);

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
        $this->cycleService->assertMutable($election);

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
            ->paginate(20)
            ->withQueryString();

        return view('elcom.elections.accredited-voters', compact('election', 'accreditedVoters'));
    }

    /**
     * Show the form for assigning an agent to a candidate.
     */
    public function assignAgentForm(Election $election, ElectionOffice $office, Candidate $candidate)
    {
        $this->assertOfficeBelongsToElection($election, $office);
        $this->assertCandidateBelongsToOffice($election, $office, $candidate);

        if ($election->status !== 'accreditation') {
            return back()->with('error', 'Agents can only be assigned during the accreditation phase.');
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
        $this->cycleService->assertMutable($election);
        $this->assertOfficeBelongsToElection($election, $office);
        $this->assertCandidateBelongsToOffice($election, $office, $candidate);

        if ($election->status !== 'accreditation') {
            return back()->with('error', 'Agents can only be assigned during the accreditation phase.');
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
        $this->cycleService->assertMutable($election);
        $this->assertOfficeBelongsToElection($election, $office);
        $this->assertCandidateBelongsToOffice($election, $office, $candidate);

        if ($election->status !== 'accreditation') {
            return back()->with('error', 'Agents can only be removed during the accreditation phase.');
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

        // Update the candidate with the approved agent (always users.id)
        $candidate->update([
            'approved_agent_id' => $suggestedAgent->user_id,
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
        if (!in_array($election->status, ['voting', 'incomplete', 'completed'])) {
            return response('Results are only available during voting or after completion.', 403);
        }

        return response()->stream(function() use ($election) {
            while (true) {
                $election->load(['offices.candidates' => function($query) {
                    $query->where('status', 'approved'); // Only approved candidates in results
                }, 'offices.candidates.alumni.user', 'offices.candidates.votes']);
                
                $timeDisplay = $election->getVotingTimeDisplay();

                $data = [
                    'totalAccredited' => $election->getTotalAccreditedVoters(),
                    'totalVotes' => $election->getTotalVotes(),
                    'voterTurnout' => number_format(($election->getTotalVotes() / max($election->getTotalAccreditedVoters(), 1)) * 100, 1),
                    'timeRemaining' => $timeDisplay['value'],
                    'timeRemainingLabel' => $timeDisplay['label'],
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
        if (!in_array($election->status, ['voting', 'incomplete', 'completed', 'archived'])) {
            return back()->with('error', 'Results are not available for this election yet.');
        }

        // Load election data with necessary relationships
        $election->load(['offices.candidates.votes']);

        // Calculate basic statistics
        $totalAccredited = $election->getTotalAccreditedVoters();
        $totalVotes = $election->getTotalVotes();
        $voterTurnout = $totalAccredited > 0 ? round(($totalVotes / $totalAccredited) * 100, 2) : 0;

        $timeDisplay = $election->getVotingTimeDisplay();
        $timeRemaining = $timeDisplay['is_countdown'] ? $timeDisplay['value'] : null;
        $timeRemainingLabel = $timeDisplay['label'];
        $votingEndedAt = !$timeDisplay['is_countdown'] && $timeDisplay['label'] === 'Voting Ended'
            ? $timeDisplay['value']
            : null;

        return view('elcom.elections.basic-results', compact(
            'election',
            'totalAccredited',
            'totalVotes',
            'voterTurnout',
            'timeRemaining',
            'timeRemainingLabel',
            'votingEndedAt'
        ));
    }

    /**
     * Verify the authenticity of an election certificate.
     */
    public function verifyCertificate(Election $election, ElectionOffice $office, Candidate $winner, string $code)
    {
        if ((int) $office->election_id !== (int) $election->id) {
            return $this->invalidCertificate('This certificate does not match the election record.');
        }

        if ((int) $winner->election_office_id !== (int) $office->id) {
            return $this->invalidCertificate('This certificate does not match the office record.');
        }

        $expectedCode = strtoupper(substr(md5($election->id . $office->id . $winner->id), 0, 8));

        if ($code !== $expectedCode) {
            return $this->invalidCertificate('This certificate appears to be invalid or has been tampered with.');
        }

        if (!$this->resultService->officeHasDeclaredWinner($office)) {
            return $this->invalidCertificate('No winner has been officially declared for this office.');
        }

        $isDeclaredWinner = ElectionResult::where('election_id', $election->id)
            ->where('election_office_id', $office->id)
            ->where('candidate_id', $winner->id)
            ->where('is_winner', true)
            ->exists();

        if (!$isDeclaredWinner) {
            return $this->invalidCertificate('This candidate is not the declared winner for this office.');
        }

        if ($office->winner_candidate_id && (int) $office->winner_candidate_id !== (int) $winner->id) {
            return $this->invalidCertificate('This certificate does not match the declared winner for this office.');
        }

        if (!in_array($election->status, ['completed', 'archived'])) {
            return $this->invalidCertificate('Certificates are only valid for fully completed elections.');
        }

        $declaredResult = ElectionResult::where('election_id', $election->id)
            ->where('election_office_id', $office->id)
            ->where('candidate_id', $winner->id)
            ->where('is_winner', true)
            ->first();

        return view('elcom.elections.certificate-verification', [
            'isValid' => true,
            'election' => $election,
            'office' => $office,
            'winner' => $winner->loadMissing('alumni.user'),
            'certificateNumber' => $code,
            'issueDate' => ($declaredResult?->declared_at ?? now())->format('F j, Y'),
        ]);
    }

    protected function invalidCertificate(string $message)
    {
        return view('elcom.elections.certificate-verification', [
            'isValid' => false,
            'message' => $message,
        ]);
    }

    public function archive(Election $election)
    {
        $this->authorize('archive', $election);

        try {
            $this->archiveService->archive($election, Auth::user());

            return redirect()
                ->route('elcom.elections.show', $election)
                ->with('success', 'Election has been archived. Historical data is now read-only.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function newCycle()
    {
        if (!Election::canStartNewCycle()) {
            return redirect()
                ->route('elcom.elections.index')
                ->with('error', 'Archive the completed election before starting a new cycle.');
        }

        $sourceElections = Election::archived()
            ->with('offices')
            ->orderByDesc('election_year')
            ->get();

        return view('elcom.elections.new-cycle', compact('sourceElections'));
    }

    public function storeNewCycle(Request $request)
    {
        $this->authorize('startCycle', Election::class);

        try {
            $validated = $request->validate([
                'clone_from_election_id' => 'nullable|exists:elections,id',
                'title' => 'required|string|max:255',
                'election_year' => 'required|integer|min:2000|max:2100|unique:elections,election_year',
                'cycle_label' => 'nullable|string|max:100',
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
            ]);

            $source = null;
            if (!empty($validated['clone_from_election_id'])) {
                $source = Election::archived()->with('offices')->findOrFail($validated['clone_from_election_id']);
            }

            $election = $this->cycleService->createFromStructure($source, $validated);

            return redirect()
                ->route('elcom.elections.show', $election)
                ->with('success', 'New election cycle created successfully. Review offices and dates before starting EOI.');
        } catch (ElectionImmutableException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to start new cycle: ' . $e->getMessage());
        }
    }

    public function rejectedCandidates(Election $election)
    {
        $rejected = $election->candidates()
            ->where('status', 'rejected')
            ->with(['alumni.user', 'office', 'screener'])
            ->orderBy('screened_at', 'desc')
            ->get();

        return view('elcom.elections.rejected-candidates', compact('election', 'rejected'));
    }

    public function printRejectedCandidates(Election $election)
    {
        $rejected = $election->candidates()
            ->where('status', 'rejected')
            ->with(['alumni.user', 'office', 'screener'])
            ->get()
            ->sortBy(fn ($c) => $c->office?->title);

        return view('elcom.elections.print-rejected-candidates', compact('election', 'rejected'));
    }

    public function exportRejectedCandidates(Election $election): StreamedResponse
    {
        $rejected = $election->candidates()
            ->where('status', 'rejected')
            ->with(['alumni.user', 'office', 'screener'])
            ->orderBy('screened_at', 'desc')
            ->get();

        $filename = 'rejected-candidates-' . ($election->election_year ?? $election->id) . '.csv';

        return response()->streamDownload(function () use ($rejected) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Name', 'Matric', 'Office', 'Rejected At', 'Screened By', 'Rejection Reason']);

            foreach ($rejected as $candidate) {
                fputcsv($handle, [
                    $candidate->alumni?->user?->name,
                    $candidate->alumni?->matriculation_number,
                    $candidate->office?->title,
                    $candidate->screened_at?->format('Y-m-d H:i'),
                    $candidate->screener?->name,
                    $candidate->rejection_reason,
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function canScreenCandidates(Election $election): bool
    {
        return in_array($election->status, ['draft', 'eoi', 'eoi_closed', 'accreditation'], true);
    }

    private function assertOfficeBelongsToElection(Election $election, ElectionOffice $office): void
    {
        if ((int) $office->election_id !== (int) $election->id) {
            abort(404);
        }
    }

    private function assertCandidateBelongsToOffice(Election $election, ElectionOffice $office, Candidate $candidate): void
    {
        if ((int) $candidate->election_office_id !== (int) $office->id
            || (int) $candidate->election_id !== (int) $election->id) {
            abort(404);
        }
    }

    private function officeCandidatesViewData(Election $election, ElectionOffice $office): array
    {
        $this->assertOfficeBelongsToElection($election, $office);

        $candidates = $office->candidates()
            ->with(['alumni.user', 'agent'])
            ->orderByDesc('created_at')
            ->get();

        return [
            'election' => $election,
            'office' => $office,
            'candidates' => $candidates,
            'canScreen' => $this->canScreenCandidates($election),
            'canAssignAgents' => $election->status === 'accreditation',
            'pendingCount' => $candidates->where('status', Candidate::STATUS_PENDING)->count(),
            'awaitingScreeningCount' => $candidates->where('status', Candidate::STATUS_PAID_AWAITING_SCREENING)->count(),
            'approvedCount' => $candidates->where('status', 'approved')->count(),
            'rejectedCount' => $candidates->where('status', 'rejected')->count(),
        ];
    }
}