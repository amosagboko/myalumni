@php
    /** @var \App\Models\Event $event */
@endphp

<a href="{{ route('alumni.events.show', $event) }}" class="text-decoration-none d-block feed-announcement-event-slide">
    <div class="card w125 h200 d-block border-0 shadow-xss rounded-xxxl bg-gradiant-bottom overflow-hidden cursor-pointer mb-0 feed-announcement-event-slide__card"
         @if($event->image) style="background-image: url('{{ asset('storage/' . $event->image) }}'); background-size: cover; background-position: center;" @else style="background: linear-gradient(135deg, #10d876 0%, #132977 100%);" @endif>
        <div class="card-body d-block p-3 w-100 position-absolute bottom-0 text-center">
            <div class="clearfix"></div>
            <h4 class="fw-600 position-relative z-index-1 ls-1 font-xssss text-white mt-2 mb-1">{{ \Illuminate\Support\Str::limit($event->eventname, 28) }}</h4>
            <span class="d-block font-xsssss text-white opacity-75">{{ $event->date?->format('M j') }}</span>
        </div>
    </div>
</a>
