<?php

namespace App\Services\Social;

use App\Models\Post;
use App\Models\User;
use App\Notifications\Social\ActivityNotification;

class NotificationService
{
    public function __construct(
        protected SocialBroadcastService $broadcastService
    ) {}

    protected function postUrl(Post $post): string
    {
        return route('alumni.home', ['post' => $post->id]);
    }

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

        $this->broadcastService->notificationCreated($receiver->id);
        $this->broadcastService->feedUpdated('connection.updated');
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

        $this->broadcastService->notificationCreated($sender->id);
        $this->broadcastService->feedUpdated('connection.updated');
    }

    public function postLiked(User $postOwner, User $liker, Post $post): void
    {
        if ($postOwner->id === $liker->id) {
            return;
        }

        $postOwner->notify(new ActivityNotification(
            message: "{$liker->name} liked your post",
            url: $this->postUrl($post),
            actorName: $liker->name,
            actorAvatar: $liker->avatar,
            postId: $post->id,
        ));

        $this->broadcastService->notificationCreated($postOwner->id);
    }

    public function postCommented(User $postOwner, User $commenter, Post $post): void
    {
        if ($postOwner->id === $commenter->id) {
            return;
        }

        $postOwner->notify(new ActivityNotification(
            message: "{$commenter->name} commented on your post",
            url: $this->postUrl($post),
            actorName: $commenter->name,
            actorAvatar: $commenter->avatar,
            postId: $post->id,
        ));

        $this->broadcastService->notificationCreated($postOwner->id);
    }

    public function commentReplied(User $parentAuthor, User $replier, Post $post): void
    {
        if ($parentAuthor->id === $replier->id) {
            return;
        }

        $parentAuthor->notify(new ActivityNotification(
            message: "{$replier->name} replied to your comment",
            url: $this->postUrl($post),
            actorName: $replier->name,
            actorAvatar: $replier->avatar,
            postId: $post->id,
        ));

        $this->broadcastService->notificationCreated($parentAuthor->id);
    }
}
