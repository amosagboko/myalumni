<?php

namespace App\Livewire\Social;

use App\Models\Event;
use App\Services\Social\EventService;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.alumni')]
class EventShow extends Component
{
    public Event $event;

    public function mount(Event $event, EventService $eventService): void
    {
        abort_unless($eventService->isVisibleToAlumni($event), 404);

        $this->event = $event;
    }

    public function render(EventService $eventService)
    {
        return view('livewire.social.event-show', [
            'shareCount' => $eventService->shareCount($this->event),
            'typeLabel' => $eventService->typeLabel($this->event->type),
            'discoverTab' => $eventService->discoverTabForType($this->event->type),
            'upcomingTeaser' => $eventService->stripCarouselByType('opportunity', (int) config('social.events_announcements_strip_limit', 3))
                ->reject(fn (Event $item) => $item->id === $this->event->id),
            'isPast' => $this->event->date && $this->event->date->isPast(),
        ]);
    }
}
