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

    protected $fillable = [
        'election_id',
        'election_office_id',
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
     * Get the approved agent for the candidate.
     */
    public function approvedAgent()
    {
        return $this->belongsTo(Alumni::class, 'approved_agent_id');
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
            'rejection_reason' => $remarks,
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

    /**
     * Get the screening status in a human-readable format.
     */
    public function getScreeningStatusAttribute(): string
    {
        if (!$this->screened_at) {
            return 'Pending Screening';
        }

        return match($this->status) {
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default => 'Pending Screening'
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

    public function markScreeningFeeAsPaid()
    {
        $this->update(['has_paid_screening_fee' => true]);
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    // For backward compatibility
    public function agent(): BelongsTo
    {
        return $this->approvedAgent();
    }
} 