@php
    $adminSurface = $adminSurface ?? false;
    $hideHeader = $hideHeader ?? false;
    $labelClass = $adminSurface ? 'form-label small text-muted mb-1' : 'mont-font fw-600 font-xsss';
    $inputClass = $adminSurface ? 'form-control form-control-sm' : 'form-control';
    $errorClass = $adminSurface ? 'text-danger small mt-1' : 'text-danger font-xssss mt-1';
    $buttonClass = $adminSurface ? 'btn btn-sm ads-btn-primary' : 'btn btn-primary btn-sm';
    $metaClass = $adminSurface ? 'text-muted small' : 'text-grey-500 font-xssss';
    $successClass = $adminSurface ? 'text-success small mb-0' : 'text-success font-xssss mb-0';
@endphp

<section>
    @unless ($hideHeader)
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                {{ __('Profile Information') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                {{ __("Update your account's profile information and email address.") }}
            </p>
        </header>
    @endunless

    <form id="send-verification" method="post" action="{{ route('verification.send') }}" class="d-none">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="{{ $hideHeader ? '' : 'mt-4' }}">
        @csrf
        @method('patch')

        <div class="mb-3">
            <label for="name" class="{{ $labelClass }}">Name</label>
            <input
                type="text"
                id="name"
                name="name"
                class="{{ $inputClass }}"
                value="{{ old('name', $user->name) }}"
                required
                autofocus
                autocomplete="name"
            >
            @error('name')
                <div class="{{ $errorClass }}">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="{{ $labelClass }}">Email</label>
            <input
                type="email"
                id="email"
                name="email"
                class="{{ $inputClass }}"
                value="{{ old('email', $user->email) }}"
                required
                autocomplete="username"
            >
            @error('email')
                <div class="{{ $errorClass }}">{{ $message }}</div>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2">
                    <p class="{{ $metaClass }}">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="text-primary border-0 bg-transparent p-0 small">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="{{ $successClass }} mt-2">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="d-flex align-items-center gap-3">
            <button type="submit" class="{{ $buttonClass }}">
                {{ __('Save') }}
            </button>

            @if (session('status') === 'profile-updated')
                <p class="{{ $successClass }}">
                    {{ __('Saved.') }}
                </p>
            @endif
        </div>
    </form>
</section>
