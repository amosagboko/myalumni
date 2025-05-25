<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            Upload your Avatar
        </h2>

        
    </header><br>

    <div class="flex items-center space-x-4">
        <form method="POST" action="{{ route('profile.avatar') }}" enctype="multipart/form-data" class="mt-4">
            @csrf
            <div class="form-group mb-3">
                <label class="mont-font fw-600 font-xsss">Profile Photo</label>
                <div class="d-flex align-items-center gap-4">
                    <div>
                        <input type="file" name="avatar" class="form-control" required>
                        <button type="submit" class="btn btn-primary btn-sm mt-3">
                            {{ __('Save') }}
                        </button>
                    </div>
                    
                    @if(auth()->user()->avatar)
                    <div>
                        <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="" 
                            class="rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
                    </div>
                    @endif
                </div>
            </div>
        </form>
    </div>
</section>
