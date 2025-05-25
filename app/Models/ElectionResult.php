<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ElectionResult extends Model
{
    protected $fillable = [
        'election_id',
        'election_office_id',
        'candidate_id',
        'total_votes',
        'is_winner',
        'declared_at'
    ];

    protected $casts = [
        'total_votes' => 'integer',
        'is_winner' => 'boolean',
        'declared_at' => 'datetime'
    ];

    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(ElectionOffice::class, 'election_office_id');
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function isWinner(): bool
    {
        return $this->is_winner;
    }

    public function getVotePercentage(int $totalVotes): float
    {
        if ($totalVotes === 0) return 0;
        return ($this->total_votes / $totalVotes) * 100;
    }
} 