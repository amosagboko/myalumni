<?php

namespace App\Livewire\Social;

use App\Livewire\Social\Concerns\ListensForSocialBroadcasts;
use App\Services\Social\ConnectionService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ConnectionRequests extends Component
{
    use ListensForSocialBroadcasts;

    public function getListeners(): array
    {
        return array_merge(
            [
                'connection-updated' => '$refresh',
            ],
            $this->backgroundFeedListener()
        );
    }

    public function accept(int $senderId, ConnectionService $connectionService): void
    {
        if ($connectionService->acceptRequest(Auth::id(), $senderId)) {
            $this->dispatch('connection-updated');
            $this->dispatch('notifications-updated');
            session()->flash('success', 'Connection request accepted.');
        } else {
            session()->flash('error', 'Could not accept this request.');
        }
    }

    public function reject(int $senderId, ConnectionService $connectionService): void
    {
        if ($connectionService->rejectRequest(Auth::id(), $senderId)) {
            $this->dispatch('connection-updated');
            session()->flash('success', 'Connection request declined.');
        } else {
            session()->flash('error', 'Could not decline this request.');
        }
    }

    public function render(ConnectionService $connectionService)
    {
        return view('livewire.social.connection-requests', array_merge([
            'pendingRequests' => $connectionService->getPendingIncoming(Auth::user(), 3),
        ], $this->socialConnectionsPollViewData()));
    }
}
