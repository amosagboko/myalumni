<?php

namespace App\Livewire\Alumni;

use App\Models\Event;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.alumni')]
class MyEvents extends Component
{
    use AuthorizesRequests;

    public function mount(): void
    {
        $this->authorize('create', Event::class);
    }

    public function deleteEvent(int $eventId): void
    {
        $event = Event::query()
            ->where('user_id', Auth::id())
            ->where('type', 'opportunity')
            ->findOrFail($eventId);

        $this->authorize('delete', $event);

        if ($event->image && Storage::disk('public')->exists($event->image)) {
            Storage::disk('public')->delete($event->image);
        }

        $event->delete();

        session()->flash('success', 'Event deleted.');
    }

    public function render()
    {
        $events = Event::query()
            ->where('user_id', Auth::id())
            ->where('type', 'opportunity')
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.alumni.my-events', compact('events'));
    }
}
