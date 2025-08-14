<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'message',
        'read_at'
    ];

    protected $casts = [
        'read_at' => 'datetime'
    ];

    /**
     * Get the sender of the message
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the receiver of the message
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Scope a query to only include messages older than specified days.
     */
    public function scopeOlderThan($query, $days)
    {
        return $query->where('created_at', '<', now()->subDays($days));
    }

    /**
     * Scope a query to only include messages newer than specified days.
     */
    public function scopeNewerThan($query, $days)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Check if this message is older than specified days.
     */
    public function isOlderThan($days): bool
    {
        return $this->created_at->lt(now()->subDays($days));
    }

    /**
     * Get the age of this message in days.
     */
    public function getAgeInDays(): int
    {
        return $this->created_at->diffInDays(now());
    }
}
