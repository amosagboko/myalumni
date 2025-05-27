<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AlumniYear extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'start_date',
        'end_date',
        'is_active'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean'
    ];

    public function categoryTransactionFees()
    {
        return $this->hasMany(CategoryTransactionFee::class);
    }

    public function transactions()
    {
        return $this->hasManyThrough(Transaction::class, CategoryTransactionFee::class);
    }
}
