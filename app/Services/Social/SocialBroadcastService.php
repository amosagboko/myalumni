<?php

namespace App\Services\Social;

use App\Events\Social\SocialFeedUpdated;
use App\Events\Social\SocialNotificationCreated;

class SocialBroadcastService
{
    public function isEnabled(): bool
    {
        return config('broadcasting.default') === 'reverb';
    }

    public function feedUpdated(string $type, ?int $postId = null): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        SocialFeedUpdated::dispatch($type, $postId);
    }

    public function notificationCreated(int $userId): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        SocialNotificationCreated::dispatch($userId);
    }
}
