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
     * Get the transaction fees for this category.
     */
    public function transactionFees()
    {
        return $this->hasMany(CategoryTransactionFee::class, 'category_id');
    }

    /**
     * Get the active transaction fees for this category.
     */
    public function activeTransactionFees()
    {
        return $this->transactionFees()->active();
    }

    /**
     * Get a specific fee type for this category.
     */
    public function getFee($feeType)
    {
        return $this->transactionFees()
            ->where('fee_type', $feeType)
            ->active()
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
}
