<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title ?? 'FuLafia | Alumni' }}</title>

    <link rel="stylesheet" href="/css/themify-icons.css">
    <link rel="stylesheet" href="/css/feather.css">
    <link rel="icon" type="image/png" sizes="16x16" href="/images/favicon.png">
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/emoji.css">
    <link rel="stylesheet" href="/css/mobile-nav-fix.css">
    <link rel="stylesheet" href="/css/lightbox.css">
    <link rel="stylesheet" href="/vendor/owl-carousel/css/owl.carousel.min.css">
    <link rel="stylesheet" href="/vendor/owl-carousel/css/owl.theme.default.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    @livewireStyles
    @stack('styles')
</head>

<body class="color-theme-blue mont-font">
    <div class="preloader"></div>

    <div class="main-wrapper">
        @include('layouts.partials.alumni-top-nav')

        <div class="mobile-menu-overlay"></div>

        @include('layouts.partials.alumni-sidebar')

        @if (session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <main class="main-content bg-lightblue theme-dark-bg right-chat-active">
            <div class="middle-sidebar-bottom">
                <div class="middle-sidebar-left">
                    @hasSection('feed_layout')
                        @yield('content')
                    @else
                        <div class="middle-wrap">
                            @yield('content')
                        </div>
                    @endif
                </div>
            </div>
        </main>

        @include('layouts.partials.alumni-right-chat')

        <div class="app-footer border-0 shadow-lg bg-primary-gradiant">
            <a href="{{ route('alumni.home') }}" class="nav-content-bttn nav-center"><i class="feather-home"></i></a>
            <a href="{{ route('friends') }}" class="nav-content-bttn"><i class="feather-users"></i></a>
            <a href="#" class="nav-content-bttn"><i class="feather-map-pin"></i></a>
            <a href="{{ route('profile.edit') }}" class="nav-content-bttn">
                @auth
                    <img src="{{ auth()->user()->avatar ? asset('storage/' . auth()->user()->avatar) : asset('/images/user-8.png') }}" alt="user" class="w30 shadow-xss rounded-circle">
                @else
                    <i class="feather-user"></i>
                @endauth
            </a>
        </div>

        <div class="app-header-search">
            <form class="search-form">
                <div class="form-group searchbox mb-0 border-0 p-1">
                    <input type="text" class="form-control border-0" placeholder="Search...">
                    <i class="input-icon"><i class="feather-search font-xs text-grey-500"></i></i>
                    <a href="#" class="ms-1 mt-1 d-inline-block close searchbox-close"><i class="ti-close font-xs"></i></a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/js/plugin.js"></script>
    <script src="/js/lightbox.js"></script>
    <script src="/js/scripts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof feather !== 'undefined') {
                feather.replace();
            }

            const navMenu = document.querySelector('.nav-menu');
            const navigation = document.querySelector('.navigation');
            const mobileOverlay = document.querySelector('.mobile-menu-overlay');

            if (navMenu && navigation) {
                navMenu.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    this.classList.toggle('active');
                    navigation.classList.toggle('nav-active');
                    if (mobileOverlay) {
                        mobileOverlay.classList.toggle('active');
                    }
                });
            }

            if (mobileOverlay && navMenu && navigation) {
                mobileOverlay.addEventListener('click', function () {
                    navMenu.classList.remove('active');
                    navigation.classList.remove('nav-active');
                    this.classList.remove('active');
                });
            }

            const modal = document.getElementById('onboardingModal');
            if (modal) {
                modal.addEventListener('click', function (e) {
                    if (e.target === this) {
                        e.preventDefault();
                    }
                });

                document.querySelectorAll('.nav-content-bttn').forEach(function (link) {
                    link.addEventListener('click', function (e) {
                        if (window.getComputedStyle(modal).display === 'block' && !modal.contains(e.target)) {
                            e.preventDefault();
                            e.stopPropagation();
                            alert('Please complete your profile or payments to continue.');
                        }
                    });
                });
            }
        });

        document.addEventListener('livewire:navigated', function () {
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
            if (typeof $ !== 'undefined' && $.fn.owlCarousel) {
                $('.category-card').owlCarousel({
                    loop: false,
                    margin: 10,
                    nav: false,
                    dots: false,
                    autoWidth: true
                });
            }
        });
    </script>
    @livewireScripts
    @stack('scripts')
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
