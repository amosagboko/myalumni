<?php

namespace App\Livewire;

use App\Models\Election;
use Livewire\Component;

class RealTimeResults extends Component
{
    public Election $election;
    public $totalAccredited;
    public $totalVotes;
    public $voterTurnout;
    public $timeRemaining;
    public $offices;

    public function mount(Election $election)
    {
        $this->election = $election;
        $this->updateResults();
    }

    public function updateResults()
    {
        $this->election->load(['offices.candidates.alumni.user', 'offices.candidates.votes']);
        
        $this->totalAccredited = $this->election->getTotalAccreditedVoters();
        $this->totalVotes = $this->election->getTotalVotes();
        $this->voterTurnout = number_format(($this->totalVotes / max($this->totalAccredited, 1)) * 100, 1);
        $this->timeRemaining = $this->election->voting_end->diffForHumans();
        
        $this->offices = $this->election->offices->map(function ($office) {
            // Count unique voters for this office
            $uniqueVoters = $office->votes()
                ->select('accredited_voter_id')
                ->distinct()
                ->count();

            return [
                'id' => $office->id,
                'title' => $office->title,
                'total_votes' => $uniqueVoters,
                'candidates' => $office->candidates->map(function ($candidate) use ($uniqueVoters) {
                    // Count unique voters for this candidate
                    $votes = $candidate->votes()
                        ->select('accredited_voter_id')
                        ->distinct()
                        ->count();
                    return [
                        'name' => $candidate->alumni->user->name,
                        'votes' => $votes,
                        'percentage' => $uniqueVoters > 0 ? ($votes / $uniqueVoters) * 100 : 0,
                    ];
                })->sortByDesc('votes')->values(),
            ];
        });
    }

    public function getListeners()
    {
        return [
            "echo:election.{$this->election->id},results.updated" => '$refresh',
        ];
    }

    public function render()
    {
        return view('livewire.real-time-results');
    }
} 