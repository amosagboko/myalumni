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
        'election_year',
        'cycle_label',
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
        'is_active',
        'archived_at',
        'archived_by',
        'cloned_from_election_id',
    ];

    protected $casts = [
        'eoi_start' => 'datetime',
        'eoi_end' => 'datetime',
        'accreditation_start' => 'datetime',
        'accreditation_end' => 'datetime',
        'voting_start' => 'datetime',
        'voting_end' => 'datetime',
        'screening_fee' => 'decimal:2',
        'is_active' => 'boolean',
        'archived_at' => 'datetime',
        'election_year' => 'integer',
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

    public function archivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'archived_by');
    }

    public function clonedFrom(): BelongsTo
    {
        return $this->belongsTo(Election::class, 'cloned_from_election_id');
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

    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    public function scopeCompletedUnarchived($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeOperational($query)
    {
        return $query->whereNotIn('status', ['completed', 'archived']);
    }

    public function scopeHistorical($query)
    {
        return $query->whereIn('status', ['completed', 'archived']);
    }

    public function isArchived(): bool
    {
        return $this->status === 'archived';
    }

    public function isHistorical(): bool
    {
        return in_array($this->status, ['completed', 'archived'], true);
    }

    public function isMutable(): bool
    {
        return !$this->isArchived();
    }

    public function canArchive(): bool
    {
        return $this->status === 'completed' && !$this->isArchived();
    }

    public static function canStartNewCycle(): bool
    {
        return !static::completedUnarchived()->exists()
            && !static::operational()->where('is_active', true)->exists();
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
        $validStatus = in_array($this->status, ['draft', 'eoi_closed'], true)
            || ($this->status === 'eoi' && $this->hasEoiEnded());

        return $validStatus
            && $this->hasAccreditationStarted()
            && !$this->hasAccreditationEnded();
    }

    public function canEndAccreditation(): bool
    {
        return $this->status === 'accreditation'
            && $this->hasAccreditationStarted()
            && $this->isAccreditationPeriodActive();
    }

    public function canStartVoting(): bool
    {
        return $this->status === 'accreditation'
            && $this->hasVotingStarted()
            && !$this->isAccreditationPeriodActive();
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

        app(\App\Services\ElectionResultService::class)->declareResults($this);

        return true;
    }

    /**
     * Check if the EOI period is currently active.
     */
    public function isEoiPeriodActive(): bool
    {
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
        return $this->status === 'draft'
            && $this->eoi_start
            && $this->eoi_end
            && !$this->hasEoiEnded();
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

        $this->update(['status' => 'eoi_closed']);
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
        if ($this->hasEoiEnded() && (!$this->accreditation_start || now() < $this->accreditation_start)) {
            return true;
        }
        
        // Can extend if EOI is active and ending soon (within 3 days) - for all offices
        if ($this->isEoiPeriodActive()) {
            $daysUntilEnd = now()->diffInDays($this->eoi_end, false);
            return $daysUntilEnd <= 3 && $daysUntilEnd > 0;
        }
        
        return false;
    }

    /**
     * Check if there are offices with no candidates.
     */
    public function hasOfficesWithNoCandidates(): bool
    {
        return $this->offices()->whereDoesntHave('candidates')->exists();
    }

    /**
     * Get offices with no candidates.
     */
    public function getOfficesWithNoCandidates()
    {
        return $this->offices()->whereDoesntHave('candidates')->get();
    }

    /**
     * Get count of offices with no candidates.
     */
    public function getOfficesWithNoCandidatesCount(): int
    {
        return $this->offices()->whereDoesntHave('candidates')->count();
    }

    /**
     * Get offices with candidates count.
     */
    public function getOfficesWithCandidatesCount(): int
    {
        return $this->offices()->whereHas('candidates')->count();
    }

    /**
     * Check if EOI period should be extended based on various criteria.
     */
    public function shouldExtendEoiPeriod(): bool
    {
        // Extend if EOI has ended but there are pending payments
        if ($this->hasEoiEnded() && $this->getPendingEoiPaymentsCount() > 0) {
            return (!$this->accreditation_start || now() < $this->accreditation_start);
        }
        
        // Extend if EOI is active and ending soon - for all offices
        if ($this->isEoiPeriodActive()) {
            $daysUntilEnd = now()->diffInDays($this->eoi_end, false);
            return $daysUntilEnd <= 3 && $daysUntilEnd > 0;
        }
        
        return false;
    }

    /**
     * Get EOI extension reasons.
     */
    public function getEoiExtensionReasons(): array
    {
        $reasons = [];
        
        if ($this->hasEoiEnded() && $this->getPendingEoiPaymentsCount() > 0) {
            $reasons[] = "Pending payments: {$this->getPendingEoiPaymentsCount()} applications";
        }
        
        if ($this->isEoiPeriodActive()) {
            $daysUntilEnd = now()->diffInDays($this->eoi_end, false);
            if ($daysUntilEnd <= 3 && $daysUntilEnd > 0) {
                $reasons[] = "EOI period ending soon: {$daysUntilEnd} days remaining";
            }
        }
        
        // Add information about offices with no candidates as additional context
        if ($this->hasOfficesWithNoCandidates()) {
            $reasons[] = "Offices with no candidates: {$this->getOfficesWithNoCandidatesCount()} offices";
        }
        
        return $reasons;
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

    /**
     * Alumni may submit EOI only when ELCOM has opened EOI and the calendar window is active.
     */
    public function canAcceptEoiSubmissions(): bool
    {
        return $this->status === 'eoi' && $this->isEoiPeriodActive();
    }

    /**
     * Alumni may accredit only when accreditation is the active phase and within its window.
     */
    public function canAcceptAccreditationSubmissions(): bool
    {
        return $this->status === 'accreditation' && $this->isAccreditationPeriodActive();
    }

    /**
     * Alumni may vote only when voting is the active phase and within its window.
     */
    public function canAcceptVoteSubmissions(): bool
    {
        return $this->status === 'voting' && $this->isVotingPeriodActive();
    }

    /**
     * Close the accreditation window early (status remains accreditation until voting starts).
     */
    public function endAccreditation(): bool
    {
        if (!$this->canEndAccreditation()) {
            return false;
        }

        $this->update(['accreditation_end' => now()->subSecond()]);

        return true;
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
     * Get total subscribed users (alumni who paid the ₦2,000 subscription fee during onboarding).
     */
    public function getTotalSubscribedUsers(): int
    {
        $subscriptionFeeType = \App\Models\FeeType::where('code', 'subscription')
            ->orWhere('code', 'subscription_registration')
            ->first();
            
        if (!$subscriptionFeeType) {
            return 0;
        }

        return \App\Models\Transaction::whereHas('feeTemplate', function ($query) use ($subscriptionFeeType) {
            $query->where('fee_type_id', $subscriptionFeeType->id);
        })->where('status', 'paid')->count();
    }

    /**
     * Get total exempted users (alumni who were exempted from paying the ₦2,000 subscription fee during onboarding).
     */
    public function getTotalExemptedUsers(): int
    {
        // 2024 graduates are exempted from all fees including subscription fee
        return \App\Models\Alumni::where('year_of_graduation', 2024)
            ->whereNotNull('contact_address')
            ->whereNotNull('phone_number')
            ->whereNotNull('qualification_type')
            ->count();
    }

    /**
     * Get total voters register (subscribed + exempted).
     */
    public function getTotalVotersRegister(): int
    {
        return $this->getTotalSubscribedUsers() + $this->getTotalExemptedUsers();
    }
}
