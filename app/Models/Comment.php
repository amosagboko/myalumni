<?php

namespace App\Models;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\MutualFriendsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comment extends Model
{
    use HasFactory, MutualFriendsTrait;

    protected $fillable = [
        'post_id',
        'user_id',
        'parent_id',
        'comment',
        'status',
    ];

    /**
     * Get the user that owns the Comment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    

    /**
     * Get the posts that owns the Comment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id')->latest();
    }

    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    public function isReply(): bool
    {
        return ! is_null($this->parent_id);
    }

    /**
     * Get comments visible to the given user
     */
    public static function getVisibleComments(User $user, $postId)
    {
        return self::where('post_id', $postId)
            ->visibleTo($user)
            ->with('user')
            ->latest()
            ->get();
    }

    public function scopeOlderThan($query, $days)
    {
        return $query->where('created_at', '<', now()->subDays($days));
    }
}
