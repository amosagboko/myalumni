@props(['title' => 'FuLafia | Alumni Relations Officer'])

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title }}</title>

    <link rel="stylesheet" href="/css/themify-icons.css">
    <link rel="stylesheet" href="/css/feather.css">
    <!-- Feather Icons CDN -->
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <!-- Favicon icon -->
    <link rel="icon" type="/image/png" sizes="16x16" href="/images/favicon.png">
    <!-- Custom Stylesheet -->
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/emoji.css">
    
    <link rel="stylesheet" href="/css/lightbox.css">
    @livewireStyles
</head>

<body class="color-theme-blue mont-font">
    <div class="preloader"></div>
    
    <div class="main-wrapper">
        <!-- navigation top-->
        <div class="nav-header bg-white shadow-xs border-0">
            <div class="nav-top">
                <a href="{{ route('alumni-relations-officer.home') }}"><i class="text-success display1-size me-2 ms-0"></i><span class="d-inline-block fredoka-font ls-3 fw-600 text-current font-xxl logo-text mb-0">{{ config('app.name') }}</span> </a>
                <a href="#" class="mob-menu ms-auto me-2 chat-active-btn"><i class="feather-message-circle text-grey-900 font-sm btn-round-md bg-greylight"></i></a>
                <a href="#" class="me-2 menu-search-icon mob-menu"><i class="feather-search text-grey-900 font-sm btn-round-md bg-greylight"></i></a>
                <button class="nav-menu me-0 ms-2"></button>
            </div>
            
            <form action="#" class="float-right header-search">
                <div class="form-group mb-0 icon-input" style="float:right">
                    <i class="feather-search font-sm text-grey-400"></i>
                    <input type="text" placeholder="Start typing to search.." class="bg-grey border-0 lh-32 pt-2 pb-2 ps-5 pe-3 font-xssss fw-500 rounded-xl w350 theme-dark-bg">
                </div>
            </form>

            <a href="#" title="Notifications" class="p-2 text-center ms-auto menu-icon" id="dropdownMenu3" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="dot-count bg-warning"></span>
                <i class="font-lg bg-greylight btn-round-lg theme-dark-bg text-grey-500">
                    <i class="feather-bell"></i>
                </i>
            </a>

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
                                <a href="{{ route('alumni-relations-officer.home') }}" class="nav-content-bttn open-font {{ request()->routeIs('alumni-relations-officer.home') ? 'active' : '' }}">
                                    <i data-feather="home" class="btn-round-md me-3" style="width: 16px; height: 16px;"></i>
                                    <span>Dashboard</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('alumni-relations-officer.users') }}" class="nav-content-bttn open-font {{ request()->routeIs('alumni-relations-officer.users*') ? 'active' : '' }}">
                                    <i data-feather="users" class="btn-round-md me-3" style="width: 16px; height: 16px;"></i>
                                    <span>Manage Alumni</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('upload.alumni') }}" class="nav-content-bttn open-font {{ request()->routeIs('upload.alumni*') ? 'active' : '' }}">
                                    <i data-feather="upload" class="btn-round-md me-3" style="width: 16px; height: 16px;"></i>
                                    <span>Upload Alumni</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('create.event.index') }}" class="nav-content-bttn open-font {{ request()->routeIs('create.event*') ? 'active' : '' }}">
                                    <i data-feather="calendar" class="btn-round-md me-3" style="width: 16px; height: 16px;"></i>
                                    <span>Create Event</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('retrieve.credentials') }}" class="nav-content-bttn open-font {{ request()->routeIs('retrieve.credentials*') ? 'active' : '' }}">
                                    <i data-feather="key" class="btn-round-md me-3" style="width: 16px; height: 16px;"></i>
                                    <span>Retrieve Credentials</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="nav-wrap bg-white bg-transparent-card rounded-3 shadow-sm ps-3 pe-3 pt-0 pb-3">
                        <div class="nav-caption fw-600 font-xssss text-grey-500"><span></span> Account</div>
                        <ul class="mb-1 pt-0">
                            <li class="nav-item">
                                <a href="{{ route('profile.update') }}" class="nav-content-bttn open-font {{ request()->routeIs('profile.update*') ? 'active' : '' }}">
                                    <i data-feather="settings" class="btn-round-md me-3" style="width: 16px; height: 16px;"></i>
                                    <span>My Profile</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
        <!-- navigation left -->

        @if (session()->has('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <div class="main-content" style="margin-left: 280px; padding: 20px; min-height: calc(100vh - 60px);">
            {{ $slot ?? '' }}
        </div>

        <script src="/js/plugin.js"></script>
        <script src="/js/lightbox.js"></script>
        <script src="/js/scripts.js"></script>
        <script>
            // Initialize Feather icons
            document.addEventListener('DOMContentLoaded', function() {
                feather.replace();
            });
        </script>
    </div>
    @livewireScripts
    @stack('scripts')
</body>
</html>