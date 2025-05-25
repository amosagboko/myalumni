<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpressionOfInterest extends Model
{
    protected $table = 'expressions_of_interest';

    protected $fillable = [
        'election_id',
        'election_office_id',
        'alumni_id',
        'status',
        'rejection_reason',
        'submitted_at',
        'reviewed_at',
        'reviewed_by'
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime'
    ];

    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(ElectionOffice::class, 'election_office_id');
    }

    public function alumni(): BelongsTo
    {
        return $this->belongsTo(Alumni::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
} 