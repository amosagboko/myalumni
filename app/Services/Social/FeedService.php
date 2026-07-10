<?php

namespace App\Services\Social;

use App\Models\Post;
use App\Models\User;
use App\Models\FriendRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class FeedService
{
    public const VISIBILITY_CONNECTIONS = 'connections';
    public const VISIBILITY_ALL_ALUMNI = 'all_alumni';

    public function supportsVisibility(): bool
    {
        return Schema::hasColumn('posts', 'visibility');
    }

    public function getConnectionUserIds(User $user): Collection
    {
        return FriendRequest::query()
            ->where('status', 'accepted')
            ->where(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                    ->orWhere('receiver_id', $user->id);
            })
            ->get()
            ->map(fn ($request) => $request->sender_id === $user->id
                ? $request->receiver_id
                : $request->sender_id);
    }

    public function feedQuery(User $user): Builder
    {
        if (!$this->supportsVisibility()) {
            return Post::visibleTo($user)
                ->where('status', 'published')
                ->with($this->baseRelations())
                ->withCount('comments')
                ->latest();
        }

        $connectionIds = $this->getConnectionUserIds($user);

        return Post::query()
            ->where('status', 'published')
            ->where(function ($query) use ($user, $connectionIds) {
                $query->where('user_id', $user->id)
                    ->orWhere(function ($q) use ($user) {
                        if ($user->hasRole('alumni') || $user->alumni) {
                            $q->where('visibility', self::VISIBILITY_ALL_ALUMNI);
                        } else {
                            $q->whereRaw('0 = 1');
                        }
                    })
                    ->orWhere(function ($q) use ($connectionIds) {
                        $q->where('visibility', self::VISIBILITY_CONNECTIONS)
                            ->whereIn('user_id', $connectionIds);
                    });
            })
            ->with($this->baseRelations())
            ->withCount('comments')
            ->latest();
    }

    protected function baseRelations(): array
    {
        $relations = [
            'user',
            'media',
            'likes',
        ];

        if (Schema::hasColumn('posts', 'event_id')) {
            $relations[] = 'event';
        }

        return $relations;
    }

    public function paginateFeed(User $user, ?int $perPage = null): LengthAwarePaginator
    {
        $perPage = $perPage ?? config('social.feed_per_page', 15);

        return $this->feedQuery($user)->paginate(max(1, $perPage));
    }

    public function canViewPost(Post $post, User $user): bool
    {
        if ($post->user_id === $user->id) {
            return true;
        }

        if (!$this->supportsVisibility()) {
            return $post->canBeViewedBy($user);
        }

        $visibility = $post->visibility ?? self::VISIBILITY_CONNECTIONS;

        if ($visibility === self::VISIBILITY_ALL_ALUMNI) {
            return $user->hasRole('alumni') || (bool) $user->alumni;
        }

        if ($visibility === self::VISIBILITY_CONNECTIONS) {
            return $this->getConnectionUserIds($user)->contains($post->user_id);
        }

        return false;
    }

    public function visibilityOptions(): array
    {
        return [
            self::VISIBILITY_CONNECTIONS => 'My Connections',
            self::VISIBILITY_ALL_ALUMNI => 'All Alumni',
        ];
    }
}
