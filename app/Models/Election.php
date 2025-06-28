<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class Election extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'eoi_start',
        'eoi_end',
        'eligibility_criteria',
        'accreditation_start',
        'accreditation_end',
        'voting_start',
        'voting_end',
        'status',
        'screening_fee',
        'is_active'
    ];

    protected $casts = [
        'eoi_start' => 'datetime',
        'eoi_end' => 'datetime',
        'accreditation_start' => 'datetime',
        'accreditation_end' => 'datetime',
        'voting_start' => 'datetime',
        'voting_end' => 'datetime',
        'screening_fee' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    // Relationships
    public function elcomChairman(): BelongsTo
    {
        return $this->belongsTo(User::class, 'elcom_chairman_id');
    }

    public function offices(): HasMany
    {
        return $this->hasMany(ElectionOffice::class);
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class);
    }

    public function accreditedVoters(): HasMany
    {
        return $this->hasMany(AccreditedVoter::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(ElectionResult::class);
    }

    public function expressionsOfInterest(): HasMany
    {
        return $this->hasMany(ExpressionOfInterest::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInAccreditation($query)
    {
        return $query->where('status', 'accreditation')
            ->where('accreditation_start', '<=', now())
            ->where('accreditation_end', '>=', now());
    }

    public function scopeInVoting($query)
    {
        return $query->where('status', 'voting')
            ->where('voting_start', '<=', now())
            ->where('voting_end', '>=', now());
    }

    // Methods
    public function canStartAccreditation(): bool
    {
        return $this->status === 'draft' && now() >= $this->accreditation_start;
    }

    public function canEndAccreditation(): bool
    {
        return $this->status === 'accreditation' && 
               $this->hasAccreditationStarted() && 
               !$this->hasAccreditationEnded() &&
               now() >= $this->accreditation_end;
    }

    public function canStartVoting(): bool
    {
        return $this->status === 'accreditation' && now() >= $this->voting_start;
    }

    public function canEndVoting(): bool
    {
        return $this->status === 'voting' && now() >= $this->voting_end;
    }

    public function getTotalAccreditedVoters(): int
    {
        return $this->accreditedVoters()->count();
    }

    /**
     * Get the total number of unique voters who have cast at least one vote.
     * This counts each accredited voter only once, regardless of how many offices they voted for.
     */
    public function getTotalVotes(): int
    {
        // Count unique accredited voters who have voted
        $totalVotes = $this->accreditedVoters()
            ->where('has_voted', true)
            ->count();
            
        // Validate that total votes don't exceed accredited voters
        if ($totalVotes > $this->getTotalAccreditedVoters()) {
            Log::error("Election {$this->id}: Total votes ({$totalVotes}) exceed total accredited voters ({$this->getTotalAccreditedVoters()})");
        }
        
        return $totalVotes;
    }

    /**
     * Validate if the total votes for a specific office exceed the total accredited voters
     */
    public function validateOfficeVotes(ElectionOffice $office): bool
    {
        $officeTotalVotes = $office->candidates->sum(function ($candidate) {
            return $candidate->votes->count();
        });
        
        return $officeTotalVotes <= $this->getTotalAccreditedVoters();
    }

    /**
     * Get a list of offices where votes exceed accredited voters
     */
    public function getOfficesWithExcessVotes(): array
    {
        return $this->offices->filter(function ($office) {
            return !$this->validateOfficeVotes($office);
        })->map(function ($office) {
            $totalVotes = $office->candidates->sum(function ($candidate) {
                return $candidate->votes->count();
            });
            return [
                'office_id' => $office->id,
                'office_title' => $office->title,
                'total_votes' => $totalVotes,
                'accredited_voters' => $this->getTotalAccreditedVoters(),
                'excess_votes' => $totalVotes - $this->getTotalAccreditedVoters()
            ];
        })->values()->toArray();
    }

    public function getVoterTurnout(): float
    {
        $totalAccredited = $this->getTotalAccreditedVoters();
        if ($totalAccredited === 0) return 0;
        return ($this->getTotalVotes() / $totalAccredited) * 100;
    }

    public function isAlumniEligibleToVote(Alumni $alumni): bool
    {
        // Check if alumni has paid all required fees
        $hasPaidFees = $alumni->getActiveFees()->every(function($fee) {
            return $fee->isPaid();
        });

        // Check if alumni is already accredited
        $isAccredited = $this->accreditedVoters()
            ->where('alumni_id', $alumni->id)
            ->exists();

        return $hasPaidFees && !$isAccredited;
    }

    public function isAlumniEligibleToRun(Alumni $alumni): bool
    {
        // Check if alumni has paid all required fees
        $hasPaidFees = $alumni->getActiveFees()->every(function($fee) {
            return $fee->isPaid();
        });

        // Check if alumni is already a candidate
        $isCandidate = $this->candidates()
            ->where('alumni_id', $alumni->id)
            ->exists();

        return $hasPaidFees && !$isCandidate;
    }

    public function getRealTimeResults()
    {
        return $this->offices()->with(['candidates' => function($query) {
            $query->withCount('votes as total_votes')
                ->orderByDesc('total_votes');
        }])->get();
    }

    public function declareResults()
    {
        if ($this->status !== 'voting' || now() < $this->voting_end) {
            return false;
        }

        DB::transaction(function() {
            // Update election status
            $this->update(['status' => 'completed']);

            // Calculate and store results for each office
            foreach ($this->offices as $office) {
                $candidates = $office->candidates()
                    ->withCount('votes as total_votes')
                    ->orderByDesc('total_votes')
                    ->get();

                foreach ($candidates as $candidate) {
                    ElectionResult::create([
                        'election_id' => $this->id,
                        'election_office_id' => $office->id,
                        'candidate_id' => $candidate->id,
                        'total_votes' => $candidate->total_votes,
                        'is_winner' => $candidate === $candidates->first(),
                        'declared_at' => now()
                    ]);
                }
            }
        });

        return true;
    }

    /**
     * Check if the EOI period is currently active.
     */
    public function isEoiPeriodActive(): bool
    {
        // If election status is 'eoi', the period is active regardless of time
        if ($this->status === 'eoi') {
            return true;
        }

        if (!$this->eoi_start || !$this->eoi_end) {
            return false;
        }

        $now = now();
        return $now->between($this->eoi_start, $this->eoi_end);
    }

    /**
     * Check if the EOI period has started.
     */
    public function hasEoiStarted(): bool
    {
        return $this->eoi_start && now()->greaterThanOrEqualTo($this->eoi_start);
    }

    /**
     * Check if the EOI period has ended.
     */
    public function hasEoiEnded(): bool
    {
        return $this->eoi_end && now()->greaterThan($this->eoi_end);
    }

    /**
     * Check if the EOI period can be started.
     */
    public function canStartEoi(): bool
    {
        return $this->status === 'draft' && 
               $this->eoi_start && 
               $this->eoi_end && 
               !$this->hasEoiStarted();
    }

    /**
     * Check if the EOI period can be ended.
     */
    public function canEndEoi(): bool
    {
        return $this->status === 'eoi' && 
               $this->hasEoiStarted() && 
               !$this->hasEoiEnded();
    }

    /**
     * Start the EOI period.
     */
    public function startEoi(): bool
    {
        if (!$this->canStartEoi()) {
            return false;
        }

        $this->update(['status' => 'eoi']);
        return true;
    }

    /**
     * End the EOI period.
     */
    public function endEoi(): bool
    {
        if (!$this->canEndEoi()) {
            return false;
        }

        $this->update(['status' => 'draft']);
        return true;
    }

    /**
     * Extend the EOI period by a specified number of days.
     */
    public function extendEoiPeriod(int $days = 7): bool
    {
        if (!$this->eoi_end) {
            return false;
        }

        $newEndDate = $this->eoi_end->addDays($days);
        
        // Ensure the new end date doesn't conflict with accreditation period
        if ($this->accreditation_start && $newEndDate >= $this->accreditation_start) {
            return false;
        }

        $this->update(['eoi_end' => $newEndDate]);
        
        // Log the extension for audit purposes
        \Illuminate\Support\Facades\Log::info('EOI period extended', [
            'election_id' => $this->getKey(),
            'old_end_date' => $this->eoi_end->subDays($days),
            'new_end_date' => $newEndDate,
            'extension_days' => $days,
            'extended_by' => Auth::id()
        ]);

        return true;
    }

    /**
     * Check if EOI period can be extended.
     */
    public function canExtendEoiPeriod(): bool
    {
        // Can extend if EOI has ended but accreditation hasn't started
        return $this->hasEoiEnded() && 
               (!$this->accreditation_start || now() < $this->accreditation_start);
    }

    /**
     * Get pending EOI payments count.
     */
    public function getPendingEoiPaymentsCount(): int
    {
        return $this->candidates()
            ->where('has_paid_screening_fee', false)
            ->where('status', 'pending')
            ->count();
    }

    /**
     * Get paid EOI applications count.
     */
    public function getPaidEoiApplicationsCount(): int
    {
        return $this->candidates()
            ->where('has_paid_screening_fee', true)
            ->count();
    }

    /**
     * Get total EOI applications count.
     */
    public function getTotalEoiApplicationsCount(): int
    {
        return $this->candidates()->count();
    }

    /**
     * Check if EOI period should be extended based on payment status.
     */
    public function shouldExtendEoiPeriod(): bool
    {
        // Extend if EOI has ended but there are pending payments
        // and we haven't reached accreditation period
        return $this->hasEoiEnded() && 
               $this->getPendingEoiPaymentsCount() > 0 &&
               (!$this->accreditation_start || now() < $this->accreditation_start);
    }

    public function hasAccreditationStarted(): bool
    {
        return ($this->accreditation_start !== null) && (now()->greaterThanOrEqualTo($this->accreditation_start));
    }

    /**
     * Check if the accreditation period is currently active.
     */
    public function isAccreditationPeriodActive(): bool
    {
        if (!$this->accreditation_start || !$this->accreditation_end) {
            return false;
        }

        $now = now();
        return $now->between($this->accreditation_start, $this->accreditation_end);
    }

    /**
     * Check if the accreditation period has ended.
     */
    public function hasAccreditationEnded(): bool
    {
        return $this->accreditation_end && now()->greaterThan($this->accreditation_end);
    }

    /**
     * Check if the voting period has started.
     */
    public function hasVotingStarted(): bool
    {
        return ($this->voting_start !== null) && (now()->greaterThanOrEqualTo($this->voting_start));
    }

    /**
     * Check if the voting period is currently active.
     */
    public function isVotingPeriodActive(): bool
    {
        if (!$this->voting_start || !$this->voting_end) {
            return false;
        }

        $now = now();
        return $now->between($this->voting_start, $this->voting_end);
    }

    /**
     * Check if the voting period has ended.
     */
    public function hasVotingEnded(): bool
    {
        return $this->voting_end && now()->greaterThan($this->voting_end);
    }
}
