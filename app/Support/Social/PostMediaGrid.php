<?php

namespace App\Support\Social;

use App\Models\PostMedia;
use Illuminate\Support\Collection;

class PostMediaGrid
{
    public static function storageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        // Relative URL so images work on any host/port (e.g. localhost:8000).
        return '/storage/'.ltrim(str_replace('\\', '/', $path), '/');
    }

    /**
     * @param  iterable<int, PostMedia|array{full: string, thumb: string, alt?: string}>  $images
     * @return array<int, array{full: string, thumb: string, alt: string}>
     */
    public static function normalizeItems(iterable $images, string $defaultAlt = 'Post image'): array
    {
        $items = [];

        foreach ($images as $image) {
            if ($image instanceof PostMedia) {
                $fullPath = $image->getMediaPath();
                if (! $fullPath || $image->getMediaType() !== 'image') {
                    continue;
                }

                $thumbPath = $image->getThumbPath() ?? $fullPath;
                $items[] = [
                    'full' => self::storageUrl($fullPath),
                    'thumb' => self::storageUrl($thumbPath),
                    'alt' => $defaultAlt,
                ];

                continue;
            }

            if (is_array($image) && isset($image['full'], $image['thumb'])) {
                $items[] = [
                    'full' => $image['full'],
                    'thumb' => $image['thumb'],
                    'alt' => $image['alt'] ?? $defaultAlt,
                ];
            }
        }

        return $items;
    }

    /**
     * @return Collection<int, PostMedia>
     */
    public static function imageMediaForPost($post): Collection
    {
        return $post->media
            ->filter(fn (PostMedia $media) => $media->getMediaType() === 'image' && $media->getMediaPath())
            ->values();
    }
}
