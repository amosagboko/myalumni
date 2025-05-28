<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AlumniCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the alumni that belong to this category.
     */
    public function alumni()
    {
        return $this->hasMany(Alumni::class, 'category_id');
    }

    /**
     * Get the fee templates for this category.
     */
    public function feeTemplates()
    {
        return $this->hasMany(FeeTemplate::class, 'category_id');
    }

    /**
     * Get the fee rules for this category.
     */
    public function feeRules()
    {
        return $this->hasMany(FeeRule::class, 'category_id');
    }

    /**
     * Get the active fee templates for this category.
     */
    public function activeFeeTemplates()
    {
        return $this->feeTemplates()
            ->where('is_active', true)
            ->where('valid_from', '<=', now())
            ->where(function ($query) {
                $query->whereNull('valid_until')
                    ->orWhere('valid_until', '>', now());
            });
    }

    /**
     * Get the active fee rules for this category.
     */
    public function activeFeeRules()
    {
        return $this->feeRules()->where('is_active', true);
    }

    /**
     * Get a specific fee type for this category.
     */
    public function getFee($feeType)
    {
        return $this->feeTemplates()
            ->whereHas('feeType', function ($query) use ($feeType) {
                $query->where('code', $feeType);
            })
            ->where('is_active', true)
            ->where('valid_from', '<=', now())
            ->where(function ($query) {
                $query->whereNull('valid_until')
                    ->orWhere('valid_until', '>', now());
            })
            ->first();
    }

    /**
     * Scope a query to only include active categories.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Check if this category has any associated fees (either templates or rules).
     */
    public function hasFees()
    {
        return $this->feeTemplates()->exists() || $this->feeRules()->exists();
    }
}
