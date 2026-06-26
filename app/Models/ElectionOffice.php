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
        'resolution_status',
        'winner_candidate_id',
        'parent_office_id',
        'by_election_mode',
        'by_election_id',
        'fee_type_id',
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

    public function winnerCandidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class, 'winner_candidate_id');
    }

    public function isDecided(): bool
    {
        return $this->resolution_status === 'decided';
    }

    public function isTied(): bool
    {
        return $this->resolution_status === 'tied';
    }

    public function isUncontested(): bool
    {
        return $this->resolution_status === 'uncontested';
    }

    public function isPendingResolution(): bool
    {
        return in_array($this->resolution_status, ['tied', 'uncontested'], true);
    }

    public function isEoiByElectionOffice(): bool
    {
        return $this->by_election_mode === 'eoi';
    }

    public function isRunoffByElectionOffice(): bool
    {
        return $this->by_election_mode === 'runoff';
    }

    public function parentOffice(): BelongsTo
    {
        return $this->belongsTo(ElectionOffice::class, 'parent_office_id');
    }

    public function byElection(): BelongsTo
    {
        return $this->belongsTo(Election::class, 'by_election_id');
    }

    public function hasActiveByElection(): bool
    {
        return $this->by_election_id !== null;
    }

    /**
     * Applicants holding an active EOI slot (pending screening or approved).
     * Rejected applicants do not count toward the office cap.
     */
    public function applicants(): HasMany
    {
        return $this->candidates()->activeApplicants();
    }

    public function getActiveApplicantsCount(): int
    {
        return $this->applicants()->count();
    }

    public function hasAvailableApplicantSlots(): bool
    {
        return $this->getActiveApplicantsCount() < $this->max_candidates;
    }

    public function getRemainingApplicantSlots(): int
    {
        return max(0, $this->max_candidates - $this->getActiveApplicantsCount());
    }

    public function isAcceptingApplications(): bool
    {
        if (!$this->relationLoaded('election')) {
            $this->load('election');
        }

        return $this->election
            && $this->election->canAcceptEoiSubmissions()
            && (!$this->election->isByElection() || $this->isEoiByElectionOffice())
            && $this->hasAvailableApplicantSlots();
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

    /**
     * Get only approved candidates for this office.
     */
    public function approvedCandidates()
    {
        return $this->candidates()->where('status', 'approved');
    }

    /**
     * Get only candidates eligible for ballot (approved status).
     */
    public function ballotCandidates()
    {
        return $this->approvedCandidates();
    }
} 