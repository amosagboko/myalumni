<div class="row feed-body events-page">
    <div class="col-xl-8 col-xxl-9 col-lg-8">
        <div class="card shadow-xss w-100 border-0 p-4 mb-3">
            <div class="card-body p-0">
                <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                    <div>
                        <h2 class="fw-700 mb-0 mt-0 font-md text-grey-900">Official Events</h2>
                        <p class="fw-500 font-xssss text-grey-500 mt-1 mb-0">
                            Discover university-published alumni events, reunions, and official announcements.
                        </p>
                    </div>
                    @if($events->total() > 0)
                        <span class="badge bg-greylight text-grey-700 font-xssss px-3 py-2">
                            {{ $events->total() }} {{ Str::plural('event', $events->total()) }}
                        </span>
                    @endif
                </div>

                <div class="events-page__filters d-flex flex-wrap gap-2 mt-4 pt-3 border-top">
                    <button type="button"
                            wire:click="setFilter('upcoming')"
                            class="btn btn-sm rounded-xl font-xssss fw-600 {{ $filter === 'upcoming' ? 'bg-primary-gradiant text-white' : 'bg-greylight text-grey-700' }}">
                        Upcoming
                    </button>
                    <button type="button"
                            wire:click="setFilter('past')"
                            class="btn btn-sm rounded-xl font-xssss fw-600 {{ $filter === 'past' ? 'bg-primary-gradiant text-white' : 'bg-greylight text-grey-700' }}">
                        Past
                    </button>
                    <button type="button"
                            wire:click="setFilter('all')"
                            class="btn btn-sm rounded-xl font-xssss fw-600 {{ $filter === 'all' ? 'bg-primary-gradiant text-white' : 'bg-greylight text-grey-700' }}">
                        All Events
                    </button>
                </div>
            </div>
        </div>

        @if($events->isNotEmpty())
            <div class="row ps-1 pe-1">
                @foreach($events as $event)
                    <div class="col-lg-4 col-md-6 pe-2 ps-2">
                        @include('alumni.partials.event-card', [
                            'event' => $event,
                            'shareCount' => $eventService->shareCount($event),
                        ])
                    </div>
                @endforeach
            </div>

            <div class="card shadow-xss border-0 rounded-xxl mb-4">
                <div class="card-body py-3">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <p class="font-xssss text-grey-500 mb-0">
                            Showing {{ $events->firstItem() }}–{{ $events->lastItem() }} of {{ $events->total() }} events
                        </p>
                        {{ $events->links() }}
                    </div>
                </div>
            </div>
        @else
            <div class="card shadow-xss border-0 rounded-xxl mb-3">
                <div class="card-body p-5 text-center">
                    <i class="feather-calendar btn-round-lg bg-greylight text-grey-500 font-md mb-3 d-inline-flex align-items-center justify-content-center"></i>
                    <h4 class="fw-700 font-xssss text-grey-900 mb-2">
                        @if($filter === 'past')
                            No past events
                        @elseif($filter === 'all')
                            No events published yet
                        @else
                            No upcoming events
                        @endif
                    </h4>
                    <p class="font-xssss text-grey-500 mb-0">
                        @if($filter === 'upcoming')
                            Check back later for new official alumni events from the university.
                        @else
                            Try another filter to browse available events.
                        @endif
                    </p>
                </div>
            </div>
        @endif
    </div>

    <div class="col-xl-4 col-xxl-3 col-lg-4 ps-lg-0">
        @include('livewire.social.partials.events-sidebar', [
            'upcomingTeaser' => $upcomingTeaser,
        ])
    </div>
</div>
