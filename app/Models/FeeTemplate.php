<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FeeTemplate extends Model
{
    public const PURPOSE_ONBOARDING = 'onboarding';

    public const PURPOSE_ANNUAL_RENEWAL = 'annual_renewal';

    /** graduation_year value: applies to every configured payment year */
    public const PAYMENT_YEAR_ALL = 0;

    protected $table = 'fee_templates';

    protected $fillable = [
        'fee_type_id',
        'fee_purpose',
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

    public function scopeAnnualRenewal($query)
    {
        return $query->where('fee_purpose', self::PURPOSE_ANNUAL_RENEWAL);
    }

    public function scopeOnboarding($query)
    {
        return $query->where('fee_purpose', self::PURPOSE_ONBOARDING);
    }

    public function isAnnualRenewal(): bool
    {
        return $this->fee_purpose === self::PURPOSE_ANNUAL_RENEWAL;
    }

    public function isOnboarding(): bool
    {
        return $this->fee_purpose === self::PURPOSE_ONBOARDING;
    }

    public function isAnnualDueType(): bool
    {
        if ($this->isAnnualRenewal()) {
            return true;
        }

        return in_array($this->feeType?->code, ['subscription', FeeType::ANNUAL_DUE_CODE], true);
    }

    /**
     * Payment year this fee applies to (for annual dues).
     */
    public function paymentYearLabel(?AlumniYear $activePaymentYear = null): ?string
    {
        if (!$this->isAnnualDueType()) {
            return null;
        }

        if ($activePaymentYear) {
            return (string) $activePaymentYear->year;
        }

        if ($this->graduation_year && (int) $this->graduation_year !== self::PAYMENT_YEAR_ALL) {
            return (string) $this->graduation_year;
        }

        $year = AlumniYear::where('is_active', true)->value('year');

        return $year ? (string) $year : null;
    }

    /**
     * Label for UI lists and modals (includes payment year or cohort where relevant).
     */
    public function displayLabel(?AlumniYear $activePaymentYear = null): string
    {
        $name = $this->description ?: $this->feeType?->name ?: 'Fee';

        if ($paymentYear = $this->paymentYearLabel($activePaymentYear)) {
            return "{$name} — payment year {$paymentYear}";
        }

        if ($this->isOnboarding() && $this->graduation_year) {
            return "{$name} — class of {$this->graduation_year}";
        }

        return $name;
    }

    /**
     * Admin-configured payable window for annual dues (valid_from – valid_until).
     */
    public function validPeriodLabel(): ?string
    {
        if (!$this->isAnnualDueType() || !$this->valid_from) {
            return null;
        }

        $from = $this->valid_from->format('M j, Y');

        if ($this->valid_until) {
            return "{$from} – {$this->valid_until->format('M j, Y')}";
        }

        return $from;
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
        if (Auth::check() && Auth::user()->alumni) {
            return $this->isPaidByAlumni(Auth::user()->alumni);
        }

        if (!Auth::check()) {
            Log::info('User not authenticated for fee template payment check', ['fee_template_id' => $this->id]);

            return false;
        }

        return $this->transactions()
            ->whereHas('alumni', function ($q) {
                $q->where('user_id', Auth::id());
            })
            ->where('status', 'paid')
            ->exists();
    }

    public function isPaidByAlumni(Alumni $alumni): bool
    {
        return $this->transactions()
            ->where('alumni_id', $alumni->id)
            ->where('status', 'paid')
            ->exists();
    }

    public function getCompletedTransaction(): ?Transaction
    {
        $alumniId = Auth::user()?->alumni?->id;
        if (!$alumniId) {
            return null;
        }

        return $this->transactions()
            ->where('alumni_id', $alumniId)
            ->where('status', 'paid')
            ->latest('id')
            ->first();
    }
}
