<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}" class="d-none">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-4">
        @csrf
        @method('patch')

        <div class="form-group mb-3">
            <label for="name" class="mont-font fw-600 font-xsss">Name</label>
            <input type="text" id="name" name="name" class="form-control" 
                value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
            @error('name')
                <div class="text-danger font-xssss mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group mb-3">
            <label for="email" class="mont-font fw-600 font-xsss">Email</label>
            <input type="email" id="email" name="email" class="form-control" 
                value="{{ old('email', $user->email) }}" required autocomplete="username">
            @error('email')
                <div class="text-danger font-xssss mt-1">{{ $message }}</div>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2">
                    <p class="text-grey-500 font-xssss">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="text-primary font-xssss border-0 bg-transparent">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="text-success font-xssss mt-2">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="d-flex align-items-center gap-3">
            <button type="submit" class="btn btn-primary btn-sm">
                {{ __('Save') }}
            </button>

            @if (session('status') === 'profile-updated')
                <p class="text-success font-xssss mb-0">
                    {{ __('Saved.') }}
                </p>
            @endif
        </div>
    </form>
</section>
