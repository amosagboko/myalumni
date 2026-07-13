@php
    $event = $post->event;
    $eventImageUrl = $event->image ? asset('storage/' . $event->image) : null;
@endphp

<div class="card-body p-0 mb-3 feed-event-share">
    <a href="{{ route('alumni.events.show', $event) }}" class="text-decoration-none d-block">
        <div class="bg-greylight rounded-xxl p-3 feed-event-share__card">
            <div class="d-flex align-items-start">
                @if($event->date)
                    <div class="bg-primary me-3 p-3 rounded-xxl text-center">
                        <h4 class="fw-700 font-sm ls-3 lh-1 text-white mb-0">
                            <span class="ls-1 d-block font-xsss text-white fw-600">{{ strtoupper($event->date->format('M')) }}</span>
                            {{ $event->date->format('j') }}
                        </h4>
                    </div>
                @endif
                <div class="min-width-0">
                    <h5 class="fw-700 text-grey-900 font-xssss mb-1">{{ $event->eventname }}</h5>
                    @if($event->venue)
                        <p class="font-xsssss text-grey-500 mb-1"><i class="feather-map-pin me-1"></i>{{ $event->venue }}</p>
                    @endif
                    @if($event->description)
                        <p class="font-xssss text-grey-500 mb-0">{{ \Illuminate\Support\Str::limit($event->description, 160) }}</p>
                    @endif
                </div>
            </div>
        </div>
    </a>

    @if($eventImageUrl)
        @include('livewire.social.partials.post-media-grid', [
            'items' => [[
                'full' => $eventImageUrl,
                'thumb' => $eventImageUrl,
                'alt' => $event->eventname,
            ]],
            'lightboxGroup' => 'post-'.$post->id,
            'caption' => $event->eventname,
        ])
    @endif
</div>
