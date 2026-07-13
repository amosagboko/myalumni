<div
    class="row feed-body discover-page"
    @if($useBackgroundPoll && $pollInterval > 0)
        wire:poll.visible.{{ $pollInterval }}s="refreshQuietly"
    @endif
>
    <div class="col-xl-8 col-xxl-9 col-lg-8">
        <div class="card shadow-xss w-100 border-0 p-4 mb-3">
            <div class="card-body p-0">
                <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                    <div>
                        <h2 class="fw-700 mb-0 mt-0 font-md text-grey-900">Discover</h2>
                        <p class="fw-500 font-xssss text-grey-500 mt-1 mb-0">
                            Browse university-published highlights, news, and events for alumni.
                        </p>
                    </div>
                    @if($items->total() > 0)
                        <span class="badge bg-greylight text-grey-700 font-xssss px-3 py-2">
                            {{ $items->total() }} {{ Str::plural('item', $items->total()) }}
                        </span>
                    @endif
                </div>

                <div class="discover-page__type-tabs d-flex flex-wrap gap-2 mt-4">
                    <button type="button"
                            wire:click="setTab('highlights')"
                            class="btn btn-sm rounded-xl font-xssss fw-600 {{ $tab === 'highlights' ? 'bg-primary-gradiant text-white' : 'bg-greylight text-grey-700' }}">
                        Highlights
                    </button>
                    <button type="button"
                            wire:click="setTab('news')"
                            class="btn btn-sm rounded-xl font-xssss fw-600 {{ $tab === 'news' ? 'bg-primary-gradiant text-white' : 'bg-greylight text-grey-700' }}">
                        News
                    </button>
                    <button type="button"
                            wire:click="setTab('events')"
                            class="btn btn-sm rounded-xl font-xssss fw-600 {{ $tab === 'events' ? 'bg-primary-gradiant text-white' : 'bg-greylight text-grey-700' }}">
                        Events
                    </button>
                </div>

                <div class="discover-page__filters d-flex flex-wrap gap-2 mt-3 pt-3 border-top">
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
                        All
                    </button>
                </div>
            </div>
        </div>

        @if($items->isNotEmpty())
            <div class="row ps-1 pe-1 discover-page__grid">
                @foreach($items as $event)
                    <div class="col-lg-4 col-md-6 pe-2 ps-2 mb-3 d-flex">
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
                            Showing {{ $items->firstItem() }}–{{ $items->lastItem() }} of {{ $items->total() }} {{ Str::lower($tabLabel) }} items
                        </p>
                        {{ $items->links() }}
                    </div>
                </div>
            </div>
        @else
            <div class="card shadow-xss border-0 rounded-xxl mb-3">
                <div class="card-body p-5 text-center">
                    <i class="feather-compass btn-round-lg bg-greylight text-grey-500 font-md mb-3 d-inline-flex align-items-center justify-content-center"></i>
                    <h4 class="fw-700 font-xssss text-grey-900 mb-2">
                        @if($filter === 'past')
                            No past {{ Str::lower($tabLabel) }}
                        @elseif($filter === 'all')
                            No {{ Str::lower($tabLabel) }} published yet
                        @else
                            No upcoming {{ Str::lower($tabLabel) }}
                        @endif
                    </h4>
                    <p class="font-xssss text-grey-500 mb-0">
                        Try another tab or filter, or check back later for new content from the university.
                    </p>
                </div>
            </div>
        @endif
    </div>

    <div class="col-xl-4 col-xxl-3 col-lg-4 ps-lg-0">
        @include('livewire.social.partials.events-sidebar', [
            'upcomingTeaser' => $upcomingEventsTeaser,
        ])
    </div>
</div>
