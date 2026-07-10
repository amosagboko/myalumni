<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use App\Services\Social\FeedService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        $attributes = [
            'uuid' => (string) Str::uuid(),
            'user_id' => User::factory(),
            'content' => fake()->sentence(),
            'status' => 'published',
            'likes' => 0,
            'comments' => 0,
        ];

        if (Schema::hasColumn('posts', 'visibility')) {
            $attributes['visibility'] = FeedService::VISIBILITY_CONNECTIONS;
        }

        return $attributes;
    }

    public function allAlumni(): static
    {
        return $this->state(function () {
            if (! Schema::hasColumn('posts', 'visibility')) {
                return [];
            }

            return ['visibility' => FeedService::VISIBILITY_ALL_ALUMNI];
        });
    }

    public function privateStatus(): static
    {
        return $this->state(fn () => [
            'status' => 'private',
        ]);
    }
}
