@php
    $universityLogo = null;

    foreach (['images/fulafia-logo.jpg', 'images/alumni-logo.jpg', 'images/favicon.png'] as $logoPath) {
        if (file_exists(public_path($logoPath))) {
            $universityLogo = asset($logoPath);
            break;
        }
    }
@endphp

<div class="nav-header bg-white shadow-xs border-0 alumni-top-nav">
    <div class="alumni-top-nav__row">
        <div class="nav-top alumni-top-nav__mobile-controls">
            <a href="#" class="mob-menu me-2 nav-icon-disabled chat-active-btn" title="Chat (coming soon)" aria-disabled="true">
                <i class="feather-message-circle text-grey-900 font-sm btn-round-md bg-greylight"></i>
            </a>
            <a href="#" class="me-2 menu-search-icon mob-menu" title="Search" aria-label="Search">
                <i class="feather-search text-grey-900 font-sm btn-round-md bg-greylight"></i>
            </a>
            <button class="nav-menu me-0" type="button" aria-label="Toggle menu"></button>
        </div>

        <a href="{{ route('alumni.home') }}" class="alumni-top-nav__brand text-decoration-none">
            @if($universityLogo)
                <img src="{{ $universityLogo }}"
                     alt="Federal University Lafia"
                     class="alumni-top-nav__logo">
            @endif
            <span class="alumni-top-nav__brand-text">{{ config('app.name') }}</span>
        </a>

        <form action="#" class="header-search alumni-top-nav__search">
            <div class="form-group mb-0 icon-input">
                <i class="feather-search font-sm text-grey-400"></i>
                <input type="search"
                       placeholder="Start typing to search.."
                       class="bg-grey border-0 lh-32 pt-2 pb-2 ps-5 pe-3 font-xssss fw-500 rounded-xl theme-dark-bg alumni-top-nav__search-input"
                       aria-label="Search alumni">
            </div>
        </form>

        <div class="alumni-top-nav__actions">
            <a href="{{ route('alumni.home') }}" class="menu-icon center-menu-icon alumni-top-nav__icon" title="Newsfeed" aria-label="Newsfeed">
                <i class="feather-activity font-lg {{ request()->routeIs('alumni.home') ? 'alert-primary text-current' : 'bg-greylight text-grey-500' }} btn-round-lg theme-dark-bg"></i>
            </a>
            <a href="{{ route('friends') }}" class="menu-icon center-menu-icon alumni-top-nav__icon" title="Connections" aria-label="Connections">
                <i class="feather-users font-lg {{ request()->routeIs('friends') ? 'alert-primary text-current' : 'bg-greylight text-grey-500' }} btn-round-lg theme-dark-bg"></i>
            </a>
            <a href="{{ route('alumni.discover') }}" class="menu-icon center-menu-icon alumni-top-nav__icon" title="Discover" aria-label="Discover">
                <i class="feather-compass font-lg {{ request()->routeIs('alumni.discover', 'alumni.events.show') ? 'alert-primary text-current' : 'bg-greylight text-grey-500' }} btn-round-lg theme-dark-bg"></i>
            </a>
            <a href="{{ route('alumni.members.show', auth()->user()) }}" class="menu-icon center-menu-icon alumni-top-nav__icon" title="My Profile" aria-label="My Profile">
                <i class="feather-user font-lg {{ request()->routeIs('alumni.members.show') && (int) request()->route('user')?->id === auth()->id() ? 'alert-primary text-current' : 'bg-greylight text-grey-500' }} btn-round-lg theme-dark-bg"></i>
            </a>

            <div class="alumni-top-nav__icon">
                <livewire:social.notification-bell />
            </div>

            <a href="#" class="menu-icon alumni-top-nav__icon nav-icon-disabled chat-active-btn" title="Chat (coming soon)" aria-disabled="true">
                <i class="feather-message-square font-xl text-grey-400"></i>
            </a>

            <div class="menu-icon alumni-top-nav__icon position-relative dropdown-menu-icon cursor-pointer" title="Theme & display" aria-label="Theme and display options">
                <i class="feather-sliders d-inline-block font-xl text-current"></i>
                <div class="dropdown-menu-settings switchcolor-wrap">
                    <h4 class="fw-700 font-sm mb-4">Settings</h4>
                    <h6 class="font-xssss text-grey-500 fw-700 mb-3 d-block">Choose Color Theme</h6>
                    <ul>
                        <li><label class="item-radio item-content"><input type="radio" name="color-radio" value="red"><i class="ti-check"></i><span class="circle-color bg-red" style="background-color: #ff3b30;"></span></label></li>
                        <li><label class="item-radio item-content"><input type="radio" name="color-radio" value="green"><i class="ti-check"></i><span class="circle-color bg-green" style="background-color: #4cd964;"></span></label></li>
                        <li><label class="item-radio item-content"><input type="radio" name="color-radio" value="blue" checked><i class="ti-check"></i><span class="circle-color bg-blue" style="background-color: #132977;"></span></label></li>
                        <li><label class="item-radio item-content"><input type="radio" name="color-radio" value="pink"><i class="ti-check"></i><span class="circle-color bg-pink" style="background-color: #ff2d55;"></span></label></li>
                        <li><label class="item-radio item-content"><input type="radio" name="color-radio" value="yellow"><i class="ti-check"></i><span class="circle-color bg-yellow" style="background-color: #ffcc00;"></span></label></li>
                        <li><label class="item-radio item-content"><input type="radio" name="color-radio" value="orange"><i class="ti-check"></i><span class="circle-color bg-orange" style="background-color: #ff9500;"></span></label></li>
                    </ul>
                    <div class="card bg-transparent-card border-0 d-block mt-3">
                        <h4 class="d-inline font-xssss mont-font fw-700">Header Background</h4>
                        <div class="d-inline float-right mt-1">
                            <label class="toggle toggle-menu-color"><input type="checkbox"><span class="toggle-icon"></span></label>
                        </div>
                    </div>
                    <div class="card bg-transparent-card border-0 d-block mt-3">
                        <h4 class="d-inline font-xssss mont-font fw-700">Menu Position</h4>
                        <div class="d-inline float-right mt-1">
                            <label class="toggle toggle-menu"><input type="checkbox"><span class="toggle-icon"></span></label>
                        </div>
                    </div>
                    <div class="card bg-transparent-card border-0 d-block mt-3">
                        <h4 class="d-inline font-xssss mont-font fw-700">Dark Mode</h4>
                        <div class="d-inline float-right mt-1">
                            <label class="toggle toggle-dark"><input type="checkbox"><span class="toggle-icon"></span></label>
                        </div>
                    </div>
                </div>
            </div>

            @auth
                <div class="user-avatar-header-slot alumni-top-nav__avatar">
                    <x-user-avatar-dropdown dropdown-id="alumniAvatarDropdown" link-class="p-0 menu-icon" />
                </div>
            @endauth
        </div>
    </div>
</div>
