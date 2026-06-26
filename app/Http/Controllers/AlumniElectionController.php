<?php

namespace App\Http\Controllers;

use App\Models\Election;
use App\Models\ElectionOffice;
use App\Models\Candidate;
use App\Models\ElectionResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Spatie\Activitylog\Facades\Activity;
use App\Models\FeeTemplate;
use App\Services\AlumniElectionParticipationService;
use App\Services\PaymentCompletionService;

class AlumniElectionController extends Controller
{
    public function __construct(
        private AlumniElectionParticipationService $participationService,
        private PaymentCompletionService $paymentCompletion,
    ) {}

    private function redirectIfArchived(Election $election)
    {
        if ($election->isArchived()) {
            return redirect()
                ->route('alumni.elections.results', $election)
                ->with('info', 'This election has been archived. You can view historical results only.');
        }

        return null;
    }

    private function redirectIfOfficeApplicantSlotsFull(ElectionOffice $office)
    {
        if (!$office->hasAvailableApplicantSlots()) {
            return redirect()
                ->route('alumni.elections')
                ->with('error', "Expression of Interest for {$office->title} is closed. All applicant slots have been filled.");
        }

        return null;
    }

    private function redirectIfUnpaidDues($alumni)
    {
            if (!$alumni->hasPaidAllActiveFees()) {
            return redirect()
                ->route('alumni.payments.index')
                ->with('error', 'You must complete all pending annual dues before voting.');
        }

        return null;
    }

    private function redirectIfPendingEoiPayment($alumni, Election $election, ElectionOffice $office)
    {
        $existingTransaction = $this->paymentCompletion->findPendingEoiTransaction(
            $alumni->id,
            $election->id,
            $office->id,
            $office->fee_type_id
        );

        if ($existingTransaction) {
            return redirect()
                ->route('alumni.payments.process', $existingTransaction)
                ->with('info', 'You have a pending payment for this position. Please complete the payment to continue.');
        }

        return null;
    }

    private function redirectIfDuplicateEoiApplication($alumni, Election $election, ElectionOffice $office)
    {
        $existingCandidate = Candidate::where('alumni_id', $alumni->id)
            ->where('election_id', $election->id)
            ->where('election_office_id', $office->id)
            ->activeApplicants()
            ->first();

        if (!$existingCandidate) {
            return null;
        }

        if (!$existingCandidate->has_paid_screening_fee) {
            if ($redirect = $this->redirectIfPendingEoiPayment($alumni, $election, $office)) {
                return $redirect;
            }
        }

        return redirect()
            ->route('alumni.elections.expression-of-interest.status')
            ->with('info', 'You already have an application for this position.');
    }

    /**
     * Show a list of elections the alumni is eligible for.
     */
    public function index()
    {
        $alumni = Auth::user()->alumni;

        $byElection = Election::query()
            ->where('election_type', 'by_election')
            ->where('is_active', true)
            ->whereNotIn('status', ['completed', 'archived'])
            ->with(['offices', 'parentElection'])
            ->first();

        $currentElection = $byElection ?? Election::query()
            ->where('is_active', true)
            ->where(function ($query) {
                $query->operational()
                    ->orWhere('status', 'incomplete');
            })
            ->with('offices')
            ->first();

        $parentElection = $byElection?->parentElection;

        $pastElections = Election::query()
            ->historical()
            ->when($currentElection, fn ($query) => $query->where('id', '!=', $currentElection->id))
            ->when($parentElection, fn ($query) => $query->where('id', '!=', $parentElection->id))
            ->with('offices')
            ->orderByDesc('election_year')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $participation = null;
        $phaseLabel = null;
        $actions = null;

        if ($currentElection && $alumni) {
            $participation = $this->participationService->participationFor($currentElection, $alumni);
            $phaseLabel = $this->participationService->phaseLabel($currentElection);
            $actions = $this->participationService->actionsFor($currentElection, $alumni, $participation);
        }

        return view('alumni.elections.index', compact(
            'currentElection',
            'pastElections',
            'participation',
            'phaseLabel',
            'actions',
            'parentElection'
        ));
    }

