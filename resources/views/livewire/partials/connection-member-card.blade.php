@php
    $avatarUrl = $avatarUrl ?? '/images/user-8.png';
    $name = $name ?? 'Alumni';
    $subtitle = $subtitle ?? '';
    $mode = $mode ?? 'none';
    $userId = $userId ?? null;
@endphp

<div class="col-md-3 col-sm-4 col-6 pe-2 ps-2">
    <div class="card d-block border-0 shadow-xss rounded-3 overflow-hidden mb-3 connection-member-card">
        <div class="card-body d-block w-100 ps-3 pe-3 pb-4 text-center">
            <figure class="avatar ms-auto me-auto mb-0 position-relative w65 z-index-1">
                <img src="{{ $avatarUrl }}"
                     alt="{{ $name }}"
                     class="float-right p-0 bg-white rounded-circle w-100 shadow-xss">
            </figure>
            <div class="clearfix"></div>
            <h4 class="fw-700 font-xsss mt-3 mb-1 text-grey-900 connection-member-card__name">{{ $name }}</h4>
            @if($subtitle !== '')
                <p class="fw-500 font-xsssss text-grey-500 mt-0 mb-3 connection-member-card__subtitle">{{ $subtitle }}</p>
            @endif

            <div class="connection-member-card__actions">
                @include('livewire.partials.connection-member-actions', [
                    'mode' => $mode,
                    'userId' => $userId,
                ])
            </div>
        </div>
    </div>
</div>
