<?php

namespace App\Services\Social;

use App\Models\FriendRequest;
use App\Models\Post;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class AlumniProfileService
{
    public function __construct(
        protected FeedService $feedService
    ) {}

    public function resolveProfileUser(User $user): User
    {
        abort_unless($user->hasRole('alumni'), 404);
        abort_if($user->is_banned, 404);
        abort_unless($user->isActive(), 404);

        return $user->loadMissing('alumni');
    }

    public function profileSubtitle(User $user): string
    {
        $parts = [];

        if ($user->alumni?->year_of_graduation) {
            $parts[] = 'Class of '.$user->alumni->year_of_graduation;
        }

        if ($user->alumni?->faculty) {
            $parts[] = $user->alumni->faculty;
        }

        return implode(' · ', $parts) ?: 'Alumni member';
    }

    public function avatarUrl(User $user): string
    {
        return $user->avatar
            ? '/storage/'.ltrim($user->avatar, '/')
            : '/images/user-8.png';
    }

    public function getConnectionCount(User $user): int
    {
        return FriendRequest::query()
            ->where('status', 'accepted')
            ->where(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                    ->orWhere('receiver_id', $user->id);
            })
            ->count();
    }

    public function countVisiblePosts(User $viewer, User $profileOwner): int
    {
        return $this->profilePostsQuery($viewer, $profileOwner)->count();
    }

    public function paginateProfilePosts(User $viewer, User $profileOwner, int $perPage = 10): LengthAwarePaginator
    {
        return $this->profilePostsQuery($viewer, $profileOwner)
            ->paginate(max(1, $perPage));
    }

    public function profilePostsQuery(User $viewer, User $profileOwner): Builder
    {
        $query = Post::query()
            ->where('user_id', $profileOwner->id)
            ->where('status', 'published');

        if ($viewer->id === $profileOwner->id) {
            return $this->withPostRelations($query)->latest();
        }

        if (! $this->feedService->supportsVisibility()) {
            if ($this->feedService->getConnectionUserIds($viewer)->contains($profileOwner->id)) {
                return $this->withPostRelations($query)->latest();
            }

            return $query->whereRaw('0 = 1');
        }

        $isConnected = $this->feedService->getConnectionUserIds($viewer)->contains($profileOwner->id);
        $viewerIsAlumni = $viewer->hasRole('alumni') || (bool) $viewer->alumni;

        return $this->withPostRelations(
            $query->where(function ($visibilityQuery) use ($isConnected, $viewerIsAlumni) {
                if ($viewerIsAlumni) {
                    $visibilityQuery->where('visibility', FeedService::VISIBILITY_ALL_ALUMNI);
                }

                if ($isConnected) {
                    $method = $viewerIsAlumni ? 'orWhere' : 'where';
                    $visibilityQuery->{$method}('visibility', FeedService::VISIBILITY_CONNECTIONS);
                }

                if (! $viewerIsAlumni && ! $isConnected) {
                    $visibilityQuery->whereRaw('0 = 1');
                }
            })
        )->latest();
    }

    protected function withPostRelations(Builder $query): Builder
    {
        $relations = ['user', 'media'];

        if (Schema::hasColumn('posts', 'event_id')) {
            $relations[] = 'event';
        }

        return $query->with($relations)->withCount('comments');
    }
}
