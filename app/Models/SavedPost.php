<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedPost extends Model
{
    //

    protected $fillable=[
      'user_id',
      'post_id',  
    ];


    /**
     * Get the user that owns the SavedPost
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function savedpost(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
