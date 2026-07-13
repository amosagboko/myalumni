@php
    /** @var \App\Models\Event $event */
    $month = $event->date?->format('M');
    $day = $event->date?->format('j');
@endphp

<a href="{{ route('alumni.events.show', $event) }}" class="text-decoration-none d-block feed-sidebar-event-slide">
    <div class="card-body d-flex pt-0 ps-4 pe-4 pb-3 overflow-hidden border-top-xs bor-0">
        @if($event->date)
            <div class="{{ $badgeClass ?? 'bg-primary' }} me-2 p-3 rounded-xxl feed-sidebar-event-slide__date">
                <h4 class="fw-700 font-lg ls-3 lh-1 text-white mb-0">
                    <span class="ls-1 d-block font-xsss text-white fw-600">{{ strtoupper($month) }}</span>{{ $day }}
                </h4>
            </div>
        @endif
        <h4 class="fw-700 text-grey-900 font-xssss mt-2 mb-0">
            {{ $event->eventname }}
            <span class="d-block font-xsssss fw-500 mt-1 lh-4 text-grey-500">{{ $event->venue ?? 'Venue TBA' }}</span>
        </h4>
    </div>
</a>
