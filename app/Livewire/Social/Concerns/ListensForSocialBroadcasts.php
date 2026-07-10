<?php

namespace App\Livewire\Social\Concerns;

use Illuminate\Support\Facades\Auth;

trait ListensForSocialBroadcasts
{
    public int $broadcastTick = 0;

    protected function broadcastingEnabled(): bool
    {
        return config('broadcasting.default') === 'reverb';
    }

    protected function socialEchoListeners(): array
    {
        if (! $this->broadcastingEnabled()) {
            return [];
        }

        return [
            'echo:alumni.social,feed.updated' => 'onSocialFeedUpdated',
        ];
    }

    protected function socialNotificationEchoListeners(): array
    {
        if (! $this->broadcastingEnabled() || ! Auth::check()) {
            return [];
        }

        return [
            'echo-private:App.Models.User.'.Auth::id().',notification.created' => '$refresh',
        ];
    }

    public function onSocialFeedUpdated(?array $payload = null): void
    {
        if ($this->shouldRefreshFromSocialBroadcast($payload)) {
            $this->broadcastTick++;
        }
    }

    protected function shouldRefreshFromSocialBroadcast(?array $payload): bool
    {
        return true;
    }
}
