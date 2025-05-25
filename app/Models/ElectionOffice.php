<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ElectionOffice extends Model
{
    protected $fillable = [
        'election_id',
        'title',
        'description',
        'max_candidates',
        'max_terms',
        'term_duration',
        'is_active',
        'fee_type_id'
    ];

    protected $casts = [
        'max_candidates' => 'integer',
        'max_terms' => 'integer',
        'term_duration' => 'integer',
        'is_active' => 'boolean'
    ];

    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    public function feeType(): BelongsTo
    {
        return $this->belongsTo(FeeType::class);
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(ElectionResult::class);
    }

    public function getWinner()
    {
        return $this->results()
            ->where('is_winner', true)
            ->with('candidate.alumni.user')
            ->first();
    }

    public function getTotalVotes(): int
    {
        return $this->votes()->count();
    }
} 