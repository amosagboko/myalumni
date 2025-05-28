<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;

class Transaction extends Model
{
    protected $table = 'vw_transactions';

    protected $fillable = [
        'user_id',
        'alumni_id',
        'amount',
        'status',
        'payment_reference',
        'payment_provider',
        'payment_provider_reference',
        'payment_link',
        'payment_details',
        'paid_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'fee_valid_from' => 'datetime',
        'fee_valid_until' => 'datetime',
        'fee_is_active' => 'boolean'
    ];

    /**
     * Get the user that owns the transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the alumni that owns the transaction.
     */
    public function alumni(): BelongsTo
    {
        return $this->belongsTo(Alumni::class);
    }

    /**
     * Get the fee type for this transaction.
     */
    public function feeType(): BelongsTo
    {
        return $this->belongsTo(FeeType::class, 'fee_type_id');
    }

    /**
     * Get the category for this transaction.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(AlumniCategory::class, 'category_id');
    }

    /**
     * Get the fee template for this transaction (new structure).
     */
    public function feeTemplate(): BelongsTo
    {
        return $this->belongsTo(FeeTemplate::class, 'fee_id')
            ->whereNotNull('fee_template_id');
    }

    /**
     * Get the category transaction fee for this transaction (old structure).
     */
    public function categoryTransactionFee(): BelongsTo
    {
        return $this->belongsTo(CategoryTransactionFee::class, 'fee_id')
            ->whereNotNull('category_transaction_fee_id');
    }

    /**
     * Get the office contest application associated with this transaction.
     */
    public function officeContestApplication(): HasOne
    {
        return $this->hasOne(OfficeContestApplication::class);
    }

    /**
     * Get the formatted fee type name.
     */
    public function getFormattedFeeTypeAttribute(): string
    {
        return $this->feeType?->name ?? 'Unknown Fee Type';
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
        return $query->where('user_id', Auth::id());
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
     * Scope a query to only include completed transactions.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
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
     * Check if this transaction is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if this transaction is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if this transaction is failed.
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Get the fee structure type (old or new).
     */
    public function getFeeStructureAttribute(): string
    {
        return $this->fee_template_id ? 'new' : 'old';
    }

    /**
     * Get the actual fee model regardless of structure.
     */
    public function getFeeAttribute()
    {
        return $this->feeStructure === 'new' 
            ? $this->feeTemplate 
            : $this->categoryTransactionFee;
    }

    /**
     * Mark this transaction as completed.
     */
    public function markAsCompleted(): bool
    {
        return $this->update([
            'status' => 'completed',
            'paid_at' => now()
        ]);
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
