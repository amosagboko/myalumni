<?php

namespace App\Services;

use App\Models\Alumni;
use App\Models\AlumniCategory;
use App\Models\AlumniYear;
use App\Models\FeeTemplate;
use App\Models\FeeType;
use App\Models\PaymentStructure;
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
     * Combined structures can surface every unpaid member that currently applies, including later-phase items.
     */
    public function getActiveFees(Alumni $alumni, $paymentYear = null): Collection
    {
        $phaseFees = $this->phaseActiveFees($alumni, $paymentYear);
        $combined = $this->resolveCombinedCheckout($alumni, $paymentYear);
        if (! $combined) {
            return $phaseFees;
        }

        $combinedIds = $combined['fees']->pluck('id');
        $extras = $phaseFees->reject(fn (FeeTemplate $fee) => $combinedIds->contains($fee->id))->values();

        return $combined['fees']->concat($extras)->values();
    }

    protected function phaseActiveFees(Alumni $alumni, $paymentYear = null): Collection
    {
        if (! $this->hasCompletedDefaultFees($alumni)) {
            return $this->getDefaultFeeTemplates($alumni)
                ->filter(fn (FeeTemplate $fee) => $fee->isValid())
                ->values();
        }

        $activeYear = $this->resolvePaymentYear($paymentYear);
        if (!$activeYear) {
            Log::warning('No active payment year found for alumni dues', ['alumni_id' => $alumni->id]);

            return collect();
        }

        $annualTemplate = $activeYear->annualDueTemplate();
        if (!$annualTemplate || ! $annualTemplate->isValid()) {
            return collect();
        }

        if ($annualTemplate->isPaidByAlumni($alumni)) {
            return collect();
        }

        return collect([$annualTemplate]);
    }

    /**
     * Active combined checkout for this alumni, or null to keep separate per-item payment.
     *
     * @return array{structure: PaymentStructure, fees: Collection<int, FeeTemplate>, total: float}|null
     */
    public function resolveCombinedCheckout(Alumni $alumni, $paymentYear = null): ?array
    {
        if (! $this->combinedServiceCodeFor($alumni)) {
            return null;
        }

        $activeYear = $this->resolvePaymentYear($paymentYear);
        $effectiveCategoryId = $this->resolveEffectiveCategoryId($alumni);

        $structures = PaymentStructure::query()
            ->combined()
            ->active()
            ->with(['items.feeTemplate.feeType', 'items.feeTemplate.category'])
            ->get();

        $candidates = [];

        foreach ($structures as $structure) {
            if ($structure->category_id && (int) $structure->category_id !== (int) $effectiveCategoryId) {
                continue;
            }

            if ($structure->graduation_year !== null
                && (int) $structure->graduation_year !== (int) $alumni->year_of_graduation) {
                continue;
            }

            $unpaid = $structure->items
                ->map(fn ($item) => $item->feeTemplate)
                ->filter()
                ->filter(fn (FeeTemplate $fee) => $this->templateAppliesToAlumni($fee, $alumni, $activeYear))
                ->filter(fn (FeeTemplate $fee) => $fee->isValid())
                ->filter(fn (FeeTemplate $fee) => ! $fee->isPaidByAlumni($alumni))
                ->values();

            if ($unpaid->count() < 2) {
                continue;
            }

            $specificity = 0;
            if ($structure->category_id) {
                $specificity += 2;
            }
            if ($structure->graduation_year !== null) {
                $specificity += 1;
            }

            $candidates[] = [
                'structure' => $structure,
                'fees' => $unpaid,
                'total' => (float) $unpaid->sum('amount'),
                'specificity' => $specificity,
            ];
        }

        if ($candidates === []) {
            return null;
        }

        usort($candidates, fn (array $a, array $b) => $b['specificity'] <=> $a['specificity']);

        $best = $candidates[0];
        unset($best['specificity']);

        return $best;
    }

    public function combinedServiceCodeFor(Alumni $alumni, string $mapKey = 'combined'): ?string
    {
        $codes = config('services.credocentral.service_codes.'.$mapKey);
        if (is_string($codes) && $codes !== '') {
            return $codes;
        }

        if (! is_array($codes)) {
            return null;
        }

        $slug = $this->resolveEffectiveCategorySlug($alumni);
        if (! $slug) {
            return null;
        }

        $code = $codes[$slug] ?? null;

        return is_string($code) && $code !== '' ? $code : null;
    }

    public function resolveEffectiveCategorySlug(Alumni $alumni): ?string
    {
        $alumni->loadMissing('category');
        $categoryId = $this->resolveEffectiveCategoryId($alumni);
        if ($categoryId) {
            $category = $alumni->category_id === $categoryId
                ? $alumni->category
                : AlumniCategory::find($categoryId);

            if ($category?->slug) {
                return $category->slug;
            }
        }

        $qualificationKey = $this->normalizedPostgraduateQualificationKey($alumni);
        if ($qualificationKey) {
            return 'postgraduate-'.$qualificationKey;
        }

        return $alumni->category?->slug;
    }

    public function feeIsPayableByAlumni(FeeTemplate $fee, Alumni $alumni, $paymentYear = null): bool
    {
        $combined = $this->resolveCombinedCheckout($alumni, $paymentYear);
        if ($combined && $combined['fees']->contains('id', $fee->id)) {
            return false;
        }

        return $this->getActiveFees($alumni, $paymentYear)->contains('id', $fee->id);
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
        $activeTemplates = $this->getSubscriptionFeeTemplatesForAlumni($alumni, includeInactive: false);

        if ($activeTemplates->isNotEmpty()) {
            return $activeTemplates->every(fn (FeeTemplate $fee) => $fee->isPaidByAlumni($alumni));
        }

        // No active subscription templates remain — either historically paid, or all deactivated.
        if ($this->hasPaidSubscriptionTransaction($alumni)) {
            return true;
        }

        return $this->getSubscriptionFeeTemplatesForAlumni($alumni, includeInactive: true)->isNotEmpty();
    }

    private function hasCompletedLegacySubscription(Alumni $alumni): bool
    {
        return $this->hasCompletedCohortSubscription($alumni);
    }

    private function unpaidSubscriptionFeeTemplates(Alumni $alumni, bool $includeInactive = false): Collection
    {
        return $this->getSubscriptionFeeTemplatesForAlumni($alumni, $includeInactive)
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
        $activeTemplates = $this->getOnboardingFeeTemplates($alumni, includeInactive: false);

        if ($activeTemplates->isNotEmpty()) {
            return $activeTemplates->every(fn (FeeTemplate $fee) => $fee->isPaidByAlumni($alumni));
        }

        // No active onboarding templates left to pay.
        $configuredTemplates = $this->getOnboardingFeeTemplates($alumni, includeInactive: true);

        return $configuredTemplates->isNotEmpty();
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
        if (!$template || ! $template->isValid() || $template->isPaidByAlumni($alumni)) {
            return null;
        }

        $combined = $this->resolveCombinedCheckout($alumni, $year);
        if ($combined && $combined['fees']->contains('id', $template->id)) {
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
        if (!$template || ! $template->isValid()) {
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

                    $combined = $this->resolveCombinedCheckout($alumni, $paymentYear);
                    if ($combined && $combined['fees']->contains('id', $template->id)) {
                        continue;
                    }

                    if ($this->createPendingDueTransaction($alumni, $template, $paymentYear)) {
                        $assigned++;
                    }
                }
            });

        return $assigned;
    }

    public function templateAppliesToAlumni(FeeTemplate $fee, Alumni $alumni, ?AlumniYear $activeYear = null): bool
    {
        $fee->loadMissing('feeType');

        if ($fee->feeType?->isEoiFee()) {
            return false;
        }

        $effectiveCategoryId = $this->resolveEffectiveCategoryId($alumni);
        if ($fee->category_id && (int) $fee->category_id !== (int) $effectiveCategoryId) {
            return false;
        }

        $year = (int) $fee->graduation_year;

        if ($fee->isAnnualRenewal() || $fee->isAnnualDueType()) {
            if ($year === FeeTemplate::PAYMENT_YEAR_ALL) {
                return true;
            }

            $activeYear ??= AlumniYear::where('is_active', true)->first();

            return $activeYear && $year === (int) $activeYear->year;
        }

        return $year === (int) $alumni->year_of_graduation
            || $year === FeeTemplate::PAYMENT_YEAR_ALL;
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

    protected function normalizedPostgraduateQualificationKey(Alumni $alumni): ?string
    {
        if (! $alumni->qualification_type) {
            return null;
        }

        $normalizedQualification = strtolower(str_replace(['.', ' ', '_'], '', trim($alumni->qualification_type)));
        $qualificationMap = [
            'phd' => 'phd',
            'ph.d' => 'phd',
            'doctorofphilosophy' => 'phd',
            'msc' => 'msc',
            'm.sc' => 'msc',
            'masters' => 'msc',
            'masterofscience' => 'msc',
            'pgd' => 'pgd',
            'pg.d' => 'pgd',
            'postgraduatediploma' => 'pgd',
        ];

        return $qualificationMap[$normalizedQualification] ?? null;
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
