<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title ?? 'Student Affairs' }}</title>
    @if(file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
        <script src="{{ asset('js/app.js') }}" defer></script>
    @endif
</head>
<body>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Student Affairs</h5>
        <nav class="d-flex gap-2">
            <a href="{{ route('student-affairs.clearance') }}" class="btn btn-sm btn-primary">Go to Clearance</a>
            <a href="{{ route('student-affairs.audit') }}" class="btn btn-sm btn-outline-secondary">Clearance Audit</a>
            <a href="{{ route('student-affairs.home') }}" class="btn btn-sm btn-outline-primary">Recent Activity</a>
        </nav>
    </div>
    {{ $slot }}
</div>
</body>
</html>

