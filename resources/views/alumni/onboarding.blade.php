@extends('layouts.alumni')

@section('content')
<div class="container mt-5 pt-5" style="margin-left: 150px; margin-top: 100px !important;">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h3 class="card-title">Complete Your Profile</h3>
                </div>
                <div class="card-body">
                    <div class="mb-4 text-muted">
                        Welcome to the Alumni Portal! Please complete your profile by updating your email address and setting a password.
                    </div>

                    <form method="POST" action="{{ route('alumni.onboarding.update') }}">
                        @csrf

                        <!-- Email Address -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                id="email" name="email" value="{{ old('email') }}" required autofocus>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Current Password -->
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control @error('current_password') is-invalid @enderror" 
                                id="current_password" name="current_password" required>
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- New Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                id="password" name="password" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" 
                                id="password_confirmation" name="password_confirmation" required>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 