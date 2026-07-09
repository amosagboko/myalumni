<div>
    <div class="mb-3">
        <a href="{{ route('alumni.events') }}" class="font-xssss fw-600 text-primary text-decoration-none">
            <i class="feather-arrow-left me-1"></i> Back to events
        </a>
    </div>

    <div class="card w-100 shadow-xss rounded-xxl border-0 mb-3 overflow-hidden">
        @if($event->image)
            <img src="{{ asset('storage/' . $event->image) }}" alt="{{ $event->eventname }}" class="w-100" style="max-height: 360px; object-fit: cover;">
        @endif

        <div class="card-body p-4">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                <span class="badge bg-primary font-xsssss">{{ $typeLabel }}</span>
                @if($event->date)
                    <span class="font-xssss text-grey-500">
                        <i class="feather-calendar me-1"></i>{{ $event->date->format('l, F j, Y') }}
                    </span>
                @endif
                @if($shareCount > 0)
                    <span class="font-xssss text-grey-500">{{ $shareCount }} {{ Str::plural('share', $shareCount) }} in feed</span>
                @endif
            </div>

            <h3 class="fw-700 text-grey-900 mb-2">{{ $event->eventname }}</h3>

            @if($event->venue)
                <p class="font-xssss text-grey-500 mb-3">
                    <i class="feather-map-pin me-1"></i>{{ $event->venue }}
                </p>
            @endif

            @if($event->description)
                <div class="font-xssss text-grey-700 lh-26 mb-4">{!! nl2br(e($event->description)) !!}</div>
            @endif

            <div class="d-flex flex-wrap gap-2">
                @if($event->link)
                    <a href="{{ $event->link }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="font-xsssss fw-700 ps-3 pe-3 lh-32 text-uppercase rounded-3 ls-2 bg-success d-inline-block text-white text-decoration-none">
                        Learn More
                    </a>
                @endif
                <a href="{{ route('alumni.home') }}"
                   class="font-xsssss fw-700 ps-3 pe-3 lh-32 text-uppercase rounded-3 ls-2 bg-primary-gradiant d-inline-block text-white text-decoration-none">
                    Share on Feed
                </a>
            </div>
        </div>
    </div>
</div>
