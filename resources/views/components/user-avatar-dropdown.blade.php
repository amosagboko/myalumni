@props([
    'dropdownId' => 'userAvatarDropdown',
    'imgClass' => 'w40 mt--1',
    'linkClass' => 'p-0 ms-3 menu-icon',
])

@once
<style>
    .user-avatar-menu { position: relative; z-index: 1050; }
    .user-avatar-header-slot { position: relative; z-index: 1050; display: inline-block; }
    .user-avatar-menu__trigger img.rounded-circle {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 50%;
    }
    .user-avatar-menu__trigger img.w40 {
        width: 40px;
        height: 40px;
        object-fit: cover;
    }
    .user-avatar-menu__trigger {
        cursor: pointer;
        line-height: 0;
    }
    .user-avatar-menu__panel {
        display: none;
        position: absolute;
        right: 0;
        top: calc(100% + 8px);
        min-width: 150px;
        margin: 0;
        padding: 0.5rem 0;
        list-style: none;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    }
    .user-avatar-menu.is-open .user-avatar-menu__panel { display: block; }
    .user-avatar-menu__panel .dropdown-item {
        width: 100%;
        text-align: left;
        border: 0;
        background: transparent;
        padding: 0.5rem 1rem;
        font-size: 13px;
        font-weight: 600;
        color: #515184;
    }
    .user-avatar-menu__panel .dropdown-item:hover {
        background: #f5f5f5;
    }
</style>
<script>
(function () {
    if (window.__userAvatarMenuInit) {
        return;
    }
    window.__userAvatarMenuInit = true;

    function closeAllMenus() {
        document.querySelectorAll('.user-avatar-menu.is-open').forEach(function (menu) {
            menu.classList.remove('is-open');
            var trigger = menu.querySelector('[data-user-avatar-toggle]');
            if (trigger) {
                trigger.setAttribute('aria-expanded', 'false');
            }
        });
    }

    document.addEventListener('click', function (event) {
        if (!event.target.closest('.user-avatar-menu')) {
            closeAllMenus();
        }
    });

    document.addEventListener('click', function (event) {
        var trigger = event.target.closest('[data-user-avatar-toggle]');
        if (!trigger) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        var menu = trigger.closest('.user-avatar-menu');
        if (!menu) {
            return;
        }

        var willOpen = !menu.classList.contains('is-open');
        closeAllMenus();

        if (willOpen) {
            menu.classList.add('is-open');
            trigger.setAttribute('aria-expanded', 'true');
        }
    });
})();
</script>
@endonce

@auth
<div {{ $attributes->class(['user-avatar-menu', 'd-inline-block']) }}>
    <button type="button"
            class="user-avatar-menu__trigger border-0 bg-transparent {{ $linkClass }}"
            data-user-avatar-toggle
            id="{{ $dropdownId }}"
            aria-haspopup="true"
            aria-expanded="false">
        <img src="{{ auth()->user()->avatar ? asset('storage/' . auth()->user()->avatar) : asset('/images/user-8.png') }}"
             alt="avatar"
             class="{{ $imgClass }}">
    </button>
    <ul class="user-avatar-menu__panel" aria-labelledby="{{ $dropdownId }}">
        <li>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="dropdown-item">
                    Logout
                </button>
            </form>
        </li>
    </ul>
</div>
@endauth
