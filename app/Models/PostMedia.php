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
        return $this->belongsTo(Post::class, 'post_id');
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    public function getMediaPath(): ?string
    {
        return $this->filePayload()['media_path'] ?? null;
    }

    public function getThumbPath(): ?string
    {
        $data = $this->filePayload();

        return $data['thumb_path'] ?? $data['media_path'] ?? null;
    }

    public function getMediaType(): ?string
    {
        $data = $this->filePayload();

        return $data['media_type'] ?? $this->filetype;
    }

    protected function filePayload(): array
    {
        $data = $this->file;

        if (is_string($data)) {
            $data = json_decode($data, true);
        }

        return is_array($data) ? $data : [];
    }
}
