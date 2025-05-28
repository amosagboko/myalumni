<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;

class Alumni extends Model
{
    protected $table = 'alumni';

    protected $fillable = [
        'user_id',
        'matric_number',
        'programme',
        'department',
        'faculty',
        'year_of_graduation',
        'date_of_birth',
        'state',
        'lga',
        'year_of_entry',
        'gender',
        'title',
        'nationality',
        'contact_address',
        'phone_number',
        'qualification_type',
        'qualification_details',
        'present_employer',
        'present_designation',
        'professional_bodies',
        'student_responsibilities',
        'hobbies',
        'other_information',
        'created_by',
    ];

    /**
     * Get the user that owns the Alumni
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the category that the alumni belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(AlumniCategory::class, 'category_id');
    }

    /**
     * Get all active transaction fees for this alumni's category and year.
     */
    public function getActiveFees($year = null)
    {
        // Get the active year if not specified
        $activeYear = $year ?? AlumniYear::where('is_active', true)->first();
        if (!$activeYear) {
            Log::warning('No active year found');
            return collect([]);
        }

        Log::info('Active year found', ['year' => $activeYear->year]);

        // For 2024 graduates, no fees
        if ($this->year_of_graduation === 2024) {
            Log::info('Alumni graduated in 2024 - no fees applicable');
            return collect([]);
        }

        // For 2023 and earlier graduates, only subscription fee
        if ($this->year_of_graduation <= 2023) {
            Log::info('Alumni graduated in 2023 or earlier', ['graduation_year' => $this->year_of_graduation]);

            // Get the subscription fee type
                $subscriptionFeeType = FeeType::where('code', 'subscription')
                    ->where('is_active', true)
                    ->first();

                Log::info('Subscription fee type lookup result', [
                    'found' => (bool)$subscriptionFeeType,
                    'fee_type_id' => $subscriptionFeeType?->id,
                    'fee_type_code' => $subscriptionFeeType?->code
                ]);

                if ($subscriptionFeeType) {
                    // Get the fee template for the active year
                    $fees = FeeTemplate::where('fee_type_id', $subscriptionFeeType->id)
                        ->where('graduation_year', $activeYear->year)
                        ->where('is_active', true)
                        ->where('valid_from', '<=', now())
                        ->where(function ($query) {
                            $query->whereNull('valid_until')
                                ->orWhere('valid_until', '>', now());
                        })
                        ->get();

                    Log::info('Subscription fees lookup result', [
                        'count' => $fees->count(),
                        'fees' => $fees->map(function($fee) {
                            return [
                                'id' => $fee->id,
                                'amount' => $fee->amount,
                                'is_active' => $fee->is_active,
                                'fee_type_id' => $fee->fee_type_id,
                                'graduation_year' => $fee->graduation_year
                            ];
                        })->toArray()
                    ]);

                    // Return the fees (even if empty) - don't fall through to 2025+ logic
                    return $fees;
                }
                
                Log::warning('No subscription fee type found');
            return collect([]);
        }

        // For 2025+ graduates only, get fees based on the alumni's category and active year
        if ($this->year_of_graduation >= 2025) {
            $fees = FeeTemplate::where('graduation_year', $activeYear->year)
                ->where('is_active', true)
                ->where('valid_from', '<=', now())
                ->where(function ($query) {
                    $query->whereNull('valid_until')
                        ->orWhere('valid_until', '>', now());
                })
                ->get();

            return $fees;
        }

        // For any other case (shouldn't happen), return empty collection
        return collect([]);
    }

    /**
     * Calculate the total amount of fees for this alumni for a specific year.
     */
    public function calculateTotalFees($year = null)
    {
        return $this->getActiveFees($year)->sum('amount');
    }

    /**
     * Get a formatted string of the total fees for a specific year.
     */
    public function getFormattedTotalFees($year = null)
    {
        return '₦' . number_format($this->calculateTotalFees($year), 2);
    }

    /**
     * Get all pending transactions for this alumni for a specific year.
     */
    public function getPendingTransactions($year = null)
    {
        $query = Transaction::where('user_id', $this->user_id)
            ->where('status', 'pending');

        if ($year) {
            $query->whereHas('categoryTransactionFee', function ($q) use ($year) {
                $q->where('alumni_year_id', $year->id);
            });
        }

        return $query->get();
    }

    /**
     * Get all paid transactions for this alumni for a specific year.
     */
    public function getPaidTransactions($year = null)
    {
        $query = Transaction::where('user_id', $this->user_id)
            ->where('status', 'paid');

        if ($year) {
            $query->whereHas('categoryTransactionFee', function ($q) use ($year) {
                $q->where('alumni_year_id', $year->id);
            });
        }

        return $query->get();
    }

    /**
     * Get all transactions for this alumni for a specific year.
     */
    public function getAllTransactions($year = null)
    {
        $query = Transaction::where('user_id', $this->user_id);

        if ($year) {
            $query->whereHas('categoryTransactionFee', function ($q) use ($year) {
                $q->where('alumni_year_id', $year->id);
            });
        }

        return $query->get();
    }

    /**
     * Get the current active year's fees.
     */
    public function getCurrentYearFees()
    {
        $currentYear = AlumniYear::where('is_active', true)->first();
        return $this->getActiveFees($currentYear);
    }

    /**
     * Get the current active year's total fees.
     */
    public function getCurrentYearTotalFees()
    {
        $currentYear = AlumniYear::where('is_active', true)->first();
        return $this->calculateTotalFees($currentYear);
    }

    /**
     * Get the formatted current year's total fees.
     */
    public function getFormattedCurrentYearTotalFees()
    {
        $currentYear = AlumniYear::where('is_active', true)->first();
        return $this->getFormattedTotalFees($currentYear);
    }

    /**
     * Check if the alumni has already expressed interest in any position.
     */
    public function hasExpressedInterest(): bool
    {
        return Candidate::where('alumni_id', $this->id)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();
    }

    /**
     * Get the alumni's current expression of interest if any.
     */
    public function getCurrentExpressionOfInterest()
    {
        return Candidate::where('alumni_id', $this->id)
            ->whereIn('status', ['pending', 'approved'])
            ->with(['election', 'office'])
            ->first();
    }

    /**
     * Check if the alumni is eligible to express interest in a position.
     */
    public function isEligibleToExpressInterest(): bool
    {
        // Check if all fees are paid
        $hasPaidFees = $this->getActiveFees()->every(function($fee) {
            return $fee->isPaid();
        });

        // Check if alumni has not already expressed interest
        $hasNotExpressedInterest = !$this->hasExpressedInterest();

        // Check if bio data is complete
        $hasCompleteBioData = $this->contact_address && 
            $this->phone_number && 
            $this->qualification_type;

        return $hasPaidFees && $hasNotExpressedInterest && $hasCompleteBioData;
    }

    /**
     * Get the candidates where this alumni is the suggested agent.
     */
    public function suggestedAgentCandidates(): HasMany
    {
        return $this->hasMany(Candidate::class, 'suggested_agent_id');
    }

    /**
     * Get the candidates where this alumni is the approved agent.
     */
    public function approvedAgentCandidates(): HasMany
    {
        return $this->hasMany(Candidate::class, 'approved_agent_id');
    }
}

