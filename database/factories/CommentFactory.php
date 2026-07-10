<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Schema;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition(): array
    {
        $attributes = [
            'post_id' => Post::factory(),
            'user_id' => User::factory(),
            'comment' => fake()->sentence(),
            'status' => 'published',
        ];

        if (Schema::hasColumn('comments', 'parent_id')) {
            $attributes['parent_id'] = null;
        }

        return $attributes;
    }

    public function reply(Comment $parent): static
    {
        return $this->state(function () use ($parent) {
            $state = ['post_id' => $parent->post_id];

            if (Schema::hasColumn('comments', 'parent_id')) {
                $state['parent_id'] = $parent->id;
            }

            return $state;
        });
    }
}
