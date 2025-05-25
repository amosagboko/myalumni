<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccreditedVoter extends Model
{
    protected $fillable = [
        'election_id',
        'alumni_id',
        'has_voted',
        'accredited_at',
        'voted_at'
    ];

    protected $casts = [
        'has_voted' => 'boolean',
        'accredited_at' => 'datetime',
        'voted_at' => 'datetime'
    ];

    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    public function alumni(): BelongsTo
    {
        return $this->belongsTo(Alumni::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function markAsVoted()
    {
        $this->update([
            'has_voted' => true,
            'voted_at' => now()
        ]);
    }

    public function hasVoted(): bool
    {
        return $this->has_voted;
    }
} 