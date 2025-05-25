<?php

namespace App\Events;

use App\Models\Election;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ElectionResultsUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $election;
    public $results;

    public function __construct(Election $election)
    {
        $this->election = $election;
        $this->election->load(['offices.candidates.alumni.user', 'offices.candidates.votes']);
        
        $this->results = [
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
    }

    public function broadcastOn()
    {
        return new Channel('election.' . $this->election->id);
    }

    public function broadcastAs()
    {
        return 'results.updated';
    }
} 