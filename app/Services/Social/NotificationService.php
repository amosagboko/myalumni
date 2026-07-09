<?php

namespace App\Services\Social;

use App\Models\Post;
use App\Models\User;
use App\Notifications\Social\ActivityNotification;

class NotificationService
{
    public function connectionRequestReceived(User $receiver, User $sender): void
    {
        if ($receiver->id === $sender->id) {
            return;
        }

        $receiver->notify(new ActivityNotification(
            message: "{$sender->name} sent you a connection request",
            url: route('friends'),
            actorName: $sender->name,
            actorAvatar: $sender->avatar,
        ));
    }

    public function connectionRequestAccepted(User $sender, User $receiver): void
    {
        if ($receiver->id === $sender->id) {
            return;
        }

        $sender->notify(new ActivityNotification(
            message: "{$receiver->name} accepted your connection request",
            url: route('friends'),
            actorName: $receiver->name,
            actorAvatar: $receiver->avatar,
        ));
    }

    public function postLiked(User $postOwner, User $liker, Post $post): void
    {
        if ($postOwner->id === $liker->id) {
            return;
        }

        $postOwner->notify(new ActivityNotification(
            message: "{$liker->name} liked your post",
            url: route('alumni.home'),
            actorName: $liker->name,
            actorAvatar: $liker->avatar,
        ));
    }

    public function postCommented(User $postOwner, User $commenter, Post $post): void
    {
        if ($postOwner->id === $commenter->id) {
            return;
        }

        $postOwner->notify(new ActivityNotification(
            message: "{$commenter->name} commented on your post",
            url: route('alumni.home'),
            actorName: $commenter->name,
            actorAvatar: $commenter->avatar,
        ));
    }
}
