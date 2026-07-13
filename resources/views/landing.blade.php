<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FuLafia Alumni Portal</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/css/lightbox.css">
    
    <style>
        body {
            background-color: #f8f9fa;
        }
        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('/images/fulafia-campus.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
        }
        .hero-logo {
            max-width: 150px;
            height: auto;
            margin-bottom: 2rem;
            filter: brightness(0) invert(1); /* Makes the logo white */
        }
        .search-section {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-top: -50px;
        }
        .feature-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
            padding: 25px;
            height: 100%;
            transition: transform 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-5px);
        }
        .feature-icon {
            font-size: 2.5rem;
            color: #0d6efd;
            margin-bottom: 20px;
        }
        .navbar-logo {
            width: 50px;
            height: 50px;
            margin-right: 20px;
        }
        .landing-content-item {
            padding: 12px;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            background: #fff;
        }
        .landing-content-carousel {
            margin-top: 1rem;
            padding-bottom: 0.5rem;
        }
        .landing-content-carousel .carousel-inner {
            min-height: 180px;
        }
        .landing-content-carousel__indicators {
            position: static;
            margin: 12px 0 0;
        }
        .landing-content-carousel__indicators [data-bs-target] {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #cbd5e1;
            border: 0;
            opacity: 1;
        }
        .landing-content-carousel__indicators .active {
            background-color: #0d6efd;
        }
        .landing-content-carousel__empty {
            margin-top: 1rem;
            color: #6c757d;
        }
        .landing-content-item__thumb {
            display: block;
            width: 88px;
            height: 88px;
            border-radius: 10px;
            overflow: hidden;
            background: #f1f4fb;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            text-decoration: none;
        }
        .landing-content-item__thumb:hover .landing-content-item__thumb-image {
            opacity: 0.92;
        }
        .landing-content-item__thumb-image {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .landing-content-item__title {
            font-size: 1rem;
            font-weight: 600;
            color: #212529;
        }
        .landing-content-item__meta {
            font-size: 0.8125rem;
            color: #6c757d;
            margin-bottom: 0;
        }
        .landing-content-item__description {
            font-size: 0.875rem;
            color: #495057;
            margin-bottom: 0;
            line-height: 1.45;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <img src="{{ asset('images/alumni-logo1.jpg') }}" alt="FuLafia Logo" class="navbar-logo">
                FuLafia Alumni Portal
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section text-center">
        <div class="container">
            
            <h1 class="display-4 mb-4">Welcome to FuLafia Alumni Portal</h1>
            <p class="lead mb-5">Connect with fellow alumni, stay updated with university news, and access exclusive alumni benefits. If you graduated in 2024 or earlier, please begin your onboarding process by entering your matriculation number to search.</p>
        </div>
    </section>

    <!-- Search Section -->
    <section class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="search-section">
                    <h2 class="text-center mb-4">Retrieve Your Alumni Credentials</h2>
                    
                    <!-- Onboarding Status Notice -->
                    @if(\App\Models\OnboardingSetting::isEnabled())
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Onboarding Status:</strong> Alumni registration is currently open. 
                            Please complete your registration to access the alumni portal.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @else
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Onboarding Status:</strong> Alumni registration is currently closed. 
                            Please check back later or contact the administrator for more information.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('info'))
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            {{ session('info') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('landing.search-credentials') }}" method="GET" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="matriculation_id" class="form-label">Matriculation Number</label>
                            <input type="text" class="form-control form-control-lg" id="matriculation_id" name="matriculation_id" required>
                            <div class="form-text">Enter your matriculation number to retrieve your alumni credentials</div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">Search</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="container my-5">
        <div class="row g-4">
            <!-- Highlights Section -->
            <div class="col-md-4">
                <div class="feature-card text-center">
                    <i class="bi bi-stars feature-icon"></i>
                    <h3>Highlights</h3>
                    @include('landing.partials.content-carousel', [
                        'items' => $connectItems,
                        'carouselId' => 'landingHighlightsCarousel',
                        'lightboxGroup' => 'landing-highlights',
                        'emptyMessage' => 'Discover highlights and featured stories from the alumni community.',
                    ])
                </div>
            </div>

            <!-- Events Section -->
            <div class="col-md-4">
                <div class="feature-card text-center">
                    <i class="bi bi-calendar-event feature-icon"></i>
                    <h3>News</h3>
                    @include('landing.partials.content-carousel', [
                        'items' => $eventItems,
                        'carouselId' => 'landingNewsCarousel',
                        'lightboxGroup' => 'landing-news',
                        'showDate' => true,
                        'showVenue' => true,
                        'emptyMessage' => 'Stay updated with the latest news and updates from the alumni community.',
                    ])
                </div>
            </div>

            <!-- Opportunities Section -->
            <div class="col-md-4">
                <div class="feature-card text-center">
                    <i class="bi bi-briefcase feature-icon"></i>
                    <h3>Events</h3>
                    @include('landing.partials.content-carousel', [
                        'items' => $opportunityItems,
                        'carouselId' => 'landingEventsCarousel',
                        'lightboxGroup' => 'landing-events',
                        'showDate' => true,
                        'showVenue' => true,
                        'emptyMessage' => 'Stay updated with our latest events and happenings as they unfold.',
                    ])
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>FuLafia Alumni Portal</h5>
                    <p>Stay connected with your alma mater and fellow alumni.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>&copy; {{ date('Y') }} Federal University of Lafia. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/js/lightbox.js"></script>
    <script>
        if (typeof lightbox !== 'undefined') {
            lightbox.option({
                resizeDuration: 400,
                fadeDuration: 400,
                imageFadeDuration: 400,
                albumLabel: 'Image %1 of %2',
                wrapAround: true,
                disableScrolling: true,
                showImageNumberLabel: true,
            });
        }

        // Form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>
</body>
</html> 