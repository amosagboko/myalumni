<?php

namespace App\Events\Social;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SocialFeedUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $type,
        public ?int $postId = null,
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel('alumni.social')];
    }

    public function broadcastAs(): string
    {
        return 'feed.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'type' => $this->type,
            'postId' => $this->postId,
        ];
    }
}
