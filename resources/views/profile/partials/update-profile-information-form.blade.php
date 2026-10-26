@php
    $adminSurface = $adminSurface ?? false;
    $hideHeader = $hideHeader ?? false;
    $compact = $compact ?? $adminSurface;
    $labelClass = $adminSurface ? 'form-label small text-muted mb-1' : ($compact ? 'form-label small mb-1' : 'mont-font fw-600 font-xsss');
    $inputClass = $compact ? 'form-control form-control-sm bg-light' : 'form-control bg-light';
    $metaClass = $adminSurface ? 'text-muted small' : ($compact ? 'text-grey-500 font-xssss' : 'text-grey-500 font-xssss');
    $successClass = $adminSurface ? 'text-success small mb-0' : 'text-success font-xssss mb-0';
@endphp

<section>
    @unless ($hideHeader)
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                {{ __('Profile Information') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                {{ __('Your name and email are shown below and cannot be changed here.') }}
            </p>
        </header>
    @endunless

    <form id="send-verification" method="post" action="{{ route('verification.send') }}" class="d-none">
        @csrf
    </form>

    <div class="{{ $hideHeader ? '' : 'mt-4' }}">
        <div class="mb-3">
            <label for="name" class="{{ $labelClass }}">Name</label>
            <input
                type="text"
                id="name"
                class="{{ $inputClass }}"
                value="{{ $user->name }}"
                readonly
                autocomplete="name"
            >
        </div>

        <div class="mb-3">
            <label for="email" class="{{ $labelClass }}">Email</label>
            <input
                type="email"
                id="email"
                class="{{ $inputClass }}"
                value="{{ $user->email }}"
                readonly
                autocomplete="username"
            >

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

        <p class="{{ $metaClass }} mb-0">Name and email cannot be edited from this page.</p>
    </div>
</section>
