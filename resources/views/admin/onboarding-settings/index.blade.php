@extends('layouts.admin')

@section('content')
<div class="container-fluid mt-3 mt-md-5 pt-3 pt-md-7 px-3 px-md-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h3 class="card-title mb-0 h4 h-md-3">Onboarding Settings</h3>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Current Status -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Current Status</h5>
                                    @if($setting->is_onboarding_enabled)
                                        <div class="text-success">
                                            <i class="bi bi-check-circle-fill fs-1"></i>
                                            <p class="mt-2 mb-0"><strong>Onboarding is OPEN</strong></p>
                                            <small class="text-muted">Alumni can register and complete onboarding</small>
                                        </div>
                                    @else
                                        <div class="text-danger">
                                            <i class="bi bi-x-circle-fill fs-1"></i>
                                            <p class="mt-2 mb-0"><strong>Onboarding is CLOSED</strong></p>
                                            <small class="text-muted">Alumni cannot register or complete onboarding</small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">Status History</h5>
                                    @if($setting->closed_at)
                                        <div class="mb-2">
                                            <small class="text-muted">Closed on:</small><br>
                                            <strong>{{ $setting->closed_at->format('M d, Y \a\t g:i A') }}</strong>
                                            @if($setting->closedBy)
                                                <br><small>by {{ $setting->closedBy->name }}</small>
                                            @endif
                                        </div>
                                    @endif

                                    @if($setting->reopened_at)
                                        <div class="mb-2">
                                            <small class="text-muted">Reopened on:</small><br>
                                            <strong>{{ $setting->reopened_at->format('M d, Y \a\t g:i A') }}</strong>
                                            @if($setting->reopenedBy)
                                                <br><small>by {{ $setting->reopenedBy->name }}</small>
                                            @endif
                                        </div>
                                    @endif

                                    @if($setting->closure_reason)
                                        <div>
                                            <small class="text-muted">Reason for closure:</small><br>
                                            <em>"{{ $setting->closure_reason }}"</em>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="row">
                        <div class="col-12">
                            @if($setting->is_onboarding_enabled)
                                <!-- Close Onboarding Form -->
                                <div class="card border-warning">
                                    <div class="card-header bg-warning text-dark">
                                        <h5 class="card-title mb-0">
                                            <i class="bi bi-exclamation-triangle me-2"></i>
                                            Close Onboarding
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted mb-3">
                                            Closing onboarding will prevent new alumni from registering and existing alumni from completing their onboarding process. 
                                            This is typically done during elections or maintenance periods.
                                        </p>
                                        
                                        <form action="{{ route('admin.onboarding-settings.close') }}" method="POST">
                                            @csrf
                                            <div class="mb-3">
                                                <label for="closure_reason" class="form-label">Reason for Closure <span class="text-danger">*</span></label>
                                                <textarea 
                                                    class="form-control @error('closure_reason') is-invalid @enderror" 
                                                    id="closure_reason" 
                                                    name="closure_reason" 
                                                    rows="3" 
                                                    placeholder="e.g., Onboarding temporarily closed during election period"
                                                    required
                                                >{{ old('closure_reason') }}</textarea>
                                                @error('closure_reason')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <button type="submit" class="btn btn-warning" 
                                                    onclick="return confirm('Are you sure you want to close onboarding? This will prevent alumni from registering and completing their profiles.')">
                                                <i class="bi bi-lock me-2"></i>
                                                Close Onboarding
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @else
                                <!-- Reopen Onboarding -->
                                <div class="card border-success">
                                    <div class="card-header bg-success text-white">
                                        <h5 class="card-title mb-0">
                                            <i class="bi bi-unlock me-2"></i>
                                            Reopen Onboarding
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted mb-3">
                                            Reopening onboarding will allow alumni to register and complete their onboarding process again.
                                        </p>
                                        
                                        <form action="{{ route('admin.onboarding-settings.reopen') }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-success" 
                                                    onclick="return confirm('Are you sure you want to reopen onboarding? Alumni will be able to register and complete their profiles again.')">
                                                <i class="bi bi-unlock me-2"></i>
                                                Reopen Onboarding
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Information Panel -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h6><i class="bi bi-info-circle me-2"></i>Important Information</h6>
                                <ul class="mb-0">
                                    <li>Closing onboarding affects <strong>all alumni categories</strong> regardless of graduation year</li>
                                    <li>Existing users who have already completed onboarding can still access the platform</li>
                                    <li>Users in the middle of onboarding will be blocked from completing their profiles</li>
                                    <li>This setting is typically used during elections or system maintenance</li>
                                    <li>All actions are logged for audit purposes</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 