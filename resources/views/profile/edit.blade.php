@php
    $user = auth()->user();
    $isARO = $user->hasRole('alumni-relations-officer');
    $isAgent = $user->hasRole('alumni-agent');
    $isElcomChairman = $user->hasRole('elcom-chairman');

    $layout = $isAgent ? 'layouts.agent' : 'layouts.app';
@endphp

@if ($isARO)
    <x-layouts.alumni-relations-officer>
        @include('profile.partials.profile-content', ['isDefaultLayout' => false])
    </x-layouts.alumni-relations-officer>
@elseif ($isElcomChairman)
    <x-layouts.elcom-chairman>
        @include('profile.partials.profile-content', ['isDefaultLayout' => false])
    </x-layouts.elcom-chairman>
@else
    @extends($layout)
    @section('content')
        @include('profile.partials.profile-content', [
            'isDefaultLayout' => ! $isAgent,
        ])
    @endsection
@endif
