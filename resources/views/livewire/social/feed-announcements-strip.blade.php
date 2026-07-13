<div
    class="feed-announcements-strip"
    @if($useBackgroundPoll && $pollInterval > 0)
        wire:poll.visible.{{ $pollInterval }}s="refreshQuietly"
    @endif
>    @include('alumni.partials.feed-announcements-strip', [
        'highlightItems' => $highlightItems,
        'newsItems' => $newsItems,
        'eventItems' => $eventItems,
    ])
</div>
