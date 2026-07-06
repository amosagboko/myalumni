@props([
    'wrapClass' => 'nav-wrap bg-white bg-transparent-card rounded-3 shadow-sm ps-3 pe-3 pt-0 pb-3 mb-2',
    'linkClass' => 'nav-content-bttn open-font h-auto pt-2 pb-2',
    'icon' => 'data-feather',
])

<div {{ $attributes->merge(['class' => $wrapClass]) }}>
    <div class="nav-caption fw-600 font-xssss text-grey-500 mb-2">Account</div>
    <ul class="mb-1 pt-0">
        <li class="nav-item">
            <a href="{{ route('profile.edit') }}"
               class="{{ $linkClass }} {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                @if($icon === 'data-feather')
                    <i data-feather="user" class="btn-round-md me-3" style="width: 16px; height: 16px;"></i>
                @else
                    <i class="feather-user font-sm me-3 text-grey-500"></i>
                @endif
                <span>My Profile</span>
            </a>
        </li>
    </ul>
</div>
