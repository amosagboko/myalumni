@extends('layouts.alumni')

@section('content')
<div class="main-content bg-lightblue theme-dark-bg right-chat-active">
    <div class="middle-sidebar-bottom">
        <div class="middle-sidebar-left">
            <div class="middle-wrap">
                <!-- Welcome Section -->
                <div class="card w-100 border-0 bg-white shadow-xs p-0 mb-4">
                    <div class="card-body p-4 w-100 bg-current border-0 d-flex rounded-3">
                        <h4 class="font-xs text-white fw-600 mb-0">Welcome, {{ Auth::user()->name }}</h4>
                    </div>
                    <div class="card-body p-lg-5 p-4 w-100 border-0">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">Profile</h5>
                                        <p class="card-text">Update your profile information and preferences.</p>
                                        <a href="{{ route('profile.update') }}" class="btn btn-primary">View Profile</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">Friends</h5>
                                        <p class="card-text">Connect with other alumni and manage your connections.</p>
                                        <a href="{{ route('friends') }}" class="btn btn-primary">View Friends</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Create Post Section -->
                <div class="card w-100 border-0 bg-white shadow-xs p-0 mb-4">
                    <div class="card-body p-4 w-100 bg-current border-0 d-flex rounded-3">
                        <h4 class="font-xs text-white fw-600 mb-0">Create Post</h4>
                    </div>
                    <div class="card-body p-lg-5 p-4 w-100 border-0">
                        @livewire('components.create-post')
                    </div>
                </div>

                <!-- Posts Feed Section -->
                <div class="card w-100 border-0 bg-white shadow-xs p-0">
                    <div class="card-body p-4 w-100 bg-current border-0 d-flex rounded-3">
                        <h4 class="font-xs text-white fw-600 mb-0">Recent Posts</h4>
                    </div>
                    <div class="card-body p-lg-5 p-4 w-100 border-0">
                        <livewire:returnpost />
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Onboarding Modal -->
@if(Auth::user()->hasRole('alumni'))
    @php
        $alumni = Auth::user()->alumni;
        $needsBioData = !$alumni || !$alumni->contact_address || !$alumni->phone_number || !$alumni->qualification_type;
        $needsPayments = $alumni && $alumni->getActiveFees()->isNotEmpty() && $alumni->getActiveFees()->contains(function($fee) {
            return !$fee->isPaid();
        });
    @endphp

    @if($needsBioData || $needsPayments)
    <div class="modal fade show" id="onboardingModal" tabindex="-1" role="dialog" style="display: block; background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Complete Your Profile</h5>
                </div>
                <div class="modal-body">
                    @if($needsBioData)
                        <div class="mb-4">
                            <h6>Bio Data Required</h6>
                            <p>Please complete your bio data to continue using the platform.</p>
                            <a href="{{ route('alumni.bio-data') }}" class="btn btn-primary">Complete Bio Data</a>
                        </div>
                    @endif

                    @if($needsPayments)
                        <div class="mb-4">
                            <h6>Pending Payments</h6>
                            <p>You have pending payments that need to be completed.</p>
                            <a href="{{ route('alumni.payments.index') }}" class="btn btn-primary">View Payments</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Prevent closing the modal by clicking outside
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