@php
    $images = $images ?? [];
    $lightboxGroup = $lightboxGroup ?? 'composer-preview';
@endphp

@if(count($images) > 0)
<div class="post-composer-thumbnails" wire:key="composer-thumbs-{{ count($images) }}">
    @foreach($images as $index => $image)
        <a href="{{ $image->temporaryUrl() }}"
           data-lightbox="{{ $lightboxGroup }}"
           data-title="Selected image {{ $index + 1 }}"
           class="post-composer-thumbnail">
            <img src="{{ $image->temporaryUrl() }}"
                 alt="Selected image {{ $index + 1 }}"
                 loading="lazy">
        </a>
    @endforeach
</div>
@endif
