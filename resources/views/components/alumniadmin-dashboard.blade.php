@props(['title' => 'FuLafia | Alumni'])

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title }}</title>

    <link rel="stylesheet" href="/css/themify-icons.css">
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
                <a href="{{ route('admin.dashboard') }}"><i class="text-success display1-size me-2 ms-0"></i><span class="d-inline-block fredoka-font ls-3 fw-600 text-current font-xxl logo-text mb-0">{{ config('app.name') }}</span> </a>
                <a href="#" class="mob-menu ms-auto me-2 chat-active-btn"><i class="feather-message-circle text-grey-900 font-sm btn-round-md bg-greylight"></i></a>
                <a href="default-video.html" class="mob-menu me-2"><i class="feather-video text-grey-900 font-sm btn-round-md bg-greylight"></i></a>
                <a href="#" class="me-2 menu-search-icon mob-menu"><i class="feather-search text-grey-900 font-sm btn-round-md bg-greylight"></i></a>
                <button class="nav-menu me-0 ms-2"></button>
            </div>
            
            <form action="#" class="float-right header-search">
                <div class="form-group mb-0 icon-input" style="float:right">
                    <i class="feather-search font-sm text-grey-400"></i>
                    <input type="text" placeholder="Start typing to search.." class="bg-grey border-0 lh-32 pt-2 pb-2 ps-5 pe-3 font-xssss fw-500 rounded-xl w350 theme-dark-bg">
                </div>
            </form>

            <div style="position: absolute; right: 20px; top: 50%; transform: translateY(-50%);">
                <a href="{{ route('profile.update') }}" class="p-0 menu-icon"><img src="{{ auth()->user()->avatar ? asset('storage/' . auth()->user()->avatar) : asset('/images/user-8.png') }}" alt="avatar" class="w40 mt--1"></a>
            </div>
        </div>
        <!-- navigation top -->

        <!-- navigation left -->
        <nav class="navigation scroll-bar">
            <div class="container ps-0 pe-0">
                <div class="nav-content">
                    <div class="nav-wrap bg-white bg-transparent-card rounded-3 shadow-sm ps-3 pe-3 pt-0 pb-3 mb-2 mt-2">
                        <ul class="mb-1 pt-0">
                            <li class="nav-item">
                                <a href="{{ route('admin.dashboard') }}" class="nav-content-bttn open-font {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                                    <i data-feather="home" class="btn-round-md me-3" style="width: 16px; height: 16px;"></i>
                                    <span>Dashboard</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.users') }}" class="nav-content-bttn open-font" ><i data-feather="users" class="btn-round-md me-3" style="width: 16px; height: 16px;"></i><span>App Users</span></a></li>
                            <li><a href="{{ route('upload.alumni') }}" class="nav-content-bttn open-font" ><i data-feather="award" class="btn-round-md me-3" style="width: 16px; height: 16px;"></i><span>Upload Alumni</span></a></li>
                            <li class="nav-item">
                                <a href="{{ route('admin.fee-types.index') }}" class="nav-content-bttn open-font {{ request()->routeIs('admin.fee-types*') ? 'active' : '' }}">
                                    <i data-feather="list" class="btn-round-md me-3" style="width: 16px; height: 16px;"></i>
                                    <span>Fee Types</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('fee-templates.index') }}" class="nav-content-bttn open-font {{ request()->routeIs('fee-templates*') ? 'active' : '' }}">
                                    <i data-feather="file-text" class="btn-round-md me-3" style="width: 16px; height: 16px;"></i>
                                    <span>Fee Management</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('alumni-years.index') }}" class="nav-content-bttn open-font {{ request()->routeIs('alumni-years.*') ? 'active' : '' }}">
                                    <i data-feather="calendar" class="btn-round-md me-3" style="width: 16px; height: 16px;"></i>
                                    <span>Alumni Years</span>
                                </a>
                            </li>
                            <li><a href="{{ route('create.event.index') }}" class="nav-content-bttn open-font" ><i data-feather="zap" class="btn-round-md me-3" style="width: 16px; height: 16px;"></i><span>Create Event</span></a></li>
                            <li class="nav-item">
                                <a href="{{ route('elcom.elections.index') }}" class="nav-content-bttn open-font {{ request()->routeIs('elcom.elections*') ? 'active' : '' }}">
                                    <i data-feather="users" class="btn-round-md me-3" style="width: 16px; height: 16px;"></i>
                                    <span>Manage Elections</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Statistics Section -->
                    <div class="nav-wrap bg-white bg-transparent-card rounded-3 shadow-sm ps-3 pe-3 pt-0 pb-3 mb-2">
                        <div class="nav-caption fw-600 font-xssss text-grey-500 mb-2">Statistics</div>
                        <ul class="mb-1 pt-0">
                            <li class="nav-item">
                                <a href="{{ route('admin.statistics.transactions') }}" class="nav-content-bttn open-font {{ request()->routeIs('admin.statistics.transactions') ? 'active' : '' }}">
                                    <i data-feather="bar-chart-2" class="btn-round-md me-3" style="width: 16px; height: 16px;"></i>
                                    <span>Transactions</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.statistics.alumni-distribution') }}" class="nav-content-bttn open-font {{ request()->routeIs('admin.statistics.alumni-distribution') ? 'active' : '' }}">
                                    <i data-feather="pie-chart" class="btn-round-md me-3" style="width: 16px; height: 16px;"></i>
                                    <span>Alumni Distribution</span>
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

        {{ $slot }}

    <script src="/js/plugin.js"></script>
    <script src="/js/lightbox.js"></script>
    <script src="/js/scripts.js"></script>
    <script>
        // Initialize Feather Icons
        document.addEventListener('DOMContentLoaded', function() {
            feather.replace();
        });
    </script>
    
    </div>
    @livewireScripts

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="/js/script.js"></script>
    @stack('scripts')
</body>
</html>