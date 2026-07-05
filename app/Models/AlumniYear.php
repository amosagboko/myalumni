<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AlumniYear extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function feeTemplates()
    {
        return $this->hasMany(FeeTemplate::class, 'graduation_year', 'year');
    }

    public function feeRules()
    {
        return $this->hasMany(FeeRule::class, 'graduation_year', 'year');
    }

    public function hasFees()
    {
        return $this->feeTemplates()->exists();
    }

    public function annualDueTemplate(): ?FeeTemplate
    {
        return $this->yearSpecificAnnualDueTemplate()
            ?? $this->sharedAnnualDueTemplate();
    }

    /**
     * Annual due template scoped to this payment year only (editable in Dues Config).
     */
    public function yearSpecificAnnualDueTemplate(): ?FeeTemplate
    {
        return FeeTemplate::query()
            ->annualRenewal()
            ->where('graduation_year', $this->year)
            ->whereNull('category_id')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Shared fallback used when this year has no dedicated template.
     */
    public function sharedAnnualDueTemplate(): ?FeeTemplate
    {
        $allYears = FeeTemplate::query()
            ->annualRenewal()
            ->where('graduation_year', FeeTemplate::PAYMENT_YEAR_ALL)
            ->whereNull('category_id')
            ->orderByDesc('id')
            ->first();

        if ($allYears) {
            return $allYears;
        }

        $subscriptionType = FeeType::where('code', 'subscription')->where('is_active', true)->first();
        if (!$subscriptionType) {
            return null;
        }

        return FeeTemplate::query()
            ->where('fee_type_id', $subscriptionType->id)
            ->whereNull('category_id')
            ->where(function ($q) {
                $q->where('fee_purpose', FeeTemplate::PURPOSE_ANNUAL_RENEWAL)
                    ->orWhereNull('fee_purpose');
            })
            ->where(function ($q) {
                $q->where('graduation_year', $this->year)
                    ->orWhere('graduation_year', FeeTemplate::PAYMENT_YEAR_ALL);
            })
            ->where('is_active', true)
            ->orderByRaw('CASE WHEN graduation_year = ? THEN 0 ELSE 1 END', [$this->year])
            ->orderByDesc('id')
            ->first();
    }

    public function onboardingTemplates()
    {
        return FeeTemplate::onboarding()->where('is_active', true);
    }

    /**
     * Onboarding templates grouped by graduation cohort year.
     */
    public function onboardingTemplatesByCohort(): \Illuminate\Support\Collection
    {
        return FeeTemplate::onboarding()
            ->with(['feeType', 'category'])
            ->orderBy('graduation_year')
            ->orderBy('fee_type_id')
            ->get()
            ->groupBy('graduation_year');
    }

    public function hasAnnualDueConfigured(): bool
    {
        return (bool) $this->annualDueTemplate();
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'alumni_year_id');
    }
}
