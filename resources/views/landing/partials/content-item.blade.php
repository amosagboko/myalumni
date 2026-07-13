@props([
    'item',
    'lightboxGroup',
    'showDate' => false,
    'showVenue' => false,
])

@php
    $imageUrl = $item->image ? asset('storage/' . $item->image) : null;
@endphp

<div class="landing-content-item">
    <div class="d-flex gap-3 text-start align-items-start">
        @if($imageUrl)
            <a href="{{ $imageUrl }}"
               data-lightbox="{{ $lightboxGroup }}"
               data-title="{{ $item->eventname }}"
               class="landing-content-item__thumb flex-shrink-0"
               aria-label="View image for {{ $item->eventname }}">
                <img src="{{ $imageUrl }}"
                     alt="{{ $item->eventname }}"
                     class="landing-content-item__thumb-image"
                     loading="lazy">
            </a>
        @endif

        <div class="min-width-0 flex-grow-1">
            <h5 class="landing-content-item__title mb-1">{{ $item->eventname }}</h5>

            @if($showDate && $item->date)
                <p class="landing-content-item__meta mb-1">
                    <i class="bi bi-calendar3 me-1"></i>{{ $item->date->format('M j, Y') }}
                </p>
            @endif

            @if($showVenue && $item->venue)
                <p class="landing-content-item__meta mb-1">
                    <i class="bi bi-geo-alt me-1"></i>{{ $item->venue }}
                </p>
            @endif

            @if($item->description)
                <p class="landing-content-item__description mb-2">{{ Str::limit($item->description, 100) }}</p>
            @endif

            @if($item->link)
                <a href="{{ $item->link }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="btn btn-sm btn-outline-primary">
                    Learn more
                </a>
            @endif
        </div>
    </div>
</div>
