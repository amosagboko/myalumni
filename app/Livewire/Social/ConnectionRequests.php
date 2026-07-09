<?php

namespace App\Livewire\Social;

use App\Services\Social\ConnectionService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ConnectionRequests extends Component
{
    protected $listeners = [
        'connection-updated' => '$refresh',
    ];

    public function accept(int $senderId, ConnectionService $connectionService): void
    {
        if ($connectionService->acceptRequest(Auth::id(), $senderId)) {
            $this->dispatch('connection-updated');
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
        return view('livewire.social.connection-requests', [
            'pendingRequests' => $connectionService->getPendingIncoming(Auth::user(), 3),
        ]);
    }
}
