<div class="card w-100 shadow-xss rounded-xxl border-0 p-0 mb-3">
    <div class="card-body d-flex align-items-center p-4 mb-0">
        <h4 class="fw-700 mb-0 font-xssss text-grey-900">People You May Know</h4>
        <a href="{{ route('friends') }}" class="fw-600 ms-auto font-xssss text-primary">See all</a>
    </div>

    @forelse($suggestions as $suggestion)
        @php $user = $suggestion['user']; @endphp
        <div class="card-body bg-transparent-card d-flex p-3 bg-greylight ms-3 me-3 rounded-3 {{ $loop->last ? 'mb-3' : '' }}" style="{{ !$loop->last ? 'margin-bottom: 0 !important;' : '' }}">
            <figure class="avatar me-2 mb-0">
                <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : asset('/images/user-8.png') }}"
                     alt="{{ $user->name }}"
                     class="shadow-sm rounded-circle w45">
            </figure>
            <h4 class="fw-700 text-grey-900 font-xssss mt-2">
                {{ $user->name }}
                <span class="d-block font-xssss fw-500 mt-1 lh-3 text-grey-500">
                    @if($suggestion['mutual_count'] > 0)
                        {{ $suggestion['mutual_count'] }} mutual {{ Str::plural('connection', $suggestion['mutual_count']) }}
                    @elseif($user->alumni?->year_of_graduation)
                        Class of {{ $user->alumni->year_of_graduation }}
                    @else
                        Suggested alumni
                    @endif
                </span>
            </h4>
            <button type="button"
                    wire:click="connect({{ $user->id }})"
                    wire:loading.attr="disabled"
                    wire:target="connect({{ $user->id }})"
                    class="btn-round-sm bg-white text-grey-900 ms-auto mt-2 border-0 d-flex align-items-center justify-content-center"
                    title="Send connection request">
                <i class="feather-user-plus font-xss"></i>
            </button>
        </div>
    @empty
        <div class="card-body pt-0 ps-4 pe-4 pb-4">
            <p class="font-xssss text-grey-500 mb-0">No suggestions right now. Check back later.</p>
        </div>
    @endforelse
</div>
