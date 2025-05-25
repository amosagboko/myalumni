<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? config('app.name') }}</title>
    
    <!-- Styles -->
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/themify-icons.css">
    <link rel="stylesheet" href="/css/feather.css">
    <!-- Feather Icons CDN -->
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    @livewireStyles
</head>
<body>
    <div class="theme-layout">
        <!-- navigation top -->
        <div class="nav-header bg-white shadow-xs border-0">
            <div class="nav-top">
                <a href="{{ route('elcom-chairman.home') }}">
                    <i class="text-success display1-size me-2 ms-0"></i>
                    <span class="d-inline-block fredoka-font ls-3 fw-600 text-current font-xxl logo-text mb-0">{{ config('app.name') }}</span>
                </a>
                <button class="nav-menu me-0 ms-2"></button>
            </div>
            
            <a href="{{ route('profile.update') }}" class="p-0 ms-3 menu-icon">
                <img src="{{ auth()->user()->avatar ? asset('storage/' . auth()->user()->avatar) : asset('/images/user-8.png') }}" alt="avatar" class="w40 mt--1">
            </a>
        </div>
        <!-- navigation top -->

        <!-- navigation left -->
        <nav class="navigation scroll-bar">
            <div class="container ps-0 pe-0">
                <div class="nav-content">
                    <div class="nav-wrap bg-white bg-transparent-card rounded-3 shadow-sm ps-3 pe-3 pt-0 pb-3 mb-2 mt-2">
                        <ul class="mb-1 pt-0">
                            <li class="nav-item">
                                <a href="{{ route('elcom-chairman.home') }}" class="nav-content-bttn open-font {{ request()->routeIs('elcom-chairman.home') ? 'active' : '' }}">
                                    <i data-feather="home" class="btn-round-md me-3" style="width: 16px; height: 16px;"></i>
                                    <span>Dashboard</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('elcom-chairman.elections.index') }}" class="nav-content-bttn open-font {{ request()->routeIs('elcom-chairman.elections*') ? 'active' : '' }}">
                                    <i data-feather="award" class="btn-round-md me-3" style="width: 16px; height: 16px;"></i>
                                    <span>Manage Election</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
        <!-- navigation left -->

        <div class="main-content" style="margin-left: 100px; padding: 20px; min-height: calc(100vh - 60px);">
            @if (session()->has('success'))
                <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session()->has('error'))
                <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{ $slot ?? '' }}
        </div>
    </div>

    <!-- Scripts -->
    <script src="/js/plugin.js"></script>
    <script src="/js/lightbox.js"></script>
    <script src="/js/scripts.js"></script>
    <script>
        // Initialize Feather icons
        document.addEventListener('DOMContentLoaded', function() {
            feather.replace();
        });
    </script>
    @livewireScripts
</body>
</html> 