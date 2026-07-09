<?php

namespace App\Models;

use App\Models\Like;
use App\Models\User;
use App\Models\Event;
use App\Models\Comment;
use App\Models\PostMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\MutualFriendsTrait;

class Post extends Model
{
    use MutualFriendsTrait;

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    

    protected $fillable=[
        'uuid',
        'user_id',
        'content',
        'status',
        'visibility',
        'event_id',
        'likes'
    ];

    public const VISIBILITY_CONNECTIONS = 'connections';
    public const VISIBILITY_ALL_ALUMNI = 'all_alumni';

    /**
     * Get the user that owns the Post
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    
    /**
     * Get all of the media for the Post
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function media(): HasMany
    {
        return $this->hasMany(PostMedia::class);
    }

    /**
     * Get all of the likes for the Post
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }



    /**
     * Get all of the comments for the Post
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function isEventShare(): bool
    {
        return \Illuminate\Support\Facades\Schema::hasColumn('posts', 'event_id')
            && $this->event_id !== null;
    }

    /**
     * Get posts visible to the given user
     */
    public static function getVisiblePosts(User $user)
    {
        return self::visibleTo($user)
            ->with(['user', 'comments', 'likes'])
            ->latest()
            ->get();
    }

    public function scopeOlderThan($query, $days)
    {
        return $query->where('created_at', '<', now()->subDays($days));
    }
}
