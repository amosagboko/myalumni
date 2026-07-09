@props(['event', 'shareCount' => null])

<a href="{{ route('alumni.events.show', $event) }}" class="text-decoration-none d-block">
    <div class="card p-3 bg-white w-100 hover-card border-0 shadow-xss rounded-xxl border-0 mb-3 overflow-hidden">
        @if($event->image)
            <div class="card-image w-100">
                <img src="{{ asset('storage/' . $event->image) }}" alt="{{ $event->eventname }}" class="w-100 rounded-3">
            </div>
        @else
            <div class="card-image w-100 rounded-3 bg-greylight d-flex align-items-center justify-content-center" style="height: 160px;">
                <i class="feather-calendar font-xl text-grey-400"></i>
            </div>
        @endif
        <div class="card-body d-flex ps-0 pe-0 pb-0">
            @if($event->date)
                <div class="bg-greylight me-3 p-3 border-light-md rounded-xxl theme-dark-bg">
                    <h4 class="fw-700 font-lg ls-3 text-grey-900 mb-0">
                        <span class="ls-3 d-block font-xsss text-grey-500 fw-500">{{ strtoupper($event->date->format('M')) }}</span>
                        {{ $event->date->format('j') }}
                    </h4>
                </div>
            @endif
            <h2 class="fw-700 lh-3 font-xss text-grey-900">
                {{ $event->eventname }}
                @if($event->venue)
                    <span class="d-flex font-xssss fw-500 mt-2 lh-3 text-grey-500">
                        <i class="ti-location-pin me-1"></i> {{ $event->venue }}
                    </span>
                @endif
            </h2>
        </div>
        <div class="card-body p-0 d-flex align-items-center justify-content-between">
            <span class="badge bg-primary font-xsssss">{{ app(\App\Services\Social\EventService::class)->typeLabel($event->type) }}</span>
            @if($shareCount !== null && $shareCount > 0)
                <span class="font-xsssss text-grey-500">{{ $shareCount }} {{ Str::plural('share', $shareCount) }} in feed</span>
            @endif
        </div>
    </div>
</a>