    /**
     * Show the accreditation page for an election.
     */
    public function accreditation(Election $election)
    {
        if ($redirect = $this->redirectIfArchived($election)) {
            return $redirect;
        }

        return view('alumni.elections.accreditation', compact('election'));
    }

    /**
     * Show the voting page for an election.
     */
    public function vote(Election $election)
    {
        if ($redirect = $this->redirectIfArchived($election)) {
            return $redirect;
        }

        $alumni = Auth::user()->alumni;

        if ($redirect = $this->redirectIfUnpaidDues($alumni)) {
            return $redirect;
        }

        // Show voting form for this election - ONLY approved candidates
        $offices = $election->offices()->with(['candidates' => function($query) {
            $query->where('status', 'approved'); // Only approved candidates appear on ballot
        }, 'candidates.alumni'])->get();
        
        $totalAccredited = $election->getTotalAccreditedVoters();
        $totalSubscribed = $election->getTotalSubscribedUsers();
        $totalExempted = $election->getTotalExemptedUsers();
        
        // Calculate time remaining
        $timeRemaining = null;
        if ($election->canAcceptVoteSubmissions()) {
            $timeRemaining = $election->voting_end->diffForHumans(['parts' => 2]);
        }
        
        return view('alumni.elections.vote', compact('election', 'offices', 'totalAccredited', 'totalSubscribed', 'totalExempted', 'timeRemaining'));
    }

    /**
     * Show the results for an election.
     */
    public function results(Election $election)
    {
        // Show results once voting has ended (incomplete or fully completed)
        if (!$election->resultsArePublished()) {
            return redirect()
                ->route('alumni.elections')
                ->with('error', 'Election results are not yet available. Results will be published after voting ends.');
        }

        // Load election data with necessary relationships - ONLY approved candidates
        $election->load(['offices.candidates' => function($query) {
            $query->where('status', 'approved');
        }, 'offices.candidates.alumni.user', 'offices.candidates.votes', 'results.candidate.alumni.user', 'results.office']);

        // Calculate basic statistics
        $totalAccredited = $election->getTotalAccreditedVoters();
        $totalVotes = $election->getTotalVotes();
        $voterTurnout = $totalAccredited > 0 ? round(($totalVotes / $totalAccredited) * 100, 2) : 0;

        // Get results for each office
        $officeResults = $election->offices->map(function ($office) {
            $candidates = $office->candidates->map(function ($candidate) {
                $votes = $candidate->votes->count();
                return [
                    'candidate' => $candidate,
                    'votes' => $votes,
                    'is_winner' => $candidate->electionResults->where('is_winner', true)->isNotEmpty()
                ];
            })->sortByDesc('votes');

            $totalOfficeVotes = $candidates->sum('votes');

            return [
                'office' => $office,
                'candidates' => $candidates->map(function ($candidate) use ($totalOfficeVotes) {
                    $percentage = $totalOfficeVotes > 0 ? round(($candidate['votes'] / $totalOfficeVotes) * 100, 1) : 0;
                    return array_merge($candidate, ['percentage' => $percentage]);
                }),
                'total_votes' => $totalOfficeVotes
            ];
        });

        return view('alumni.elections.results', compact('election', 'officeResults', 'totalAccredited', 'totalVotes', 'voterTurnout'))
            ->with('resolution', app(\App\Services\ElectionResultService::class)->getResolutionSummary($election));
    }

