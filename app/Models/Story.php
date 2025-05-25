<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Story extends Model
{
    //

    protected $fillable=[
      'story_id',
      'user_id',
      'story_comment',
      'status',
    ];

    /**
     * Get the user that owns the Story
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function story(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
