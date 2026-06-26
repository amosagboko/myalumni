<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Candidate extends Model
{
    use SoftDeletes;

    public const STATUS_PENDING = 'pending';

    public const STATUS_PAID_AWAITING_SCREENING = 'paid_awaiting_screening';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    /** Statuses where the applicant still holds an EOI slot (not rejected). */
    public const ACTIVE_APPLICANT_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PAID_AWAITING_SCREENING,
        self::STATUS_APPROVED,
    ];

    /** Statuses ELCOM may screen (approve or reject). */
    public const SCREENABLE_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PAID_AWAITING_SCREENING,
    ];

    protected $fillable = [
        'election_id',
        'election_office_id',
        'parent_candidate_id',
        'alumni_id',
        'suggested_agent_id',
        'approved_agent_id',
        'agent_status',
        'agent_rejection_reason',
        'status',
        'rejection_reason',
        'has_paid_screening_fee',
        'passport',
        'manifesto',
        'documents',
        'screened_at',
        'screened_by'
    ];

    protected $casts = [
        'has_paid_screening_fee' => 'boolean',
        'documents' => 'array',
        'screened_at' => 'datetime'
    ];

    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    /**
     * Get the office that the candidate is running for.
     */
    public function office()
    {
        return $this->belongsTo(ElectionOffice::class, 'election_office_id');
    }

    /**
     * Get the alumni that owns the candidate.
     */
    public function alumni()
    {
        return $this->belongsTo(Alumni::class);
    }

    /**
     * Get the suggested agent for the candidate.
     */
    public function suggestedAgent()
    {
        return $this->belongsTo(Alumni::class, 'suggested_agent_id');
    }

    /**
     * Get the approved agent user for the candidate.
     * approved_agent_id always references users.id.
     */
    public function approvedAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_agent_id');
    }

    public function isApprovedAgent(User $user): bool
    {
        return $this->approved_agent_id !== null
            && (int) $this->approved_agent_id === (int) $user->id;
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function result(): HasMany
    {
        return $this->hasMany(ElectionResult::class);
    }

    public function electionResults(): HasMany
    {
        return $this->hasMany(ElectionResult::class);
    }

    public function getTotalVotes(): int
    {
        return $this->votes()->count();
    }

    /**
     * Approve the candidate's expression of interest.
     */
    public function approve(string $remarks = null)
    {
        $this->update([
            'status' => 'approved',
            'rejection_reason' => null,
            'screened_at' => now(),
            'screened_by' => Auth::id()
        ]);

        // Notify the alumni
        $this->notifyAlumni('approved', $remarks);

        return $this;
    }

    /**
     * Reject the candidate's expression of interest.
     */
    public function reject(string $reason)
    {
        $this->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'screened_at' => now(),
            'screened_by' => Auth::id()
        ]);

        // Notify the alumni
        $this->notifyAlumni('rejected', $reason);

        return $this;
    }

    /**
     * Notify the alumni about their screening status.
     */
    protected function notifyAlumni(string $status, ?string $remarks = null)
    {
        $alumni = $this->alumni;
        $office = $this->office;
        $user = \App\Models\User::find($alumni->user_id);

        if (!$user) {
            \Illuminate\Support\Facades\Log::error('Could not find user for alumni', [
                'alumni_id' => $alumni->getKey(),
                'candidate_id' => $this->getKey()
            ]);
            return;
        }

        $data = [
            'status' => $status,
            'office' => $office->title,
            'remarks' => $remarks,
            'alumni_name' => $user->name,
            'screened_at' => $this->screened_at->format('F j, Y H:i:s'),
            'screened_by' => Auth::user()->name
        ];

        // Send email notification
        \Illuminate\Support\Facades\Mail::send(
            'emails.candidate-screening-status',
            $data,
            function($message) use ($user, $status, $office) {
                $message->to($user->email)
                    ->subject("Expression of Interest Status Update - {$office->title}");
            }
        );

        // Create notification in database
        $user->notifications()->create([
            'type' => 'candidate_screening',
            'data' => $data,
            'read_at' => null
        ]);
    }

    public function getScreeningStatusAttribute(): string
    {
        return $this->status_label;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Pending payment',
            self::STATUS_PAID_AWAITING_SCREENING => 'Paid, awaiting ELCOM screening',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            default => ucfirst(str_replace('_', ' ', (string) $this->status)),
        };
    }

    /**
     * Get the formatted screening date.
     */
    public function getFormattedScreenedAtAttribute(): ?string
    {
        return $this->screened_at?->format('F j, Y H:i:s');
    }

    /**
     * Get the screener's name.
     */
    public function getScreenerNameAttribute(): ?string
    {
        return $this->screener?->name;
    }

    /**
     * Get the screener relationship.
     */
    public function screener()
    {
        return $this->belongsTo(User::class, 'screened_by');
    }

    public function markScreeningFeeAsPaid(): void
    {
        $this->update([
            'has_paid_screening_fee' => true,
            'status' => self::STATUS_PAID_AWAITING_SCREENING,
        ]);
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /** Submitted EOI, screening fee not yet paid. */
    public function isUnpaidPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isPaidAwaitingScreening(): bool
    {
        return $this->status === self::STATUS_PAID_AWAITING_SCREENING;
    }

    /** ELCOM may approve or reject (unpaid or paid, not yet screened). */
    public function canBeScreened(): bool
    {
        return in_array($this->status, self::SCREENABLE_STATUSES, true);
    }

    /**
     * @deprecated Use isUnpaidPending() or canBeScreened() for clarity.
     */
    public function isPending(): bool
    {
        return $this->canBeScreened();
    }

    public function scopeScreenable($query)
    {
        return $query->whereIn('status', self::SCREENABLE_STATUSES);
    }

    /**
     * Scope a query to only include approved candidates.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Applicants holding an EOI slot (paid, approved, or unpaid within the payment grace window).
     * Rejected applicants and stale unpaid applications do not count toward the office cap.
     */
    public function scopeActiveApplicants($query)
    {
        $graceHours = (int) config('election.eoi_payment_grace_hours', 48);
        $cutoff = now()->subHours($graceHours);

        return $query->where('status', '!=', self::STATUS_REJECTED)
            ->where(function ($q) use ($cutoff) {
                $q->whereIn('status', [
                    self::STATUS_PAID_AWAITING_SCREENING,
                    self::STATUS_APPROVED,
                ])->orWhere(function ($q2) use ($cutoff) {
                    $q2->where('status', self::STATUS_PENDING)
                        ->where('created_at', '>=', $cutoff);
                });
            });
    }

    /**
     * Unpaid EOI applications past the payment grace window (eligible for cleanup).
     */
    public function scopeAbandonedUnpaid($query)
    {
        $graceHours = (int) config('election.eoi_payment_grace_hours', 48);
        $cutoff = now()->subHours($graceHours);

        return $query->where('status', self::STATUS_PENDING)
            ->where('has_paid_screening_fee', false)
            ->where('created_at', '<', $cutoff);
    }

    /**
     * Scope a query to only include candidates eligible for ballot.
     */
    public function scopeEligibleForBallot($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Check if this candidate can appear on the ballot.
     */
    public function canAppearOnBallot(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if this candidate can receive votes.
     */
    public function canReceiveVotes(): bool
    {
        return $this->status === 'approved';
    }

    // For backward compatibility
    public function agent(): BelongsTo
    {
        return $this->approvedAgent();
    }
} 