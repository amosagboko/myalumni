<?php

namespace App\Livewire\Social;

use App\Livewire\Social\Concerns\ListensForSocialBroadcasts;
use App\Services\Social\EventService;
use Livewire\Component;

class FeedAnnouncementsStrip extends Component
{
    use ListensForSocialBroadcasts;

    public function render(EventService $eventService)
    {
        $stripLimit = (int) config('social.events_announcements_strip_limit', 3);

        return view('livewire.social.feed-announcements-strip', array_merge([
            'highlightItems' => $eventService->stripCarouselByType('connect', $stripLimit),
            'newsItems' => $eventService->stripCarouselByType('event', $stripLimit),
            'eventItems' => $eventService->stripCarouselByType('opportunity', $stripLimit),
        ], $this->socialPollViewData()));
    }
}
