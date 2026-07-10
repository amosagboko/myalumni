<?php

namespace App\Livewire\Social\Concerns;

use Illuminate\Support\Facades\Auth;

trait ListensForSocialBroadcasts
{
    protected function broadcastingEnabled(): bool
    {
        return config('broadcasting.default') === 'reverb'
            && config('social.realtime_enabled', false);
    }

    protected function backgroundFeedListener(): array
    {
        if (! $this->broadcastingEnabled()) {
            return [];
        }

        return [
            'background-feed-sync' => 'onBackgroundFeedSync',
        ];
    }

    protected function backgroundNotificationListener(): array
    {
        if (! $this->broadcastingEnabled() || ! Auth::check()) {
            return [];
        }

        return [
            'background-notification-sync' => '$refresh',
        ];
    }

    public function onBackgroundFeedSync(?array $payload = null): void
    {
        if (! $this->shouldRefreshFromSocialBroadcast($payload)) {
            return;
        }

        if ($this->isOwnBroadcastAction($payload)) {
            return;
        }
    }

    protected function isOwnBroadcastAction(?array $payload): bool
    {
        if (! is_array($payload) || ! isset($payload['actorUserId'])) {
            return false;
        }

        return (int) $payload['actorUserId'] === Auth::id();
    }

    protected function shouldRefreshFromSocialBroadcast(?array $payload): bool
    {
        return true;
    }
}
