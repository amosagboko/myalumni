<?php

namespace App\Notifications\Social;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ActivityNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $message,
        public string $url,
        public ?string $actorName = null,
        public ?string $actorAvatar = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => $this->message,
            'url' => $this->url,
            'actor_name' => $this->actorName,
            'actor_avatar' => $this->actorAvatar,
        ];
    }
}
