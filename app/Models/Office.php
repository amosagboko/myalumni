<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Office extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * Get the office contest applications for this office.
     */
    public function applications(): HasMany
    {
        return $this->hasMany(OfficeContestApplication::class);
    }

    /**
     * Get active applications for this office.
     */
    public function activeApplications()
    {
        return $this->applications()->where('status', 'pending');
    }

    /**
     * Scope a query to only include active offices.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the approved application for this office.
     */
    public function getApprovedApplication()
    {
        return $this->applications()
            ->where('status', 'approved')
            ->latest()
            ->first();
    }

    /**
     * Check if this office has an approved application.
     */
    public function hasApprovedApplication(): bool
    {
        return $this->applications()
            ->where('status', 'approved')
            ->exists();
    }
} 