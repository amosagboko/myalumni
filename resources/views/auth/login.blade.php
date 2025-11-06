<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ config('app.name') }}</title>

    <link rel="stylesheet" href="/css/themify-icons.css">
    <link rel="stylesheet" href="/css/feather.css">
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <!-- Favicon icon -->
    <link rel="icon" type="/image/png" sizes="16x16" href="/images/favicon.png">
    <!-- Custom Stylesheet -->
    <link rel="stylesheet" href="/css/style.css"> 
    @livewireStyles



</head>

<body class="color-theme-blue">

    <div class="preloader"></div>

    <div class="main-wrap">

        <div class="nav-header bg-transparent shadow-none border-0">
            <div class="nav-top d-flex justify-content-between align-items-center w-100">
                <a href="{{ route('landing') }}"><i class="feather-zap text-success display1-size me-2 ms-0"></i><span class="d-inline-block fredoka-font ls-3 fw-600 text-current font-xxl logo-text mb-0">FuLafia | Alumni</span></a>
                <a href="{{ route('landing') }}" class="header-btn d-none d-lg-block bg-current fw-500 text-white font-xsss p-3 ms-auto w100 text-center lh-20 rounded-xl">Home</a>
            </div>
        </div>

        <div class="min-vh-100 d-flex align-items-center justify-content-center bg-white" style="padding: 2rem 1rem;">
            <div class="card shadow-lg border-0" style="max-width: 450px; width: 100%; border-radius: 15px;">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h2 class="fw-700 mb-2" style="color: #333; font-size: 2rem;">Welcome Back</h2>
                        <p class="text-muted mb-0">Login to your alumni account</p>
                    </div>
                        
                        <!-- Session Status -->
                        <x-auth-session-status class="mb-4" :status="session('status')" />

                        <!-- Error Messages -->
                        @if ($errors->any())
                            <div class="mb-4">
                                @foreach ($errors->all() as $error)
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        {{ $error }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            <!--email inputs-->
                            <div class="mb-3">
                                <label for="email" class="form-label text-muted small">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i data-feather="mail" class="text-muted" style="width: 18px; height: 18px;"></i>
                                    </span>
                                    <input type="email" name="email" id="email" class="form-control border-start-0" placeholder="your.email@example.com" value="{{ old('email') }}" required autofocus autocomplete="username">
                                </div>
                            </div>
                            <!--password inputs-->
                            <div class="mb-3">
                                <label for="password" class="form-label text-muted small">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i data-feather="lock" class="text-muted" style="width: 18px; height: 18px;"></i>
                                    </span>
                                    <input type="password" name="password" id="password" class="form-control border-start-0" placeholder="Enter your password" required autocomplete="current-password">
                                </div>
                            </div>
                            <!--remember and forgot password-->
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input type="checkbox" name="remember" class="form-check-input" id="remember_me">
                                    <label class="form-check-label text-muted small" for="remember_me">{{ __('Remember me') }}</label>
                                </div>
                                @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-decoration-none small text-muted">{{ __('Forgot password?') }}</a>
                                @endif
                            </div>
                            <!--submit button-->
                            <div class="d-grid">
                                <button type="submit" class="btn btn-dark btn-lg rounded-3 fw-600">{{ __('Log in') }}</button>
                            </div>
                        </form>
                    </div>
                </div> 
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="/js/plugin.js"></script>
    <script src="/js/scripts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/js/script.js"></script>
    @livewireScripts
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            feather.replace();
            // Hide preloader after page loads
            setTimeout(function() {
                $('.preloader').fadeOut(300);
            }, 500);
        });
    </script>
</body>

</html>





