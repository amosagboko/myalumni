<?php

namespace App\Livewire;

use App\Models\User;
use App\Livewire\Social\Concerns\ListensForSocialBroadcasts;
use Livewire\Component;
use App\Models\FriendRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use App\Services\FriendRequestService;
use App\Services\Social\ConnectionService;

#[Layout('layouts.alumni')]
class FriendRequestManager extends Component
{
    use ListensForSocialBroadcasts;
    use WithPagination;

    public $counter = 0;
    public $search = '';
    public $searchError = '';
    public Collection $searchResults;
    public Collection $friends;
    public Collection $sentRequests;
    public Collection $receivedRequests;
    public $followerCount = 0;
    public $followingCount = 0;
    public $isSearching = false;

    public string $activeTab = 'connections';

    protected $listeners = [
        'connection-updated' => 'loadUserRequests',
    ];

    public function refreshQuietly(): void
    {
        $this->loadUserRequests();

        if ($this->search && strlen($this->search) >= 2) {
            $this->searchUsers();
        }
    }

    protected $rules = [
        'search' => 'nullable|string|min:2|max:50'
    ];

    protected $friendRequestService;
    protected ConnectionService $connectionService;

    public function boot(FriendRequestService $friendRequestService, ConnectionService $connectionService)
    {
        $this->friendRequestService = $friendRequestService;
        $this->connectionService = $connectionService;
        $this->searchResults = collect();
        $this->friends = collect();
        $this->sentRequests = collect();
        $this->receivedRequests = collect();
    }

    public function setActiveTab(string $tab): void
    {
        if (in_array($tab, ['connections', 'received', 'sent'], true)) {
            $this->activeTab = $tab;
        }
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

    public function performSearch(): void
    {
        $this->searchUsers();
    }

    public function updatedSearch(): void
    {
        $this->searchError = null;
        $term = trim($this->search);

        if (strlen($term) >= 2) {
            $this->searchUsers();
        } else {
            $this->searchResults = collect();
        }
    }

    public function searchUsers(): void
    {
        try {
            $this->searchResults = $this->connectionService->searchAlumni(
                Auth::user(),
                $this->search
            );
            $this->searchError = null;
        } catch (\Exception $e) {
            Log::error('Error searching users', [
                'error' => $e->getMessage(),
                'term' => $this->search,
            ]);
            $this->searchError = 'An error occurred while searching. Please try again.';
            $this->searchResults = collect();
        }
    }

    public function clearSearch(): void
    {
        $this->search = '';
        $this->searchError = null;
        $this->searchResults = collect();
    }

    public function testSearch(): void
    {
        $this->search = 'a';
        $this->searchUsers();
    }

    public function sendRequest($userId)
    {
        try {
            $success = $this->friendRequestService->sendRequest(Auth::id(), $userId);
            if ($success) {
                $this->loadUserRequests();
                $this->searchUsers();
                $this->dispatch('connection-updated');
                $this->dispatch('notifications-updated');
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
                $this->dispatch('connection-updated');
                $this->dispatch('notifications-updated');
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
                $this->dispatch('connection-updated');
                $this->dispatch('notifications-updated');
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
                $this->searchUsers();
                $this->dispatch('connection-updated');
                $this->dispatch('notifications-updated');
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
                $this->dispatch('connection-updated');
                $this->dispatch('notifications-updated');
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
        return view('livewire.friend-request-manager', $this->socialConnectionsPollViewData());
    }
}
