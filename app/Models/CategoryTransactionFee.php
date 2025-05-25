<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class CategoryTransactionFee extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'alumni_year_id',
        'fee_type_id',
        'amount',
        'description',
        'is_active',
        'is_test_mode'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_active' => 'boolean',
        'is_test_mode' => 'boolean'
    ];

    /**
     * Get the category that owns the fee.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(AlumniCategory::class, 'category_id');
    }

    /**
     * Get the alumni year that owns the fee.
     */
    public function alumniYear(): BelongsTo
    {
        return $this->belongsTo(AlumniYear::class);
    }

    /**
     * Get the fee type that owns the fee.
     */
    public function feeType(): BelongsTo
    {
        return $this->belongsTo(FeeType::class, 'fee_type_id');
    }

    /**
     * Get the transactions for this fee.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Scope a query to only include active fees.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include test mode fees.
     */
    public function scopeTestMode($query)
    {
        return $query->where('is_test_mode', true);
    }

    /**
     * Get the formatted fee type name.
     */
    public function getFeeTypeNameAttribute()
    {
        return $this->feeType?->name ?? '';
    }

    /**
     * Get the formatted amount with currency symbol.
     */
    public function getFormattedAmountAttribute()
    {
        return '₦' . number_format($this->amount, 2);
    }

    /**
     * Check if this fee has been paid by the given alumni.
     */
    public function isPaid()
    {
        return $this->transactions()
            ->where('alumni_id', Auth::user()->alumni->id)
            ->where('status', 'paid')
            ->exists();
    }

    /**
     * Get the completed transaction for this fee.
     */
    public function getCompletedTransaction()
    {
        return $this->transactions()
            ->where('alumni_id', Auth::user()->alumni->id)
            ->where('status', 'paid')
            ->first();
    }
}
