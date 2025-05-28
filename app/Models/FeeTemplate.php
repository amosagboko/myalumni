<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FeeTemplate extends Model
{
    protected $table = 'fee_templates';

    protected $fillable = [
        'fee_type_id',
        'category_id',
        'graduation_year',
        'amount',
        'description',
        'is_active',
        'valid_from',
        'valid_until',
        'is_test_mode'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_active' => 'boolean',
        'is_test_mode' => 'boolean',
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
     * Get the category that owns this template.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(AlumniCategory::class);
    }

    /**
     * Get the transactions for this template.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
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
     * Check if this template is currently valid.
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
        if (!Auth::check()) {
            Log::info('User not authenticated for fee template payment check', ['fee_template_id' => $this->id]);
            return false;
        }

        $exists = $this->transactions()
            ->whereHas('alumni', function($q) {
                $q->where('user_id', Auth::id());
            })
            ->where('status', 'paid')
            ->exists();

        Log::info('Fee template payment check result', [
            'fee_template_id' => $this->id,
            'user_id' => Auth::id(),
            'is_paid' => $exists
        ]);

        return $exists;
    }
}
