<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ config('app.name') }}</title>

    <link rel="stylesheet" href="/css/themify-icons.css">
    <link rel="stylesheet" href="/css/feather.css">
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
            <div class="nav-top w-100">
                <a href="{{ url('/') }}"><i class="feather-zap text-success display1-size me-2 ms-0"></i><span class="d-inline-block fredoka-font ls-3 fw-600 text-current font-xxl logo-text mb-0">FuLafia | Alumni</span> </a>
                <a href="#" class="mob-menu ms-auto me-2 chat-active-btn"><i class="feather-message-circle text-grey-900 font-sm btn-round-md bg-greylight"></i></a>
                
                <a href="/login" class="header-btn d-none d-lg-block bg-dark fw-500 text-white font-xsss p-3 ms-auto w100 text-center lh-20 rounded-xl">Login</a>
                <a href="{{ route('landing') }}" class="header-btn d-none d-lg-block bg-current fw-500 text-white font-xsss p-3 ms-2 w100 text-center lh-20 rounded-xl">Home</a>

            </div>
            
            
        </div>

        <div class="row">
            <div class="col-xl-5 d-none d-xl-block p-0 vh-100 bg-image-cover bg-no-repeat" style="background-image: url(images/login-bg.jpg);"></div>
            <div class="col-xl-7 vh-100 align-items-center d-flex bg-white rounded-3 overflow-hidden">
                <div class="card shadow-none border-0 ms-auto me-auto login-card">
                    <div class="card-body rounded-0 text-left">
                        <h2 class="fw-700 display1-size display2-md-size mb-3">Login into <br>your account</h2>
                        
                        <!-- Session Status -->
                        <x-auth-session-status class="mb-4" :status="session('status')" />

                        <!-- Error Messages -->
                        @if ($errors->any())
                            <div class="mb-4">
                                @foreach ($errors->all() as $error)
                                    <div class="p-4 mb-4 text-sm text-red-600 bg-red-50 rounded-lg font-weight-bold" role="alert">
                                        {{ $error }}
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            <!--email inputs-->
                            <div class="form-group icon-input mb-3">
                                <i class="font-sm ti-email text-grey-500 pe-0"></i>
                                <input type="text" name="email" class="style2-input ps-5 form-control text-grey-900 font-xsss fw-600" placeholder="Your Email Address" value="{{ old('email') }}" required autofocus autocomplete="username">                        
                            </div>
                            <!--password inputs-->
                            <div class="form-group icon-input mb-1">
                                <input type="Password" name="password" class="style2-input ps-5 form-control text-grey-900 font-xss ls-3" placeholder="Password" required autocomplete="current-password">
                                <i class="font-sm ti-lock text-grey-500 pe-0"></i>
                            </div>
                            <!--remember inputs-->
                            <div class="form-check text-left mb-3">
                                <input type="checkbox" name="remember" class="form-check-input mt-2" id="remember_me">
                                <label class="form-check-label font-xsss text-grey-500" for="remember_me">{{ __('Remember me') }}</label>
                                @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="fw-600 font-xsss text-grey-700 mt-1 float-right">{{ __('Forgot your password?') }}</a>
                                @endif
                            </div>
                            <div class="form-group mb-1">
                                <button class="form-control text-center style2-input text-white fw-600 bg-dark border-0 p-0 ">{{ __('Log in') }}</button>
                            </div>
                        </form>
                         
                        <div class="col-sm-12 p-0 text-left">
                            
                            <h6 class="text-grey-500 font-xsss fw-500 mt-0 mb-0 lh-32">Dont have account <a href="{{ route('register') }}" class="fw-700 ms-1">Register</a></h6>
                        </div>
                        
                    </div>
                </div> 
            </div>
        </div>
    </div>  
    <script src="/js/plugin.js"></script>
    <script src="/js/scripts.js"></script>
    @livewireScripts
</body>

</html>





