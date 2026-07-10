<?php

namespace App\Services\Social;

use App\Events\Social\SocialFeedUpdated;
use App\Events\Social\SocialNotificationCreated;
use Illuminate\Support\Facades\Log;

class SocialBroadcastService
{
    public function isEnabled(): bool
    {
        return config('broadcasting.default') === 'reverb';
    }

    public function feedUpdated(string $type, ?int $postId = null, ?int $actorUserId = null): void
    {
        if (! $this->isEnabled() || ! config('social.realtime_enabled', false)) {
            return;
        }

        try {
            SocialFeedUpdated::dispatch($type, $postId, $actorUserId);
        } catch (\Throwable $e) {
            Log::warning('Social feed broadcast failed', [
                'type' => $type,
                'post_id' => $postId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function notificationCreated(int $userId): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        try {
            SocialNotificationCreated::dispatch($userId);
        } catch (\Throwable $e) {
            Log::warning('Social notification broadcast failed', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
