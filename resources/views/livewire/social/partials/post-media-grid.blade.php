@php
    $items = $items ?? [];
    $lightboxGroup = $lightboxGroup ?? null;
    $caption = $caption ?? '';
    $visibleMax = (int) config('social.post_images.grid_visible_max', 5);
    $total = count($items);
@endphp

@if($total > 0)
<div class="card-body d-block p-0 mt-3 post-media-grid post-media-grid--count-{{ min($total, $visibleMax) }}">
    @if($total === 1)
        <div class="row ps-2 pe-2">
            @include('livewire.social.partials.post-media-cell', [
                'item' => $items[0],
                'columnClass' => 'col-12',
                'lightboxGroup' => $lightboxGroup,
                'caption' => $caption,
            ])
        </div>
    @elseif($total === 2)
        <div class="row ps-2 pe-2">
            @include('livewire.social.partials.post-media-cell', [
                'item' => $items[0],
                'columnClass' => 'col-6 col-sm-6',
                'lightboxGroup' => $lightboxGroup,
                'caption' => $caption,
            ])
            @include('livewire.social.partials.post-media-cell', [
                'item' => $items[1],
                'columnClass' => 'col-6 col-sm-6',
                'lightboxGroup' => $lightboxGroup,
                'caption' => $caption,
            ])
        </div>
    @elseif($total === 3)
        <div class="row ps-2 pe-2">
            @foreach(array_slice($items, 0, 3) as $item)
                @include('livewire.social.partials.post-media-cell', [
                    'item' => $item,
                    'columnClass' => 'col-4 col-sm-4',
                    'lightboxGroup' => $lightboxGroup,
                    'caption' => $caption,
                ])
            @endforeach
        </div>
    @elseif($total === 4)
        <div class="row ps-2 pe-2">
            @include('livewire.social.partials.post-media-cell', [
                'item' => $items[0],
                'columnClass' => 'col-6 col-sm-6',
                'lightboxGroup' => $lightboxGroup,
                'caption' => $caption,
            ])
            @include('livewire.social.partials.post-media-cell', [
                'item' => $items[1],
                'columnClass' => 'col-6 col-sm-6',
                'lightboxGroup' => $lightboxGroup,
                'caption' => $caption,
            ])
        </div>
        <div class="row ps-2 pe-2">
            @include('livewire.social.partials.post-media-cell', [
                'item' => $items[2],
                'columnClass' => 'col-6 col-sm-6',
                'lightboxGroup' => $lightboxGroup,
                'caption' => $caption,
            ])
            @include('livewire.social.partials.post-media-cell', [
                'item' => $items[3],
                'columnClass' => 'col-6 col-sm-6',
                'lightboxGroup' => $lightboxGroup,
                'caption' => $caption,
            ])
        </div>
    @else
        <div class="row ps-2 pe-2 post-media-row post-media-row--primary">
            @include('livewire.social.partials.post-media-cell', [
                'item' => $items[0],
                'columnClass' => 'col-6 col-sm-6',
                'lightboxGroup' => $lightboxGroup,
                'caption' => $caption,
            ])
            @include('livewire.social.partials.post-media-cell', [
                'item' => $items[1],
                'columnClass' => 'col-6 col-sm-6',
                'lightboxGroup' => $lightboxGroup,
                'caption' => $caption,
            ])
        </div>
        <div class="row ps-2 pe-2 post-media-row post-media-row--secondary">
            @include('livewire.social.partials.post-media-cell', [
                'item' => $items[2],
                'columnClass' => 'col-4 col-sm-4',
                'lightboxGroup' => $lightboxGroup,
                'caption' => $caption,
            ])
            @include('livewire.social.partials.post-media-cell', [
                'item' => $items[3],
                'columnClass' => 'col-4 col-sm-4',
                'lightboxGroup' => $lightboxGroup,
                'caption' => $caption,
            ])
            @include('livewire.social.partials.post-media-cell', [
                'item' => $items[4],
                'columnClass' => 'col-4 col-sm-4',
                'showMoreOverlay' => $total > $visibleMax,
                'moreCount' => $total - $visibleMax,
                'lightboxGroup' => $lightboxGroup,
                'caption' => $caption,
            ])
        </div>
        @if($lightboxGroup && $total > $visibleMax)
            @foreach(array_slice($items, $visibleMax) as $hiddenItem)
                <a href="{{ $hiddenItem['full'] }}"
                   data-lightbox="{{ $lightboxGroup }}"
                   data-title="{{ $caption !== '' ? $caption : ($hiddenItem['alt'] ?? 'Post image') }}"
                   class="post-media-lightbox-hidden"
                   tabindex="-1"
                   aria-hidden="true"></a>
            @endforeach
        @endif
    @endif
</div>
@endif
