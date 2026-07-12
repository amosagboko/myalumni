<div
    class="card w-100 shadow-xss rounded-xxl border-0 mb-3"
    @if($useBackgroundPoll && $pollInterval > 0)
        wire:poll.visible.{{ $pollInterval }}s="refreshQuietly"
    @endif
    wire:loading.delay.longest.class="social-connections-syncing"
>
    <div class="card-body d-flex align-items-center p-4">
        <h4 class="fw-700 mb-0 font-xssss text-grey-900">Connection Requests</h4>
        <a href="{{ route('friends') }}" class="fw-600 ms-auto font-xssss text-primary">See all</a>
    </div>

    @forelse($pendingRequests as $request)
        @if($request->sender)
            <div class="card-body d-flex pt-4 ps-4 pe-4 pb-0 border-top-xs bor-0">
                <figure class="avatar me-3">
                    <img src="{{ $request->sender->avatar ? asset('storage/' . $request->sender->avatar) : asset('/images/user-8.png') }}"
                         alt="{{ $request->sender->name }}"
                         class="shadow-sm rounded-circle w45">
                </figure>
                <h4 class="fw-700 text-grey-900 font-xssss mt-1">
                    {{ $request->sender->name }}
                    <span class="d-block font-xssss fw-500 mt-1 lh-3 text-grey-500">
                        @if(($request->mutual_count ?? 0) > 0)
                            {{ $request->mutual_count }} mutual {{ Str::plural('connection', $request->mutual_count) }}
                        @else
                            Wants to connect
                        @endif
                    </span>
                </h4>
            </div>
            <div class="card-body d-flex align-items-center pt-0 ps-4 pe-4 pb-4">
                <button type="button"
                        wire:click="accept({{ $request->sender_id }})"
                        wire:loading.attr="disabled"
                        wire:target="accept({{ $request->sender_id }})"
                        class="p-2 lh-20 w100 bg-primary-gradiant me-2 text-white text-center font-xssss fw-600 ls-1 rounded-xl border-0">
                    Confirm
                </button>
                <button type="button"
                        wire:click="reject({{ $request->sender_id }})"
                        wire:loading.attr="disabled"
                        wire:target="reject({{ $request->sender_id }})"
                        class="p-2 lh-20 w100 bg-grey text-grey-800 text-center font-xssss fw-600 ls-1 rounded-xl border-0">
                    Delete
                </button>
            </div>
        @endif
    @empty
        <div class="card-body pt-0 ps-4 pe-4 pb-4 border-top-xs bor-0">
            <p class="font-xssss text-grey-500 mb-0">No pending connection requests.</p>
        </div>
    @endforelse
</div>
