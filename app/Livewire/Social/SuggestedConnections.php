<?php

namespace App\Livewire\Social;

use App\Services\Social\ConnectionService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SuggestedConnections extends Component
{
    protected $listeners = [
        'connection-updated' => '$refresh',
    ];

    public function connect(int $userId, ConnectionService $connectionService): void
    {
        if ($connectionService->sendRequest(Auth::id(), $userId)) {
            $this->dispatch('connection-updated');
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
