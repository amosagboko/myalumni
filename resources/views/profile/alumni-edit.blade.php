@extends('layouts.alumni')

@section('title', 'My Profile | FuLafia Alumni')

@section('content')
<div class="alumni-profile-page w-100">
    <div class="alumni-profile-shell mx-auto">

        <div class="card border-0 bg-white shadow-xs mb-3">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                    <div>
                        <h4 class="fw-600 mb-1">My profile</h4>
                        <p class="text-grey-500 font-xssss mb-0">Manage your account details, password, and profile photo.</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('alumni.home') }}" class="btn btn-outline-secondary btn-sm">
                            <i data-feather="arrow-left" style="width: 14px; height: 14px;"></i>
                            Dashboard
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary btn-sm">
                                <i data-feather="log-out" style="width: 14px; height: 14px;"></i>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success py-2 small mb-3">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger py-2 small mb-3">{{ session('error') }}</div>
        @endif

        @if (session('status') === 'profile-updated')
            <div class="alert alert-success py-2 small mb-3">Profile information saved.</div>
        @endif

        @if (session('status') === 'password-updated')
            <div class="alert alert-success py-2 small mb-3">Password updated.</div>
        @endif

        <div class="card border-0 bg-white shadow-xs mb-3">
            <div class="card-body p-3 p-md-4">
                <h5 class="fw-600 font-sm mb-1">Profile information</h5>
                <p class="text-grey-500 font-xssss mb-3">Update your account name and email address.</p>

                <form id="send-verification" method="post" action="{{ route('verification.send') }}" class="d-none">
                    @csrf
                </form>

                <form method="post" action="{{ route('profile.update') }}">
                    @csrf
                    @method('patch')

                    <div class="mb-3">
                        <label for="name" class="form-label small mb-1">Name</label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            class="form-control form-control-sm @error('name') is-invalid @enderror"
                            value="{{ old('name', $user->name) }}"
                            required
                            autofocus
                            autocomplete="name"
                        >
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label small mb-1">Email</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control form-control-sm @error('email') is-invalid @enderror"
                            value="{{ old('email', $user->email) }}"
                            required
                            autocomplete="username"
                        >
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                            <div class="form-text font-xssss">
                                Your email address is unverified.
                                <button form="send-verification" class="btn btn-link btn-sm p-0 align-baseline">
                                    Resend verification email
                                </button>
                            </div>

                            @if (session('status') === 'verification-link-sent')
                                <div class="text-success font-xssss mt-1">A new verification link has been sent.</div>
                            @endif
                        @endif
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm">
                        <i data-feather="save" style="width: 14px; height: 14px;"></i>
                        Save changes
                    </button>
                </form>
            </div>
        </div>

        <div class="card border-0 bg-white shadow-xs mb-3">
            <div class="card-body p-3 p-md-4">
                <h5 class="fw-600 font-sm mb-1">Update password</h5>
                <p class="text-grey-500 font-xssss mb-3">Use a long, random password to keep your account secure.</p>

                <form method="post" action="{{ route('password.update') }}">
                    @csrf
                    @method('put')

                    <div class="mb-3">
                        <label for="update_password_current_password" class="form-label small mb-1">Current password</label>
                        <input
                            id="update_password_current_password"
                            name="current_password"
                            type="password"
                            class="form-control form-control-sm @if ($errors->updatePassword->has('current_password')) is-invalid @endif"
                            autocomplete="current-password"
                        >
                        @if ($errors->updatePassword->has('current_password'))
                            <div class="invalid-feedback">{{ $errors->updatePassword->get('current_password')[0] }}</div>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label for="update_password_password" class="form-label small mb-1">New password</label>
                        <input
                            id="update_password_password"
                            name="password"
                            type="password"
                            class="form-control form-control-sm @if ($errors->updatePassword->has('password')) is-invalid @endif"
                            autocomplete="new-password"
                        >
                        @if ($errors->updatePassword->has('password'))
                            <div class="invalid-feedback">{{ $errors->updatePassword->get('password')[0] }}</div>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label for="update_password_password_confirmation" class="form-label small mb-1">Confirm password</label>
                        <input
                            id="update_password_password_confirmation"
                            name="password_confirmation"
                            type="password"
                            class="form-control form-control-sm @if ($errors->updatePassword->has('password_confirmation')) is-invalid @endif"
                            autocomplete="new-password"
                        >
                        @if ($errors->updatePassword->has('password_confirmation'))
                            <div class="invalid-feedback">{{ $errors->updatePassword->get('password_confirmation')[0] }}</div>
                        @endif
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm">
                        <i data-feather="save" style="width: 14px; height: 14px;"></i>
                        Update password
                    </button>
                </form>
            </div>
        </div>

        <div class="card border-0 bg-white shadow-xs mb-3">
            <div class="card-body p-3 p-md-4">
                <h5 class="fw-600 font-sm mb-1">Profile photo</h5>
                <p class="text-grey-500 font-xssss mb-3">Upload a photo for your alumni account.</p>

                <form method="POST" action="{{ route('profile.avatar') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label for="avatar" class="form-label small mb-1">Profile photo</label>
                        <input
                            type="file"
                            id="avatar"
                            name="avatar"
                            class="form-control form-control-sm"
                            accept="image/*"
                            required
                        >
                    </div>

                    @if ($user->avatar)
                        <div class="mb-3">
                            <div class="text-grey-500 font-xssss mb-1">Current photo</div>
                            <img
                                src="{{ asset('storage/' . $user->avatar) }}"
                                alt="{{ $user->name }}"
                                class="rounded-circle border"
                                style="width: 88px; height: 88px; object-fit: cover;"
                            >
                        </div>
                    @endif

                    <button type="submit" class="btn btn-primary btn-sm">
                        <i data-feather="upload" style="width: 14px; height: 14px;"></i>
                        Upload photo
                    </button>
                </form>
            </div>
        </div>

        <div class="card border-0 bg-white shadow-xs mb-3">
            <div class="card-body p-3 p-md-4">
                <h5 class="fw-600 font-sm mb-1">Delete account</h5>
                <p class="text-grey-500 font-xssss mb-3">
                    Once your account is deleted, all of its resources and data will be permanently removed.
                </p>

                <form method="post" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('delete')

                    <div class="mb-3">
                        <label for="password" class="form-label small mb-1">Confirm with password</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            class="form-control form-control-sm @if ($errors->userDeletion->has('password')) is-invalid @endif"
                            placeholder="Enter your password to confirm"
                        >
                        @if ($errors->userDeletion->has('password'))
                            <div class="invalid-feedback">{{ $errors->userDeletion->get('password')[0] }}</div>
                        @endif
                    </div>

                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        <i data-feather="trash-2" style="width: 14px; height: 14px;"></i>
                        Delete account
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection

@push('styles')
<style>
    .alumni-profile-page {
        width: 100%;
        min-width: 0;
    }

    .alumni-profile-shell {
        width: 100%;
        max-width: 520px;
        padding-bottom: 1.5rem;
    }

    .alumni-profile-shell .card-body .form-label {
        color: #6b7280;
    }

    .alumni-profile-shell .card {
        border-radius: 0.5rem;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    });
</script>
@endpush
