<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoryComment extends Model
{
    //

    protected $fillable=[
      'story_id',
      'user_id',
      'story_comment',
      'status',  
    ];
}
