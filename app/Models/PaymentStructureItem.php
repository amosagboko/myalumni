<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentStructureItem extends Model
{
    protected $fillable = [
        'payment_structure_id',
        'fee_template_id',
    ];

    public function structure(): BelongsTo
    {
        return $this->belongsTo(PaymentStructure::class, 'payment_structure_id');
    }

    public function feeTemplate(): BelongsTo
    {
        return $this->belongsTo(FeeTemplate::class);
    }
}
