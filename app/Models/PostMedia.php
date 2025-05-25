<?php

namespace App\Models;

use App\Models\Post;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostMedia extends Model
{
    //
    protected $fillable=[
        'post_id',
        'filetype',
        'file',
        'position',
    ];

    protected $casts = [
        'file' => 'json',
    ];

    


    /**
     * Get the media that owns the PostMedia
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function media(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
