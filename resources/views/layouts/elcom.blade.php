<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title ?? 'FuLafia | ELCOM' }}</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom Stylesheet -->
    <link rel="stylesheet" href="/css/style.css">
    @livewireStyles
    @stack('styles')
</head>

<body class="bg-light">
    <div class="wrapper">
        <!-- Top Navigation -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
            <div class="container-fluid">
                <a class="navbar-brand" href="{{ route('elcom.elections.index') }}">
                    <span class="fw-bold">{{ config('app.name') }} ELCOM</span>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        @auth
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                    <img src="{{ auth()->user()->avatar ? asset('storage/' . auth()->user()->avatar) : asset('/images/user-8.png') }}" 
                                         alt="avatar" 
                                         class="rounded-circle me-2"
                                         style="width: 32px; height: 32px; object-fit: cover;">
                                    <span>{{ auth()->user()->name }}</span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                            <i class="fas fa-user me-2"></i> My Profile
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="fas fa-sign-out-alt me-2"></i> Logout
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        @endauth
                    </ul>
                </div>
            </div>
        </nav>

        <div class="container-fluid">
            <div class="row">
                <!-- Sidebar Navigation -->
                <nav class="col-md-3 col-lg-2 d-md-block bg-white sidebar collapse shadow-sm" style="min-height: calc(100vh - 56px);">
                    <div class="position-sticky pt-3">
                        <div class="px-3 mb-3">
                            <h6 class="text-muted text-uppercase small fw-bold">Election Management</h6>
                        </div>
                        <ul class="nav flex-column">
                            @auth
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('elcom.elections.index') ? 'active' : '' }}" 
                                       href="{{ route('elcom.elections.index') }}">
                                        <i class="fas fa-list me-2"></i> All Elections
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('elcom.elections.create') ? 'active' : '' }}" 
                                       href="{{ route('elcom.elections.create') }}">
                                        <i class="fas fa-plus-circle me-2"></i> Create Election
                                    </a>
                                </li>
                                
                                @if(auth()->user()->hasRole('elcom-chairman'))
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('elcom-chairman.dashboard') }}">
                                            <i class="fas fa-tachometer-alt me-2"></i> ELCOM Chairman Dashboard
                                        </a>
                                    </li>
                                @endif

                                @if(auth()->user()->hasRole('administrator'))
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('admin.dashboard') }}">
                                            <i class="fas fa-cog me-2"></i> Admin Dashboard
                                        </a>
                                    </li>
                                @endif
                            @endauth
                        </ul>
                    </div>
                </nav>

                <!-- Main Content -->
                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                    @if (session()->has('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (session()->has('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @hasSection('content')
                        @yield('content')
                    @else
                        {{ $slot ?? '' }}
                    @endif
                </main>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom Scripts -->
    <script src="/js/scripts.js"></script>
    @stack('scripts')
    @livewireScripts

    <style>
        .sidebar {
            position: fixed;
            top: 56px;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
        }
        
        .sidebar .nav-link {
            color: #333;
            padding: .5rem 1rem;
            font-weight: 500;
        }
        
        .sidebar .nav-link.active {
            color: #0d6efd;
            background-color: rgba(13, 110, 253, 0.1);
        }
        
        .sidebar .nav-link:hover {
            color: #0d6efd;
        }
        
        .sidebar .nav-link i {
            width: 20px;
            text-align: center;
        }
        
        main {
            margin-top: 56px;
        }
        
        @media (max-width: 767.98px) {
            .sidebar {
                position: static;
                height: auto;
                min-height: auto;
            }
            
            main {
                margin-top: 0;
            }
        }
    </style>
</body>
</html> 