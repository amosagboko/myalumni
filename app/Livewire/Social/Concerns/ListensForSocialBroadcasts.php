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

    /**
     * Quiet background refresh when realtime is off (wire:poll.visible).
     */
    public function refreshQuietly(): void
    {
        // Components may override; default relies on render().
    }

    /**
     * @return array{useBackgroundPoll: bool, pollInterval: int}
     */
    protected function socialPollViewData(): array
    {
        return $this->pollViewDataForConfigKey('social.poll_interval_seconds', 10);
    }

    /**
     * @return array{useBackgroundPoll: bool, pollInterval: int}
     */
    protected function socialConnectionsPollViewData(): array
    {
        return $this->pollViewDataForConfigKey('social.connections_poll_interval_seconds', 30);
    }

    /**
     * @return array{useBackgroundPoll: bool, pollInterval: int}
     */
    protected function pollViewDataForConfigKey(string $configKey, int $default): array
    {
        $interval = (int) config($configKey, $default);

        return [
            'useBackgroundPoll' => ! $this->broadcastingEnabled() && $interval > 0,
            'pollInterval' => $interval,
        ];
    }
}
