<?php

namespace App\Services\Social;

use App\Models\User;
use App\Models\FriendRequest;
use App\Services\FriendRequestService;
use Illuminate\Support\Collection;

class ConnectionService
{
    public function __construct(
        protected FriendRequestService $friendRequestService,
        protected FeedService $feedService
    ) {}

    public function getPendingIncoming(User $user, int $limit = 3): Collection
    {
        return FriendRequest::with('sender')
            ->where('receiver_id', $user->id)
            ->where('status', 'pending')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function (FriendRequest $request) use ($user) {
                $request->mutual_count = $this->getMutualConnectionCount($user, $request->sender);

                return $request;
            });
    }

    public function getSuggestions(User $user, int $limit = 3): Collection
    {
        $excludeIds = $this->getExcludedUserIds($user);
        $graduationYear = $user->alumni?->year_of_graduation;

        $baseQuery = User::query()
            ->role('alumni')
            ->whereNotIn('id', $excludeIds)
            ->where('is_banned', false)
            ->where('status', 'active')
            ->with('alumni');

        if ($graduationYear) {
            $sameYear = (clone $baseQuery)
                ->whereHas('alumni', fn ($q) => $q->where('year_of_graduation', $graduationYear))
                ->inRandomOrder()
                ->limit($limit)
                ->get();

            if ($sameYear->count() >= $limit) {
                return $this->mapSuggestions($user, $sameYear);
            }

            $remaining = $limit - $sameYear->count();
            $others = (clone $baseQuery)
                ->whereNotIn('id', $sameYear->pluck('id'))
                ->inRandomOrder()
                ->limit($remaining)
                ->get();

            return $this->mapSuggestions($user, $sameYear->merge($others));
        }

        return $this->mapSuggestions(
            $user,
            $baseQuery->inRandomOrder()->limit($limit)->get()
        );
    }

    protected function mapSuggestions(User $user, Collection $users): Collection
    {
        return $users->map(fn (User $suggested) => [
            'user' => $suggested,
            'mutual_count' => $this->getMutualConnectionCount($user, $suggested),
        ]);
    }

    public function getMutualConnectionCount(User $user, User $other): int
    {
        if ($user->id === $other->id) {
            return 0;
        }

        $myConnections = $this->feedService->getConnectionUserIds($user);
        $theirConnections = $this->feedService->getConnectionUserIds($other);

        return $myConnections->intersect($theirConnections)->count();
    }

    protected function getExcludedUserIds(User $user): array
    {
        $related = FriendRequest::query()
            ->where(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                    ->orWhere('receiver_id', $user->id);
            })
            ->whereIn('status', ['pending', 'accepted'])
            ->get();

        $ids = [$user->id];

        foreach ($related as $request) {
            $ids[] = $request->sender_id === $user->id
                ? $request->receiver_id
                : $request->sender_id;
        }

        return array_unique($ids);
    }

    public function sendRequest(int $senderId, int $receiverId): bool
    {
        return $this->friendRequestService->sendRequest($senderId, $receiverId);
    }

    public function acceptRequest(int $receiverId, int $senderId): bool
    {
        return $this->friendRequestService->acceptRequest($receiverId, $senderId);
    }

    public function rejectRequest(int $receiverId, int $senderId): bool
    {
        return $this->friendRequestService->rejectRequest($receiverId, $senderId);
    }

    public function removeConnection(int $userId, int $otherUserId): bool
    {
        return $this->friendRequestService->unfriend($userId, $otherUserId);
    }

    public function getRequestStatus(int $userId, int $otherUserId): ?string
    {
        return $this->friendRequestService->getRequestStatus($userId, $otherUserId);
    }

    public function getConnectionActionMode(User $viewer, User $other): string
    {
        if ($viewer->id === $other->id) {
            return 'self';
        }

        $status = $this->getRequestStatus($viewer->id, $other->id);

        if ($status === 'accepted') {
            return 'accepted';
        }

        if ($status === 'pending') {
            $request = FriendRequest::query()
                ->where(function ($query) use ($viewer, $other) {
                    $query->where(function ($inner) use ($viewer, $other) {
                        $inner->where('sender_id', $viewer->id)
                            ->where('receiver_id', $other->id);
                    })->orWhere(function ($inner) use ($viewer, $other) {
                        $inner->where('sender_id', $other->id)
                            ->where('receiver_id', $viewer->id);
                    });
                })
                ->where('status', 'pending')
                ->first();

            return $request && $request->sender_id === $viewer->id ? 'pending' : 'received';
        }

        return 'none';
    }
}
