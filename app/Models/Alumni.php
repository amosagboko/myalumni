<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use App\Services\AlumniDuesService;

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
        'category_id',
        'student_affairs_cleared',
        'academic_affairs_cleared',
    ];

    protected $casts = [
        'student_affairs_cleared' => 'boolean',
        'academic_affairs_cleared' => 'boolean',
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
        return app(AlumniDuesService::class)->getActiveFees($this, $year);
    }

    public function hasCompletedOnboardingFees(): bool
    {
        return app(AlumniDuesService::class)->hasCompletedDefaultFees($this);
    }

    public function hasCompletedDefaultFees(): bool
    {
        return app(AlumniDuesService::class)->hasCompletedDefaultFees($this);
    }

    public function getDuesPhase(): string
    {
        return app(AlumniDuesService::class)->getDuesPhase($this);
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
        $query = Transaction::where('alumni_id', $this->id)
            ->where('status', 'pending');

        if ($year) {
            $query->whereHas('feeTemplate', function ($q) use ($year) {
                $q->where('graduation_year', $year->year);
            });
        }

        return $query->get();
    }

    /**
     * Get all paid transactions for this alumni for a specific year.
     */
    public function getPaidTransactions($year = null)
    {
        $query = Transaction::where('alumni_id', $this->id)
            ->where('status', 'paid');

        if ($year) {
            $query->whereHas('feeTemplate', function ($q) use ($year) {
                $q->where('graduation_year', $year->year);
            });
        }

        return $query->get();
    }

    /**
     * Get all transactions for this alumni for a specific year.
     */
    public function getAllTransactions($year = null)
    {
        $query = Transaction::where('alumni_id', $this->id);

        if ($year) {
            $query->whereHas('feeTemplate', function ($q) use ($year) {
                $q->where('graduation_year', $year->year);
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
            ->activeApplicants()
            ->exists();
    }

    /**
     * Get the alumni's current expression of interest if any.
     */
    public function getCurrentExpressionOfInterest()
    {
        return Candidate::where('alumni_id', $this->id)
            ->activeApplicants()
            ->with(['election', 'office'])
            ->first();
    }

    /**
     * Whether all fees required for this alumni right now are paid.
     */
    public function hasPaidAllActiveFees(): bool
    {
        return $this->getActiveFees()->every(fn ($fee) => $fee->isPaid());
    }

    /**
     * Check if the alumni is eligible to express interest in a position.
     */
    public function isEligibleToExpressInterest(): bool
    {
        $hasPaidFees = $this->hasPaidAllActiveFees();

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
     * Get candidates where this alumni's user account is the approved agent.
     */
    public function approvedAgentCandidates(): HasMany
    {
        return $this->hasMany(Candidate::class, 'approved_agent_id', 'user_id');
    }
}

