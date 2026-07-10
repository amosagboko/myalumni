<?php

namespace Tests\Unit\Social;

use App\Models\Post;
use App\Models\User;
use App\Notifications\Social\ActivityNotification;
use App\Services\Social\NotificationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function test_post_activity_notifications_include_post_deep_link(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $actor = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $owner->id]);

        $service = app(NotificationService::class);
        $service->postLiked($owner, $actor, $post);

        Notification::assertSentTo($owner, ActivityNotification::class, function (ActivityNotification $notification) use ($post) {
            return $notification->postId === $post->id
                && str_contains($notification->url, 'post='.$post->id);
        });
    }
}
