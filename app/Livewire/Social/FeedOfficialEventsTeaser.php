<?php

namespace App\Livewire\Social;

use App\Livewire\Social\Concerns\ListensForSocialBroadcasts;
use App\Services\Social\EventService;
use Livewire\Component;

class FeedOfficialEventsTeaser extends Component
{
    use ListensForSocialBroadcasts;

    public function render(EventService $eventService)
    {
        $stripLimit = (int) config('social.events_announcements_strip_limit', 3);

        return view('livewire.social.feed-official-events-teaser', array_merge([
            'upcomingEvents' => $eventService->stripCarouselByType('opportunity', $stripLimit),
        ], $this->socialPollViewData()));
    }
}
