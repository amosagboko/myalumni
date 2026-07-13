@props(['event', 'shareCount' => null])

@php
    $imageUrl = $event->image ? asset('storage/' . ltrim($event->image, '/')) : null;
    $typeLabel = app(\App\Services\Social\EventService::class)->typeLabel($event->type);
    $isPast = $event->date && $event->date->isPast();
@endphp

<div class="card bg-white w-100 h-100 hover-card border-0 shadow-xss rounded-xxl overflow-hidden event-card">
    <a href="{{ route('alumni.events.show', $event) }}" class="text-decoration-none event-card__media-link">
        <div class="event-card__image-wrap">
            @if($imageUrl)
                <img src="{{ $imageUrl }}" alt="{{ $event->eventname }}" class="event-card__image" loading="lazy">
            @else
                <div class="event-card__placeholder d-flex align-items-center justify-content-center">
                    <i class="feather-image font-xl text-grey-400"></i>
                </div>
            @endif
        </div>
    </a>

    <div class="event-card__body d-flex flex-column flex-grow-1 p-3">
        <div class="event-card__badges d-flex flex-wrap align-items-center gap-1 mb-2">
            <span class="badge bg-primary font-xsssss">{{ $typeLabel }}</span>
            @if($isPast)
                <span class="badge bg-grey text-grey-700 font-xsssss">Past</span>
            @endif
        </div>

        <a href="{{ route('alumni.events.show', $event) }}" class="text-decoration-none">
            <h2 class="fw-700 font-xssss text-grey-900 mb-0 event-card__title">{{ $event->eventname }}</h2>
        </a>

        <div class="event-card__meta mt-2">
            @if($event->date)
                <p class="font-xsssss text-grey-500 mb-1 event-card__meta-line">
                    <i class="feather-calendar me-1"></i>{{ $event->date->format('M j, Y') }}
                </p>
            @else
                <p class="font-xsssss text-grey-500 mb-1 event-card__meta-line event-card__meta-line--placeholder" aria-hidden="true">&nbsp;</p>
            @endif
            @if($event->venue)
                <p class="font-xsssss text-grey-500 mb-0 event-card__meta-line">
                    <i class="feather-map-pin me-1"></i>{{ $event->venue }}
                </p>
            @else
                <p class="font-xsssss text-grey-500 mb-0 event-card__meta-line event-card__meta-line--placeholder" aria-hidden="true">&nbsp;</p>
            @endif
        </div>

        <p class="font-xsssss text-grey-500 mt-2 mb-0 event-card__description">
            {{ $event->description ? \Illuminate\Support\Str::limit($event->description, 100) : '' }}
        </p>

        <div class="event-card__footer d-flex align-items-center justify-content-between gap-2 mt-auto pt-3">
            <div class="event-card__footer-meta">
                @if($shareCount !== null && $shareCount > 0)
                    <span class="font-xsssss text-grey-500">{{ $shareCount }} {{ Str::plural('share', $shareCount) }}</span>
                @endif
            </div>
            <a href="{{ route('alumni.events.show', $event) }}"
               class="font-xsssss fw-700 px-3 py-2 text-uppercase rounded-3 ls-1 {{ $isPast ? 'bg-greylight text-grey-800' : 'bg-success text-white' }} text-decoration-none event-card__cta flex-shrink-0">
                {{ $isPast ? 'View' : 'Details' }}
            </a>
        </div>
    </div>
</div>
