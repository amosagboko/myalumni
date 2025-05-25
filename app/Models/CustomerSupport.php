<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerSupport extends Model
{
    //

    protected $fillable=[
        'user_id',
        'title',
        'content',
    ];

    /**
     * Get the user that owns the CustomerSupport
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
