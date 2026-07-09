@extends('layouts.alumni')

@section('feed_layout')
@endsection

@section('content')
<div class="row feed-body">
    <div class="col-xl-8 col-xxl-9 col-lg-8">
        @include('alumni.partials.feed-announcements-strip')

        <livewire:social.post-composer />

        <livewire:social.feed />
    </div>

    @include('alumni.partials.feed-right-sidebar')
</div>

@include('alumni.partials.onboarding-modal')

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('onboardingModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                e.preventDefault();
            }
        });
    }
});
</script>
@endpush
@endsection
