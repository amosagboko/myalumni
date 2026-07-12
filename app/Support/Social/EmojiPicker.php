<?php

namespace App\Support\Social;

use InvalidArgumentException;

class EmojiPicker
{
    public function append(string $text, string $emoji): string
    {
        if (! in_array($emoji, $this->items(), true)) {
            throw new InvalidArgumentException('That emoji is not available.');
        }

        return $text.$emoji;
    }

    public function items(): array
    {
        return array_values(array_unique(
            config('social.emoji_picker.items', [])
        ));
    }
}
