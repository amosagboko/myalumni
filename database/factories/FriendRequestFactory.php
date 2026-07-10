<?php

namespace Database\Factories;

use App\Models\FriendRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FriendRequest>
 */
class FriendRequestFactory extends Factory
{
    protected $model = FriendRequest::class;

    public function definition(): array
    {
        return [
            'sender_id' => User::factory(),
            'receiver_id' => User::factory(),
            'status' => 'pending',
        ];
    }

    public function accepted(): static
    {
        return $this->state(fn () => [
            'status' => 'accepted',
        ]);
    }
}
