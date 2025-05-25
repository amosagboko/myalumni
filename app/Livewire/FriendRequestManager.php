<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use App\Models\FriendRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use App\Services\FriendRequestService;

#[Layout('layouts.alumni')]
class FriendRequestManager extends Component
{
    use WithPagination;

    public $counter = 0;
    public $search = '';
    public $searchError = '';
    public Collection $users;
    public Collection $friends;
    public Collection $sentRequests;
    public Collection $receivedRequests;
    public $followerCount = 0;
    public $followingCount = 0;
    public $isSearching = false;

    protected $listeners = [];

    protected $rules = [
        'search' => 'nullable|string|min:2|max:50'
    ];

    protected $friendRequestService;

    public function boot(FriendRequestService $friendRequestService)
    {
        $this->friendRequestService = $friendRequestService;
        $this->users = collect();
        $this->friends = collect();
        $this->sentRequests = collect();
        $this->receivedRequests = collect();
    }

    public function mount()
    {
        Log::info('FriendRequestManager mounting');
        $this->loadUserRequests();
        $this->checkDatabaseState();
    }

    private function checkDatabaseState()
    {
        try {
            $userId = Auth::id();
            
            // Check total friend requests
            $totalRequests = FriendRequest::count();
            Log::info('Total friend requests in database', ['count' => $totalRequests]);
            
            // Check requests for current user
            $userRequests = FriendRequest::where('sender_id', $userId)
                ->orWhere('receiver_id', $userId)
                ->get();
            
            Log::info('Friend requests for current user', [
                'total' => $userRequests->count(),
                'sent' => $userRequests->where('sender_id', $userId)->count(),
                'received' => $userRequests->where('receiver_id', $userId)->count(),
                'pending' => $userRequests->where('status', 'pending')->count(),
                'accepted' => $userRequests->where('status', 'accepted')->count(),
                'rejected' => $userRequests->where('status', 'rejected')->count()
            ]);
            
            // Check if there are any users
            $totalUsers = User::count();
            Log::info('Total users in database', ['count' => $totalUsers]);
            
        } catch (\Exception $e) {
            Log::error('Error checking database state', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function increment()
    {
        Log::info('Increment called');
        $this->counter++;
        Log::info('Counter is now: ' . $this->counter);
        $this->emit('searchPerformed');
    }

    public function performSearch()
    {
        Log::info('Perform search called with term: ' . $this->search);
        
        try {
            if (empty($this->search) || strlen($this->search) < 2) {
                Log::info('Search term too short');
                $this->users = collect();
                return;
            }

            $this->isSearching = true;
            $currentUserId = Auth::id();
            
            Log::info('Searching for users with term: ' . $this->search);
            
            $results = User::where('id', '!=', $currentUserId)
                ->where(function($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                          ->orWhere('email', 'like', '%' . $this->search . '%');
                })
                ->where('is_banned', false)
                ->where('status', 'active')
                ->orderBy('name')
                ->limit(20)
                ->get();

            Log::info('Search completed. Found ' . $results->count() . ' results');
            
            if ($results->isNotEmpty()) {
                Log::info('First result: ' . $results->first()->name);
            }

            $this->users = $results;
            $this->emit('searchPerformed');

        } catch (\Exception $e) {
            Log::error('Search error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            $this->users = collect();
            $this->searchError = 'An error occurred while searching. Please try again.';
        } finally {
            $this->isSearching = false;
        }
    }

    public function updatedSearch()
    {
        if (strlen($this->search) >= 2) {
            $this->searchUsers();
        } else {
            $this->users = collect();
        }
    }

    public function searchUsers()
    {
        try {
            Log::info('Searching users', ['term' => $this->search]);
            $this->users = $this->friendRequestService->searchUsers(
                $this->search,
                Auth::id()
            );
            $this->searchError = null;
        } catch (\Exception $e) {
            Log::error('Error searching users', [
                'error' => $e->getMessage(),
                'term' => $this->search
            ]);
            $this->searchError = 'An error occurred while searching users.';
            $this->users = collect();
        }
    }

    public function testSearch()
    {
        Log::info('Test search button clicked');
        try {
            // First, let's check if we can get any users at all
            $allUsers = User::where('id', '!=', Auth::id())->get();
            Log::info('Total users in database (excluding current user): ' . $allUsers->count());

            // Now try to get one user
            $results = User::where('id', '!=', Auth::id())->take(1)->get();
            $this->users = $results;
            
            if ($this->users->isNotEmpty()) {
                Log::info('Test search result: ' . $this->users->first()->name);
            } else {
                Log::info('No users found in database for test search');
            }
        } catch (\Exception $e) {
            Log::error('Test search error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            $this->users = collect();
        }
    }

    public function clearSearch()
    {
        $this->search = '';
        $this->searchError = null;
        $this->users = collect();
    }

    public function sendRequest($userId)
    {
        try {
            $success = $this->friendRequestService->sendRequest(Auth::id(), $userId);
            if ($success) {
                $this->loadUserRequests();
                $this->searchUsers(); // Refresh search results
            }
        } catch (\Exception $e) {
            Log::error('Error sending friend request', [
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
        }
    }

    public function loadCounts()
    {
        try {
            Log::info('Loading friend counts');
            $userId = Auth::id();

            $this->followerCount = FriendRequest::where('receiver_id', $userId)
                ->where('status', 'accepted')
                ->count();

            $this->followingCount = FriendRequest::where('sender_id', $userId)
                ->where('status', 'accepted')
                ->count();

            Log::info('Friend counts loaded', [
                'follower_count' => $this->followerCount,
                'following_count' => $this->followingCount
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading friend counts', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function loadUserRequests()
    {
        try {
            Log::info('Loading user requests', ['user_id' => Auth::id()]);
            
            $requests = $this->friendRequestService->getUserRequests(Auth::id());
            
            $this->sentRequests = collect($requests['sent']);
            $this->receivedRequests = collect($requests['received']);
            $this->friends = collect($requests['friends']);
            
            Log::info('User requests loaded', [
                'sent_count' => $this->sentRequests->count(),
                'received_count' => $this->receivedRequests->count(),
                'friends_count' => $this->friends->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading user requests', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->sentRequests = collect();
            $this->receivedRequests = collect();
            $this->friends = collect();
        }
    }

    public function acceptRequest($senderId)
    {
        try {
            $success = $this->friendRequestService->acceptRequest(Auth::id(), $senderId);
            if ($success) {
                $this->loadUserRequests();
            }
        } catch (\Exception $e) {
            Log::error('Error accepting friend request', [
                'error' => $e->getMessage(),
                'sender_id' => $senderId
            ]);
        }
    }

    public function rejectRequest($senderId)
    {
        try {
            $success = $this->friendRequestService->rejectRequest(Auth::id(), $senderId);
            if ($success) {
                $this->loadUserRequests();
            }
        } catch (\Exception $e) {
            Log::error('Error rejecting friend request', [
                'error' => $e->getMessage(),
                'sender_id' => $senderId
            ]);
        }
    }

    public function unfriend($userId)
    {
        try {
            $success = $this->friendRequestService->unfriend(Auth::id(), $userId);
            if ($success) {
                $this->loadUserRequests();
                $this->searchUsers(); // Refresh search results
            }
        } catch (\Exception $e) {
            Log::error('Error removing friend', [
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
        }
    }

    /**
     * Check if two users are mutual friends
     */
    public static function areMutualFriends($userId1, $userId2)
    {
        // Check if user1 has sent a friend request to user2 that was accepted
        $friendship1 = FriendRequest::where('sender_id', $userId1)
            ->where('receiver_id', $userId2)
            ->where('status', 'accepted')
            ->exists();

        // Check if user2 has sent a friend request to user1 that was accepted
        $friendship2 = FriendRequest::where('sender_id', $userId2)
            ->where('receiver_id', $userId1)
            ->where('status', 'accepted')
            ->exists();

        // They are mutual friends if both friendships exist
        return $friendship1 && $friendship2;
    }

    public function testCreateRequest()
    {
        try {
            Log::info('Creating test friend request');
            
            // Get a random user that's not the current user
            $otherUser = User::where('id', '!=', Auth::id())
                ->where('is_banned', false)
                ->where('status', 'active')
                ->inRandomOrder()
                ->first();
            
            if (!$otherUser) {
                Log::error('No eligible users found for test request');
                return;
            }
            
            $success = $this->friendRequestService->sendRequest(Auth::id(), $otherUser->id);
            
            Log::info('Test friend request result', [
                'success' => $success,
                'other_user_id' => $otherUser->id
            ]);
            
            if ($success) {
                $this->loadUserRequests();
            }
        } catch (\Exception $e) {
            Log::error('Error creating test request', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function render()
    {
        return view('livewire.friend-request-manager');
    }
}
