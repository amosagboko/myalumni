<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    //

    protected $fillable=[
        'user_id',
        'type',
        'eventname',
        'date',
        'venue',
        'description',
        'image',
        'link',
        'is_published',
        'order',
    ];

    protected $casts = [
        'date' => 'date',
        'is_published' => 'boolean',
    ];

    /**
     * Scope a query to only include published events.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope a query to filter by type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to order by order field or date.
     */
    public function scopeOrdered($query)
    {
        return $query->orderByRaw('COALESCE(`order`, 999999) ASC')->orderBy('date', 'desc');
    }


    /**
     * Get the user that owns the Event
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
