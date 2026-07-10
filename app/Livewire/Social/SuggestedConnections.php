<?php

namespace App\Livewire\Social;

use App\Livewire\Social\Concerns\ListensForSocialBroadcasts;
use App\Services\Social\ConnectionService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SuggestedConnections extends Component
{
    use ListensForSocialBroadcasts;

    public function getListeners(): array
    {
        return array_merge(
            [
                'connection-updated' => '$refresh',
            ],
            $this->socialEchoListeners()
        );
    }

    public function connect(int $userId, ConnectionService $connectionService): void
    {
        if ($connectionService->sendRequest(Auth::id(), $userId)) {
            $this->dispatch('connection-updated');
            $this->dispatch('notifications-updated');
            session()->flash('success', 'Connection request sent.');
        } else {
            session()->flash('error', 'Could not send connection request.');
        }
    }

    public function render(ConnectionService $connectionService)
    {
        return view('livewire.social.suggested-connections', [
            'suggestions' => $connectionService->getSuggestions(Auth::user(), 3),
        ]);
    }
}
