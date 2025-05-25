<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Election;
use App\Models\Vote;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ElectionOffice;
use App\Models\AccreditedVoter;
use App\Models\Candidate;

class SimulateVoting extends Command
{
    protected $signature = 'election:simulate-voting {election_id? : The ID of the election to simulate voting for}';
    protected $description = 'Simulate all accredited voters casting their votes in an election';

    public function handle()
    {
        $electionId = $this->argument('election_id');
        $election = Election::findOrFail($electionId);

        if ($election->status !== 'voting') {
            $this->error("Election is not in voting status!");
            return 1;
        }

        // Get all offices for this election
        $offices = ElectionOffice::where('election_id', $electionId)->get();
        $this->info("\nFound " . $offices->count() . " offices to vote for.");

        // Get all accredited voters who haven't voted yet
        $accreditedVoters = AccreditedVoter::where('election_id', $electionId)
            ->where('has_voted', false)
            ->get();

        $this->info("\nFound " . $accreditedVoters->count() . " accredited voters who haven't voted yet.");

        // Track vote distribution for debugging
        $voteDistribution = [];
        $duplicateVotes = 0;

        DB::beginTransaction();
        try {
            foreach ($accreditedVoters as $voter) {
                // Delete any existing votes for this voter
                DB::table('votes')->where('accredited_voter_id', $voter->id)->delete();

                // Get offices this voter hasn't voted for yet
                $votedOffices = DB::table('votes')
                    ->where('accredited_voter_id', $voter->id)
                    ->where('election_id', $electionId)
                    ->pluck('election_office_id')
                    ->toArray();

                $availableOffices = $offices->whereNotIn('id', $votedOffices);

                foreach ($availableOffices as $office) {
                    // Get candidates for this office
                    $candidates = Candidate::where('election_office_id', $office->id)->get();
                    
                    if ($candidates->isEmpty()) {
                        $this->warn("No candidates found for office: " . $office->title);
                        continue;
                    }

                    // Randomly select a candidate
                    $selectedCandidate = $candidates->random();

                    // Check if this voter has already voted for this office (using a unique constraint on (election_id, election_office_id, accredited_voter_id) if available, or a query)
                    $existingVote = DB::table('votes')->where('election_id', $electionId)->where('election_office_id', $office->id)->where('accredited_voter_id', $voter->id)->first();

                    if ($existingVote) {
                        $duplicateVotes++;
                        $this->warn("Duplicate vote detected for voter {$voter->id} in office {$office->id}");
                        continue;
                    }

                    // Create the vote (using candidate_id) if no vote exists for this voter in this office
                    DB::table('votes')->insert([
                        'election_id' => $electionId,
                        'election_office_id' => $office->id,
                        'candidate_id' => $selectedCandidate->id,
                        'accredited_voter_id' => $voter->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Track vote distribution (now counting unique voters per office)
                    if (!isset($voteDistribution[$office->id])) {
                        $voteDistribution[$office->id] = [
                            'office' => $office->title,
                            'total_votes' => 0,
                            'unique_voters' => 0
                        ];
                    }
                    $voteDistribution[$office->id]['total_votes']++;
                    $voteDistribution[$office->id]['unique_voters'] = DB::table('votes')->where('election_id', $electionId)->where('election_office_id', $office->id)->distinct('accredited_voter_id')->count('accredited_voter_id');
                }

                // Mark voter as having voted
                $voter->update(['has_voted' => true]);
            }

            DB::commit();

            // Display vote distribution
            $this->info("\nVote Distribution:");
            foreach ($voteDistribution as $officeId => $stats) {
                $this->info("Office: {$stats['office']}");
                $this->info("  Total Votes: {$stats['total_votes']}");
                $this->info("  Unique Voters: {$stats['unique_voters']}");
            }

            if ($duplicateVotes > 0) {
                $this->warn("\nDetected {$duplicateVotes} duplicate votes that were prevented.");
            }

            $totalVotes = DB::table('votes')->where('election_id', $electionId)->distinct('accredited_voter_id')->count('accredited_voter_id');
            $totalVoters = $accreditedVoters->count();

            $this->info("\nVoting simulation completed for {$election->title}");
            $this->info("Total Accredited Voters: {$totalVoters}");
            $this->info("Total Votes Cast: {$totalVotes}");

            if ($totalVotes > $totalVoters) {
                $this->warn("WARNING: Total votes ({$totalVotes}) exceed total accredited voters ({$totalVoters})!");
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error during voting simulation: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
} 