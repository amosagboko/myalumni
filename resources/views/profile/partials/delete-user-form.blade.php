<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Delete Account') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
        @csrf
        @method('delete')

        <div class="mt-6">
            <label for="password" class="block font-medium text-sm text-gray-700 sr-only">
                {{ __('Password') }}
            </label>

            <input
                id="password"
                name="password"
                type="password"
                class="form-control mt-1 block w-3/4"
                placeholder="{{ __('Password') }}"
            />

            @if ($errors->userDeletion->has('password'))
                <div class="text-sm text-red-600 mt-2">
                    {{ $errors->userDeletion->get('password')[0] }}
                </div>
            @endif
        </div>

        <div class="mt-6 flex justify-end">
            <button type="submit" class="btn btn-danger">
                {{ __('Delete Account') }}
            </button>
        </div>
    </form>
</section>
