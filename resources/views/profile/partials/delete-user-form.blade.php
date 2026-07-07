@php
    $adminSurface = $adminSurface ?? false;
    $hideHeader = $hideHeader ?? false;
    $labelClass = $adminSurface ? 'form-label small text-muted mb-1' : 'block font-medium text-sm text-gray-700 sr-only';
    $inputClass = $adminSurface ? 'form-control form-control-sm' : 'form-control mt-1 block w-3/4';
    $errorClass = $adminSurface ? 'text-danger small mt-1' : 'text-sm text-red-600 mt-2';
    $buttonClass = $adminSurface ? 'btn btn-sm btn-outline-danger' : 'btn btn-danger';
@endphp

<section>
    @unless ($hideHeader)
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                {{ __('Delete Account') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
            </p>
        </header>
    @endunless

    <form method="post" action="{{ route('profile.destroy') }}" class="{{ $hideHeader ? '' : 'p-6' }}">
        @csrf
        @method('delete')

        <div class="mb-3" style="{{ $adminSurface ? 'max-width: 320px;' : '' }}">
            <label for="password" class="{{ $labelClass }}">
                {{ __('Password') }}
            </label>
            <input
                id="password"
                name="password"
                type="password"
                class="{{ $inputClass }}"
                placeholder="{{ __('Enter your password to confirm') }}"
            >

            @if ($errors->userDeletion->has('password'))
                <div class="{{ $errorClass }}">
                    {{ $errors->userDeletion->get('password')[0] }}
                </div>
            @endif
        </div>

        <div class="{{ $hideHeader ? '' : 'mt-6 flex justify-end' }}">
            <button type="submit" class="{{ $buttonClass }}">
                {{ __('Delete Account') }}
            </button>
        </div>
    </form>
</section>
