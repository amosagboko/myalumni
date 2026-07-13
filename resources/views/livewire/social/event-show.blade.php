<div class="row feed-body events-page event-show-page">
    <div class="col-xl-8 col-xxl-9 col-lg-8">
        <div class="mb-3">
            <a href="{{ route('alumni.discover', ['tab' => $discoverTab]) }}" class="font-xssss fw-600 text-primary text-decoration-none">
                <i class="feather-arrow-left me-1"></i> Back to Discover
            </a>
        </div>

        <div class="card w-100 shadow-xss rounded-xxl border-0 mb-3 overflow-hidden">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                    <span class="badge bg-primary font-xsssss">{{ $typeLabel }}</span>
                    @if($isPast)
                        <span class="badge bg-greylight text-grey-700 font-xsssss">Past event</span>
                    @else
                        <span class="badge bg-success font-xsssss">Upcoming</span>
                    @endif
                    @if($shareCount > 0)
                        <span class="font-xssss text-grey-500">{{ $shareCount }} {{ Str::plural('share', $shareCount) }} in feed</span>
                    @endif
                </div>

                <h2 class="fw-700 text-grey-900 font-md mb-3 event-show-page__title">{{ $event->eventname }}</h2>

                <div class="event-show-page__meta d-flex flex-wrap gap-3 mb-4">
                    @if($event->date)
                        <div class="event-show-page__meta-item">
                            <i class="feather-calendar text-primary me-2"></i>
                            <span class="font-xssss text-grey-700">{{ $event->date->format('l, F j, Y') }}</span>
                        </div>
                    @endif
                    @if($event->venue)
                        <div class="event-show-page__meta-item">
                            <i class="feather-map-pin text-primary me-2"></i>
                            <span class="font-xssss text-grey-700">{{ $event->venue }}</span>
                        </div>
                    @endif
                </div>

                @if($event->image)
                    @php
                        $eventImageUrl = asset('storage/' . $event->image);
                        $lightboxGroup = 'event-' . $event->id;
                    @endphp
                    <div class="event-show-page__gallery mb-4">
                        <h4 class="fw-700 font-xssss text-grey-900 mb-2">Event photo</h4>
                        <a href="{{ $eventImageUrl }}"
                           data-lightbox="{{ $lightboxGroup }}"
                           data-title="{{ $event->eventname }}"
                           class="event-show-page__thumbnail"
                           aria-label="View full size image for {{ $event->eventname }}">
                            <img src="{{ $eventImageUrl }}"
                                 alt="{{ $event->eventname }}"
                                 class="event-show-page__thumbnail-image"
                                 loading="lazy">
                            <span class="event-show-page__thumbnail-zoom" aria-hidden="true">
                                <i class="feather-maximize-2"></i>
                            </span>
                        </a>
                        <p class="font-xsssss text-grey-500 mt-2 mb-0">Tap to view full image</p>
                    </div>
                @endif

                @if($event->description)
                    <div class="event-show-page__description border-top pt-4">
                        <h4 class="fw-700 font-xssss text-grey-900 mb-3">About this event</h4>
                        <div class="font-xssss text-grey-700 lh-26 mb-0">{!! nl2br(e($event->description)) !!}</div>
                    </div>
                @endif

                <div class="d-flex flex-wrap gap-2 mt-4 pt-3 border-top">
                    @if($event->link)
                        <a href="{{ $event->link }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="btn btn-sm rounded-xl font-xssss fw-600 bg-success text-white text-decoration-none px-4">
                            Learn more
                        </a>
                    @endif
                    <a href="{{ route('alumni.home', ['share_event' => $event->id]) }}"
                       class="btn btn-sm rounded-xl font-xssss fw-600 bg-primary-gradiant text-white text-decoration-none px-4">
                        Share on feed
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-xxl-3 col-lg-4 ps-lg-0">
        @if($event->date)
            <div class="card w-100 shadow-xss rounded-xxl border-0 mb-3">
                <div class="card-body p-4 text-center">
                    <div class="event-show-page__date-badge d-inline-block bg-primary-gradiant rounded-xxl px-4 py-3 mb-3">
                        <div class="font-xsss text-white fw-600 text-uppercase ls-1">{{ $event->date->format('M') }}</div>
                        <div class="fw-700 font-xl text-white lh-1">{{ $event->date->format('j') }}</div>
                        <div class="font-xsssss text-white opacity-75 mt-1">{{ $event->date->format('Y') }}</div>
                    </div>
                    <p class="font-xssss text-grey-500 mb-0">{{ $event->date->format('l') }}</p>
                </div>
            </div>
        @endif

        @include('livewire.social.partials.events-sidebar', [
            'upcomingTeaser' => $upcomingTeaser,
        ])
    </div>
</div>
