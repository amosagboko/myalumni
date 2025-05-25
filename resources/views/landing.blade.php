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
            <div class="col-md-4">
                <div class="feature-card text-center">
                    <i class="bi bi-people feature-icon"></i>
                    <h3>Connect</h3>
                    <p>Connect with fellow alumni and expand your professional network.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card text-center">
                    <i class="bi bi-calendar-event feature-icon"></i>
                    <h3>Events</h3>
                    <p>Stay updated with alumni events, reunions, and networking opportunities.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card text-center">
                    <i class="bi bi-briefcase feature-icon"></i>
                    <h3>Opportunities</h3>
                    <p>Access exclusive job opportunities and career development resources.</p>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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