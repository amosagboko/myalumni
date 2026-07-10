<?php

namespace Tests\Support;

use App\Models\FriendRequest;
use App\Models\User;

trait CreatesSocialConnections
{
    protected function connectUsers(User $userA, User $userB): FriendRequest
    {
        return FriendRequest::factory()->accepted()->create([
            'sender_id' => $userA->id,
            'receiver_id' => $userB->id,
        ]);
    }
}
