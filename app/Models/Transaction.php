<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;

class Transaction extends Model
{
    protected $table = 'transactions';

    protected $fillable = [
        'alumni_id',
        'fee_template_id',
        'amount',
        'status',
        'payment_reference',
        'payment_provider',
        'payment_provider_reference',
        'payment_link',
        'payment_details',
        'metadata',
        'is_test_mode',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'fee_valid_from' => 'datetime',
        'fee_valid_until' => 'datetime',
        'fee_is_active' => 'boolean',
        'is_test_mode' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Get the user that owns the transaction through alumni.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'alumni_id', 'id')->through('alumni');
    }

    /**
     * Get the alumni that owns the transaction.
     */
    public function alumni(): BelongsTo
    {
        return $this->belongsTo(Alumni::class);
    }

    /**
     * Get the fee template for this transaction.
     */
    public function feeTemplate(): BelongsTo
    {
        return $this->belongsTo(FeeTemplate::class, 'fee_template_id');
    }

    /**
     * Get the office contest application associated with this transaction.
     */
    public function officeContestApplication(): HasOne
    {
        return $this->hasOne(OfficeContestApplication::class);
    }

    /**
     * Human-readable fee line for receipts and history.
     */
    public function getDisplayDescriptionAttribute(): string
    {
        return $this->feeTemplate?->description
            ?? $this->feeTemplate?->feeType?->name
            ?? 'Payment';
    }

    /**
     * Short category label (onboarding, annual due, EOI, etc.).
     */
    public function getFeeCategoryLabelAttribute(): string
    {
        $feeType = $this->feeTemplate?->feeType;
        if ($feeType?->isEoiFee()) {
            return 'Election (EOI)';
        }

        $purpose = $this->feeTemplate?->fee_purpose;

        return match ($purpose) {
            FeeTemplate::PURPOSE_ANNUAL_RENEWAL => 'Annual due',
            FeeTemplate::PURPOSE_ONBOARDING => 'Onboarding',
            default => match ($feeType?->code) {
                'subscription', 'annual_due' => 'Annual due',
                default => $feeType?->name ?? 'Payment',
            },
        };
    }

    /**
     * Payment year for annual dues (from metadata, not template graduation_year 0).
     */
    public function getPaymentYearLabelAttribute(): ?string
    {
        $year = $this->metadata['payment_year']
            ?? ($this->payment_details['payment_year'] ?? null);

        if ($year) {
            return (string) $year;
        }

        $gradYear = $this->feeTemplate?->graduation_year;
        if ($gradYear && (int) $gradYear !== FeeTemplate::PAYMENT_YEAR_ALL) {
            return (string) $gradYear;
        }

        return null;
    }

    /**
     * Get the formatted fee type name.
     */
    public function getFormattedFeeTypeAttribute(): string
    {
        return $this->feeTemplate?->feeType?->name ?? 'Unknown Fee Type';
    }

    /**
     * Get the formatted amount with currency symbol.
     */
    public function getFormattedAmountAttribute(): string
    {
        return '₦' . number_format($this->amount, 2);
    }

    /**
     * Get the formatted fee amount with currency symbol.
     */
    public function getFormattedFeeAmountAttribute(): string
    {
        return '₦' . number_format($this->fee_amount, 2);
    }

    /**
     * Scope a query to only include transactions for the current user.
     */
    public function scopeForCurrentUser($query)
    {
        return $query->whereHas('alumni', function($q) {
            $q->where('user_id', Auth::id());
        });
    }

    /**
     * Scope a query to only include paid transactions.
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope a query to only include pending transactions.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * @deprecated Use scopePaid() — transaction status is normalized to "paid".
     */
    public function scopeCompleted($query)
    {
        return $query->paid();
    }

    /**
     * Scope a query to only include failed transactions.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope a query to only include transactions for a specific alumni.
     */
    public function scopeForAlumni($query, $alumniId)
    {
        return $query->where('alumni_id', $alumniId);
    }

    /**
     * Check if the transaction is paid.
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Check if the transaction is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the transaction is failed.
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Get the fee model.
     */
    public function getFeeAttribute()
    {
        return $this->feeTemplate;
    }

    /**
     * Mark this transaction as paid.
     */
    public function markAsPaid(?string $paidAt = null): bool
    {
        return $this->update([
            'status' => 'paid',
            'paid_at' => $paidAt ?? now()
        ]);
    }

    /**
     * Mark this transaction as completed (alias for markAsPaid for backward compatibility).
     */
    public function markAsCompleted(): bool
    {
        return $this->markAsPaid();
    }

    /**
     * Mark this transaction as failed.
     */
    public function markAsFailed(): bool
    {
        return $this->update([
            'status' => 'failed'
        ]);
    }

    /**
     * Mark this transaction as pending.
     */
    public function markAsPending(): bool
    {
        return $this->update([
            'status' => 'pending'
        ]);
    }

    /**
     * Get the payment details with proper type casting.
     */
    public function getPaymentDetailsAttribute($value)
    {
        return json_decode($value, true);
    }

    /**
     * Set the payment details with proper JSON encoding.
     */
    public function setPaymentDetailsAttribute($value)
    {
        $this->attributes['payment_details'] = json_encode($value);
    }
}
