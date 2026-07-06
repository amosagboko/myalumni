<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>FuLafia | Alumni Agent Portal</title>

    <link rel="stylesheet" href="/css/themify-icons.css">
    <link rel="stylesheet" href="/css/feather.css">
    <link rel="icon" type="image/png" sizes="16x16" href="/images/favicon.png">
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
                <a href="{{ route('agent.dashboard') }}">
                    <i class="text-success display1-size me-2 ms-0"></i>
                    <span class="d-inline-block fredoka-font ls-3 fw-600 text-current font-xxl logo-text mb-0">
                        {{ config('app.name') }} - Agent Portal
                    </span>
                </a>
                <button class="nav-menu me-0 ms-2"></button>
            </div>

            @auth
                <div class="user-avatar-header-slot ms-3">
                    <x-user-avatar-dropdown dropdown-id="agentAvatarDropdown" link-class="p-0 menu-icon" />
                </div>
            @endauth
        </div>

        <!-- navigation left -->
        <nav class="navigation scroll-bar">
            <div class="container ps-0 pe-0">
                <div class="nav-content">
                    <div class="nav-wrap bg-white bg-transparent-card rounded-xxl shadow-xss pt-3 pb-1 mb-2 mt-2">
                        <div class="nav-caption fw-600 font-xssss text-grey-500"><span>Agent </span>Dashboard</div>
                        <ul class="mb-1 top-content">
                            <li class="logo d-none d-xl-block d-lg-block"></li>
                            <li>
                                <a href="{{ route('agent.dashboard') }}" 
                                   class="nav-content-bttn open-font {{ request()->routeIs('agent.dashboard') ? 'active' : '' }}">
                                    <i class="feather-home btn-round-md bg-blue-gradiant me-3"></i>
                                    <span>Dashboard</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('agent.candidates.index') }}" 
                                   class="nav-content-bttn open-font {{ request()->routeIs('agent.candidates.*') ? 'active' : '' }}">
                                    <i class="feather-users btn-round-md bg-red-gradiant me-3"></i>
                                    <span>My Candidates</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    @auth
                        <div class="nav-wrap bg-white bg-transparent-card rounded-xxl shadow-xss pt-3 pb-1">
                            <div class="nav-caption fw-600 font-xssss text-grey-500"><span>Account</span></div>
                            <ul class="mb-1">
                                <li class="logo d-none d-xl-block d-lg-block"></li>
                                <li>
                                    <a href="{{ route('profile.edit') }}"
                                       class="nav-content-bttn open-font h-auto pt-2 pb-2 {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                                        <i class="feather-user font-sm me-3 text-grey-500"></i>
                                        <span>My Profile</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    @endauth
                </div>
            </div>
        </nav>

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

        <main>
            @if(View::hasSection('content'))
                @yield('content')
            @else
                {{ $slot ?? '' }}
            @endif
        </main>
    </div>

    @livewireScripts
    <script src="/js/plugin.js"></script>
    <script src="/js/lightbox.js"></script>
    <script src="/js/scripts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 