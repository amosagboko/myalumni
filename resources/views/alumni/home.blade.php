@extends('layouts.alumni')

@section('content')
                <!-- Status Badges Row -->
                @php
                    $alumni = Auth::user()->alumni;
                    $needsBioData = !$alumni || !$alumni->contact_address || !$alumni->phone_number || !$alumni->qualification_type;
                    $activeFees = $alumni ? $alumni->getActiveFees() : collect([]);
                    $unpaidFees = $activeFees->filter(function($fee) { return !$fee->isPaid(); });
                    $needsPayments = $alumni && $activeFees->isNotEmpty() && $unpaidFees->isNotEmpty();
                    
                    // Only show clearance statuses for alumni graduating 2025 or later
                    $yearOfGraduation = $alumni->year_of_graduation ?? null;
                    $requiresClearance = $yearOfGraduation && $yearOfGraduation >= 2025;
                    
                    $studentCleared = $requiresClearance ? (bool) ($alumni->student_affairs_cleared ?? false) : null;
                    $academicCleared = $requiresClearance ? (bool) ($alumni->academic_affairs_cleared ?? false) : null;
                    $overallCleared = $requiresClearance ? ($studentCleared && $academicCleared) : null;
                @endphp
                <div class="card w-100 border-0 bg-white shadow-xs p-0 mb-3">
                    <div class="card-body p-3">
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <span class="badge {{ $needsBioData ? 'bg-danger' : 'bg-success' }}">Onboarding: {{ $needsBioData ? 'Pending ✖' : 'Completed ✔' }}</span>
                            <span class="badge {{ $needsPayments ? 'bg-danger' : 'bg-success' }}">Payments: {{ $needsPayments ? 'Pending ✖' : 'Completed ✔' }}</span>
                            @if($requiresClearance)
                                <span class="badge {{ $studentCleared ? 'bg-success' : 'bg-danger' }}">Student Affairs: {{ $studentCleared ? 'Cleared ✔' : 'Not Cleared ✖' }}</span>
                                <span class="badge {{ $academicCleared ? 'bg-success' : 'bg-danger' }}">Academic Affairs: {{ $academicCleared ? 'Cleared ✔' : 'Not Cleared ✖' }}</span>
                                <span class="badge {{ $overallCleared ? 'bg-success' : 'bg-warning' }}">Overall: {{ $overallCleared ? 'Cleared' : 'Pending' }}</span>
                                <a href="{{ route('alumni.clearance-status') }}" class="btn btn-sm btn-outline-primary ms-auto">View Clearance Status</a>
                            @endif
                        </div>
                    </div>
                </div>

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

<!-- Onboarding Modal -->
@if(Auth::user()->hasRole('alumni'))
    @php
        $alumni = Auth::user()->alumni;
        $needsBioData = !$alumni || !$alumni->contact_address || !$alumni->phone_number || !$alumni->qualification_type;
        
        // Debug logging for payments
        $activeFees = $alumni ? $alumni->getActiveFees() : collect([]);
        $unpaidFees = $activeFees->filter(function($fee) {
            return !$fee->isPaid();
        });
        $needsPayments = $alumni && $activeFees->isNotEmpty() && $unpaidFees->isNotEmpty();
        
        \Illuminate\Support\Facades\Log::info('Alumni payment check', [
            'alumni_id' => $alumni?->id,
            'has_active_fees' => $activeFees->isNotEmpty(),
            'unpaid_fees_count' => $unpaidFees->count(),
            'needs_payments' => $needsPayments,
            'graduation_year' => $alumni?->year_of_graduation
        ]);
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
                            <p>You have the following pending payments that need to be completed:</p>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Fee Type</th>
                                            <th>Amount</th>
                                            <th>Due Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($alumni->getActiveFees() as $fee)
                                            @if(!$fee->isPaid())
                                                <tr>
                                                    <td>{{ $fee->feeType->name }}</td>
                                                    <td>₦{{ number_format($fee->amount, 2) }}</td>
                                                    <td>{{ $fee->alumniYear?->end_date?->format('M d, Y') ?? 'N/A' }}</td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                <a href="{{ route('alumni.payments.index') }}" class="btn btn-primary">View and Pay Fees</a>
                            </div>
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