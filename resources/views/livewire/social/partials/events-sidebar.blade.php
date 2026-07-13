@php
    $upcomingTeaser = $upcomingTeaser ?? collect();
@endphp

<div class="card w-100 shadow-xss rounded-xxl border-0 mb-3">
    <div class="card-body d-flex align-items-center p-4 pb-2">
        <h4 class="fw-700 mb-0 font-xssss text-grey-900">Upcoming Events</h4>
        @if($upcomingTeaser->isNotEmpty())
            <span class="badge bg-primary font-xsssss ms-2">{{ $upcomingTeaser->count() }}</span>
        @endif
        <a href="{{ route('alumni.discover', ['tab' => 'events']) }}" class="fw-600 ms-auto font-xssss text-primary">See all</a>
    </div>

    <div class="feed-official-events-carousel px-1 pb-2">
        @include('alumni.partials.feed-event-carousel', [
            'items' => $upcomingTeaser,
            'carouselId' => 'eventsPageSidebarCarousel',
            'variant' => 'sidebar',
            'emptyMessage' => 'No upcoming events.',
        ])
    </div>
</div>

<div class="card w-100 shadow-xss rounded-xxl border-0 mb-3">
    <div class="card-body p-4">
        <h4 class="fw-700 mb-2 font-xssss text-grey-900">About Discover</h4>
        <ul class="font-xssss text-grey-500 mb-3 ps-3">
            <li class="mb-2">Highlights, news, and events published by the university.</li>
            <li class="mb-2">Share an item to your feed from the composer on Home.</li>
            <li>Open any item for full details and external links.</li>
        </ul>
        <a href="{{ route('alumni.home') }}" class="font-xssss fw-600 text-primary text-decoration-none">
            Go to Newsfeed <i class="feather-arrow-right ms-1"></i>
        </a>
    </div>
</div>
