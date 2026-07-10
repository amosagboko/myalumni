<?php

namespace App\Services\Social;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class PostImageProcessor
{
    public function process(UploadedFile $file): array
    {
        $manager = new ImageManager(new Driver());

        $displayMax = (int) config('social.post_images.display_max', 1920);
        $thumbSize = (int) config('social.post_images.thumb_size', 600);
        $quality = (int) config('social.post_images.jpeg_quality', 85);
        $thumbQuality = (int) config('social.post_images.thumb_quality', 80);

        $display = $manager->read($file);
        $display->scaleDown($displayMax, $displayMax);

        $thumb = $manager->read($file);
        $thumb->cover($thumbSize, $thumbSize);

        $baseName = 'post-images/'.Str::uuid();
        $displayPath = $baseName.'_display.jpg';
        $thumbPath = $baseName.'_thumb.jpg';

        Storage::disk('public')->put($displayPath, (string) $display->toJpeg($quality));
        Storage::disk('public')->put($thumbPath, (string) $thumb->toJpeg($thumbQuality));

        return [
            'media_path' => $displayPath,
            'thumb_path' => $thumbPath,
            'media_type' => 'image',
            'width' => $display->width(),
            'height' => $display->height(),
        ];
    }
}
