<?php

namespace App\Livewire\Social;

use App\Livewire\Social\Concerns\ListensForSocialBroadcasts;
use App\Models\User;
use App\Services\Social\AlumniProfileService;
use App\Services\Social\ConnectionService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.alumni')]
class AlumniProfileShow extends Component
{
    use ListensForSocialBroadcasts;
    use WithPagination;

    public int $profileUserId;

    protected $listeners = [
        'connection-updated' => '$refresh',
    ];

    public function mount(User $user, AlumniProfileService $profileService): void
    {
        $profileService->resolveProfileUser($user);
        $this->profileUserId = $user->id;
    }

    public function getProfileUserProperty(): User
    {
        return User::with('alumni')->findOrFail($this->profileUserId);
    }

    public function sendRequest(int $userId, ConnectionService $connectionService): void
    {
        if ($connectionService->sendRequest(Auth::id(), $userId)) {
            $this->dispatch('connection-updated');
            $this->dispatch('notifications-updated');
            session()->flash('success', 'Connection request sent.');
        } else {
            session()->flash('error', 'Could not send connection request.');
        }
    }

    public function acceptRequest(int $userId, ConnectionService $connectionService): void
    {
        if ($connectionService->acceptRequest(Auth::id(), $userId)) {
            $this->dispatch('connection-updated');
            $this->dispatch('notifications-updated');
            session()->flash('success', 'Connection confirmed.');
        } else {
            session()->flash('error', 'Could not confirm connection.');
        }
    }

    public function rejectRequest(int $userId, ConnectionService $connectionService): void
    {
        if ($connectionService->rejectRequest(Auth::id(), $userId)) {
            $this->dispatch('connection-updated');
            $this->dispatch('notifications-updated');
            session()->flash('success', 'Connection request removed.');
        } else {
            session()->flash('error', 'Could not remove connection request.');
        }
    }

    public function unfriend(int $userId, ConnectionService $connectionService): void
    {
        if ($connectionService->removeConnection(Auth::id(), $userId)) {
            $this->dispatch('connection-updated');
            $this->dispatch('notifications-updated');
            session()->flash('success', 'Connection removed.');
        } else {
            session()->flash('error', 'Could not remove connection.');
        }
    }

    public function render(AlumniProfileService $profileService, ConnectionService $connectionService)
    {
        $profileUser = $this->profileUser;
        $viewer = Auth::user();

        return view('livewire.social.alumni-profile-show', array_merge([
            'profileUser' => $profileUser,
            'isSelf' => $viewer->id === $profileUser->id,
            'connectionMode' => $connectionService->getConnectionActionMode($viewer, $profileUser),
            'connectionCount' => $profileService->getConnectionCount($profileUser),
            'postsCount' => $profileService->countVisiblePosts($viewer, $profileUser),
            'subtitle' => $profileService->profileSubtitle($profileUser),
            'avatarUrl' => $profileService->avatarUrl($profileUser),
            'posts' => $profileService->paginateProfilePosts($viewer, $profileUser, 10),
        ], $this->socialConnectionsPollViewData()));    }
}
