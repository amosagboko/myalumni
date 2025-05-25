<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Page extends Model
{
    //

    protected $fillable=[
        'uuid',
        'user_id',
        'icon',
        'description',
        'name',
        'type',
        'followers',
        'likes',
    ];


    /**
     * Get the user that owns the Page
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    /**
     * Get all of the comments for the Page
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'comment_id');
    }
}
