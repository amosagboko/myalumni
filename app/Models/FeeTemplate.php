<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FeeTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'alumni_year_id',
        'name',
        'description',
        'amount',
        'due_date',
        'is_active'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'is_active' => 'boolean'
    ];

    public function alumniYear()
    {
        return $this->belongsTo(AlumniYear::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
