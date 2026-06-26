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
    public $timeRemainingLabel;
    public $offices;
    public $isLive;

    public function mount(Election $election)
    {
        $this->election = $election;
        $this->isLive = $election->status === 'voting' && $election->canAcceptVoteSubmissions();
        $this->updateResults();
    }

    public function updateResults()
    {
        $this->election->refresh();
        $this->isLive = $this->election->status === 'voting' && $this->election->canAcceptVoteSubmissions();
        $this->election->load([
            'offices.candidates' => fn ($q) => $q->where('status', 'approved'),
            'offices.candidates.alumni.user',
            'offices.candidates.votes',
        ]);

        $this->totalAccredited = $this->election->getTotalAccreditedVoters();
        $this->totalVotes = $this->election->getTotalVotes();
        $this->voterTurnout = number_format(($this->totalVotes / max($this->totalAccredited, 1)) * 100, 1);

        $timeDisplay = $this->election->getVotingTimeDisplay();
        $this->timeRemainingLabel = $timeDisplay['label'];
        $this->timeRemaining = $timeDisplay['value'];

        $this->offices = $this->election->offices->map(function ($office) {
            $uniqueVoters = $office->votes()
                ->select('accredited_voter_id')
                ->distinct()
                ->count();

            $sorted = $office->candidates->map(function ($candidate) use ($uniqueVoters) {
                $votes = $candidate->votes()
                    ->select('accredited_voter_id')
                    ->distinct()
                    ->count();

                return [
                    'name' => $candidate->alumni->user->name,
                    'votes' => $votes,
                    'percentage' => $uniqueVoters > 0 ? ($votes / $uniqueVoters) * 100 : 0,
                ];
            })->sortByDesc('votes')->values();

            $maxVotes = $sorted->max('votes') ?? 0;
            $tiedAtTop = $sorted->isNotEmpty() && $sorted->where('votes', $maxVotes)->count() > 1;

            $resolutionStatus = $office->resolution_status;
            $isTied = $resolutionStatus === 'tied' || ($this->isLive && $tiedAtTop);
            $isUncontested = $resolutionStatus === 'uncontested'
                || ($sorted->isEmpty() && in_array($this->election->status, ['incomplete', 'completed', 'archived']));

            $candidates = $sorted->map(function ($candidate, $index) use ($maxVotes, $isTied) {
                $candidate['is_tied'] = $isTied && $candidate['votes'] === $maxVotes;
                $candidate['is_leading'] = !$isTied && $index === 0 && $candidate['votes'] > 0;
                return $candidate;
            });

            return [
                'id' => $office->id,
                'title' => $office->title,
                'total_votes' => $uniqueVoters,
                'resolution_status' => $resolutionStatus,
                'is_tied' => $isTied,
                'is_uncontested' => $isUncontested,
                'candidates' => $candidates,
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
