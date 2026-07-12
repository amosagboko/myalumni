<?php

namespace App\Livewire\Social;

use App\Livewire\Social\Concerns\ListensForSocialBroadcasts;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationBell extends Component
{
    use ListensForSocialBroadcasts;

    public function getListeners(): array
    {
        return array_merge(
            [
                'connection-updated' => '$refresh',
                'notifications-updated' => '$refresh',
            ],
            $this->backgroundNotificationListener()
        );
    }

    public function markAsRead(string $notificationId): void
    {
        $notification = Auth::user()->notifications()->where('id', $notificationId)->first();

        if ($notification) {
            $notification->markAsRead();
        }

        $this->dispatch('notifications-updated');
    }

    public function markAllRead(): void
    {
        Auth::user()->unreadNotifications->markAsRead();
        $this->dispatch('notifications-updated');
    }

    public function render()
    {
        $user = Auth::user();
        $notifications = $user->notifications()->limit(10)->get();
        $unreadCount = $user->unreadNotifications()->count();

        return view('livewire.social.notification-bell', array_merge([
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ], $this->socialPollViewData()));
    }
}
