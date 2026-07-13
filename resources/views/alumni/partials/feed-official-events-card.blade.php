<div class="card w-100 shadow-xss rounded-xxl border-0 mb-3">
    <div class="card-body d-flex align-items-center p-4 pb-2">
        <h4 class="fw-700 mb-0 font-xssss text-grey-900">Upcoming Events</h4>
        @if($upcomingEvents->isNotEmpty())
            <span class="badge bg-primary font-xsssss ms-2">{{ $upcomingEvents->count() }}</span>
        @endif
        <a href="{{ route('alumni.discover', ['tab' => 'events']) }}" class="fw-600 ms-auto font-xssss text-primary">See all</a>
    </div>

    <div class="feed-official-events-carousel px-1 pb-2">
        @include('alumni.partials.feed-event-carousel', [
            'items' => $upcomingEvents,
            'carouselId' => 'feedSidebarEventsCarousel',
            'variant' => 'sidebar',
            'emptyMessage' => 'No upcoming events.',
        ])
    </div>
</div>
