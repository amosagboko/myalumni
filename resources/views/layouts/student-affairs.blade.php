<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Student Affairs' }}</title>

    <link rel="stylesheet" href="/css/themify-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <link rel="icon" type="/image/png" sizes="16x16" href="/images/favicon.png">
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/emoji.css">
    <link rel="stylesheet" href="/css/lightbox.css">
    @livewireStyles
</head>

<body class="color-theme-blue mont-font">
<div class="preloader"></div>
<div class="main-wrapper">
    <div class="nav-header bg-white shadow-xs border-0">
        <div class="nav-top d-flex justify-content-between align-items-center">
            <a href="{{ route('student-affairs.home') }}"><span class="d-inline-block fredoka-font ls-3 fw-600 text-current font-xxl logo-text mb-0">Student Affairs</span></a>
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted font-sm">Welcome, {{ auth()->user()->name ?? 'User' }}</span>
                <button class="nav-menu me-0 ms-2"></button>
            </div>
        </div>
    </div>

    <nav class="navigation scroll-bar">
        <div class="container ps-0 pe-0">
            <div class="nav-content">
                <div class="nav-wrap bg-white bg-transparent-card rounded-3 shadow-sm ps-3 pe-3 pt-0 pb-3 mb-2 mt-2">
                    <ul class="mb-1 pt-0">
                        <li class="nav-item">
                            <a href="{{ route('student-affairs.home') }}" class="nav-content-bttn open-font {{ request()->routeIs('student-affairs.home') ? 'active' : '' }}">
                                <i data-feather="activity" class="btn-round-md me-3" style="width: 16px; height: 16px;"></i>
                                <span>Recent Activity</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('student-affairs.clearance') }}" class="nav-content-bttn open-font {{ request()->routeIs('student-affairs.clearance') ? 'active' : '' }}">
                                <i data-feather="user-check" class="btn-round-md me-3" style="width: 16px; height: 16px;"></i>
                                <span>Go to Clearance</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('student-affairs.audit') }}" class="nav-content-bttn open-font {{ request()->routeIs('student-affairs.audit') ? 'active' : '' }}">
                                <i data-feather="clipboard" class="btn-round-md me-3" style="width: 16px; height: 16px;"></i>
                                <span>Clearance Audit</span>
                            </a>
                        </li>
                        <li class="nav-item mt-4">
                            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="nav-content-bttn open-font w-100 text-start border-0 bg-transparent text-danger">
                                    <i data-feather="log-out" class="btn-round-md me-3" style="width: 16px; height: 16px;"></i>
                                    <span>Logout</span>
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    {{ $slot }}

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="/js/plugin.js"></script>
    <script src="/js/lightbox.js"></script>
    <script src="/js/scripts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/js/script.js"></script>
    @livewireScripts
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            feather.replace();
        });
        // Hide preloader when Livewire initializes
        document.addEventListener('livewire:init', function () {
            setTimeout(function() {
                $('.preloader').fadeOut(300);
            }, 500);
        });
    </script>
    @stack('scripts')
</div>
</body>
</html>

