@php
    $upcomingTeaser = $upcomingTeaser ?? collect();
@endphp

<div class="card w-100 shadow-xss rounded-xxl border-0 mb-3">
    <div class="card-body d-flex align-items-center p-4">
        <h4 class="fw-700 mb-0 font-xssss text-grey-900">Coming Up</h4>
        @if($upcomingTeaser->isNotEmpty())
            <span class="badge bg-primary font-xsssss ms-2">{{ $upcomingTeaser->count() }}</span>
        @endif
    </div>

    @forelse($upcomingTeaser as $event)
        <a href="{{ route('alumni.events.show', $event) }}" class="text-decoration-none d-block">
            <div class="card-body d-flex pt-0 ps-4 pe-4 pb-3 overflow-hidden {{ $loop->first ? 'border-top-xs bor-0' : '' }}">
                @if($event->date)
                    @php
                        $badgeClass = ['bg-success', 'bg-warning', 'bg-primary'][$loop->index % 3];
                    @endphp
                    <div class="{{ $badgeClass }} me-2 p-3 rounded-xxl events-sidebar__date">
                        <h4 class="fw-700 font-lg ls-3 lh-1 text-white mb-0">
                            <span class="ls-1 d-block font-xsss text-white fw-600">{{ strtoupper($event->date->format('M')) }}</span>
                            {{ $event->date->format('j') }}
                        </h4>
                    </div>
                @endif
                <h4 class="fw-700 text-grey-900 font-xssss mt-2 mb-0">
                    {{ $event->eventname }}
                    <span class="d-block font-xsssss fw-500 mt-1 lh-4 text-grey-500">{{ $event->venue ?? 'Venue TBA' }}</span>
                </h4>
            </div>
        </a>
    @empty
        <div class="card-body pt-0 ps-4 pe-4 pb-4 border-top-xs bor-0">
            <p class="font-xssss text-grey-500 mb-0">No upcoming official events right now.</p>
        </div>
    @endforelse
</div>

<div class="card w-100 shadow-xss rounded-xxl border-0 mb-3">
    <div class="card-body p-4">
        <h4 class="fw-700 mb-2 font-xssss text-grey-900">About official events</h4>
        <ul class="font-xssss text-grey-500 mb-3 ps-3">
            <li class="mb-2">Published by the university for all alumni.</li>
            <li class="mb-2">Share an event to your feed from the composer on Home.</li>
            <li>Open an event for full details and external links.</li>
        </ul>
        <a href="{{ route('alumni.home') }}" class="font-xssss fw-600 text-primary text-decoration-none">
            Go to Newsfeed <i class="feather-arrow-right ms-1"></i>
        </a>
    </div>
</div>
