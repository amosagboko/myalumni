@php
    $adminSurface = $adminSurface ?? false;
    $hideHeader = $hideHeader ?? false;
    $labelClass = $adminSurface ? 'form-label small text-muted mb-1' : 'block font-medium text-sm text-gray-700';
    $inputClass = $adminSurface ? 'form-control form-control-sm' : 'form-control mt-1 block w-full';
    $errorClass = $adminSurface ? 'text-danger small mt-1' : 'text-sm text-red-600 mt-2';
    $buttonClass = $adminSurface ? 'btn btn-sm ads-btn-primary' : 'btn btn-primary';
    $successClass = $adminSurface ? 'text-success small mb-0' : 'text-sm text-success';
@endphp

<section>
    @unless ($hideHeader)
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                {{ __('Update Password') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                {{ __('Ensure your account is using a long, random password to stay secure.') }}
            </p>
        </header>
    @endunless

    <form method="post" action="{{ route('password.update') }}" class="{{ $hideHeader ? '' : 'mt-6 space-y-6' }}">
        @csrf
        @method('put')

        <div class="mb-3">
            <label for="update_password_current_password" class="{{ $labelClass }}">
                {{ __('Current Password') }}
            </label>
            <input
                id="update_password_current_password"
                name="current_password"
                type="password"
                class="{{ $inputClass }}"
                autocomplete="current-password"
            >
            @if ($errors->updatePassword->has('current_password'))
                <div class="{{ $errorClass }}">
                    {{ $errors->updatePassword->get('current_password')[0] }}
                </div>
            @endif
        </div>

        <div class="mb-3">
            <label for="update_password_password" class="{{ $labelClass }}">
                {{ __('New Password') }}
            </label>
            <input
                id="update_password_password"
                name="password"
                type="password"
                class="{{ $inputClass }}"
                autocomplete="new-password"
            >
            @if ($errors->updatePassword->has('password'))
                <div class="{{ $errorClass }}">
                    {{ $errors->updatePassword->get('password')[0] }}
                </div>
            @endif
        </div>

        <div class="mb-3">
            <label for="update_password_password_confirmation" class="{{ $labelClass }}">
                {{ __('Confirm Password') }}
            </label>
            <input
                id="update_password_password_confirmation"
                name="password_confirmation"
                type="password"
                class="{{ $inputClass }}"
                autocomplete="new-password"
            >
            @if ($errors->updatePassword->has('password_confirmation'))
                <div class="{{ $errorClass }}">
                    {{ $errors->updatePassword->get('password_confirmation')[0] }}
                </div>
            @endif
        </div>

        <div class="d-flex align-items-center gap-3">
            <button type="submit" class="{{ $buttonClass }}">
                {{ __('Save') }}
            </button>

            @if (session('status') === 'password-updated')
                <p class="{{ $successClass }}">
                    {{ __('Saved.') }}
                </p>
            @endif
        </div>
    </form>
</section>
