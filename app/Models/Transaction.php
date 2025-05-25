<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'alumni_id',
        'category_transaction_fee_id',
        'amount',
        'status',
        'payment_reference',
        'paid_at',
        'is_test_mode'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'is_test_mode' => 'boolean'
    ];

    public function alumni()
    {
        return $this->belongsTo(Alumni::class);
    }

    public function categoryTransactionFee()
    {
        return $this->belongsTo(CategoryTransactionFee::class);
    }
}
