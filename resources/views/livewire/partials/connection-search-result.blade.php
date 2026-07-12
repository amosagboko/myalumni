@php
    $result = $result ?? [];
    $user = $result['user'] ?? null;
@endphp

@if($user)
    <div class="card w-100 shadow-xss rounded-xxl border-0 mb-2 connection-search-result">
        <div class="card-body d-flex align-items-center p-3 gap-2">
            <a href="{{ route('alumni.members.show', $user) }}" class="d-flex align-items-center flex-grow-1 text-decoration-none min-width-0 me-2">
                <figure class="avatar me-3 mb-0 flex-shrink-0">
                    <img src="{{ $result['avatar_url'] }}"
                         alt="{{ $user->name }}"
                         class="shadow-sm rounded-circle w45">
                </figure>
                <div class="min-width-0">
                    <h4 class="fw-700 text-grey-900 font-xssss mt-0 mb-0 text-truncate">{{ $user->name }}</h4>
                    <p class="font-xsssss fw-500 text-grey-500 mt-1 mb-0 text-truncate">
                        {{ $result['subtitle'] }}
                        @if(($result['mutual_count'] ?? 0) > 0)
                            · {{ $result['mutual_count'] }} mutual {{ Str::plural('connection', $result['mutual_count']) }}
                        @endif
                    </p>
                </div>
            </a>
            <div class="connection-search-result__actions flex-shrink-0">
                @include('livewire.partials.connection-member-actions', [
                    'mode' => $result['mode'] ?? 'none',
                    'userId' => $user->id,
                ])
            </div>
        </div>
    </div>
@endif
