@php
    $user = $user ?? auth()->user();
    $isAgent = $user->hasRole('alumni-agent');
    $layout = $isAgent ? 'layouts.agent' : 'layouts.app';
@endphp

@extends($layout)

@section('content')
    @include('profile.partials.profile-content', [
        'isDefaultLayout' => ! $isAgent,
    ])
@endsection
