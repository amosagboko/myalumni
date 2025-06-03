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

class AlumniElectionController extends Controller
{
    /**
     * Show a list of elections the alumni is eligible for.
     */
    public function index()
    {
        $alumni = Auth::user()->alumni;
        $now = now();
        $elections = Election::where('status', '!=', 'draft')
            ->where(function($query) use ($now) {
                // Show elections that are in EOI period
                $query->where('status', 'eoi')
                    // OR elections between accreditation_start and voting_end
                    ->orWhere(function($q) use ($now) {
                        $q->where('accreditation_start', '<=', $now)
                          ->where('voting_end', '>=', $now);
                    });
            })
            ->orderBy('accreditation_start', 'desc')
            ->get();
        return view('alumni.elections.index', compact('elections'));
    }

    /**
     * Show the accreditation page for an election.
     */
    public function accreditation(Election $election)
    {
        // Show accreditation form or status for this election
        return view('alumni.elections.accreditation', compact('election'));
    }

    /**
     * Show the voting page for an election.
     */
    public function vote(Election $election)
    {
        // Show voting form for this election
        $offices = $election->offices()->with('candidates.alumni')->get();
        $totalAccredited = $election->getTotalAccreditedVoters();
        
        // Calculate time remaining
        $timeRemaining = null;
        if ($election->status === 'voting' && now()->between($election->voting_start, $election->voting_end)) {
            $timeRemaining = $election->voting_end->diffForHumans(['parts' => 2]);
        }
        
        return view('alumni.elections.vote', compact('election', 'offices', 'totalAccredited', 'timeRemaining'));
    }

    /**
     * Show the results for an election.
     */
    public function results(Election $election)
    {
        // Show results for this election
        $results = $election->results()->with(['office', 'candidate.alumni'])->get();
        return view('alumni.elections.results', compact('election', 'results'));
    }

    /**
     * Show the expression of interest form for a specific office in an election.
     */
    public function expressionOfInterestForm(Election $election, ElectionOffice $office)
    {
        $alumni = Auth::user()->alumni;

        // Check if EOI period is active
        if (!$election->isEoiPeriodActive()) {
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

        // Check if alumni is eligible to express interest
        if (!$alumni->isEligibleToExpressInterest()) {
            if ($alumni->hasExpressedInterest()) {
                $currentInterest = $alumni->getCurrentExpressionOfInterest();
                return redirect()
                    ->route('alumni.elections')
                    ->with('error', 'You have already expressed interest for ' . $currentInterest->office->title . '. You can only express interest in one position at a time.');
            }

            if (!$alumni->getActiveFees()->every(fn($fee) => $fee->isPaid())) {
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
        $alumni = Auth::user()->alumni;

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
        $alumni = Auth::user()->alumni;

        // First check if they already have an expression of interest
        if ($alumni->hasExpressedInterest()) {
            $currentInterest = $alumni->getCurrentExpressionOfInterest();
            // Clear any preview data
            session()->forget('eoi_preview');
            return redirect()
                ->route('alumni.elections')
                ->with('error', 'You have already expressed interest for ' . $currentInterest->office->title . '. You can only express interest in one position at a time.');
        }

        // Check for any pending or successful EOI transactions
        $existingTransaction = Transaction::where('alumni_id', $alumni->id)
            ->whereHas('feeTemplate', function($query) use ($office) {
                $query->where('fee_type_id', $office->fee_type_id);
            })
            ->whereIn('status', ['pending', 'success'])
            ->first();

        if ($existingTransaction) {
            if ($existingTransaction->status === 'pending') {
                // Clear any preview data
                session()->forget('eoi_preview');
                return redirect()
                    ->route('alumni.payments.process', $existingTransaction)
                    ->with('info', 'You have a pending payment for this position. Please complete the payment to continue.');
            } else {
                // Clear any preview data
                session()->forget('eoi_preview');
                return redirect()
                    ->route('alumni.elections')
                    ->with('error', 'You have already paid for this position. Please wait for the screening process.');
            }
        }

        // Then check other eligibility criteria
        if (!$alumni->getActiveFees()->every(fn($fee) => $fee->isPaid())) {
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

            // Create a pending transaction for the screening fee
            $transaction = Transaction::create([
                'alumni_id' => $alumni->id,
                'fee_template_id' => $screeningFee->id,
                'amount' => $screeningFee->amount,
                'status' => 'pending',
                'payment_reference' => 'EOI-' . strtoupper(uniqid()),
                'is_test_mode' => true, // Force test mode for screening fees
                'payment_provider' => 'credo',
                'metadata' => json_encode([
                    'election_id' => $election->id,
                    'office_id' => $office->id,
                    'passport' => $passportPath,
                    'documents' => $documentPaths,
                    'manifesto' => $data['manifesto'] ?? null,
                    'is_eoi' => true, // Flag to identify EOI transactions
                ])
            ]);

            // Store EOI candidate details in session, keyed by payment_reference
            session(['eoi_candidate_' . $transaction->payment_reference => [
                'election_id' => $election->id,
                'office_id' => $office->id,
                'passport' => $passportPath,
                'documents' => $documentPaths,
                'manifesto' => $data['manifesto'] ?? null,
            ]]);

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
        $activeElection = \App\Models\Election::where('status', 'eoi')->orderBy('accreditation_start', 'desc')->first();
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
        $alumni = Auth::user()->alumni;

        // Check if accreditation period is active
        if (!$election->status === 'accreditation' || 
            !now()->between($election->accreditation_start, $election->accreditation_end)) {
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
        $alumni = Auth::user()->alumni;

        // Check if voting period is active
        if (!$election->status === 'voting' || 
            !now()->between($election->voting_start, $election->voting_end)) {
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

        // Verify that each vote is for a valid candidate in this election
        $offices = $election->offices()->with(['candidates.alumni.user'])->get();
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
        $alumni = Auth::user()->alumni;

        // Check if voting period is active
        if (!$election->status === 'voting' || 
            !now()->between($election->voting_start, $election->voting_end)) {
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