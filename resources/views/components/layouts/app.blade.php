<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Alumni Management System' }}</title>
    
    <!-- Scripts -->
    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="/css/app.css">
        <script src="/js/app.js" defer></script>
    @endif
    
    <!-- Styles -->
    @livewireStyles
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        <!-- Navigation -->
        <nav class="bg-white border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="shrink-0 flex items-center">
                            <a href="{{ route('dashboard') }}">
                                <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                            </a>
                        </div>

                        <!-- Navigation Links -->
                        <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                            <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                                {{ __('Dashboard') }}
                            </x-nav-link>
                            
                            @role('administrator')
                            <x-nav-link :href="route('admin.users')" :active="request()->routeIs('admin.users')">
                                {{ __('Manage Users') }}
                            </x-nav-link>
                            @endrole
                            <x-nav-link :href="route('profile.edit')" :active="request()->routeIs('profile.*')">
                                {{ __('My Profile') }}
                            </x-nav-link>
                        </div>
                    </div>

                    <!-- Avatar menu -->
                    <div class="hidden sm:flex sm:items-center sm:ml-6">
                        <x-user-avatar-dropdown
                            dropdown-id="appLayoutAvatarDropdown"
                            link-class="p-0"
                            img-class="rounded-circle"
                        />
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>
</html> 