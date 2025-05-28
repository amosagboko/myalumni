<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeType extends Model
{
    use HasFactory;

    protected $table = 'fee_types';

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
        'is_system'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_system' => 'boolean'
    ];

    /**
     * Get the fee templates for this fee type.
     */
    public function feeTemplates(): HasMany
    {
        return $this->hasMany(FeeTemplate::class);
    }

    /**
     * Get the fee rules for this fee type.
     */
    public function feeRules(): HasMany
    {
        return $this->hasMany(FeeRule::class);
    }

    /**
     * Get active fee templates for this fee type.
     */
    public function activeFeeTemplates()
    {
        return $this->feeTemplates()
            ->where('is_active', true)
            ->where('valid_from', '<=', now())
            ->where(function ($query) {
                $query->where('valid_until', '>=', now())
                    ->orWhereNull('valid_until');
            });
    }

    /**
     * Get active fee rules for this fee type.
     */
    public function activeFeeRules()
    {
        return $this->feeRules()->where('is_active', true);
    }

    /**
     * Get all fees for this fee type (both old and new structures).
     */
    public function getAllFeesAttribute()
    {
        return FeeTemplate::where('fee_type_id', $this->id)->get();
    }

    /**
     * Scope a query to only include active fee types.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include system fee types.
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * Get the total number of fees (both old and new structures).
     */
    public function getTotalFeesCountAttribute(): int
    {
        return $this->template_count + $this->old_fee_count;
    }

    /**
     * Check if this fee type has any fees (either old or new structure).
     */
    public function hasFees(): bool
    {
        return $this->total_fees_count > 0;
    }

    /**
     * Get all active fees for this fee type.
     */
    public function getActiveFeesAttribute()
    {
        return FeeTemplate::where('fee_type_id', $this->id)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('valid_until')
                    ->orWhere('valid_until', '>', now());
            })
            ->where('valid_from', '<=', now())
            ->get();
    }

    /**
     * Get fees for a specific graduation year.
     */
    public function getFeesForYear($year)
    {
        return FeeTemplate::where('fee_type_id', $this->id)
            ->where('graduation_year', $year)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('valid_until')
                    ->orWhere('valid_until', '>', now());
            })
            ->where('valid_from', '<=', now())
            ->get();
    }

    /**
     * Get fees for a specific category.
     */
    public function getFeesForCategory($categoryId)
    {
        return FeeTemplate::where('fee_type_id', $this->id)
            ->where('category_id', $categoryId)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('valid_until')
                    ->orWhere('valid_until', '>', now());
            })
            ->where('valid_from', '<=', now())
            ->get();
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($feeType) {
            if (empty($feeType->code)) {
                $feeType->code = Str::slug($feeType->name);
            }
        });
    }
} 