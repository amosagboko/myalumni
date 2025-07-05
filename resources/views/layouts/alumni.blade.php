<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title ?? 'FuLafia | Alumni' }}</title>

    <!-- Feather Icons -->
    <script src="{{ asset('js/feather-icons/feather.min.js') }}"></script>
    
    <link rel="stylesheet" href="/css/themify-icons.css">
    <link rel="stylesheet" href="/css/feather.css">
    <!-- Favicon icon -->
    <link rel="icon" type="/image/png" sizes="16x16" href="/images/favicon.png">
    <!-- Custom Stylesheet -->
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/emoji.css">
    <link rel="stylesheet" href="/css/mobile-nav-fix.css">
    
    <!-- Inline Mobile Navigation Fix -->
    <style>
        @media (max-width: 992px) {
            .navigation {
                left: -320px !important;
                right: auto !important;
                transition: left 0.3s ease !important;
            }
            .navigation.nav-active {
                left: 0 !important;
                right: auto !important;
            }
            .nav-menu {
                display: block !important;
            }
            .mobile-menu-overlay {
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                width: 100% !important;
                height: 100% !important;
                background-color: rgba(0, 0, 0, 0.5) !important;
                z-index: 999 !important;
                display: none !important;
            }
            .mobile-menu-overlay.active {
                display: block !important;
            }
        }
    </style>
    
    <link rel="stylesheet" href="/css/lightbox.css">
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    @livewireStyles
</head>