    /**
     * Show the expression of interest form for a specific office in an election.
     */
    public function expressionOfInterestForm(Election $election, ElectionOffice $office)
    {
        if ($redirect = $this->redirectIfArchived($election)) {
            return $redirect;
        }

        $alumni = Auth::user()->alumni;

        // Check if EOI period is active
        if (!$election->canAcceptEoiSubmissions()) {
            if ($election->hasEoiEnded()) {
                return redirect()
                    ->route('alumni.elections')
                    ->with('error', 'The Expression of Interest period has ended.');
            } else {
                return redirect()
                    ->route('alumni.elections')
                    ->with('error', 'The Expression of Interest period has not started yet.');
            }
        }

        if ($office->isRunoffByElectionOffice()) {
            return redirect()
                ->route('alumni.elections')
                ->with('error', 'This office is in a runoff. Candidates are already on the ballot — no new applications are accepted.');
        }

        if ($redirect = $this->redirectIfOfficeApplicantSlotsFull($office)) {
            return $redirect;
        }

        if ($redirect = $this->redirectIfPendingEoiPayment($alumni, $election, $office)) {
            return $redirect;
        }

        if ($redirect = $this->redirectIfDuplicateEoiApplication($alumni, $election, $office)) {
            return $redirect;
        }

        // Check if alumni is eligible to express interest
        if (!$alumni->isEligibleToExpressInterest()) {
            if ($alumni->hasExpressedInterest()) {
                $currentInterest = $alumni->getCurrentExpressionOfInterest();
                return redirect()
                    ->route('alumni.elections')
                    ->with('error', 'You have already expressed interest for ' . $currentInterest->office->title . '. You can only express interest in one position at a time.');
            }

            if (!$alumni->hasPaidAllActiveFees()) {
                return redirect()
                    ->route('alumni.payments.index')
                    ->with('error', 'You must complete all pending payments before expressing interest in a position.');
            }

            if (!$alumni->contact_address || !$alumni->phone_number || !$alumni->qualification_type) {
                return redirect()
                    ->route('alumni.bio-data')
                    ->with('error', 'Please complete your bio data before expressing interest in a position.');
            }
        }

        // Get the screening fee for this office
        $screeningFee = FeeTemplate::where('fee_type_id', $office->fee_type_id)
            ->where('is_active', true)
            ->where('valid_from', '<=', now())
            ->where(function ($query) {
                $query->whereNull('valid_until')->orWhere('valid_until', '>', now());
            })
            ->first();

        if (!$screeningFee) {
            return redirect()
                ->route('alumni.elections')
                ->with('error', 'Screening fee not found for this position. Please contact support.');
        }

        return view('alumni.elections.expression-of-interest', compact('election', 'office', 'screeningFee'));
    }

