@props([
    'items',
    'carouselId',
    'variant' => 'sidebar',
    'emptyMessage' => '',
])

@if($items->isEmpty())
    @if($variant === 'announcement')
        <div class="feed-events-carousel feed-events-carousel--announcement feed-events-carousel--empty">
            <div class="card w125 h200 d-block border-0 shadow-xss rounded-xxxl bg-greylight overflow-hidden mb-0 feed-announcement-event-slide__card">
                <div class="card-body d-flex align-items-center justify-content-center h-100 p-2 text-center">
                    <p class="feed-events-carousel__empty mb-0">{{ $emptyMessage }}</p>
                </div>
            </div>
        </div>
    @else
        <p class="feed-events-carousel__empty mb-0">{{ $emptyMessage }}</p>
    @endif
@else
    <div id="{{ $carouselId }}"
         class="feed-events-carousel feed-events-carousel--{{ $variant }}"
         @if($items->count() > 1)
         x-data="{
             index: 0,
             total: {{ $items->count() }},
             timer: null,
             interval: 10000,
             start() {
                 clearInterval(this.timer);
                 this.timer = setInterval(() => {
                     this.index = (this.index + 1) % this.total;
                 }, this.interval);
             },
             pause() {
                 clearInterval(this.timer);
                 this.timer = null;
             },
             goTo(i) {
                 this.index = i;
             }
         }"
         x-init="start()"
         @mouseenter="pause()"
         @mouseleave="start()"
         @endif
    >
        <div class="feed-events-carousel__viewport">
            @foreach($items as $index => $item)
                <div class="feed-events-carousel__slide"
                     @if($items->count() > 1)
                     x-show="index === {{ $index }}"
                     x-transition:enter="transition ease-out duration-400"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     @endif
                >
                    @if($variant === 'announcement')
                        @include('alumni.partials.feed-announcement-event-slide', ['event' => $item])
                    @else
                        @include('alumni.partials.feed-sidebar-event-slide', [
                            'event' => $item,
                            'badgeClass' => ['bg-success', 'bg-warning', 'bg-primary'][$index % 3],
                        ])
                    @endif
                </div>
            @endforeach
        </div>

        @if($items->count() > 1)
            <div class="feed-events-carousel__indicators">
                @foreach($items as $index => $item)
                    <button type="button"
                            @click="goTo({{ $index }})"
                            :class="{ 'active': index === {{ $index }} }"
                            aria-label="Go to slide {{ $index + 1 }}"
                            @if($index === 0) aria-current="true" @endif></button>
                @endforeach
            </div>
        @endif
    </div>
@endif
