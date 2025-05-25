<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

        <!-- Font Awesome -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

        <!-- Bootstrap Pagination Styles -->
        <style>
            .pagination {
                --bs-pagination-padding-x: 0.75rem;
                --bs-pagination-padding-y: 0.375rem;
                --bs-pagination-font-size: 1rem;
                --bs-pagination-color: var(--bs-link-color);
                --bs-pagination-bg: #fff;
                --bs-pagination-border-width: 1px;
                --bs-pagination-border-color: #dee2e6;
                --bs-pagination-border-radius: 0.375rem;
                --bs-pagination-hover-color: var(--bs-link-hover-color);
                --bs-pagination-hover-bg: #e9ecef;
                --bs-pagination-hover-border-color: #dee2e6;
                --bs-pagination-focus-color: var(--bs-link-hover-color);
                --bs-pagination-focus-bg: #e9ecef;
                --bs-pagination-focus-box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
                --bs-pagination-active-color: #fff;
                --bs-pagination-active-bg: #0d6efd;
                --bs-pagination-active-border-color: #0d6efd;
                --bs-pagination-disabled-color: #6c757d;
                --bs-pagination-disabled-bg: #fff;
                --bs-pagination-disabled-border-color: #dee2e6;
                display: flex;
                padding-left: 0;
                list-style: none;
                margin: 0;
            }
            .pagination .page-link {
                position: relative;
                display: block;
                padding: var(--bs-pagination-padding-y) var(--bs-pagination-padding-x);
                font-size: var(--bs-pagination-font-size);
                color: var(--bs-pagination-color);
                text-decoration: none;
                background-color: var(--bs-pagination-bg);
                border: var(--bs-pagination-border-width) solid var(--bs-pagination-border-color);
                transition: color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out;
            }
            .pagination .page-item.active .page-link {
                z-index: 3;
                color: var(--bs-pagination-active-color);
                background-color: var(--bs-pagination-active-bg);
                border-color: var(--bs-pagination-active-border-color);
            }
        </style>

        <!-- Lightbox CSS -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css" rel="stylesheet">
        
        <!-- jQuery and Lightbox JavaScript -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script>

        <!-- Bootstrap JavaScript -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

        <!-- Livewire Scripts -->
        @livewireStyles
        @livewireScripts

        <!-- Lightbox Configuration -->
        <script>
            // Prevent default comment modal
            document.addEventListener('DOMContentLoaded', function() {
                // Remove all modal-related attributes
                document.querySelectorAll('[data-modal], [data-toggle], [data-target], [data-bs-toggle], [data-bs-target]').forEach(function(el) {
                    el.removeAttribute('data-modal');
                    el.removeAttribute('data-toggle');
                    el.removeAttribute('data-target');
                    el.removeAttribute('data-bs-toggle');
                    el.removeAttribute('data-bs-target');
                });

                // Prevent modal from being shown
                document.addEventListener('click', function(e) {
                    if (e.target.closest('[data-toggle="modal"]') || 
                        e.target.closest('[data-target^="#Modal"]') || 
                        e.target.closest('[data-bs-toggle="modal"]') || 
                        e.target.closest('[data-bs-target^="#Modal"]')) {
                        e.preventDefault();
                        e.stopPropagation();
                        e.stopImmediatePropagation();
                    }
                }, true);

                // Override Bootstrap modal
                if (typeof bootstrap !== 'undefined') {
                    const Modal = bootstrap.Modal;
                    const originalModal = Modal.prototype.show;
                    Modal.prototype.show = function() {
                        // Do nothing
                        return false;
                    };
                }

                // Initialize lightbox with custom options
                lightbox.option({
                    'resizeDuration': 200,
                    'wrapAround': true,
                    'albumLabel': 'Image %1 of %2',
                    'fadeDuration': 300,
                    'imageFadeDuration': 300,
                    'positionFromTop': 50,
                    'maxWidth': 800,
                    'maxHeight': 600,
                    'disableScrolling': true,
                    'alwaysShowNavOnTouchDevices': false,
                    'onStart': function() {
                        // Prevent any modal from showing
                        document.querySelectorAll('.modal').forEach(function(modal) {
                            modal.style.display = 'none';
                            modal.classList.remove('show');
                            modal.setAttribute('aria-hidden', 'true');
                            modal.removeAttribute('aria-modal');
                            modal.removeAttribute('role');
                        });
                        // Remove any modal backdrops
                        document.querySelectorAll('.modal-backdrop').forEach(function(backdrop) {
                            backdrop.remove();
                        });
                    }
                });

                // Override lightbox click handler
                const originalLightboxClick = lightbox.init;
                lightbox.init = function() {
                    // Remove any existing click handlers
                    document.querySelectorAll('[data-lightbox]').forEach(function(el) {
                        el.removeEventListener('click', lightbox.start);
                    });

                    // Add our custom click handler
                    document.querySelectorAll('[data-lightbox]').forEach(function(el) {
                        el.addEventListener('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            e.stopImmediatePropagation();
                            lightbox.start(this);
                        });
                    });
                };

                // Initialize lightbox
                lightbox.init();
            });
        </script>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                @yield('content')
            </main>
        </div>
    </body>
</html>
