<div
    @if($useBackgroundPoll && $pollInterval > 0)
        wire:poll.visible.{{ $pollInterval }}s="refreshQuietly"
    @endif
>
    @include('alumni.partials.feed-official-events-card', [
        'upcomingEvents' => $upcomingEvents,
    ])
</div>
