@if(isset($isDefaultLayout) && $isDefaultLayout)
    {{-- Default layout content --}}
    <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
        <div class="max-w-xl">
            @include('profile.partials.update-profile-information-form')
        </div>
    </div>

    <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
        <div class="max-w-xl">
            @include('profile.partials.update-password-form')
        </div>
    </div>

    <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
        <div class="max-w-xl">
            @include('profile.partials.avatar-form')
        </div>
    </div>

    <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
        <div class="max-w-xl">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
@else
    {{-- Custom layout content --}}
    <div class="main-content bg-lightblue theme-dark-bg right-chat-active">
        <div class="middle-sidebar-bottom">
            <div class="middle-sidebar-left">
                <div class="middle-wrap">
                    <div class="card w-100 border-0 bg-white shadow-xs p-0 mb-4">
                        <div class="card-body p-4">
                            <h2 class="fw-700 mb-0 mt-0 font-md text-grey-900">Profile Information</h2>
                            <p class="fw-500 font-xssss text-grey-500 mt-0 mb-3">Update your account's profile information and email address.</p>
                            @include('profile.partials.update-profile-information-form')
                        </div>
                    </div>

                    <div class="card w-100 border-0 bg-white shadow-xs p-0 mb-4">
                        <div class="card-body p-4">
                            <h2 class="fw-700 mb-0 mt-0 font-md text-grey-900">Update Password</h2>
                            <p class="fw-500 font-xssss text-grey-500 mt-0 mb-3">Ensure your account is using a long, random password to stay secure.</p>
                            @include('profile.partials.update-password-form')
                        </div>
                    </div>

                    <div class="card w-100 border-0 bg-white shadow-xs p-0 mb-4">
                        <div class="card-body p-4">
                            <h2 class="fw-700 mb-0 mt-0 font-md text-grey-900">Profile Photo</h2>
                            <p class="fw-500 font-xssss text-grey-500 mt-0 mb-3">Update your profile photo.</p>
                            @include('profile.partials.avatar-form')
                        </div>
                    </div>

                    <div class="card w-100 border-0 bg-white shadow-xs p-0 mb-4">
                        <div class="card-body p-4">
                            <h2 class="fw-700 mb-0 mt-0 font-md text-grey-900">Delete Account</h2>
                            <p class="fw-500 font-xssss text-grey-500 mt-0 mb-3">Once your account is deleted, all of its resources and data will be permanently deleted.</p>
                            @include('profile.partials.delete-user-form')
                        </div>
                    </div>

                    <div class="card w-100 border-0 bg-white shadow-xs p-0 mb-4">
                        <div class="card-body p-4">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-danger">Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif 