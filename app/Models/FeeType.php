<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class FeeType extends Model
{
    use HasFactory;

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
     * Get the transaction fees for this fee type.
     */
    public function transactionFees()
    {
        return $this->hasMany(CategoryTransactionFee::class, 'fee_type_id');
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