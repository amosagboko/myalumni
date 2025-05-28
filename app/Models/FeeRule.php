<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeRule extends Model
{
    protected $fillable = [
        'fee_type_id',
        'rule_type',
        'rule_parameters',
        'is_active'
    ];

    protected $casts = [
        'rule_parameters' => 'array',
        'is_active' => 'boolean'
    ];

    /**
     * Get the fee type that owns this rule.
     */
    public function feeType(): BelongsTo
    {
        return $this->belongsTo(FeeType::class);
    }

    /**
     * Scope a query to only include active rules.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include rules of a specific type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('rule_type', $type);
    }

    /**
     * Check if this rule is applicable for a given alumni.
     */
    public function isApplicable(Alumni $alumni): bool
    {
        if (!$this->is_active) {
            return false;
        }

        switch ($this->rule_type) {
            case 'graduation_year_range':
                $range = $this->rule_parameters['range'];
                return $alumni->graduation_year >= $range['start'] && 
                       $alumni->graduation_year <= $range['end'];

            case 'office_type':
                return $alumni->hasPendingOfficeApplication();

            case 'custom':
                // Implement custom rule logic here
                return true;

            default:
                return false;
        }
    }

    /**
     * Get the rule parameters with proper type casting.
     */
    public function getRuleParametersAttribute($value)
    {
        return json_decode($value, true);
    }

    /**
     * Set the rule parameters with proper JSON encoding.
     */
    public function setRuleParametersAttribute($value)
    {
        $this->attributes['rule_parameters'] = json_encode($value);
    }
} 