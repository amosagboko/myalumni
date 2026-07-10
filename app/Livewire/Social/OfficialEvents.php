<?php

namespace App\Livewire\Social;

use App\Services\Social\EventService;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.alumni')]
class OfficialEvents extends Component
{
    use WithPagination;

    public string $filter = 'upcoming';

    protected $queryString = [
        'filter' => ['except' => 'upcoming'],
    ];

    public function setFilter(string $filter): void
    {
        if (! in_array($filter, ['upcoming', 'past', 'all'], true)) {
            return;
        }

        $this->filter = $filter;
        $this->resetPage();
    }

    public function render(EventService $eventService)
    {
        $events = $eventService->paginate($this->filter, 9);

        return view('livewire.social.official-events', [
            'events' => $events,
            'eventService' => $eventService,
            'upcomingTeaser' => $eventService->teaser(5),
        ]);
    }
}
