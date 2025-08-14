<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vote extends Model
{
    protected $fillable = [
        'election_id',
        'election_office_id',
        'candidate_id',
        'accredited_voter_id'
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

    public function accreditedVoter(): BelongsTo
    {
        return $this->belongsTo(AccreditedVoter::class);
    }

    /**
     * Boot the model to add validation rules.
     */
    protected static function boot()
    {
        parent::boot();

        // Ensure only approved candidates can receive votes
        static::creating(function ($vote) {
            $candidate = \App\Models\Candidate::find($vote->candidate_id);
            if (!$candidate || $candidate->status !== 'approved') {
                throw new \Exception('Cannot create vote for non-approved candidate.');
            }
        });
    }
} 