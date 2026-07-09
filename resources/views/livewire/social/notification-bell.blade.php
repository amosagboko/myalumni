<div class="d-inline-block">
    <a href="#"
       class="p-2 text-center ms-auto menu-icon"
       id="alumniNotificationDropdown"
       data-bs-toggle="dropdown"
       aria-expanded="false"
       title="Notifications">
        @if($unreadCount > 0)
            <span class="dot-count bg-warning"></span>
        @endif
        <i class="feather-bell font-xl text-current"></i>
    </a>
    <div class="dropdown-menu dropdown-menu-end p-4 rounded-3 border-0 shadow-lg" aria-labelledby="alumniNotificationDropdown" style="min-width: 320px; max-width: 380px;">
        <div class="d-flex align-items-center mb-4">
            <h4 class="fw-700 font-xss mb-0">Notifications</h4>
            @if($unreadCount > 0)
                <button type="button"
                        wire:click="markAllRead"
                        class="btn btn-link font-xsssss fw-600 text-primary ms-auto p-0 border-0">
                    Mark all read
                </button>
            @endif
        </div>

        @forelse($notifications as $notification)
            @php
                $data = $notification->data;
                $avatar = $data['actor_avatar'] ?? null;
                $message = $data['message'] ?? 'New notification';
                $url = $data['url'] ?? route('alumni.home');
            @endphp
            <a href="{{ $url }}"
               wire:click="markAsRead('{{ $notification->id }}')"
               class="card bg-transparent-card w-100 border-0 ps-5 mb-3 text-decoration-none {{ $notification->read_at ? '' : 'opacity-100' }}">
                <img src="{{ $avatar ? asset('storage/' . $avatar) : asset('/images/user-8.png') }}"
                     alt=""
                     class="w40 position-absolute left-0 rounded-circle">
                <h5 class="font-xsss text-grey-900 mb-1 mt-0 fw-700 d-block">
                    {{ $data['actor_name'] ?? 'Alumni' }}
                    <span class="text-grey-400 font-xsssss fw-600 float-end mt-1">{{ $notification->created_at->diffForHumans(short: true) }}</span>
                </h5>
                <h6 class="text-grey-500 fw-500 font-xssss lh-4 mb-0">{{ $message }}</h6>
            </a>
        @empty
            <p class="font-xssss text-grey-500 mb-0">No notifications yet.</p>
        @endforelse
    </div>
</div>
