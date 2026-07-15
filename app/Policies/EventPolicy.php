<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view events');
    }

    public function view(User $user, Event $event): bool
    {
        if ($this->staffCanManage($user)) {
            return true;
        }

        if (! $event->is_published) {
            return (int) $event->user_id === (int) $user->id;
        }

        return $user->can('view events');
    }

    public function create(User $user): bool
    {
        return $user->can('create event');
    }

    public function update(User $user, Event $event): bool
    {
        if ($this->staffCanManage($user)) {
            return true;
        }

        return $user->can('create event')
            && (int) $event->user_id === (int) $user->id
            && $event->isCommunityEvent();
    }

    public function delete(User $user, Event $event): bool
    {
        return $this->update($user, $event);
    }

    private function staffCanManage(User $user): bool
    {
        return $user->hasAnyRole([
            'super-admin',
            'support-admin',
            'administrator',
            'alumni-relations-officer',
        ]);
    }
}
