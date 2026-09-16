<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentStructure extends Model
{
    public const MODE_SEPARATE = 'separate';

    public const MODE_COMBINED = 'combined';

    protected $fillable = [
        'name',
        'display_title',
        'payment_mode',
        'graduation_year',
        'category_id',
        'credo_service_code_key',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'graduation_year' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(AlumniCategory::class, 'category_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PaymentStructureItem::class);
    }

    public function feeTemplates(): BelongsToMany
    {
        return $this->belongsToMany(FeeTemplate::class, 'payment_structure_items')
            ->withTimestamps();
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function isCombined(): bool
    {
        return $this->payment_mode === self::MODE_COMBINED;
    }

    public function payerTitle(): string
    {
        return $this->display_title ?: $this->name ?: 'Combined payment';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCombined($query)
    {
        return $query->where('payment_mode', self::MODE_COMBINED);
    }

    public function formattedTotal(): string
    {
        $total = $this->relationLoaded('feeTemplates')
            ? $this->feeTemplates->sum('amount')
            : $this->feeTemplates()->sum('amount');

        return '₦'.number_format((float) $total, 2);
    }
}
