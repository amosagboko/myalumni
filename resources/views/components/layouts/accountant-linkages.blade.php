@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name').' | Accountant-Linkages' }}</title>

    <link rel="stylesheet" href="/css/themify-icons.css">
    <link rel="stylesheet" href="/css/feather.css">
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <link rel="icon" type="image/png" sizes="16x16" href="/images/favicon.png">
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/emoji.css">
    <link rel="stylesheet" href="/css/lightbox.css">
    @livewireStyles
    @stack('styles')
</head>

<body class="color-theme-blue mont-font">
    <div class="preloader"></div>

    <div class="main-wrapper">
        <div class="nav-header bg-white shadow-xs border-0" style="position: relative;">
            <div class="nav-top">
                <a href="{{ route('accountant-linkages.payments') }}">
                    <i class="text-success display1-size me-2 ms-0"></i>
                    <span class="d-inline-block fredoka-font ls-3 fw-600 text-current font-xxl logo-text mb-0">{{ config('app.name') }}</span>
                </a>
                <button class="nav-menu me-0 ms-2" type="button" aria-label="Toggle menu"></button>
            </div>

            <div style="position: absolute; right: 20px; top: 50%; transform: translateY(-50%);">
                <div class="user-avatar-header-slot">
                    <x-user-avatar-dropdown dropdown-id="accountantLinkagesAvatarDropdown" link-class="p-0 menu-icon" />
                </div>
            </div>
        </div>

        <nav class="navigation scroll-bar">
            <div class="container ps-0 pe-0">
                <div class="nav-content">
                    <div class="nav-wrap bg-white bg-transparent-card rounded-3 shadow-sm ps-3 pe-3 pt-0 pb-3 mb-2 mt-2">
                        <ul class="mb-1 pt-0">
                            <li class="nav-item">
                                <a href="{{ route('accountant-linkages.payments') }}" class="nav-content-bttn open-font {{ request()->routeIs('accountant-linkages.payments*') ? 'active' : '' }}">
                                    <i data-feather="credit-card" class="btn-round-md me-3" style="width: 16px; height: 16px;"></i>
                                    <span>Payment Status</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <x-sidebar-my-profile />
                </div>
            </div>
        </nav>

        <div class="accountant-linkages-slot">
            {{ $slot }}
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="/js/plugin.js"></script>
    <script src="/js/lightbox.js"></script>
    <script src="/js/scripts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof feather !== 'undefined') feather.replace();
            if (window.jQuery) jQuery('.preloader').fadeOut(300);
        });
    </script>
    @livewireScripts
    @stack('scripts')
</body>
</html>
