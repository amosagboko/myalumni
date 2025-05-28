<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfficeContestApplication extends Model
{
    protected $fillable = [
        'alumni_id',
        'office_id',
        'transaction_id',
        'status',
        'application_details',
        'rejection_reason',
        'approved_at',
        'rejected_at'
    ];

    protected $casts = [
        'application_details' => 'array',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime'
    ];

    /**
     * Get the alumni that owns this application.
     */
    public function alumni(): BelongsTo
    {
        return $this->belongsTo(Alumni::class);
    }

    /**
     * Get the office that this application is for.
     */
    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    /**
     * Get the transaction associated with this application.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Scope a query to only include pending applications.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include approved applications.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include rejected applications.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Approve this application.
     */
    public function approve(): bool
    {
        return $this->update([
            'status' => 'approved',
            'approved_at' => now()
        ]);
    }

    /**
     * Reject this application.
     */
    public function reject(string $reason): bool
    {
        return $this->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'rejected_at' => now()
        ]);
    }

    /**
     * Check if this application is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if this application is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if this application is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
} 