<body class="color-theme-blue mont-font">
    <div class="preloader"></div>
    
    <div class="main-wrapper">
        <!-- navigation top-->
        <div class="nav-header bg-white shadow-xs border-0">
            <div class="nav-top">
                <a href="{{ route('alumni.home') }}"><i class="text-success display1-size me-2 ms-0"></i><span class="d-inline-block fredoka-font ls-3 fw-600 text-current font-xxl logo-text mb-0">{{ config('app.name') }}</span> </a>
                <a href="#" class="mob-menu ms-auto me-2 chat-active-btn"><i data-feather="message-circle" class="text-grey-900 font-sm"></i></a>
                <a href="#" class="me-2 menu-search-icon mob-menu"><i data-feather="search" class="text-grey-900 font-sm"></i></a>
                <button class="nav-menu me-0 ms-2"></button>
                <!-- Test button for debugging -->
                <button id="test-mobile-menu" class="btn btn-sm btn-danger ms-2" style="display: none;">Test Mobile Menu</button>
            </div>
            
            <form action="#" class="float-right header-search">
                <div class="form-group mb-0 icon-input" style="float:right">
                    <i data-feather="search" class="font-sm text-grey-400"></i>
                    <input type="text" placeholder="Start typing to search.." class="bg-grey border-0 lh-32 pt-2 pb-2 ps-5 pe-3 font-xssss fw-500 rounded-xl w350 theme-dark-bg">
                </div>
            </form>

            <a href="#" title="Notifications" class="p-2 text-center ms-auto menu-icon" id="dropdownMenu3" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="dot-count bg-warning"></span>
                <i class="font-lg text-grey-500">
                    <i data-feather="bell"></i>
                </i>
            </a>

            @auth
                <a href="{{ route('profile.edit') }}" class="p-0 ms-3 menu-icon">
                    <img src="{{ auth()->user()->avatar ? asset('storage/' . auth()->user()->avatar) : asset('/images/user-8.png') }}" alt="avatar" class="w40 mt--1">
                </a>
            @endauth
        </div>
        <!-- navigation top -->

        <!-- Mobile Menu Overlay -->
        <div class="mobile-menu-overlay"></div>
        
        <!-- navigation left -->
        <nav class="navigation scroll-bar">
            <div class="container ps-0 pe-0">
                <div class="nav-content">
                    <div class="nav-wrap bg-white bg-transparent-card rounded-xxl shadow-xss pt-3 pb-1 mb-2 mt-2">
                        <div class="nav-caption fw-600 font-xssss text-grey-500"><span>My </span>Dashboard</div>
                        <ul class="mb-1 top-content">
                            <li class="logo d-none d-xl-block d-lg-block"></li>
                            @auth
                                <li><a href="{{ route('alumni.home') }}" class="nav-content-bttn open-font"><i data-feather="home" class="me-3"></i><span>Home</span></a></li>
                                <li><a href="{{ route('friends') }}" class="nav-content-bttn open-font"><i data-feather="users" class="me-3"></i><span>Friends</span></a></li>
                                <li><a href="#" class="nav-content-bttn open-font"><i data-feather="calendar" class="me-3"></i><span>Events</span></a></li>
                                <li><a href="#" class="nav-content-bttn open-font"><i data-feather="message-square" class="me-3"></i><span>Messages</span></a></li>
                                <li><a href="{{ route('alumni.payments.history') }}" class="nav-content-bttn open-font"><i data-feather="file-text" class="me-3"></i><span>Payment History</span></a></li>
                                <li>
                                    @php
                                        $alumni = Auth::user()->alumni ?? null;
                                        $needsBioData = !$alumni || !$alumni->contact_address || !$alumni->phone_number || !$alumni->qualification_type;
                                        $needsPayments = $alumni && $alumni->getActiveFees()->isNotEmpty() && $alumni->getActiveFees()->contains(function($fee) {
                                            return !$fee->isPaid();
                                        });
                                        $clearanceDisabled = $needsBioData || $needsPayments;
                                    @endphp
                                    <a href="{{ $clearanceDisabled ? '#' : route('reports') }}"
                                       class="nav-content-bttn open-font{{ $clearanceDisabled ? ' disabled-link' : '' }}"
                                       @if($clearanceDisabled)
                                           onclick="event.preventDefault(); alert('Please complete your profile and payments to access the Clearance Form.');"
                                           tabindex="-1" aria-disabled="true"
                                       @endif
                                    >
                                        <i data-feather="file-text" class="me-3"></i>
                                        <span>Clearance Form</span>
                                    </a>
                                </li>
                                @if(auth()->user()->hasRole('alumni'))
                                    @php
                                        $electionLinksDisabled = $needsBioData || $needsPayments;
                                    @endphp
                                    <li>
                                        <a href="{{ $electionLinksDisabled ? '#' : route('alumni.elections') }}"
                                           class="nav-content-bttn open-font{{ $electionLinksDisabled ? ' disabled-link' : '' }}"
                                           @if($electionLinksDisabled)
                                               onclick="event.preventDefault(); alert('Please complete your profile and payments to access Elections.');"
                                               tabindex="-1" aria-disabled="true"
                                           @endif
                                        >
                                            <i data-feather="check-square" class="me-3"></i>
                                            <span>Elections</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ $electionLinksDisabled ? '#' : route('alumni.elections.expression-of-interest.status') }}"
                                           class="nav-content-bttn open-font{{ $electionLinksDisabled ? ' disabled-link' : '' }}"
                                           @if($electionLinksDisabled)
                                               onclick="event.preventDefault(); alert('Please complete your profile and payments to access EOI Status.');"
                                               tabindex="-1" aria-disabled="true"
                                           @endif
                                        >
                                            <i data-feather="clipboard" class="me-3"></i>
                                            <span>EOI Status</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ $electionLinksDisabled ? '#' : route('alumni.elections') . '#accreditation' }}"
                                           class="nav-content-bttn open-font{{ $electionLinksDisabled ? ' disabled-link' : '' }}"
                                           @if($electionLinksDisabled)
                                               onclick="event.preventDefault(); alert('Please complete your profile and payments to access Accreditation.');"
                                               tabindex="-1" aria-disabled="true"
                                           @endif
                                        >
                                            <i data-feather="user-check" class="me-3"></i>
                                            <span>Accreditation</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ $electionLinksDisabled ? '#' : route('alumni.elections') . '#results' }}"
                                           class="nav-content-bttn open-font{{ $electionLinksDisabled ? ' disabled-link' : '' }}"
                                           @if($electionLinksDisabled)
                                               onclick="event.preventDefault(); alert('Please complete your profile and payments to access Election Results.');"
                                               tabindex="-1" aria-disabled="true"
                                           @endif
                                        >
                                            <i data-feather="bar-chart-2" class="me-3"></i>
                                            <span>Election Results</span>
                                        </a>
                                    </li>
                                @endif

                                {{-- Agent Menu Items --}}
                                @if(auth()->user()->hasRole('alumni-agent'))
                                    <li class="mt-3">
                                        <div class="nav-caption fw-600 font-xssss text-grey-500"><span>Agent </span>Dashboard</div>
                                    </li>
                                    <li>
                                        <a href="{{ route('agent.dashboard') }}" 
                                           class="nav-content-bttn open-font {{ request()->routeIs('agent.dashboard') ? 'active' : '' }}">
                                            <i data-feather="home" class="me-3"></i>
                                            <span>Agent Dashboard</span>
                                        </a>
                                    </li>
                                    <li>
                                        {{-- <a href="{{ route('agent.candidates.index') }}" 
                                           class="nav-content-bttn open-font {{ request()->routeIs('agent.candidates.*') ? 'active' : '' }}">
                                            <i data-feather="users" class="me-3"></i>
                                            <span>My Candidates</span>
                                        </a> --}}
                                    </li>
                                @endif
                            @endauth
                        </ul>
                    </div>

                    @auth
                        <div class="nav-wrap bg-white bg-transparent-card rounded-xxl shadow-xss pt-3 pb-1">
                            <div class="nav-caption fw-600 font-xssss text-grey-500"><span></span> Account</div>
                            <ul class="mb-1">
                                <li class="logo d-none d-xl-block d-lg-block"></li>
                                <li><a href="{{ route('profile.update') }}" class="nav-content-bttn open-font h-auto pt-2 pb-2"><i data-feather="settings" class="font-sm me-3 text-grey-500"></i><span>My Profile</span></a></li>
                                <li><a href="#" class="nav-content-bttn open-font h-auto pt-2 pb-2"><i data-feather="message-square" class="font-sm me-3 text-grey-500"></i><span>Messages</span><span class="circle-count bg-warning mt-0">23</span></a></li>
                            </ul>
                        </div>
                    @endauth
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

        <main>
            @if(View::hasSection('content'))
                @yield('content')
            @else
                {{ $slot ?? '' }}
            @endif
        </main>

        <script src="/js/plugin.js"></script>
        <script src="/js/lightbox.js"></script>
        <script src="/js/scripts.js"></script>
        <!-- Select2 -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            // Initialize Feather Icons
            document.addEventListener('DOMContentLoaded', function() {
                feather.replace();
            });
        </script>
        @push('scripts')
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Prevent closing the modal by clicking outside
            const modal = document.getElementById('onboardingModal');
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        e.preventDefault();
                    }
                });

                // Disable all nav links when modal is active
                document.querySelectorAll('.nav-content-bttn').forEach(function(link) {
                    link.addEventListener('click', function(e) {
                        // Check if modal is visible (display: block)
                        if (window.getComputedStyle(modal).display === 'block') {
                            // Allow only links inside the modal
                            if (!modal.contains(e.target)) {
                                e.preventDefault();
                                e.stopPropagation();
                                alert('Please complete your profile or payments to continue.');
                            }
                        }
                    });
                });
            }
        });
        </script>
        @endpush
    </div>
    @livewireScripts
    <style>
    .disabled-link {
        pointer-events: none !important;
        color: #aaa !important;
        opacity: 0.6 !important;
        cursor: not-allowed !important;
        text-decoration: none !important;
    }
    </style>
</body>
</html> 