<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class FeeTemplate extends Model
{
    protected $table = 'fee_templates';

    protected $fillable = [
        'fee_type_id',
        'name',
        'graduation_year',
        'amount',
        'description',
        'is_active',
        'valid_from',
        'valid_until'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_active' => 'boolean',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime'
    ];

    /**
     * Get the fee type that owns this template.
     */
    public function feeType(): BelongsTo
    {
        return $this->belongsTo(FeeType::class);
    }

    /**
     * Get the transactions associated with this template.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'fee_template_id');
    }

    /**
     * Scope a query to only include active templates.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('valid_until')
                    ->orWhere('valid_until', '>', now());
            })
            ->where('valid_from', '<=', now());
    }

    /**
     * Scope a query to only include templates for a specific graduation year.
     */
    public function scopeForGraduationYear($query, $year)
    {
        return $query->where('graduation_year', $year);
    }

    /**
     * Get the formatted amount with currency symbol.
     */
    public function getFormattedAmountAttribute(): string
    {
        return '₦' . number_format($this->amount, 2);
    }

    /**
     * Check if the template is currently valid.
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();
        if ($this->valid_from > $now) {
            return false;
        }

        if ($this->valid_until && $this->valid_until < $now) {
            return false;
        }

        return true;
    }

    /**
     * Get the original fee model (either new or old structure).
     */
    public function getOriginalFeeAttribute()
    {
        if ($this->fee_structure === 'new') {
            return FeeTemplate::query()
                ->from('fee_templates')
                ->where('id', $this->new_template_id)
                ->first();
        }

        return CategoryTransactionFee::query()
            ->where('id', $this->old_fee_id)
            ->first();
    }

    /**
     * Get the fee structure type (old or new).
     */
    public function getFeeStructureAttribute(): string
    {
        return $this->new_template_id ? 'new' : 'old';
    }

    /**
     * Get the actual template name.
     */
    public function getNameAttribute($value)
    {
        return $value ?? $this->description;
    }

    /**
     * Check if this fee template has been paid by the current user.
     */
    public function isPaid()
    {
        return $this->transactions()
            ->where('user_id', Auth::id())
            ->where('status', 'paid')
            ->exists();
    }
}
