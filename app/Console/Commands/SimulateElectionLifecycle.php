<?php

namespace App\Console\Commands;

use App\Models\AccreditedVoter;
use App\Models\Alumni;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\ElectionOffice;
use App\Models\FeeTemplate;
use App\Models\FeeType;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Vote;
use App\Services\ElectionArchiveService;
use App\Services\ElectionCycleService;
use App\Services\ElectionResultService;
use App\Services\PaymentCompletionService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SimulateElectionLifecycle extends Command
{
    protected $signature = 'election:simulate-lifecycle {--fresh : Archive conflicting elections before running}';

    protected $description = 'Run a full end-to-end election lifecycle simulation';

    private array $log = [];

    public function handle(
        ElectionCycleService $cycleService,
        PaymentCompletionService $paymentService,
        ElectionResultService $resultService,
        ElectionArchiveService $archiveService,
    ): int {
        if (! app()->environment('local')) {
            $this->error('This simulation command only runs in the local environment.');

            return self::FAILURE;
        }

        $this->info('=== Election Lifecycle Simulation ===');
        $this->newLine();

        try {
            if ($this->option('fresh')) {
                $this->prepareEnvironment();
            }

            $elcom = User::where('email', 'elcom@test.com')->firstOrFail();
            Auth::login($elcom);

            $election = $this->setupSimulationElection();
            $offices = $election->offices()->orderBy('id')->get();
            $feeType = FeeType::where('code', 'screening_fee')->firstOrFail();
            $feeTemplate = FeeTemplate::where('fee_type_id', $feeType->id)->where('is_active', true)->firstOrFail();

            $candidateAlumni = $this->activeAlumni(['alumni3@test.com', 'alumni6@test.com', 'alumni18@test.com']);
            $voterAlumni = $this->activeAlumni(['alumni9@test.com', 'alumni12@test.com', 'alumni15@test.com']);

            // Phase 1: Draft setup
            $this->assertStep(
                '1. Draft election ready',
                $election->status === 'draft' && $offices->count() >= 3,
                "status={$election->status}, offices={$offices->count()}"
            );

            // Phase 2: Start EOI
            $cycleService->beginOperationalPhase($election->fresh());
            $election->fresh()->startEoi();
            $election = $election->fresh();
            $this->assertStep('2. EOI opened', $election->status === 'eoi', "status={$election->status}");

            // Phase 3: Alumni EOI + payment
            $candidates = [];
            foreach ($candidateAlumni as $index => $alumni) {
                $office = $offices[$index];
                $candidate = Candidate::create([
                    'election_id' => $election->id,
                    'election_office_id' => $office->id,
                    'alumni_id' => $alumni->id,
                    'has_paid_screening_fee' => false,
                    'manifesto' => 'Simulation manifesto for '.$office->title,
                    'passport' => 'simulation/passport-'.$alumni->id.'.jpg',
                    'documents' => [],
                    'status' => Candidate::STATUS_PENDING,
                ]);

                $transaction = Transaction::create([
                    'alumni_id' => $alumni->id,
                    'fee_template_id' => $feeTemplate->id,
                    'amount' => $feeTemplate->amount,
                    'status' => 'pending',
                    'payment_reference' => 'EOI-SIM-'.strtoupper(uniqid()),
                    'is_test_mode' => true,
                    'payment_provider' => 'credo',
                    'metadata' => [
                        'election_id' => $election->id,
                        'office_id' => $office->id,
                        'candidate_id' => $candidate->id,
                        'is_eoi' => true,
                    ],
                    'payment_details' => [
                        'eoi' => [
                            'election_id' => $election->id,
                            'office_id' => $office->id,
                            'candidate_id' => $candidate->id,
                        ],
                    ],
                ]);

                $paymentService->complete($transaction->fresh());
                $candidate = $candidate->fresh();
                $candidates[] = $candidate;

                $this->assertStep(
                    "3. EOI payment ({$alumni->user->email} → {$office->title})",
                    $candidate->status === Candidate::STATUS_PAID_AWAITING_SCREENING
                        && $candidate->has_paid_screening_fee,
                    "status={$candidate->status}"
                );
            }

            // Phase 4: Screening
            foreach ($candidates as $candidate) {
                Auth::login($elcom);
                $candidate->approve();
                $candidate = $candidate->fresh();
                $this->assertStep(
                    "4. Screening approved (candidate #{$candidate->id})",
                    $candidate->status === Candidate::STATUS_APPROVED,
                    "status={$candidate->status}"
                );
            }

            // Phase 5: Close EOI, open accreditation
            $election->fresh()->endEoi();
            $election = $election->fresh();
            $this->assertStep('5a. EOI closed', $election->status === 'eoi_closed', "status={$election->status}");

            $cycleService->beginOperationalPhase($election);
            $election->update([
                'status' => 'accreditation',
                'accreditation_start' => now()->subHour(),
                'accreditation_end' => now()->addDays(2),
            ]);
            $election = $election->fresh();
            $this->assertStep(
                '5b. Accreditation opened',
                $election->status === 'accreditation' && $election->canAcceptAccreditationSubmissions(),
                "status={$election->status}"
            );

            // Phase 6: Alumni accreditation
            $accreditedVoters = [];
            foreach ($voterAlumni as $alumni) {
                Auth::login($alumni->user);
                $voter = $election->accreditedVoters()->create([
                    'alumni_id' => $alumni->id,
                    'accredited_at' => now(),
                    'has_voted' => false,
                ]);
                $accreditedVoters[] = $voter;
                $this->assertStep(
                    "6. Accredited voter ({$alumni->user->email})",
                    $election->accreditedVoters()->where('alumni_id', $alumni->id)->exists(),
                    'accreditation record created'
                );
            }

            // Phase 7: Voting
            Auth::login($elcom);
            $election->fresh()->endAccreditation();
            $election = $election->fresh();

            if (! $election->canStartVoting()) {
                throw new \RuntimeException('Cannot start voting: accreditation may still be active or voting window not open.');
            }

            $cycleService->beginOperationalPhase($election);
            $election->update(['status' => 'voting']);
            $election = $election->fresh();
            $this->assertStep(
                '7a. Voting opened',
                $election->status === 'voting' && $election->canAcceptVoteSubmissions(),
                "status={$election->status}"
            );

            foreach ($accreditedVoters as $voter) {
                $alumni = $voter->alumni;
                Auth::login($alumni->user);

                foreach ($offices as $office) {
                    $approved = $office->candidates()->where('status', Candidate::STATUS_APPROVED)->first();
                    if (! $approved) {
                        continue;
                    }

                    Vote::create([
                        'election_id' => $election->id,
                        'election_office_id' => $office->id,
                        'candidate_id' => $approved->id,
                        'accredited_voter_id' => $voter->id,
                    ]);
                }

                $voter->markAsVoted();
                $this->assertStep(
                    "7b. Vote cast ({$alumni->user->email})",
                    $voter->fresh()->has_voted && $election->votes()->where('accredited_voter_id', $voter->id)->exists(),
                    'votes recorded'
                );
            }

            // Phase 8: Declare results
            $election->update(['voting_end' => now()->subMinute()]);
            $election = $election->fresh();
            $this->assertStep(
                '8a. Voting window ended',
                $election->canEndVoting(),
                'canEndVoting=true'
            );

            $summary = $resultService->declareResults($election);
            $election = $election->fresh();
            $this->assertStep(
                '8b. Results declared',
                in_array($election->status, ['completed', 'incomplete'], true),
                "status={$election->status}, decided={$summary['decided']}, tied={$summary['tied']}, uncontested={$summary['uncontested']}"
            );

            $resultsCount = $election->results()->count();
            $this->assertStep(
                '8c. Election results stored',
                $resultsCount > 0,
                "results={$resultsCount}"
            );

            // Phase 9: Archive (only if completed)
            if ($election->status === 'completed') {
                Auth::login($elcom);
                $archiveService->archive($election, $elcom);
                $election = $election->fresh();
                $this->assertStep(
                    '9. Election archived',
                    $election->status === 'archived' && ! $election->is_active,
                    "status={$election->status}, archived_at={$election->archived_at}"
                );
            } else {
                $this->warn('Step 9 skipped: election ended incomplete (ties/uncontested). Check resolution flow.');
                $this->log[] = ['step' => '9. Election archived', 'pass' => null, 'detail' => 'skipped — incomplete'];
            }

            $this->newLine();
            $this->printSummary();

            $failed = collect($this->log)->contains(fn ($row) => $row['pass'] === false);

            return $failed ? self::FAILURE : self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Simulation failed: '.$e->getMessage());
            $this->line($e->getTraceAsString());

            return self::FAILURE;
        }
    }

    private function prepareEnvironment(): void
    {
        $this->warn('Preparing environment: archiving conflicting operational elections...');

        Election::query()
            ->whereIn('status', ['eoi', 'eoi_closed', 'accreditation', 'voting'])
            ->update([
                'status' => 'archived',
                'is_active' => false,
                'archived_at' => now(),
            ]);

        Election::query()
            ->where('status', 'completed')
            ->update([
                'status' => 'archived',
                'is_active' => false,
                'archived_at' => now(),
            ]);

        Candidate::query()->delete();
        AccreditedVoter::query()->delete();
        Vote::query()->delete();
        DB::table('election_results')->delete();

        $this->info('Environment prepared.');
        $this->newLine();
    }

    private function setupSimulationElection(): Election
    {
        $feeType = FeeType::where('code', 'screening_fee')->firstOrFail();
        $now = Carbon::now();

        $election = Election::query()
            ->where('status', 'draft')
            ->where('title', 'like', '%Draft Election%')
            ->orderByDesc('id')
            ->first();

        if (! $election) {
            $election = Election::create([
                'title' => 'Lifecycle Simulation '.now()->year,
                'description' => 'Automated lifecycle simulation election',
                'eligibility_criteria' => 'Active alumni members in good standing',
                'election_year' => (int) now()->format('Y'),
                'eoi_start' => $now->copy()->subHour(),
                'eoi_end' => $now->copy()->addDays(2),
                'accreditation_start' => $now->copy()->addDays(2)->addHour(),
                'accreditation_end' => $now->copy()->addDays(4),
                'voting_start' => $now->copy()->subHour(),
                'voting_end' => $now->copy()->addDays(6),
                'screening_fee' => 5000,
                'status' => 'draft',
                'is_active' => false,
            ]);

            foreach (['President', 'Vice President', 'Secretary'] as $title) {
                ElectionOffice::create([
                    'election_id' => $election->id,
                    'title' => $title,
                    'description' => $title.' office',
                    'max_candidates' => 3,
                    'max_terms' => 2,
                    'fee_type_id' => $feeType->id,
                    'is_active' => true,
                ]);
            }
        } else {
            $election->update([
                'title' => 'Lifecycle Simulation '.now()->year,
                'description' => 'Automated lifecycle simulation election',
                'election_year' => (int) now()->format('Y'),
                'eoi_start' => $now->copy()->subHour(),
                'eoi_end' => $now->copy()->addDays(2),
                'accreditation_start' => $now->copy()->subHour(),
                'accreditation_end' => $now->copy()->addDays(4),
                'voting_start' => $now->copy()->subHour(),
                'voting_end' => $now->copy()->addDays(6),
                'status' => 'draft',
                'is_active' => false,
                'archived_at' => null,
                'archived_by' => null,
            ]);

            $election->candidates()->delete();
            $election->accreditedVoters()->delete();
            $election->votes()->delete();
            $election->results()->delete();
        }

        Election::where('id', '!=', $election->id)->update(['is_active' => false]);
        $election->update(['is_active' => true]);

        return $election->fresh(['offices']);
    }

    /**
     * @param  list<string>  $emails
     * @return list<Alumni>
     */
    private function activeAlumni(array $emails): array
    {
        $alumni = [];
        foreach ($emails as $email) {
            $user = User::where('email', $email)->firstOrFail();
            $record = $user->alumni;
            if (! $record) {
                throw new \RuntimeException("No alumni profile for {$email}");
            }
            $alumni[] = $record;
        }

        return $alumni;
    }

    private function assertStep(string $label, bool $pass, string $detail): void
    {
        $this->log[] = ['step' => $label, 'pass' => $pass, 'detail' => $detail];

        if ($pass) {
            $this->line("<fg=green>✓ PASS</> {$label} — {$detail}");
        } else {
            $this->line("<fg=red>✗ FAIL</> {$label} — {$detail}");
        }
    }

    private function printSummary(): void
    {
        $passed = collect($this->log)->where('pass', true)->count();
        $failed = collect($this->log)->where('pass', false)->count();
        $skipped = collect($this->log)->whereNull('pass')->count();

        $this->info("Results: {$passed} passed, {$failed} failed, {$skipped} skipped");

        if ($failed === 0) {
            $this->info('Simulation completed successfully.');
        } else {
            $this->error('Simulation completed with failures — review steps above.');
        }
    }
}
