<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionItem extends Model
{
    protected $fillable = [
        'transaction_id',
        'fee_template_id',
        'fee_type_id',
        'category_id',
        'description',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function feeTemplate(): BelongsTo
    {
        return $this->belongsTo(FeeTemplate::class);
    }

    public function feeType(): BelongsTo
    {
        return $this->belongsTo(FeeType::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AlumniCategory::class, 'category_id');
    }

    public function getFormattedAmountAttribute(): string
    {
        return '₦'.number_format((float) $this->amount, 2);
    }
}
