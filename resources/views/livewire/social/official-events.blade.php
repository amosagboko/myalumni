<div>
    <div class="card w-100 shadow-xss rounded-xxl border-0 mb-3">
        <div class="card-body p-4">
            <h4 class="fw-700 mb-1 font-xssss text-grey-900">Official Events</h4>
            <p class="font-xssss text-grey-500 mb-3">Upcoming alumni events published by the university.</p>

            <ul class="nav nav-tabs border-0" role="tablist">
                <li class="nav-item" role="presentation">
                    <button type="button"
                            wire:click="setFilter('upcoming')"
                            class="nav-link font-xssss fw-600 {{ $filter === 'upcoming' ? 'active text-primary' : 'text-grey-500' }}">
                        Upcoming
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button type="button"
                            wire:click="setFilter('past')"
                            class="nav-link font-xssss fw-600 {{ $filter === 'past' ? 'active text-primary' : 'text-grey-500' }}">
                        Past
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button type="button"
                            wire:click="setFilter('all')"
                            class="nav-link font-xssss fw-600 {{ $filter === 'all' ? 'active text-primary' : 'text-grey-500' }}">
                        All
                    </button>
                </li>
            </ul>
        </div>
    </div>

    @if($events->isNotEmpty())
        <div class="row">
            @foreach($events as $event)
                <div class="col-lg-4 col-md-6 pe-2 ps-2">
                    @include('alumni.partials.event-card', [
                        'event' => $event,
                        'shareCount' => $eventService->shareCount($event),
                    ])
                </div>
            @endforeach
        </div>

        <div class="d-flex justify-content-center mt-2 mb-4">
            {{ $events->links() }}
        </div>
    @else
        <div class="card w-100 shadow-xss rounded-xxl border-0 mb-3">
            <div class="card-body p-5 text-center">
                <i class="feather-calendar font-xl text-grey-400 mb-3 d-block"></i>
                <p class="font-xssss text-grey-500 mb-0">
                    @if($filter === 'past')
                        No past official events to show.
                    @elseif($filter === 'all')
                        No official events have been published yet.
                    @else
                        No upcoming official events right now. Check back later.
                    @endif
                </p>
            </div>
        </div>
    @endif
</div>
