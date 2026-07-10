@php
    $item = $item ?? [];
    $columnClass = $columnClass ?? 'col-12';
    $showMoreOverlay = $showMoreOverlay ?? false;
    $moreCount = (int) ($moreCount ?? 0);
    $lightboxGroup = $lightboxGroup ?? null;
    $caption = $caption ?? '';
    $extraClass = $extraClass ?? '';
    $overlay = $showMoreOverlay && $moreCount > 0;
    $title = $caption !== '' ? $caption : ($item['alt'] ?? 'Post image');
@endphp

<div class="{{ $columnClass }} p-1">
    <a href="{{ $item['full'] }}"
       @if($lightboxGroup)
           data-lightbox="{{ $lightboxGroup }}"
           data-title="{{ $title }}"
       @endif
       class="post-media-cell position-relative d-block {{ $extraClass }}">
        <img src="{{ $item['thumb'] }}"
             class="rounded-3 w-100 post-media-thumb"
             alt="{{ $item['alt'] ?? 'Post image' }}"
             loading="lazy">
        @if($overlay)
            <span class="post-media-more font-sm text-white ls-3 fw-600">
                <b>+{{ $moreCount }}</b>
            </span>
        @endif
    </a>
</div>
