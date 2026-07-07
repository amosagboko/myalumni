<x-alumniadmin-dashboard title="My Profile | FuLafia Alumni">
    <x-admin.surface-styles />

    <div class="main-content right-chat-active admin-surface admin-profile-page">
        <div class="middle-sidebar-bottom">
            <div class="middle-sidebar-left pe-0">
                <div class="row justify-content-center">
                    <div class="col-12 ads-profile-shell">

                        <div class="ads-page-header">
                            <div>
                                <h1 class="ads-page-title">My profile</h1>
                                <p class="ads-page-subtitle">Manage your account details, password, and profile photo.</p>
                            </div>
                            <div class="ads-page-actions d-flex flex-wrap gap-2">
                                <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-secondary">
                                    <i data-feather="arrow-left" style="width: 14px; height: 14px;"></i>
                                    Back to dashboard
                                </a>
                                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                                        <i data-feather="log-out" style="width: 14px; height: 14px;"></i>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>

                        @if (session('success'))
                            <div class="ads-alert ads-alert-success">{{ session('success') }}</div>
                        @endif

                        @if (session('error'))
                            <div class="ads-alert ads-alert-error">{{ session('error') }}</div>
                        @endif

                        @if (session('status') === 'profile-updated')
                            <div class="ads-alert ads-alert-success">Profile information saved.</div>
                        @endif

                        @if (session('status') === 'password-updated')
                            <div class="ads-alert ads-alert-success">Password updated.</div>
                        @endif

                        <div class="ads-section">
                            <div class="ads-section-card">
                                <h2 class="ads-section-title">Profile information</h2>
                                <p class="text-muted small mb-3">Update your account name and email address.</p>

                                <form id="send-verification" method="post" action="{{ route('verification.send') }}" class="d-none">
                                    @csrf
                                </form>

                                <form method="post" action="{{ route('profile.update') }}">
                                    @csrf
                                    @method('patch')

                                    <div class="mb-3">
                                        <label for="name" class="form-label">Name</label>
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
                                        <label for="email" class="form-label">Email</label>
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
                                            <div class="form-text">
                                                Your email address is unverified.
                                                <button form="send-verification" class="btn btn-link btn-sm p-0 align-baseline">
                                                    Resend verification email
                                                </button>
                                            </div>

                                            @if (session('status') === 'verification-link-sent')
                                                <div class="text-success small mt-1">A new verification link has been sent.</div>
                                            @endif
                                        @endif
                                    </div>

                                    <button type="submit" class="btn btn-sm ads-btn-primary">
                                        <i data-feather="save" style="width: 14px; height: 14px;"></i>
                                        Save changes
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="ads-section">
                            <div class="ads-section-card">
                                <h2 class="ads-section-title">Update password</h2>
                                <p class="text-muted small mb-3">Use a long, random password to keep your account secure.</p>

                                <form method="post" action="{{ route('password.update') }}">
                                    @csrf
                                    @method('put')

                                    <div class="mb-3">
                                        <label for="update_password_current_password" class="form-label">Current password</label>
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
                                        <label for="update_password_password" class="form-label">New password</label>
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
                                        <label for="update_password_password_confirmation" class="form-label">Confirm password</label>
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

                                    <button type="submit" class="btn btn-sm ads-btn-primary">
                                        <i data-feather="save" style="width: 14px; height: 14px;"></i>
                                        Update password
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="ads-section">
                            <div class="ads-section-card">
                                <h2 class="ads-section-title">Profile photo</h2>
                                <p class="text-muted small mb-3">Upload a photo for your admin account.</p>

                                <form method="POST" action="{{ route('profile.avatar') }}" enctype="multipart/form-data">
                                    @csrf

                                    <div class="mb-3">
                                        <label for="avatar" class="form-label">Profile photo</label>
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
                                            <div class="small text-muted mb-1">Current photo</div>
                                            <img
                                                src="{{ asset('storage/' . $user->avatar) }}"
                                                alt="{{ $user->name }}"
                                                class="rounded-circle border"
                                                style="width: 100px; height: 100px; object-fit: cover;"
                                            >
                                        </div>
                                    @endif

                                    <button type="submit" class="btn btn-sm ads-btn-primary">
                                        <i data-feather="upload" style="width: 14px; height: 14px;"></i>
                                        Upload photo
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="ads-section">
                            <div class="ads-section-card">
                                <h2 class="ads-section-title">Delete account</h2>
                                <p class="text-muted small mb-3">
                                    Once your account is deleted, all of its resources and data will be permanently removed.
                                </p>

                                <form method="post" action="{{ route('profile.destroy') }}">
                                    @csrf
                                    @method('delete')

                                    <div class="mb-3">
                                        <label for="password" class="form-label">Confirm with password</label>
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

                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i data-feather="trash-2" style="width: 14px; height: 14px;"></i>
                                        Delete account
                                    </button>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        });
    </script>
    @endpush
</x-alumniadmin-dashboard>
