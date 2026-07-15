<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

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

    public static function supportsContentFields(): bool
    {
        return Schema::hasColumn('events', 'is_published')
            && Schema::hasColumn('events', 'type');
    }

    /**
     * Scope a query to only include published events.
     */
    public function scopePublished($query)
    {
        if (Schema::hasColumn('events', 'is_published')) {
            return $query->where('is_published', true);
        }

        return $query;
    }

    /**
     * Scope a query to filter by type.
     */
    public function scopeOfType($query, string $type)
    {
        if (Schema::hasColumn('events', 'type')) {
            return $query->where('type', $type);
        }

        return $query;
    }

    /**
     * Alumni-facing sort: manual order, then soonest upcoming date, then newest.
     */
    public function scopeOrderedForAlumniDisplay($query)
    {
        if (Schema::hasColumn('events', 'order')) {
            return $query
                ->orderByRaw('COALESCE(`order`, 999999) ASC')
                ->orderByRaw('CASE WHEN `date` IS NULL THEN 1 ELSE 0 END')
                ->orderBy('date', 'asc')
                ->orderByDesc('created_at');
        }

        return $query
            ->orderByRaw('CASE WHEN `date` IS NULL THEN 1 ELSE 0 END')
            ->orderBy('date', 'asc')
            ->orderByDesc('created_at');
    }

    /**
     * Scope a query to order by order field or date.
     */
    public function scopeOrdered($query)
    {
        if (Schema::hasColumn('events', 'order')) {
            return $query->orderByRaw('COALESCE(`order`, 999999) ASC')->orderBy('date', 'desc');
        }

        return $query->orderBy('date', 'desc');
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

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function isCommunityEvent(): bool
    {
        return $this->type === 'opportunity';
    }

    public function scopeCreatedBy($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