    /**
     * Handle the preview step of expression of interest submission.
     */
    public function previewExpressionOfInterest(Request $request, Election $election, ElectionOffice $office)
    {
        if ($redirect = $this->redirectIfArchived($election)) {
            return $redirect;
        }

        if (!$election->canAcceptEoiSubmissions()) {
            return redirect()
                ->route('alumni.elections')
                ->with('error', 'The Expression of Interest period is not open for submissions.');
        }

        if ($redirect = $this->redirectIfOfficeApplicantSlotsFull($office)) {
            return $redirect;
        }

        $alumni = Auth::user()->alumni;

        if ($redirect = $this->redirectIfPendingEoiPayment($alumni, $election, $office)) {
            return $redirect;
        }

        if ($redirect = $this->redirectIfDuplicateEoiApplication($alumni, $election, $office)) {
            return $redirect;
        }

        // Validate eligibility
        if (!$alumni->isEligibleToExpressInterest()) {
            return redirect()
                ->route('alumni.elections')
                ->with('error', 'You are not eligible to express interest at this time.');
        }

        // Validate the form
        $validated = $request->validate([
            'passport' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'manifesto' => 'nullable|string|min:100',
            'documents' => 'nullable|array',
            'documents.*' => 'nullable|file|mimes:pdf,doc,docx|max:5120', // 5MB max
        ]);

        // Get the screening fee
        $screeningFee = FeeTemplate::where('fee_type_id', $office->fee_type_id)
            ->where('is_active', true)
            ->where('valid_from', '<=', now())
            ->where(function ($query) {
                $query->whereNull('valid_until')->orWhere('valid_until', '>', now());
            })
            ->first();

        if (!$screeningFee) {
            return redirect()
                ->back()
                ->with('error', 'Screening fee not found for this position. Please contact support.');
        }

        // Store the files temporarily
        $passportPath = $request->file('passport')->store('temp/passports', 'public');
        $documentPaths = [];
        
        // Only process documents if they were uploaded
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $document) {
                $documentPaths[] = $document->store('temp/documents', 'public');
            }
        }

        // Generate a preview token
        $previewToken = encrypt([
            'passport' => $passportPath,
            'documents' => $documentPaths,
            'manifesto' => $validated['manifesto'] ?? null,
            'timestamp' => now()->timestamp
        ]);

        // Store the preview data in the session
        session([
            'eoi_preview' => [
                'token' => $previewToken,
                'expires_at' => now()->addMinutes(30)
            ]
        ]);

        return view('alumni.elections.expression-of-interest-preview', [
            'election' => $election,
            'office' => $office,
            'screeningFee' => $screeningFee,
            'manifesto' => $validated['manifesto'] ?? null,
            'documents' => $documentPaths,
            'passport' => $passportPath,
            'previewToken' => $previewToken
        ]);
    }

    /**
     * Handle final submission of the expression of interest form (with payment).
     */
    public function submitExpressionOfInterest(Request $request, Election $election, ElectionOffice $office)
    {
        if ($redirect = $this->redirectIfArchived($election)) {
            return $redirect;
        }

        if (!$election->canAcceptEoiSubmissions()) {
            session()->forget('eoi_preview');
            return redirect()
                ->route('alumni.elections')
                ->with('error', 'The Expression of Interest period is not open for submissions.');
        }

        $alumni = Auth::user()->alumni;

        if ($redirect = $this->redirectIfDuplicateEoiApplication($alumni, $election, $office)) {
            session()->forget('eoi_preview');
            return $redirect;
        }

        // First check if they already have an expression of interest
        if ($alumni->hasExpressedInterest()) {
            $currentInterest = $alumni->getCurrentExpressionOfInterest();
            // Clear any preview data
            session()->forget('eoi_preview');
            return redirect()
                ->route('alumni.elections')
                ->with('error', 'You have already expressed interest for ' . $currentInterest->office->title . '. You can only express interest in one position at a time.');
        }

        // Check for any pending EOI transactions for this election/office
        if ($redirect = $this->redirectIfPendingEoiPayment($alumni, $election, $office)) {
            session()->forget('eoi_preview');
            return $redirect;
        }

        // Then check other eligibility criteria
        if (!$alumni->hasPaidAllActiveFees()) {
            session()->forget('eoi_preview');
            return redirect()
                ->route('alumni.payments.index')
                ->with('error', 'You must complete all pending payments before expressing interest in a position.');
        }

        if (!$alumni->contact_address || !$alumni->phone_number || !$alumni->qualification_type) {
            session()->forget('eoi_preview');
            return redirect()
                ->route('alumni.bio-data')
                ->with('error', 'Please complete your bio data before expressing interest in a position.');
        }

        if ($redirect = $this->redirectIfOfficeApplicantSlotsFull($office)) {
            session()->forget('eoi_preview');
            return $redirect;
        }

        // Validate the preview token
        $previewData = session('eoi_preview');
        if (!$previewData || 
            !isset($previewData['token']) || 
            $previewData['token'] !== $request->input('preview_token') ||
            now()->isAfter($previewData['expires_at'])) {
            session()->forget('eoi_preview');
            return redirect()
                ->route('alumni.elections.expression-of-interest.form', ['election' => $election, 'office' => $office])
                ->with('error', 'Your preview session has expired. Please submit your application again.');
        }

        // Decrypt the preview data
        try {
            $data = decrypt($previewData['token']);
        } catch (\Exception $e) {
            session()->forget('eoi_preview');
            return redirect()
                ->route('alumni.elections.expression-of-interest.form', ['election' => $election, 'office' => $office])
                ->with('error', 'Invalid preview data. Please submit your application again.');
        }

        // Get the screening fee
        $screeningFee = FeeTemplate::where('fee_type_id', $office->fee_type_id)
            ->where('is_active', true)
            ->where('valid_from', '<=', now())
            ->where(function ($query) {
                $query->whereNull('valid_until')->orWhere('valid_until', '>', now());
            })
            ->first();

        if (!$screeningFee) {
            session()->forget('eoi_preview');
            return redirect()
                ->route('alumni.elections')
                ->with('error', 'Screening fee not found for this position. Please contact support.');
        }

        try {
            DB::beginTransaction();

            $office = ElectionOffice::whereKey($office->id)->lockForUpdate()->firstOrFail();

            if (!$office->hasAvailableApplicantSlots()) {
                DB::rollBack();
                session()->forget('eoi_preview');

                return redirect()
                    ->route('alumni.elections')
                    ->with('error', "Expression of Interest for {$office->title} is closed. All applicant slots have been filled.");
            }

            // Store the files temporarily
            $passportPath = str_replace('temp/', '', $data['passport']);
            Storage::disk('public')->move($data['passport'], $passportPath);

            $documentPaths = [];
            if (!empty($data['documents'])) {
                foreach ($data['documents'] as $tempPath) {
                    $newPath = str_replace('temp/', '', $tempPath);
                    Storage::disk('public')->move($tempPath, $newPath);
                    $documentPaths[] = $newPath;
                }
            }

            // Create the candidate record immediately (before payment)
            $candidate = Candidate::create([
                'election_id' => $election->id,
                'election_office_id' => $office->id,
                'alumni_id' => $alumni->id,
                'has_paid_screening_fee' => false,
                'manifesto' => $data['manifesto'] ?? null,
                'passport' => $passportPath,
                'documents' => $documentPaths,
                'status' => 'pending',
            ]);

            // Create a pending transaction for the screening fee, store candidate_id in metadata
            $metadata = [
                'election_id' => $election->id,
                'office_id' => $office->id,
                'candidate_id' => $candidate->id,
                'passport' => $passportPath,
                'documents' => $documentPaths,
                'manifesto' => $data['manifesto'] ?? null,
                'is_eoi' => true, // Flag to identify EOI transactions
            ];
            $transaction = Transaction::create([
                'alumni_id' => $alumni->id,
                'fee_template_id' => $screeningFee->id,
                'amount' => $screeningFee->amount,
                'status' => 'pending',
                'payment_reference' => 'EOI-' . strtoupper(uniqid()),
                'is_test_mode' => (bool) config('services.credocentral.test_mode', false),
                'payment_provider' => 'credo',
                'metadata' => $metadata,
                'payment_details' => [
                    'eoi' => $metadata,
                ],
            ]);

            // Store EOI candidate details in session, keyed by payment_reference (for redundancy)
            session(['eoi_candidate_' . $transaction->payment_reference => $metadata]);

            // Clear the preview session BEFORE committing the transaction
            session()->forget('eoi_preview');

            DB::commit();

            // Redirect to payment page
            return redirect()
                ->route('alumni.payments.process', $transaction)
                ->with('success', 'Please complete the payment to finalize your application.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Log the error with more context
            Log::error('Failed to submit expression of interest', [
                'error' => $e->getMessage(),
                'alumni_id' => $alumni->id,
                'office_id' => $office->id,
                'election_id' => $election->id,
                'trace' => $e->getTraceAsString()
            ]);

            // Clean up any uploaded files
            if (isset($data['passport'])) {
                Storage::disk('public')->delete($data['passport']);
            }
            if (isset($data['documents'])) {
                foreach ($data['documents'] as $path) {
                    Storage::disk('public')->delete($path);
                }
            }

            // Clear the preview session
            session()->forget('eoi_preview');

            return redirect()
                ->route('alumni.elections.expression-of-interest.form', ['election' => $election, 'office' => $office])
                ->with('error', 'Failed to submit expression of interest. Please try again.');
        }
    }

    /**
     * Show the status of the alumni's expression of interest.
     */
    public function expressionOfInterestStatus()
    {
        $alumni = Auth::user()->alumni;
        $expressionOfInterest = $alumni->getCurrentExpressionOfInterest();

        if ($expressionOfInterest) {
            // Show EOI details/status view
            return view('alumni.elections.expression-of-interest-status', compact('expressionOfInterest'));
        }

        // If no EOI found, redirect to EOI form for the current election/office if possible
        // Try to find an active election and office for EOI
        $activeElection = \App\Models\Election::whereIn('status', ['eoi', 'eoi_closed'])
            ->orderBy('accreditation_start', 'desc')
            ->first();
        if ($activeElection) {
            $office = $activeElection->offices()->first();
            if ($office) {
                return redirect()->route('alumni.elections.expression-of-interest.form', ['election' => $activeElection->id, 'office' => $office->id])
                    ->with('info', 'You have not expressed interest yet. Please complete the EOI form.');
            }
        }
        // Fallback: redirect to elections list
        return redirect()->route('alumni.elections')
            ->with('info', 'You have not expressed interest in any position yet.');
    }

    /**
     * Show published (approved) candidates for an election/office.
     */
    public function publishedCandidates(Election $election, ElectionOffice $office)
    {
        $candidates = $office->candidates()->where('status', 'approved')->with('alumni.user')->get();
        return view('alumni.elections.published-candidates', compact('election', 'office', 'candidates'));
    }

    /**
     * Submit accreditation for an election.
     */
    public function submitAccreditation(Request $request, Election $election)
    {
        if ($redirect = $this->redirectIfArchived($election)) {
            return $redirect;
        }

        $alumni = Auth::user()->alumni;

        // Check if accreditation period is active
        if (!$election->canAcceptAccreditationSubmissions()) {
            return back()->with('error', 'Accreditation period is not active.');
        }

        // Check eligibility
        if (!$election->isAlumniEligibleToVote($alumni)) {
            return back()->with('error', 'You are not eligible for accreditation. Please check your eligibility status.');
        }

        // Check if already accredited
        if ($election->accreditedVoters()->where('alumni_id', $alumni->id)->exists()) {
            return back()->with('error', 'You are already accredited for this election.');
        }

        try {
            DB::beginTransaction();

            // Create accreditation record
            $election->accreditedVoters()->create([
                'alumni_id' => $alumni->id,
                'accredited_at' => now(),
                'has_voted' => false
            ]);

            // Log the activity using the Activity facade
            Activity::performedOn($election)
                ->causedBy($alumni->user)
                ->withProperties([
                    'alumni_id' => $alumni->id,
                    'accredited_at' => now()
                ])
                ->log('Alumni accredited for election');

            DB::commit();

            return redirect()
                ->route('alumni.elections.accreditation', $election)
                ->with('success', 'You have been successfully accredited for this election.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Accreditation submission failed', [
                'election_id' => $election->id,
                'alumni_id' => $alumni->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to submit accreditation. Please try again.');
        }
    }

    /**
     * Preview votes before final submission.
     */
    public function previewVote(Request $request, Election $election)
    {
        if ($redirect = $this->redirectIfArchived($election)) {
            return $redirect;
        }

        $alumni = Auth::user()->alumni;

        if ($redirect = $this->redirectIfUnpaidDues($alumni)) {
            return $redirect;
        }

        // Check if voting period is active
        if (!$election->canAcceptVoteSubmissions()) {
            return back()->with('error', 'Voting period is not active.');
        }

        // Check if user is accredited
        $accreditedVoter = $election->accreditedVoters()
            ->where('alumni_id', $alumni->id)
            ->first();

        if (!$accreditedVoter) {
            return back()->with('error', 'You are not accredited for this election.');
        }

        // Check if user has already voted
        if ($accreditedVoter->has_voted) {
            return back()->with('error', 'You have already cast your vote in this election.');
        }

        // Validate the votes
        $validated = $request->validate([
            'votes' => 'required|array',
            'votes.*' => 'required|exists:candidates,id'
        ]);

        // Verify that each vote is for a valid candidate in this election - ONLY approved candidates
        $offices = $election->offices()->with(['candidates' => function($query) {
            $query->where('status', 'approved'); // Only approved candidates can receive votes
        }, 'candidates.alumni.user'])->get();
        $validCandidates = $offices->flatMap->candidates->pluck('id')->toArray();

        foreach ($validated['votes'] as $officeId => $candidateId) {
            if (!in_array($candidateId, $validCandidates)) {
                return back()->with('error', 'Invalid candidate selection detected.');
            }
        }

        // Get the selected candidates with their details
        $selectedCandidates = [];
        foreach ($validated['votes'] as $officeId => $candidateId) {
            $office = $offices->firstWhere('id', $officeId);
            $candidate = $office->candidates->firstWhere('id', $candidateId);
            
            $selectedCandidates[] = [
                'office' => $office,
                'candidate' => $candidate
            ];
        }

        // Store the votes in the session for final submission
        session(['vote_preview' => [
            'votes' => $validated['votes'],
            'expires_at' => now()->addMinutes(30)
        ]]);

        return view('alumni.elections.vote-preview', compact('election', 'selectedCandidates'));
    }

    /**
     * Submit votes for an election.
     */
    public function submitVote(Request $request, Election $election)
    {
        if ($redirect = $this->redirectIfArchived($election)) {
            return $redirect;
        }

        $alumni = Auth::user()->alumni;

        if ($redirect = $this->redirectIfUnpaidDues($alumni)) {
            return $redirect;
        }

        // Check if voting period is active
        if (!$election->canAcceptVoteSubmissions()) {
            return back()->with('error', 'Voting period is not active.');
        }

        // Check if user is accredited
        $accreditedVoter = $election->accreditedVoters()
            ->where('alumni_id', $alumni->id)
            ->first();

        if (!$accreditedVoter) {
            return back()->with('error', 'You are not accredited for this election.');
        }

        // Check if user has already voted
        if ($accreditedVoter->has_voted) {
            return back()->with('error', 'You have already cast your vote in this election.');
        }

        // Get the preview data from session
        $previewData = session('vote_preview');
        if (!$previewData || 
            !isset($previewData['votes']) || 
            now()->isAfter($previewData['expires_at'])) {
            return redirect()
                ->route('alumni.elections.vote', $election)
                ->with('error', 'Your vote preview has expired. Please submit your votes again.');
        }

        try {
            DB::beginTransaction();

            // Record the votes from the preview data
            foreach ($previewData['votes'] as $officeId => $candidateId) {
                $election->votes()->create([
                    'election_office_id' => $officeId,
                    'candidate_id' => $candidateId,
                    'accredited_voter_id' => $accreditedVoter->id
                ]);
            }

            // Mark the voter as having voted
            $accreditedVoter->markAsVoted();

            // Clear the preview session
            session()->forget('vote_preview');

            // Log the activity
            Activity::performedOn($election)
                ->causedBy($alumni->user)
                ->withProperties([
                    'alumni_id' => $alumni->id,
                    'voted_at' => now()
                ])
                ->log('Alumni cast vote in election');

            DB::commit();

            return redirect()
                ->route('alumni.elections.vote', $election)
                ->with('success', 'Your vote has been successfully recorded.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Vote submission failed', [
                'election_id' => $election->id,
                'alumni_id' => $alumni->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to submit vote. Please try again.');
        }
    }
} 