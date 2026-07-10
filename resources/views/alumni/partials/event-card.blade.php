@props(['event', 'shareCount' => null])

@php
    $imageUrl = $event->image ? '/storage/' . ltrim($event->image, '/') : null;
    $typeLabel = app(\App\Services\Social\EventService::class)->typeLabel($event->type);
    $isPast = $event->date && $event->date->isPast();
@endphp

<div class="card p-3 bg-white w-100 hover-card border-0 shadow-xss rounded-xxl mb-3 overflow-hidden event-card">
    <a href="{{ route('alumni.events.show', $event) }}" class="text-decoration-none d-block event-card__media-link">
        @if($imageUrl)
            <div class="card-image w-100 event-card__image">
                <img src="{{ $imageUrl }}" alt="{{ $event->eventname }}" class="w-100 rounded-3">
            </div>
        @else
            <div class="card-image w-100 rounded-3 bg-greylight d-flex align-items-center justify-content-center event-card__placeholder">
                <i class="feather-calendar font-xl text-grey-400"></i>
            </div>
        @endif
    </a>

    <div class="card-body d-flex ps-0 pe-0 pb-0 pt-3">
        @if($event->date)
            <div class="bg-greylight me-3 p-3 border-light-md rounded-xxl theme-dark-bg event-card__date-badge {{ $isPast ? 'opacity-75' : '' }}">
                <h4 class="fw-700 font-lg ls-3 text-grey-900 mb-0">
                    <span class="ls-3 d-block font-xsss text-grey-500 fw-500">{{ strtoupper($event->date->format('M')) }}</span>
                    {{ $event->date->format('j') }}
                </h4>
            </div>
        @endif
        <a href="{{ route('alumni.events.show', $event) }}" class="text-decoration-none flex-grow-1">
            <h2 class="fw-700 lh-3 font-xss text-grey-900 mb-0 event-card__title">
                {{ $event->eventname }}
                @if($event->venue)
                    <span class="d-flex font-xssss fw-500 mt-2 lh-3 text-grey-500">
                        <i class="ti-location-pin me-1"></i> {{ $event->venue }}
                    </span>
                @endif
            </h2>
        </a>
    </div>

    <div class="card-body p-0 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="badge bg-primary font-xsssss">{{ $typeLabel }}</span>
            @if($isPast)
                <span class="badge bg-grey text-grey-700 font-xsssss">Past</span>
            @endif
            @if($shareCount !== null && $shareCount > 0)
                <span class="font-xsssss text-grey-500">{{ $shareCount }} {{ Str::plural('share', $shareCount) }} in feed</span>
            @endif
        </div>
        <a href="{{ route('alumni.events.show', $event) }}"
           class="font-xsssss fw-700 ps-3 pe-3 lh-32 text-uppercase rounded-3 ls-2 {{ $isPast ? 'bg-grey text-grey-800' : 'bg-success text-white' }} d-inline-block text-decoration-none event-card__cta">
            {{ $isPast ? 'View' : 'Details' }}
        </a>
    </div>
</div>
