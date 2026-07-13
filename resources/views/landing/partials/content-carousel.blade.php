@props([
    'items',
    'carouselId',
    'lightboxGroup',
    'showDate' => false,
    'showVenue' => false,
    'emptyMessage' => '',
])

@if($items->isEmpty())
    <p class="landing-content-carousel__empty mb-0">{{ $emptyMessage }}</p>
@else
    <div id="{{ $carouselId }}"
         class="carousel slide landing-content-carousel"
         data-bs-ride="carousel"
         data-bs-interval="10000"
         data-bs-pause="hover">
        <div class="carousel-inner">
            @foreach($items as $index => $item)
                <div @class(['carousel-item', 'active' => $index === 0])>
                    @include('landing.partials.content-item', [
                        'item' => $item,
                        'lightboxGroup' => $lightboxGroup,
                        'showDate' => $showDate,
                        'showVenue' => $showVenue,
                    ])
                </div>
            @endforeach
        </div>

        @if($items->count() > 1)
            <div class="carousel-indicators landing-content-carousel__indicators">
                @foreach($items as $index => $item)
                    <button type="button"
                            data-bs-target="#{{ $carouselId }}"
                            data-bs-slide-to="{{ $index }}"
                            @class(['active' => $index === 0])
                            aria-label="Go to slide {{ $index + 1 }}"
                            @if($index === 0) aria-current="true" @endif></button>
                @endforeach
            </div>
        @endif
    </div>
@endif
