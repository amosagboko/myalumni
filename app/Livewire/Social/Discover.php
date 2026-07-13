<?php

namespace App\Livewire\Social;

use App\Livewire\Social\Concerns\ListensForSocialBroadcasts;
use App\Services\Social\EventService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.alumni')]
class Discover extends Component
{
    use ListensForSocialBroadcasts;
    use WithPagination;

    public string $tab = 'highlights';

    public string $filter = 'upcoming';

    protected $queryString = [
        'tab' => ['except' => 'highlights'],
        'filter' => ['except' => 'upcoming'],
    ];

    public function mount(): void
    {
        $this->tab = $this->normalizeTab($this->tab);
        $this->filter = $this->normalizeFilter($this->filter);
    }

    public function setTab(string $tab): void
    {
        $tab = $this->normalizeTab($tab);

        if ($tab === $this->tab) {
            return;
        }

        $this->tab = $tab;
        $this->resetPage();
    }

    public function setFilter(string $filter): void
    {
        $filter = $this->normalizeFilter($filter);

        if ($filter === $this->filter) {
            return;
        }

        $this->filter = $filter;
        $this->resetPage();
    }

    public function render(EventService $eventService)
    {
        $type = $eventService->typeForDiscoverTab($this->tab);
        $items = $eventService->paginateByType(
            $type,
            $this->filter,
            (int) config('social.discover_per_page', 6)
        );
        $stripLimit = (int) config('social.events_announcements_strip_limit', 3);

        return view('livewire.social.discover', array_merge([
            'items' => $items,
            'eventService' => $eventService,
            'tabLabel' => $eventService->discoverTabLabel($this->tab),
            'upcomingEventsTeaser' => $eventService->stripCarouselByType('opportunity', $stripLimit),
        ], $this->socialPollViewData()));
    }

    protected function normalizeTab(string $tab): string
    {
        return in_array($tab, ['highlights', 'news', 'events'], true) ? $tab : 'highlights';
    }

    protected function normalizeFilter(string $filter): string
    {
        return in_array($filter, ['upcoming', 'past', 'all'], true) ? $filter : 'upcoming';
    }
}
