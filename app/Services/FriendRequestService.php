<?php

namespace App\Services;

use App\Models\User;
use App\Models\FriendRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class FriendRequestService
{
    /**
     * Search for users to add as friends
     */
    public function searchUsers(string $searchTerm, int $currentUserId): Collection
    {
        try {
            Log::info('Searching users', ['term' => $searchTerm, 'current_user' => $currentUserId]);

            return User::where('id', '!=', $currentUserId)
                ->where(function($query) use ($searchTerm) {
                    $query->where('name', 'like', '%' . $searchTerm . '%')
                          ->orWhere('email', 'like', '%' . $searchTerm . '%');
                })
                ->where('is_banned', false)
                ->where('status', 'active')
                ->orderBy('name')
                ->limit(20)
                ->get()
                ->map(function($user) use ($currentUserId) {
                    $user->request_status = $this->getRequestStatus($currentUserId, $user->id);
                    return $user;
                });
        } catch (\Exception $e) {
            Log::error('Error searching users', [
                'error' => $e->getMessage(),
                'term' => $searchTerm
            ]);
            return collect();
        }
    }

    /**
     * Get the status of a friend request between two users
     */
    public function getRequestStatus(int $userId1, int $userId2): ?string
    {
        $request = FriendRequest::where(function($query) use ($userId1, $userId2) {
            $query->where(function($q) use ($userId1, $userId2) {
                $q->where('sender_id', $userId1)
                  ->where('receiver_id', $userId2);
            })
            ->orWhere(function($q) use ($userId1, $userId2) {
                $q->where('sender_id', $userId2)
                  ->where('receiver_id', $userId1);
            });
        })->first();

        return $request ? $request->status : null;
    }

    /**
     * Send a friend request
     */
    public function sendRequest(int $senderId, int $receiverId): bool
    {
        try {
            // Check if users are already friends
            if ($this->getRequestStatus($senderId, $receiverId) === 'accepted') {
                Log::info('Users are already friends', [
                    'sender_id' => $senderId,
                    'receiver_id' => $receiverId
                ]);
                return false;
            }

            // Create the request
            FriendRequest::createRequest($senderId, $receiverId);
            return true;
        } catch (\Exception $e) {
            Log::error('Error sending friend request', [
                'error' => $e->getMessage(),
                'sender_id' => $senderId,
                'receiver_id' => $receiverId
            ]);
            return false;
        }
    }

    /**
     * Accept a friend request
     */
    public function acceptRequest(int $receiverId, int $senderId): bool
    {
        try {
            Log::info('Attempting to accept friend request', [
                'sender_id' => $senderId,
                'receiver_id' => $receiverId
            ]);

            $request = FriendRequest::where('sender_id', $senderId)
                ->where('receiver_id', $receiverId)
                ->where('status', 'pending')
                ->first();

            if (!$request) {
                Log::warning('No pending request found', [
                    'sender_id' => $senderId,
                    'receiver_id' => $receiverId
                ]);
                return false;
            }

            $success = $request->accept();
            Log::info('Friend request accept result', [
                'success' => $success,
                'request_id' => $request->id
            ]);
            return $success;
        } catch (\Exception $e) {
            Log::error('Error accepting friend request', [
                'error' => $e->getMessage(),
                'sender_id' => $senderId,
                'receiver_id' => $receiverId
            ]);
            return false;
        }
    }

    /**
     * Reject a friend request
     */
    public function rejectRequest(int $receiverId, int $senderId): bool
    {
        try {
            Log::info('Attempting to reject friend request', [
                'sender_id' => $senderId,
                'receiver_id' => $receiverId
            ]);

            $request = FriendRequest::where('sender_id', $senderId)
                ->where('receiver_id', $receiverId)
                ->where('status', 'pending')
                ->first();

            if (!$request) {
                Log::warning('No pending request found', [
                    'sender_id' => $senderId,
                    'receiver_id' => $receiverId
                ]);
                return false;
            }

            $success = $request->reject();
            Log::info('Friend request reject result', [
                'success' => $success,
                'request_id' => $request->id
            ]);
            return $success;
        } catch (\Exception $e) {
            Log::error('Error rejecting friend request', [
                'error' => $e->getMessage(),
                'sender_id' => $senderId,
                'receiver_id' => $receiverId
            ]);
            return false;
        }
    }

    /**
     * Remove a friendship
     */
    public function unfriend(int $userId1, int $userId2): bool
    {
        try {
            $deleted = FriendRequest::where(function($query) use ($userId1, $userId2) {
                $query->where(function($q) use ($userId1, $userId2) {
                    $q->where('sender_id', $userId1)
                      ->where('receiver_id', $userId2);
                })
                ->orWhere(function($q) use ($userId1, $userId2) {
                    $q->where('sender_id', $userId2)
                      ->where('receiver_id', $userId1);
                });
            })
            ->where('status', 'accepted')
            ->delete();

            Log::info('Friendship removed', [
                'user1_id' => $userId1,
                'user2_id' => $userId2,
                'deleted' => $deleted
            ]);

            return $deleted > 0;
        } catch (\Exception $e) {
            Log::error('Error removing friendship', [
                'error' => $e->getMessage(),
                'user1_id' => $userId1,
                'user2_id' => $userId2
            ]);
            return false;
        }
    }

    /**
     * Get all friend requests for a user
     */
    public function getUserRequests(int $userId): array
    {
        try {
            Log::info('Getting user requests', ['user_id' => $userId]);

            // Get sent requests
            $sentRequests = FriendRequest::where('sender_id', $userId)
                ->with('receiver')
                ->get();
            
            Log::info('Sent requests query', [
                'sql' => FriendRequest::where('sender_id', $userId)->toSql(),
                'bindings' => FriendRequest::where('sender_id', $userId)->getBindings(),
                'count' => $sentRequests->count()
            ]);

            // Get received requests
            $receivedRequests = FriendRequest::getPendingRequests($userId);
            
            Log::info('Received requests query', [
                'sql' => FriendRequest::where('receiver_id', $userId)->where('status', 'pending')->toSql(),
                'bindings' => FriendRequest::where('receiver_id', $userId)->where('status', 'pending')->getBindings(),
                'count' => $receivedRequests->count()
            ]);

            // Get friends
            $friendRequests = FriendRequest::where(function($query) use ($userId) {
                $query->where('sender_id', $userId)
                      ->orWhere('receiver_id', $userId);
            })
            ->where('status', 'accepted')
            ->with(['sender', 'receiver'])
            ->get();
            
            Log::info('Friends query', [
                'sql' => FriendRequest::where(function($query) use ($userId) {
                    $query->where('sender_id', $userId)
                          ->orWhere('receiver_id', $userId);
                })->where('status', 'accepted')->toSql(),
                'bindings' => FriendRequest::where(function($query) use ($userId) {
                    $query->where('sender_id', $userId)
                          ->orWhere('receiver_id', $userId);
                })->where('status', 'accepted')->getBindings(),
                'count' => $friendRequests->count()
            ]);

            // Transform friend requests into user objects
            $friends = $friendRequests->map(function($request) use ($userId) {
                return $request->sender_id === $userId ? $request->receiver : $request->sender;
            });

            Log::info('User requests retrieved', [
                'sent_count' => $sentRequests->count(),
                'received_count' => $receivedRequests->count(),
                'friends_count' => $friends->count(),
                'raw_friend_requests_count' => $friendRequests->count()
            ]);

            return [
                'sent' => $sentRequests,
                'received' => $receivedRequests,
                'friends' => $friends
            ];
        } catch (\Exception $e) {
            Log::error('Error getting user requests', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $userId
            ]);
            return [
                'sent' => collect(),
                'received' => collect(),
                'friends' => collect()
            ];
        }
    }
} 