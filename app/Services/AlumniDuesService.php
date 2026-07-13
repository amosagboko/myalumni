<?php

namespace App\Services;

use App\Models\Alumni;
use App\Models\AlumniCategory;
use App\Models\AlumniYear;
use App\Models\FeeTemplate;
use App\Models\FeeType;
use App\Models\Transaction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AlumniDuesService
{
    public const PHASE_EXEMPT = 'exempt';

    public const PHASE_ONBOARDING = 'onboarding';

    public const PHASE_ANNUAL = 'annual';

    public const PHASE_NONE = 'none';

    public function getDuesPhase(Alumni $alumni): string
    {
        if (! $this->hasCompletedDefaultFees($alumni)) {
            return self::PHASE_ONBOARDING;
        }

        $activeYear = AlumniYear::where('is_active', true)->first();
        if (!$activeYear || !$activeYear->annualDueTemplate()) {
            return self::PHASE_NONE;
        }

        return self::PHASE_ANNUAL;
    }

    /**
     * Fees the alumni must pay right now (default graduation fees, then current year's annual due).
     */
    public function getActiveFees(Alumni $alumni, $paymentYear = null): Collection
    {
        $activeYear = $this->resolvePaymentYear($paymentYear);
        if (!$activeYear) {
            Log::warning('No active payment year found for alumni dues', ['alumni_id' => $alumni->id]);

            return collect();
        }

        if (! $this->hasCompletedDefaultFees($alumni)) {
            return $this->getDefaultFeeTemplates($alumni);
        }

        $annualTemplate = $activeYear->annualDueTemplate();
        if (!$annualTemplate) {
            return collect();
        }

        if ($annualTemplate->isPaidByAlumni($alumni)) {
            return collect();
        }

        return collect([$annualTemplate]);
    }

    /**
     * Whether cohort default fees are satisfied (onboarding + subscription for 2025+, subscription for earlier cohorts).
     */
    public function hasCompletedDefaultFees(Alumni $alumni): bool
    {
        if ($alumni->year_of_graduation >= 2025) {
            return $this->hasCompletedOnboardingFeesForCohort($alumni)
                && $this->hasCompletedCohortSubscription($alumni);
        }

        return $this->hasCompletedLegacySubscription($alumni);
    }

    public function hasCompletedOnboardingFees(Alumni $alumni): bool
    {
        return $this->hasCompletedDefaultFees($alumni);
    }

    public function getDefaultFeeTemplates(Alumni $alumni, bool $includeInactive = false): Collection
    {
        if ($alumni->year_of_graduation >= 2025) {
            if (! $this->hasCompletedOnboardingFeesForCohort($alumni)) {
                return $this->getOnboardingFeeTemplates($alumni, $includeInactive);
            }

            return $this->unpaidSubscriptionFeeTemplates($alumni, $includeInactive);
        }

        return $this->unpaidSubscriptionFeeTemplates($alumni, $includeInactive);
    }

    private function hasCompletedCohortSubscription(Alumni $alumni): bool
    {
        $templates = $this->getSubscriptionFeeTemplatesForAlumni($alumni, includeInactive: true);

        if ($templates->isNotEmpty()) {
            return $templates->every(fn (FeeTemplate $fee) => $fee->isPaidByAlumni($alumni));
        }

        return $this->hasPaidSubscriptionTransaction($alumni);
    }

    private function hasCompletedLegacySubscription(Alumni $alumni): bool
    {
        return $this->hasCompletedCohortSubscription($alumni);
    }

    private function unpaidSubscriptionFeeTemplates(Alumni $alumni, bool $includeInactive = false): Collection
    {
        $templates = $this->getSubscriptionFeeTemplatesForAlumni($alumni, $includeInactive)
            ->filter(fn (FeeTemplate $fee) => ! $fee->isPaidByAlumni($alumni));

        if ($templates->isNotEmpty()) {
            return $templates->values();
        }

        if ($includeInactive || $this->hasCompletedCohortSubscription($alumni)) {
            return collect();
        }

        return $this->getSubscriptionFeeTemplatesForAlumni($alumni, includeInactive: true)
            ->filter(fn (FeeTemplate $fee) => ! $fee->isPaidByAlumni($alumni))
            ->values();
    }

    private function getSubscriptionFeeTemplatesForAlumni(Alumni $alumni, bool $includeInactive = false): Collection
    {
        $subscriptionType = FeeType::where('code', 'subscription')->where('is_active', true)->first();
        if (! $subscriptionType) {
            return collect();
        }

        $query = FeeTemplate::query()
            ->with('feeType')
            ->where('fee_type_id', $subscriptionType->id)
            ->where(function ($q) use ($alumni) {
                $q->where('graduation_year', $alumni->year_of_graduation)
                    ->orWhere('graduation_year', FeeTemplate::PAYMENT_YEAR_ALL);
            });

        if (! $includeInactive) {
            $query->active();
        }

        return $query->orderByRaw(
            'CASE WHEN graduation_year = ? THEN 0 ELSE 1 END',
            [$alumni->year_of_graduation]
        )->orderByDesc('id')->get();
    }

    private function hasPaidSubscriptionTransaction(Alumni $alumni): bool
    {
        $subscriptionType = FeeType::where('code', 'subscription')->where('is_active', true)->first();
        if (! $subscriptionType) {
            return true;
        }

        return Transaction::where('alumni_id', $alumni->id)
            ->where('status', 'paid')
            ->whereHas('feeTemplate', fn ($query) => $query->where('fee_type_id', $subscriptionType->id))
            ->exists();
    }

    private function hasCompletedOnboardingFeesForCohort(Alumni $alumni): bool
    {
        $templates = $this->getOnboardingFeeTemplates($alumni, includeInactive: true);

        if ($templates->isEmpty()) {
            return false;
        }

        return $templates->every(fn (FeeTemplate $fee) => $fee->isPaidByAlumni($alumni));
    }

    public function getOnboardingFeeTemplates(Alumni $alumni, bool $includeInactive = false): Collection
    {
        $categoryId = $this->resolveEffectiveCategoryId($alumni);
        if (!$categoryId) {
            return collect();
        }

        $query = FeeTemplate::query()
            ->with('feeType')
            ->where('graduation_year', $alumni->year_of_graduation)
            ->where('category_id', $categoryId)
            ->where(function ($q) {
                $q->where('fee_purpose', FeeTemplate::PURPOSE_ONBOARDING)
                    ->orWhereHas('feeType', function ($typeQuery) {
                        $typeQuery->whereIn('code', FeeType::ONBOARDING_FEE_CODES);
                    });
            });

        if (!$includeInactive) {
            $query->active();
        }

        return $query->orderBy('fee_type_id')->get();
    }

    public function feeIsPayableByAlumni(FeeTemplate $fee, Alumni $alumni, $paymentYear = null): bool
    {
        return $this->getActiveFees($alumni, $paymentYear)->contains('id', $fee->id);
    }

    /**
     * Create a pending transaction for the active year's annual due when appropriate.
     */
    public function ensureAnnualDueAssigned(Alumni $alumni, ?AlumniYear $paymentYear = null): ?Transaction
    {
        $year = $this->resolvePaymentYear($paymentYear);
        if (!$year) {
            return null;
        }

        if ($this->getDuesPhase($alumni) !== self::PHASE_ANNUAL) {
            return null;
        }

        $template = $year->annualDueTemplate();
        if (!$template || $template->isPaidByAlumni($alumni)) {
            return null;
        }

        return $this->createPendingDueTransaction($alumni, $template, $year);
    }

    /**
     * Assign pending annual-due transactions for all eligible alumni in a payment year.
     */
    public function assignAnnualDuesForPaymentYear(AlumniYear $paymentYear): int
    {
        $template = $paymentYear->annualDueTemplate();
        if (!$template) {
            return 0;
        }

        $assigned = 0;

        Alumni::query()
            ->orderBy('id')
            ->chunkById(100, function ($alumniRows) use ($paymentYear, $template, &$assigned) {
                foreach ($alumniRows as $alumni) {
                    if (! $this->hasCompletedDefaultFees($alumni)) {
                        continue;
                    }

                    if ($template->isPaidByAlumni($alumni)) {
                        continue;
                    }

                    if ($this->createPendingDueTransaction($alumni, $template, $paymentYear)) {
                        $assigned++;
                    }
                }
            });

        return $assigned;
    }

    public function resolveEffectiveCategoryId(Alumni $alumni): ?int
    {
        $effectiveCategoryId = $alumni->category_id;

        if ($alumni->category?->slug !== 'postgraduate') {
            return $effectiveCategoryId;
        }

        if (!$alumni->qualification_type) {
            return $effectiveCategoryId;
        }

        $normalizedQualification = strtolower(str_replace(['.', ' ', '_'], '', trim($alumni->qualification_type)));
        $qualificationMap = [
            'phd' => 'phd',
            'doctorofphilosophy' => 'phd',
            'msc' => 'msc',
            'masters' => 'msc',
            'masterofscience' => 'msc',
            'pgd' => 'pgd',
            'postgraduatediploma' => 'pgd',
        ];

        $qualificationKey = $qualificationMap[$normalizedQualification] ?? null;
        if (!$qualificationKey) {
            return $effectiveCategoryId;
        }

        $specializedCategory = AlumniCategory::where('slug', "postgraduate-{$qualificationKey}")->first();

        return $specializedCategory?->id ?? $effectiveCategoryId;
    }

    protected function resolvePaymentYear($paymentYear): ?AlumniYear
    {
        if ($paymentYear instanceof AlumniYear) {
            return $paymentYear;
        }

        return AlumniYear::where('is_active', true)->first();
    }

    protected function createPendingDueTransaction(
        Alumni $alumni,
        FeeTemplate $template,
        AlumniYear $paymentYear
    ): ?Transaction {
        $existingPending = Transaction::where('alumni_id', $alumni->id)
            ->where('fee_template_id', $template->id)
            ->where('status', 'pending')
            ->first();

        if ($existingPending) {
            return $existingPending;
        }

        $alumni->loadMissing('user');

        return Transaction::create([
            'alumni_id' => $alumni->id,
            'fee_template_id' => $template->id,
            'amount' => $template->amount,
            'status' => 'pending',
            'payment_reference' => 'DUE-' . $paymentYear->year . '-' . strtoupper(Str::random(8)),
            'payment_provider' => 'credocentral',
            'payment_details' => [
                'fee_type' => $template->feeType?->code,
                'fee_description' => $template->description,
                'payment_year' => $paymentYear->year,
                'dues_phase' => 'annual',
                'assigned_at' => now()->toIso8601String(),
            ],
            'metadata' => [
                'payment_year' => $paymentYear->year,
                'dues_phase' => 'annual',
                'auto_assigned' => true,
            ],
        ]);
    }
}
