<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Update Password') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="block font-medium text-sm text-gray-700">
                {{ __('Current Password') }}
            </label>
            <input 
                id="update_password_current_password" 
                name="current_password" 
                type="password" 
                class="form-control mt-1 block w-full" 
                autocomplete="current-password" 
            />
            @if ($errors->updatePassword->has('current_password'))
                <div class="text-sm text-red-600 mt-2">
                    {{ $errors->updatePassword->get('current_password')[0] }}
                </div>
            @endif
        </div>

        <div>
            <label for="update_password_password" class="block font-medium text-sm text-gray-700">
                {{ __('New Password') }}
            </label>
            <input 
                id="update_password_password" 
                name="password" 
                type="password" 
                class="form-control mt-1 block w-full" 
                autocomplete="new-password" 
            />
            @if ($errors->updatePassword->has('password'))
                <div class="text-sm text-red-600 mt-2">
                    {{ $errors->updatePassword->get('password')[0] }}
                </div>
            @endif
        </div>

        <div>
            <label for="update_password_password_confirmation" class="block font-medium text-sm text-gray-700">
                {{ __('Confirm Password') }}
            </label>
            <input 
                id="update_password_password_confirmation" 
                name="password_confirmation" 
                type="password" 
                class="form-control mt-1 block w-full" 
                autocomplete="new-password" 
            />
            @if ($errors->updatePassword->has('password_confirmation'))
                <div class="text-sm text-red-600 mt-2">
                    {{ $errors->updatePassword->get('password_confirmation')[0] }}
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="btn btn-primary">
                {{ __('Save') }}
            </button>

            @if (session('status') === 'password-updated')
                <p class="text-sm text-success">
                    {{ __('Saved.') }}
                </p>
            @endif
        </div>
    </form>
</section>
