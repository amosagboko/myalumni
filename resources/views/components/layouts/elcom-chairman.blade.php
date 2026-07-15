@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name').' | ELCOM Chairman' }}</title>

    <link rel="stylesheet" href="/css/themify-icons.css">
    <link rel="stylesheet" href="/css/feather.css">
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <link rel="icon" type="image/png" sizes="16x16" href="/images/favicon.png">
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/emoji.css">
    <link rel="stylesheet" href="/css/lightbox.css">
    @livewireStyles
    @stack('styles')
    <style>
        /* Header is in-flow; kill the theme's 90px main-content top pad so pages sit under the nav. */
        .elcom-chairman-slot > .main-content {
            padding-top: 1.25rem !important;
            padding-right: 1.25rem !important;
        }
    </style>
</head>

<body class="color-theme-blue mont-font">
    <div class="preloader"></div>

    <div class="main-wrapper">
        <div class="nav-header bg-white shadow-xs border-0" style="position: relative;">
            <div class="nav-top">
                <a href="{{ route('elcom-chairman.home') }}">
                    <i class="text-success display1-size me-2 ms-0"></i>
                    <span class="d-inline-block fredoka-font ls-3 fw-600 text-current font-xxl logo-text mb-0">{{ config('app.name') }}</span>
                </a>
                <button class="nav-menu me-0 ms-2" type="button" aria-label="Toggle menu"></button>
            </div>

            <div style="position: absolute; right: 20px; top: 50%; transform: translateY(-50%);">
                <div class="user-avatar-header-slot">
                    <x-user-avatar-dropdown dropdown-id="elcomChairmanAvatarDropdown" link-class="p-0 menu-icon" />
                </div>
            </div>
        </div>

        <nav class="navigation scroll-bar">
            <div class="container ps-0 pe-0">
                <div class="nav-content">
                    <div class="nav-wrap bg-white bg-transparent-card rounded-3 shadow-sm ps-3 pe-3 pt-0 pb-3 mb-2 mt-2">
                        <ul class="mb-1 pt-0">
                            <li class="nav-item">
                                <a href="{{ route('elcom-chairman.home') }}" class="nav-content-bttn open-font {{ request()->routeIs('elcom-chairman.home') || request()->routeIs('elcom-chairman.dashboard') ? 'active' : '' }}">
                                    <i data-feather="home" class="btn-round-md me-3" style="width: 16px; height: 16px;"></i>
                                    <span>Dashboard</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('elcom-chairman.elections.index') }}" class="nav-content-bttn open-font {{ request()->routeIs('elcom-chairman.elections*') || request()->routeIs('elcom.elections*') ? 'active' : '' }}">
                                    <i data-feather="award" class="btn-round-md me-3" style="width: 16px; height: 16px;"></i>
                                    <span>Manage Election</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <x-sidebar-my-profile />
                </div>
            </div>
        </nav>

        <div class="elcom-chairman-slot">
            @if (session()->has('success'))
                <div class="alert alert-success alert-dismissible fade show py-2 mx-3" role="alert" style="margin-left: calc(280px + 1rem); margin-right: 1rem;">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session()->has('error'))
                <div class="alert alert-danger alert-dismissible fade show py-2 mx-3" role="alert" style="margin-left: calc(280px + 1rem); margin-right: 1rem;">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

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
        });
        document.addEventListener('livewire:init', function () {
            setTimeout(function () {
                if (window.jQuery) jQuery('.preloader').fadeOut(300);
            }, 500);
        });
        document.addEventListener('livewire:navigated', function () {
            if (typeof feather !== 'undefined') feather.replace();
        });
    </script>
    @livewireScripts
    @stack('scripts')
</body>
</html>
