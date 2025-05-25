@php
    $user = auth()->user();
    $isAdmin = $user->hasRole('administrator');
    $isARO = $user->hasRole('alumni-relations-officer');
    $isAlumni = $user->hasRole('alumni');
    $isAgent = $user->hasRole('alumni-agent');
    $isElcomChairman = $user->hasRole('elcom-chairman');
    
    // Debug information
    $roles = $user->getRoleNames();
    $currentRole = $roles->first();

    // Determine which layout to use
    if ($isAdmin) {
        $layout = 'layouts.admin-profile';
    } elseif ($isARO) {
        $layout = 'components.layouts.alumni-relations-officer';
    } elseif ($isElcomChairman) {
        $layout = 'components.layouts.elcom-chairman';
    } elseif ($isAlumni) {
        $layout = 'layouts.alumni';
    } elseif ($isAgent) {
        $layout = 'layouts.agent';
    } else {
        $layout = 'layouts.app';
    }
@endphp

{{-- Debug output - will be removed after testing --}}
@if(config('app.debug'))
    <div style="display: none;">
        Debug Info:
        User ID: {{ $user->id }}
        User Name: {{ $user->name }}
        User Roles: {{ $roles->implode(', ') }}
        Current Role: {{ $currentRole }}
        isAdmin: {{ $isAdmin ? 'true' : 'false' }}
        isARO: {{ $isARO ? 'true' : 'false' }}
        isAlumni: {{ $isAlumni ? 'true' : 'false' }}
        isAgent: {{ $isAgent ? 'true' : 'false' }}
        isElcomChairman: {{ $isElcomChairman ? 'true' : 'false' }}
    </div>
@endif

@if($isARO)
    <x-layouts.alumni-relations-officer>
        @include('profile.partials.profile-content', ['isDefaultLayout' => false])
    </x-layouts.alumni-relations-officer>
@elseif($isElcomChairman)
    <x-layouts.elcom-chairman>
        @include('profile.partials.profile-content', ['isDefaultLayout' => false])
    </x-layouts.elcom-chairman>
@else
    @extends($layout)
    @section('content')
        @include('profile.partials.profile-content', ['isDefaultLayout' => !$isAdmin && !$isARO && !$isElcomChairman && !$isAlumni && !$isAgent])
    @endsection
@endif
